<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataGroup;
use App\Models\TaskGenerator;
use App\Models\ShopeeBrandPortalShopData;

class DataGroupController extends Controller
{
    public function index()
    {
        // Ambil semua parent groups beserta nested children
        $data = DataGroup::whereNull('parent_id')->with('allChildren')->get();

        // Group by type, brand_id, dan market_place_id untuk memastikan pengelompokan unik
        $groupedData = $data->groupBy(function ($group) {
            return $group->type . '|' . $group->brand_id . '|' . $group->market_place_id;
        })->map(function ($groups, $key) {
            // Pisahkan kunci untuk mendapatkan type, brand_id, dan market_place_id
            [$type, $brandId, $marketPlaceId] = explode('|', $key);

            return [
                'type' => $type,
                'brand_id' => $brandId ? (int) $brandId : null, // Pastikan brand_id tetap null jika tidak ada
                'market_place_id' => $marketPlaceId ? (int) $marketPlaceId : null, // Pastikan market_place_id tetap null jika tidak ada
                'groups' => $groups->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'keyword' => $group->allChildren->isEmpty() ? $group->keyword : null, // Tampilkan keyword jika tidak ada children
                        'children' => $group->allChildren, // Nested children
                    ];
                }),
            ];
        })->values(); // Konversi menjadi array numerik

        return response()->json($groupedData);
    }


    public function create() {}

    public function store(Request $request)
    {
        try {
            // Validasi data parent
            $validatedData = $request->validate([
                'name' => 'required|string',
                'type' => 'required|string',
                'market_place_id' => 'required|integer',
                'brand_id' => 'required|integer',
                'keyword' => 'nullable|string',
                'children' => 'nullable|array',
                'children.*.name' => 'required|string',
                'children.*.keyword' => 'nullable|string',
            ]);

            // Periksa apakah group memiliki children
            $hasChildren = !empty($validatedData['children']);

            // Simpan parent group dengan keyword diset null jika ada children
            $parent = DataGroup::create([
                'name' => $validatedData['name'],
                'type' => $validatedData['type'],
                'market_place_id' => $validatedData['market_place_id'],
                'brand_id' => $validatedData['brand_id'],
                'keyword' => $hasChildren ? null : $validatedData['keyword'],
            ]);

            // Simpan children jika ada
            if ($hasChildren) {
                foreach ($validatedData['children'] as $childData) {
                    DataGroup::create([
                        'parent_id' => $parent->id,
                        'name' => $childData['name'],
                        'keyword' => $childData['keyword'],
                        'type' => $parent->type, // Samakan dengan parent
                        'market_place_id' => $parent->market_place_id,
                        'brand_id' => $parent->brand_id,
                    ]);
                }
            }

            return response()->json(['message' => 'Group and Sub Groups Added Successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed : ' . $e->getMessage(), 'type' => 'error'], 500);
        }
    }

    public function show() {}

    public function edit(string $id)
    {
        $edit = DataGroup::with('allChildren')->findOrFail($id);

        $data = [
            'name' => $edit->name,
            'type' => $edit->type,
            'market_place_id' => $edit->market_place_id,
            'brand_id' => $edit->brand_id,
            'keyword' => $edit->keyword,
            'children' => $edit->allChildren->map(function ($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'keyword' => $child->keyword,
                    // Jika children memiliki nested children lagi
                    'children' => $child->allChildren->map(function ($nestedChild) {
                        return [
                            'id' => $nestedChild->id,
                            'name' => $nestedChild->name,
                            'keyword' => $nestedChild->keyword,
                        ];
                    }),
                ];
            }),
        ];

        return response()->json($data);
    }


    public function update(Request $request)
    {
        $id = $request->input('id');

        try {
            // Validasi data parent
            $validatedData = $request->validate([
                'name' => 'required|string',
                'type' => 'required|string',
                'market_place_id' => 'required|integer',
                'brand_id' => 'required|integer',
                'keyword' => 'nullable|string',
                'children' => 'nullable|array',
                'children.*.id' => 'nullable|integer', // ID untuk identifikasi existing child
                'children.*.name' => 'required_with:children.*.id|string',
                'children.*.keyword' => 'nullable|string',
            ]);

            // Ambil data parent
            $parent = DataGroup::findOrFail($id);

            // Periksa apakah group memiliki children
            $hasChildren = !empty($validatedData['children']);

            // Update parent dengan keyword diset ke null jika memiliki children
            $parent->update([
                'name' => $validatedData['name'],
                'type' => $validatedData['type'],
                'market_place_id' => $validatedData['market_place_id'],
                'brand_id' => $validatedData['brand_id'],
                'keyword' => $hasChildren ? null : $validatedData['keyword'],
            ]);

            // Ambil ID children yang dikirim
            $receivedChildIds = collect($validatedData['children'] ?? [])
                ->pluck('id')
                ->filter(); // Hanya ambil ID yang valid

            // Hapus children yang tidak ada di request
            DataGroup::where('parent_id', $parent->id)
                ->whereNotIn('id', $receivedChildIds)
                ->delete();

            // Proses children untuk update dan create
            foreach ($validatedData['children'] ?? [] as $childData) {
                if (!empty($childData['id'])) {
                    // Update existing child
                    $child = DataGroup::findOrFail($childData['id']);
                    $child->update([
                        'name' => $childData['name'],
                        'keyword' => $childData['keyword'],
                        'type' => $parent->type,
                        'market_place_id' => $parent->market_place_id,
                        'brand_id' => $parent->brand_id,
                    ]);
                } else {
                    // Create new child
                    DataGroup::create([
                        'parent_id' => $parent->id,
                        'name' => $childData['name'],
                        'keyword' => $childData['keyword'],
                        'type' => $parent->type,
                        'market_place_id' => $parent->market_place_id,
                        'brand_id' => $parent->brand_id,
                    ]);
                }
            }

            return response()->json(['message' => 'Group and Children Updated Successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed: ' . $e->getMessage(), 'type' => 'error'], 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            $destroy = DataGroup::findOrFail($id);
            $destroy->delete();

            return response()->json(['message' => 'Group Deleted Sucessfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed : ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function getDataGroupType()
    {
        $types = TaskGenerator::distinct()->pluck('type');
        return response()->json($types);
    }

    public function getDataGroupByType($type = null)
    {
        if (!$type) {
            return response()->json(['error' => 'Please select type'], 400);
        }

        if ($type === 'shopee_brand_portal_shop') {
            $data = ShopeeBrandPortalShopData::select(
                'product_name as name',
                'product_id as id_mapping',
                'data_group_id'
            )
                ->groupBy('product_name', 'product_id', 'data_group_id')
                ->get();
            return response()->json($data);
        }

        return response()->json([]);
    }
}
