<?php

namespace App\Observers;

use App\Models\MetaCpasData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;

class MetaCpasDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'meta_cpas') {
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
                    $existingData = MetaCpasData::where('data_date', $dataItem['Day'])
                        ->where('ad_set_id', $dataItem['Ad set ID'])
                        ->where('brand_id', $rawData->brand_id)
                        ->where('market_place_id', $rawData->market_place_id)
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    MetaCpasData::create([
                        'data_date' => $dataItem['Day'],
                        'ad_set_name' => $dataItem['Ad set name'],
                        'ad_set_id' => $dataItem['Ad set ID'],
                        'ad_name' => $dataItem['Ad name'],
                        'amount_spent' => $dataItem['Amount spent (IDR)'],
                        'content_views_with_shared_items' => $dataItem['Content views with shared items'],
                        'adds_to_cart_with_shared_items' => $dataItem['Adds to cart with shared items'],
                        'purchases_with_shared_items' => $dataItem['Purchases with shared items'],
                        'purchases_conversion_value_for_shared_items_only' => $dataItem['Purchases conversion value for shared items only'],
                        'impressions' => $dataItem['Impressions'],
                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                        'brand_id' => $rawData->brand_id,
                        'market_place_id' => $rawData->market_place_id,
                        'raw_data_id' => $rawData->id
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'ad_set_name' => $dataItem['Ad set name'],
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
