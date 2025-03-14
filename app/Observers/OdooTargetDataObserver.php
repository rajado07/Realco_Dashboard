<?php

namespace App\Observers;

use App\Models\OdooTargetData;
use App\Models\ImportData;
use Illuminate\Support\Facades\Log;

class OdooTargetDataObserver
{
    public function created(ImportData $importData)
    {
        if ($importData->type === 'odoo_target') {
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
                    $existingData = OdooTargetData::where('data_date', $dataItem['data_date'])
                        ->where('odoo_user', $dataItem['odoo_user'])
                        ->where('type', $dataItem['type'])
                        ->where('brand_id', $dataItem['brand_id'])
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    // Bersihkan koma dari target sebelum menyimpan
                    $cleanedTarget = str_replace(',', '', $dataItem['target']);

                    OdooTargetData::create([
                        'data_date' => $dataItem['data_date'],
                        'odoo_user' => $dataItem['odoo_user'],
                        'target' => $cleanedTarget, 
                        'type' => $dataItem['type'],
                        'brand_id' => $dataItem['brand_id'],
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'data_date' => $dataItem['data_date'],
                        'odoo_user' => $dataItem['odoo_user'],
                        'target' => $dataItem['target'],
                        'type' => $dataItem['type'],
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
