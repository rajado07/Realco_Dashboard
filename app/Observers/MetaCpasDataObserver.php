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
            $errorDetails = [];

            foreach ($jsonData as $dataItem) {
                try {
                    MetaCpasData::create([
                        'data_date' => $dataItem['Day'],
                        'ad_set_name' => $dataItem['Ad set name'],
                        'amount_spent' => $dataItem['Amount spent (IDR)'],
                        'content_views_with_shared_items' => $dataItem['Content views with shared items'],
                        'adds_to_cart_with_shared_items' => $dataItem['Adds to cart with shared items'],
                        'purchases_with_shared_items' => $dataItem['Purchases with shared items'],
                        'purchases_conversion_value_for_shared_items_only' => $dataItem['Purchases conversion value for shared items only'],
                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                        'brand_id' => $rawData->brand_id,
                        'market_place_id' => $rawData->market_place_id,
                        'raw_data_id' => $rawData->id
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorDetails[] = 'Failed to insert data for Ad Set Name: ' . $dataItem['Ad set name'] . ' with error: ' . $e->getMessage();
                }
            }

            $status = 2; // Assume success by default
            $message = "All entries processed successfully. Processed $totalEntries entries: $successCount successful, " . ($totalEntries - $successCount) . " failed.";

            if ($successCount === 0) {
                $status = 4; // All failed
                $message = "All entries failed to process. Processed $totalEntries entries: $successCount successful, " . ($totalEntries - $successCount) . " failed.";
            } elseif ($successCount < $totalEntries) {
                $status = 3; // Partial success
                $message = "Partial success in processing entries. Processed $totalEntries entries: $successCount successful, " . ($totalEntries - $successCount) . " failed.";
            }

            // Log summary of the process
            Log::info("RawData ID $rawData->id, $message");

            // Append error details to the message and log each error
            if (!empty($errorDetails)) {
                foreach ($errorDetails as $error) {
                    Log::error($error);
                }
                $message .= ' Errors: ' . implode('; ', $errorDetails);
            }

            // Update the status and message in RawData
            $rawData->update([
                'status' => $status,
                'message' => $message,
            ]);
        }
    }

    
}
