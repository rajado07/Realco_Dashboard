<?php

namespace App\Observers;

use App\Models\TiktokGmvMaxData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;

class TiktokGmvMaxDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'tiktok_gmv_max') {
            $jsonData = json_decode($rawData->data, true);

            $totalEntries = count($jsonData);
            $successCount = 0;
            $skipCount = 0;
            $errorDetails = [];
            $skippedDetails = [];
            $failedDetails = [];

            foreach ($jsonData as $dataItem) {
                try {
                    // Check for existing data
                    $existingData = TiktokGmvMaxData::where('data_date', $rawData->data_date)
                        ->where('campaign_id', $dataItem['Campaign ID'])
                        ->where('brand_id', $rawData->brand_id)
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }
            
                    TiktokGmvMaxData::create([
                        'data_date' => $rawData->data_date,
                        'campaign_id' => $dataItem['Campaign ID'],
                        'campaign_name' => $dataItem['Campaign name'],
                        'cost' => $dataItem['Cost'],
                        'orders' => $dataItem['Orders (SKU)'],
                        'gross_revenue' => $dataItem['Gross revenue'],
                        
                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                        'brand_id' => $rawData->brand_id,
                        'raw_data_id' => $rawData->id,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'campaign_id', $dataItem['Campaign ID'],
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
            Log::info("RawData ID $rawData->id, processing result: Total entries: $totalEntries, Successful: $successCount, Skipped: $skipCount, Failed: " . ($totalEntries - $successCount - $skipCount));

            // Update the status and message in RawData
            $rawData->update([
                'status' => $status,
                'message' => json_encode($messageDetails),
            ]);
        }
    }
}
