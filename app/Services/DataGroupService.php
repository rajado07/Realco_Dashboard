<?php

namespace App\Services;

use App\Models\DataGroup;
use Illuminate\Support\Collection;

class DataGroupService
{
    public function getDataGroups(string $type, int $marketPlaceId, int $brandId = null): Collection
    {
        // Validasi parameter
        if (empty($type)) {
            throw new \InvalidArgumentException("Parameter 'type' tidak boleh kosong.");
        }

        if ($marketPlaceId <= 0) {
            throw new \InvalidArgumentException("Parameter 'market_place_id' harus lebih besar dari 0.");
        }

        // Mengambil grup berdasarkan parameter yang diberikan
        $dataGroups = DataGroup::where('type', $type)
            ->where('market_place_id', $marketPlaceId)
            ->when($brandId && $brandId != 0, function ($query) use ($brandId) {
                $query->where('brand_id', $brandId);
            })
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        // Meratakan hierarki grup secara internal
        return $this->flattenGroupHierarchy($dataGroups);
    }

    public function groupData(Collection $results, Collection $groupHierarchy, string $searchField): array
    {
        $grouped = [];
        $assignedIds = [];

        foreach ($results as $item) {
            foreach ($groupHierarchy as $group) {
                $keyword = strtolower($group->keyword);
                if ($keyword && strpos(strtolower($item->{$searchField}), $keyword) !== false) {
                    $grouped[$group->id][] = $item;
                    $assignedIds[] = $item->id;
                    break; // Alokasikan ke grup yang pertama cocok
                }
            }
        }

        $others = $results->whereNotIn('id', $assignedIds);

        // Tambahkan "Others" sebagai grup khusus
        $grouped['others'] = $others;

        return $grouped;
    }

