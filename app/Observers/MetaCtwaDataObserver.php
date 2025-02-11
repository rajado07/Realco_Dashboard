<?php

namespace App\Observers;

use App\Models\MetaCtwaData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;

class MetaCtwaDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'meta_ctwa') {
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
                    $existingData = MetaCtwaData::where('data_date', $dataItem['Day'])
                        ->where('ad_set_id', $dataItem['Ad set ID'])
                        ->where('ad_set_name', $dataItem['Ad set name'])
                        ->where('ad_name', $dataItem['Ad name'])
                        ->where('brand_id', $rawData->brand_id)
                        ->where('market_place_id', $rawData->market_place_id)
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    MetaCtwaData::create([
                        'data_date' => $dataItem['Day'],
                        'ad_set_name' => $dataItem['Ad set name'],
                        'ad_set_id' => $dataItem['Ad set ID'],
                        'ad_name' => $dataItem['Ad name'],
                        'amount_spent' => $dataItem['Amount spent (IDR)'],
                        'messaging_conversations_started' => $dataItem['Messaging conversations started'],
                        'cost_per_messaging_conversation_started' => $dataItem['Cost per messaging conversation started'],
                        'purchases' => $dataItem['Purchases conversion value'],
                        'purchases_conversion_value' => $dataItem['Amount spent (IDR)'],
                        
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
