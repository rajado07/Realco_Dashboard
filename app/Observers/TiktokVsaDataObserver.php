<?php

namespace App\Observers;

use App\Models\TiktokVsaData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;

class TiktokVsaDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'tiktok_vsa') {
            $jsonData = json_decode($rawData->data, true);

            $totalEntries = count($jsonData);
            $successCount = 0;
            $updateCount = 0;
            $skipCount = 0;
            $errorDetails = [];
            $failedDetails = [];

            foreach ($jsonData as $dataItem) {
                try {
                    // Check for existing data
                    $existingData = TiktokVsaData::where('data_date', $dataItem['By Day'])
                        ->where('ad_group_id', $dataItem['Ad group ID'])
                        ->where('ad_id', $dataItem['Ad ID'])
                        ->where('brand_id', $rawData->brand_id)
                        ->first();

                    if ($existingData) {
                        // Update the existing record if data is dirty (excluding retrieved_at and file_name)
                        $existingData->fill([
                            'ad_group_name' => $dataItem['Ad group name'],
                            'ad_name' => $dataItem['Ad name'],
                            'cost' => $dataItem['Cost'],
                            'average_watch_time_per_video_view' => $dataItem['Average play time per video view'],
                            'adds_to_cart' => $dataItem['Adds to cart (Shop)'],
                            'purchases' => $dataItem['Purchases (Shop)'],
                            'gross_revenue' => $dataItem['Gross revenue (Shop)'],
                            'checkouts_initiated' => $dataItem['Checkouts initiated (Shop)'],
                            'product_page_views' => $dataItem['Product page views (Shop)'],
                            'impressions' => $dataItem['Impressions'],
                        ]);

                        if ($existingData->isDirty()) {
                            $existingData->retrieved_at = $rawData->retrieved_at;
                            $existingData->file_name = $rawData->file_name;
                            $existingData->raw_data_id = $rawData->id;
                            $existingData->save();
                            $updateCount++;
                        } else {
                            $skipCount++;
                        }
                    } else {
                        // Create a new record
                        TiktokVsaData::create([
                            'data_date' => $dataItem['By Day'],
                            'ad_group_name' => $dataItem['Ad group name'],
                            'ad_group_id' => $dataItem['Ad group ID'],
                            'ad_id' => $dataItem['Ad ID'],
                            'ad_name' => $dataItem['Ad name'],
                            'cost' => $dataItem['Cost'],
                            'average_watch_time_per_video_view' => $dataItem['Average play time per video view'],
                            'adds_to_cart' => $dataItem['Adds to cart (Shop)'],
                            'purchases' => $dataItem['Purchases (Shop)'],
                            'gross_revenue' => $dataItem['Gross revenue (Shop)'],
                            'checkouts_initiated' => $dataItem['Checkouts initiated (Shop)'],
                            'product_page_views' => $dataItem['Product page views (Shop)'],
                            'impressions' => $dataItem['Impressions'],
                            'retrieved_at' => $rawData->retrieved_at,
                            'file_name' => $rawData->file_name,
                            'brand_id' => $rawData->brand_id,
                            'raw_data_id' => $rawData->id,
                        ]);
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'ad_id' => $dataItem['Ad ID'],
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $status = 2; // Assume success by default
            $messageDetails = [
                'total_entries' => $totalEntries,
                'successful' => $successCount,
                'updated' => $updateCount,
                'skipped' => $skipCount,
                'failed' => $totalEntries - $successCount - $updateCount - $skipCount,
                'failed_details' => $failedDetails,
                'errors' => $errorDetails,
            ];

            if ($successCount === 0 && $updateCount === 0 && $skipCount === $totalEntries) {
                $status = 6; // All skipped
            } elseif ($successCount === 0 && $updateCount === 0 && count($failedDetails) === $totalEntries) {
                $status = 5; // All failed
            } elseif ($successCount + $updateCount === $totalEntries) {
                $status = 2; // All successful
            } elseif (($successCount > 0 || $updateCount > 0) && count($failedDetails) > 0) {  
                $status = 4; // Partial error
            } else {
                $status = 2; // Default to all successful if no partial errors
            }

            // Log summary of the process
            Log::info("RawData ID $rawData->id, processing result: Total entries: $totalEntries, Successful: $successCount, Updated: $updateCount, Skipped: $skipCount, Failed: " . ($totalEntries - $successCount - $updateCount - $skipCount));

            // Update the status and message in RawData
            $rawData->update([
                'status' => $status,
                'message' => json_encode($messageDetails),
            ]);
        }
    }
}