    public function countDataByGroup(array $groupedCurrentData, array $groupedPreviousData, Collection $groupHierarchy, array $metricsConfig): array
    {
        // Data "Others" dari current dan previous
        $othersCurrent  = isset($groupedCurrentData['others']) ? $groupedCurrentData['others'] : collect();
        $othersPrevious = isset($groupedPreviousData['others']) ? $groupedPreviousData['others'] : collect();

        // Fungsi rekursif untuk menghitung data grup
        $computeGroupData = function ($group) use (&$computeGroupData, $groupedCurrentData, $groupedPreviousData, $metricsConfig) {
            // Data saat ini dan sebelumnya untuk grup ini
            $currentData  = isset($groupedCurrentData[$group->id]) ? collect($groupedCurrentData[$group->id]) : collect();
            $previousData = isset($groupedPreviousData[$group->id]) ? collect($groupedPreviousData[$group->id]) : collect();

            // Kumpulkan product_names
            $currentProductNames  = $currentData->pluck('product_name')->unique()->values()->all();
            $previousProductNames = $previousData->pluck('product_name')->unique()->values()->all();

            // Inisialisasi totals
            $currentTotals  = [];
            $previousTotals = [];

            // Hitung jumlah berdasarkan metricsConfig
            foreach ($metricsConfig['sum'] as $metric) {
                $currentTotals[$metric]  = $currentData->sum($metric);
                $previousTotals[$metric] = $previousData->sum($metric);
            }

            // Rekursi untuk anak-anak
            $childrenData = $group->children->map(function ($child) use ($computeGroupData) {
                return $computeGroupData($child);
            });

            // Agregasi data anak (hanya jumlah metrik)
            foreach ($childrenData as $childData) {
                foreach ($metricsConfig['sum'] as $metric) {
                    $currentTotals[$metric]  += $childData[$metric]['now'];
                    $previousTotals[$metric] += $childData[$metric]['previous'];
                }
            }

            // Hitung metrik tambahan
            foreach ($metricsConfig['additional'] as $key => $calc) {
                if ($calc['type'] === 'ratio') {
                    $baseNumerator   = $calc['base'][0];
                    $baseDenominator = $calc['base'][1];
                    $ratio = 0;
                    if ($currentTotals[$baseDenominator] > 0) {
                        $ratio = $currentTotals[$baseNumerator] / $currentTotals[$baseDenominator];
                        if (isset($calc['multiply_by_100']) && $calc['multiply_by_100']) {
                            $ratio *= 100;
                        }
                    }
                    $currentTotals[$key] = $ratio;

                    $ratio = 0;
                    if ($previousTotals[$baseDenominator] > 0) {
                        $ratio = $previousTotals[$baseNumerator] / $previousTotals[$baseDenominator];
                        if (isset($calc['multiply_by_100']) && $calc['multiply_by_100']) {
                            $ratio *= 100;
                        }
                    }
                    $previousTotals[$key] = $ratio;
                }

                // Tambahkan tipe kalkulasi lainnya jika diperlukan
            }

            // Hitung perubahan
            $changes = [];

            // Hitung perubahan untuk metrik jumlah
            foreach ($metricsConfig['sum'] as $metric) {
                $previousValue = $previousTotals[$metric] ?? 0;
                $changes[$metric] = $previousValue > 0
                    ? (($currentTotals[$metric] - $previousValue) / $previousValue) * 100
                    : ($currentTotals[$metric] > 0 ? 100 : 0);
            }

            // Hitung perubahan untuk metrik tambahan
            foreach ($metricsConfig['additional'] as $key => $calc) {
                $previousValue = $previousTotals[$key] ?? 0;
                $changes[$key] = $previousValue > 0
                    ? (($currentTotals[$key] - $previousValue) / $previousValue) * 100
                    : ($currentTotals[$key] > 0 ? 100 : 0);
            }

            // Format metrik spesifik jika diperlukan
            if (isset($metricsConfig['format'])) {
                foreach ($metricsConfig['format'] as $metric => $format) {
                    if ($format === 'percentage') {
                        if (isset($currentTotals[$metric])) {
                            $currentTotals[$metric]  = number_format($currentTotals[$metric], 2) . '%';
                        }
                        if (isset($previousTotals[$metric])) {
                            $previousTotals[$metric] = number_format($previousTotals[$metric], 2) . '%';
                        }
                        if (isset($changes[$metric])) {
                            $changes[$metric]        = number_format($changes[$metric], 2) . '%';
                        }
                    }
                }
            }

            // Membangun array return secara dinamis
            $result = [
                'data_group_id' => $group->id,
                'data_group_brand_id' => $group->brand_id,
                'data_group_name' => $group->name,
                'children'        => $childrenData,
                'product_names_now'      => $currentProductNames,
                'product_names_previous' => $previousProductNames,
            ];

            // Iterasi metrik jumlah
            foreach ($metricsConfig['sum'] as $metric) {
                $result[$metric] = [
                    'now'      => $currentTotals[$metric],
                    'previous' => $previousTotals[$metric],
                    'change'   => $changes[$metric],
                ];
            }

            // Iterasi metrik tambahan
            foreach ($metricsConfig['additional'] as $key => $calc) {
                $result[$key] = [
                    'now'      => $currentTotals[$key],
                    'previous' => $previousTotals[$key],
                    'change'   => $changes[$key],
                ];
            }

            return $result;
        };

        // Memproses semua grup induk
        $output = $groupHierarchy
            ->filter(fn($group) => is_null($group->parent_id))
            ->map(function ($parentGroup) use ($computeGroupData) {
                return $computeGroupData($parentGroup);
            })
            ->values();

        // Memproses "Others"
        if ($othersCurrent->isNotEmpty() || $othersPrevious->isNotEmpty()) {
            // Inisialisasi totals
            $othersCurrentTotals  = [];
            $othersPreviousTotals = [];

            // Hitung jumlah untuk "Others"
            foreach ($metricsConfig['sum'] as $metric) {
                $othersCurrentTotals[$metric]  = $othersCurrent->sum($metric);
                $othersPreviousTotals[$metric] = $othersPrevious->sum($metric);
            }

            // Hitung metrik tambahan untuk "Others"
            foreach ($metricsConfig['additional'] as $key => $calc) {
                if ($calc['type'] === 'ratio') {
                    $baseNumerator   = $calc['base'][0];
                    $baseDenominator = $calc['base'][1];
                    $ratio = 0;
                    if ($othersCurrentTotals[$baseDenominator] > 0) {
                        $ratio = $othersCurrentTotals[$baseNumerator] / $othersCurrentTotals[$baseDenominator];
                        if (isset($calc['multiply_by_100']) && $calc['multiply_by_100']) {
                            $ratio *= 100;
                        }
                    }
                    $othersCurrentTotals[$key] = $ratio;

                    $ratio = 0;
                    if ($othersPreviousTotals[$baseDenominator] > 0) {
                        $ratio = $othersPreviousTotals[$baseNumerator] / $othersPreviousTotals[$baseDenominator];
                        if (isset($calc['multiply_by_100']) && $calc['multiply_by_100']) {
                            $ratio *= 100;
                        }
                    }
                    $othersPreviousTotals[$key] = $ratio;
                }

                // Tambahkan tipe kalkulasi lainnya jika diperlukan
            }

            // Hitung perubahan untuk "Others"
            $othersChanges = [];

            // Hitung perubahan untuk metrik jumlah
            foreach ($metricsConfig['sum'] as $metric) {
                $previousValue = $othersPreviousTotals[$metric] ?? 0;
                $othersChanges[$metric] = $previousValue > 0
                    ? (($othersCurrentTotals[$metric] - $previousValue) / $previousValue) * 100
                    : ($othersCurrentTotals[$metric] > 0 ? 100 : 0);
            }

            // Hitung perubahan untuk metrik tambahan
            foreach ($metricsConfig['additional'] as $key => $calc) {
                $previousValue = $othersPreviousTotals[$key] ?? 0;
                $othersChanges[$key] = $previousValue > 0
                    ? (($othersCurrentTotals[$key] - $previousValue) / $previousValue) * 100
                    : ($othersCurrentTotals[$key] > 0 ? 100 : 0);
            }

            // Format metrik spesifik jika diperlukan
            if (isset($metricsConfig['format'])) {
                foreach ($metricsConfig['format'] as $metric => $format) {
                    if ($format === 'percentage') {
                        if (isset($othersCurrentTotals[$metric])) {
                            $othersCurrentTotals[$metric]  = number_format($othersCurrentTotals[$metric], 2) . '%';
                        }
                        if (isset($othersPreviousTotals[$metric])) {
                            $othersPreviousTotals[$metric] = number_format($othersPreviousTotals[$metric], 2) . '%';
                        }
                        if (isset($othersChanges[$metric])) {
                            $othersChanges[$metric]        = number_format($othersChanges[$metric], 2) . '%';
                        }
                    }
                }
            }

            // Kumpulkan product_names untuk "Others"
            $othersCurrentProductNames  = $othersCurrent->pluck('product_name')->unique()->values()->all();
            $othersPreviousProductNames = $othersPrevious->pluck('product_name')->unique()->values()->all();

            // Membangun hasil "Others" secara dinamis
            $othersResult = [
                'data_group_name' => 'Others',
                'children'        => [],
                'product_names_now'      => $othersCurrentProductNames,
                'product_names_previous' => $othersPreviousProductNames,
            ];

            // Iterasi metrik jumlah
            foreach ($metricsConfig['sum'] as $metric) {
                $othersResult[$metric] = [
                    'now'      => $othersCurrentTotals[$metric],
                    'previous' => $othersPreviousTotals[$metric],
                    'change'   => $othersChanges[$metric],
                ];
            }

            // Iterasi metrik tambahan
            foreach ($metricsConfig['additional'] as $key => $calc) {
                $othersResult[$key] = [
                    'now'      => $othersCurrentTotals[$key],
                    'previous' => $othersPreviousTotals[$key],
                    'change'   => $othersChanges[$key],
                ];
            }

            // Append "Others" ke output
            $output->push($othersResult);
        }

        return $output->toArray();
    }

