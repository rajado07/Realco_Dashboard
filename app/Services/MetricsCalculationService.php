<?php

namespace App\Services;

use Illuminate\Support\Collection;

class MetricsCalculationService
{
    public function calculateNowPreviousChange(Collection $currentData, Collection $previousData, array $metricsConfig)
    {
        $results = [];

        // Calculate sum metrics
        if (isset($metricsConfig['sum'])) {
            foreach ($metricsConfig['sum'] as $metric) {
                $currentValue = $currentData->sum($metric);
                $previousValue = $previousData->sum($metric);
                $change = $this->calculateChange($currentValue, $previousValue);

                $results[$metric] = [
                    'now' => $currentValue,
                    'previous' => $previousValue,
                    'change' => $change,
                ];
            }
        }

        // Calculate additional metrics (ratios)
        if (isset($metricsConfig['additional'])) {
            foreach ($metricsConfig['additional'] as $metric => $config) {
                if ($config['type'] === 'ratio' && count($config['base']) === 2) {
                    $numerator = $currentData->sum($config['base'][0]);
                    $denominator = $currentData->sum($config['base'][1]);
                    $currentValue = $denominator ? $numerator / $denominator : 0;

                    $previousNumerator = $previousData->sum($config['base'][0]);
                    $previousDenominator = $previousData->sum($config['base'][1]);
                    $previousValue = $previousDenominator ? $previousNumerator / $previousDenominator : 0;

                    if ($config['multiply_by_100']) {
                        $currentValue *= 100;
                        $previousValue *= 100;
                    }

                    $change = $this->calculateChange($currentValue, $previousValue);

                    $results[$metric] = [
                        'now' => $currentValue,
                        'previous' => $previousValue,
                        'change' => $change,
                    ];
                }
            }
        }

        // Format metrics (e.g., percentage)
        if (isset($metricsConfig['format'])) {
            foreach ($metricsConfig['format'] as $metric => $format) {
                if (isset($results[$metric]) && $format === 'percentage') {
                    $results[$metric]['now'] = number_format($results[$metric]['now'], 2) . '%';
                    $results[$metric]['previous'] = number_format($results[$metric]['previous'], 2) . '%';
                    $results[$metric]['change'] = number_format($results[$metric]['change'], 2) . '%';
                }
            }
        }

        return $results;
    }

    private function calculateChange($current, $previous)
    {
        return $previous ? (($current - $previous) / $previous) * 100 : 0;
    }
}
