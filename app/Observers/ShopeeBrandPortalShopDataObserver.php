<?php

namespace App\Observers;

use App\Models\RawData;
use App\Models\ShopeeBrandPortalShopData;
use Illuminate\Support\Facades\Log;

class ShopeeBrandPortalShopDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'shopee_brand_portal_shop') {
            $jsonData = json_decode($rawData->data, true);

            $totalEntries = count($jsonData);
            $successCount = 0;
            $errorDetails = [];

            foreach ($jsonData as $dataItem) {
                try {
                    ShopeeBrandPortalShopData::create([
                        'product_name' => $dataItem['Product Name'],
                        'product_id' => $dataItem['Product ID'],
                        'gross_sales' => $dataItem['Gross Sales(Rp)'],
                        'gross_orders' => $dataItem['Gross Orders'],
                        'gross_units_sold' => $dataItem['Gross Units Sold'],
                        'product_views' => $dataItem['Product Views'],
                        'product_visitors' => $dataItem['Product Visitors'],
                        'retrieved_at' => $rawData->retrieved_at,
                        'data_date' => $rawData->data_date,
                        'file_name' => $rawData->file_name,
                        'brand_id' => $rawData->brand_id,
                        'raw_data_id' => $rawData->id
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorDetails[] = 'Failed to insert data for product ID: ' . $dataItem['Product ID'] . ' with error: ' . $e->getMessage();
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
