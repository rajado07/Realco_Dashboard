<?php

namespace App\Observers;

use App\Models\BrandTargetData;
use App\Models\ImportData;
use Illuminate\Support\Facades\Log;

class BrandTargetDataObserver
{
    public function created(ImportData $importData)
    {
        if ($importData->type === 'brand_target') {
            $jsonData = json_decode($importData->data, true);

            $totalEntries = count($jsonData);
            $successCount = 0;
            $skipCount = 0;
            $errorDetails = [];
            $skippedDetails = [];
            $failedDetails = [];

            foreach ($jsonData as $dataItem) {
                try {
                    // Check for existing data
                    $existingData = BrandTargetData::where('data_date', $dataItem['data_date'])
                        ->where('sub_brand_name', $dataItem['sub_brand_name'])
                        ->where('brand_id', $dataItem['brand_id'])
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    BrandTargetData::create([
                        'data_date' => $dataItem['data_date'],
                        'sub_brand_name' => $dataItem['sub_brand_name'],
                        'target_nmv' => str_replace(',', '', $dataItem['target_nmv']),
                        'target_ads_to_nmv' => str_replace(',', '', $dataItem['target_ads_to_nmv']),
                        'composition_cpas' => str_replace(',', '', $dataItem['composition_cpas']),
                        'composition_iklanku' => str_replace(',', '', $dataItem['composition_iklanku']),
                        'brand_id' => $dataItem['brand_id'],
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'data_date' => $dataItem['data_date'],
                        'sub_brand_name' => $dataItem['sub_brand_name'],
                        'brand_id' => $dataItem['brand_id'],
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $status = 2; // Assume success by default
            $messageDetails = [
                'total_entries' => $totalEntries,
                'successful' => $successCount,
                'skipped' => $skipCount,
                'failed' => $totalEntries - $successCount - $skipCount,
                'skipped_details' => $skippedDetails,
                'failed_details' => $failedDetails,
                'errors' => $errorDetails,
            ];

            if ($successCount === 0 && $skipCount === $totalEntries) {
                $status = 6; // All skipped
            } elseif ($successCount === 0 && count($failedDetails) === $totalEntries) {
                $status = 5; // All failed
            } elseif ($successCount === $totalEntries) {
                $status = 2; // All successful
            } elseif ($successCount > 0 && count($failedDetails) > 0) {  
                $status = 4; // Partial error
            } elseif ($successCount > 0 && $skipCount > 0) {
                $status = 3; // Partial success
            } 

            // Log summary of the process
            Log::info("Import Data ID $importData->id, processing result: Total entries: $totalEntries, Successful: $successCount, Skipped: $skipCount, Failed: " . ($totalEntries - $successCount - $skipCount));

            // Update the status and message in RawData
            $importData->update([
                'status' => $status,
                'message' => json_encode($messageDetails),
            ]);
        }
    }
}