    public function showDataByGroup(array $groupedData, Collection $groupHierarchy, array $metricsConfig): array
    {
        $fields = $metricsConfig['fields'] ?? ['product_name'];

        // Fungsi rekursif untuk mengumpulkan data
        $collectData = function ($group) use (&$collectData, $groupedData, $fields) {
            // Data untuk grup ini
            $groupData  = isset($groupedData[$group->id]) ? collect($groupedData[$group->id]) : collect();

            // Mengambil field yang ditentukan
            $groupItems = $groupData->map(function ($item) use ($fields) {
                return $item->only($fields);
            })->values()->all();

            // Rekursi untuk anak-anak
            $childrenData = $group->children->map(function ($child) use ($collectData) {
                return $collectData($child);
            })->toArray();

            // Membangun array return
            return [
                'data_group_id' => $group->id,
                'data_group_brand_id' => $group->brand_id,
                'data_group_name' => $group->name,
                'children' => $childrenData,
                'items' => $groupItems,
            ];
        };

        // Memproses semua grup induk
        $output = $groupHierarchy
            ->filter(fn($group) => is_null($group->parent_id))
            ->map(function ($parentGroup) use ($collectData) {
                return $collectData($parentGroup);
            })
            ->values()
            ->all();

        // Memproses "Others"
        if (isset($groupedData['others'])) {
            $othersData = $groupedData['others'];

            // Mengambil field yang ditentukan
            $othersItems = $othersData->map(function ($item) use ($fields) {
                return $item->only($fields);
            })->values()->all();

            // Membangun hasil "Others"
            $othersResult = [
                'data_group_name' => 'Others',
                'children' => [],
                'items' => $othersItems,
            ];

            // Menambahkan "Others" ke output
            $output[] = $othersResult;
        }

        return $output;
    }
    
    // PRIVATE FUNCTION

    private function flattenGroupHierarchy(Collection $groups): Collection
    {
        $flattened = collect();

        foreach ($groups as $group) {
            $flattened->push($group);
            foreach ($group->children as $child) {
                $flattened->push($child);
            }
        }

        return $flattened;
    }
}
