<?php

namespace App\Observers;

use App\Models\TiktokLsaData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;

class TiktokLsaDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'tiktok_lsa') {
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
                    $existingData = TiktokLsaData::where('data_date', $dataItem['Date'])
                        ->where('ad_group_id', $dataItem['Ad group ID'])
                        ->where('brand_id', $rawData->brand_id)
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    TiktokLsaData::create([
                        'data_date' => $dataItem['Date'],
                        'ad_group_name' => $dataItem['Ad Group Name'],
                        'ad_group_id' => $dataItem['Ad group ID'],
                        'cost' => $dataItem['Cost'],
                        'live_views' => $dataItem['LIVE Views'],
                        'live_unique_views' => $dataItem['LIVE Unique Views'],
                        'effective_live_views' => $dataItem['Effective LIVE Views'],
                        'purchases' => $dataItem['Purchases (Shop)'],
                        'gross_revenue' => $dataItem['Gross revenue (Shop)'],
                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                        'brand_id' => $rawData->brand_id,
                        'raw_data_id' => $rawData->id,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'ad_group_name' => $dataItem['Ad Group Name'],
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

            
            if ($successCount === 0) {
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
