<?php

namespace App\Observers;

use App\Models\RawData;
use App\Models\ShopeeBrandPortalAdsData;
use Illuminate\Support\Facades\Log;

class ShopeeBrandPortalAdsDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'shopee_brand_portal_ads') {
            $jsonData = json_decode($rawData->data, true);

            $totalEntries = count($jsonData);
            $successCount = 0;
            $skippedAllCount = 0;
            $skippedExistingCount = 0;
            $errorDetails = [];

            foreach ($jsonData as $dataItem) {
                // Skip data where Shop Name is "All"
                if ($dataItem['Shop Name'] === 'All') {
                    $skippedAllCount++;
                    continue;
                }

                // Convert the date format from dd/MM/yyyy to yyyy-MM-dd
                $dataDate = \DateTime::createFromFormat('d/m/Y', $dataItem['Date']);
                if (!$dataDate) {
                    $errorDetails[] = 'Invalid date format for Date: ' . $dataItem['Date'];
                    continue;
                }
                $formattedDate = $dataDate->format('Y-m-d');

                // Skip data if shop_id and data_date already exist
                $existingData = ShopeeBrandPortalAdsData::where('shop_id', $dataItem['Shop ID'])
                    ->where('data_date', $formattedDate)
                    ->first();
                if ($existingData) {
                    $skippedExistingCount++;
                    continue;
                }

                try {
                    ShopeeBrandPortalAdsData::create([
                        'shop_name' => $dataItem['Shop Name'],
                        'shop_id' => $dataItem['Shop ID'],
                        'impressions' => $dataItem['Impressions'],
                        'orders' => $dataItem['Orders'],
                        'gross_sales' => $dataItem['Gross Sales(Local currency)'],
                        'ads_spend' => $dataItem['Ads Spend(Local currency)'],
                        'units_sold' => $dataItem['Units Sold'],
                        'data_date' => $formattedDate,
                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                        'brand_id' => $rawData->brand_id,
                        'raw_data_id' => $rawData->id
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorDetails[] = 'Failed to insert data for shop ID: ' . $dataItem['Shop ID'] . ' on date: ' . $dataItem['Date'] . ' with error: ' . $e->getMessage();
                }
            }

            $status = 2; // Assume success by default
            $message = "Processed $totalEntries entries: $successCount successful, $skippedAllCount skipped (Shop Name 'All'), $skippedExistingCount skipped (Existing data), " . ($totalEntries - $successCount - $skippedAllCount - $skippedExistingCount) . " failed.";

            if ($successCount === 0) {
                $status = 4; // All failed
                $message = "All entries failed to process. Processed $totalEntries entries: $successCount successful, $skippedAllCount skipped (Shop Name 'All'), $skippedExistingCount skipped (Existing data), " . ($totalEntries - $successCount - $skippedAllCount - $skippedExistingCount) . " failed.";
            } elseif ($successCount < $totalEntries - $skippedAllCount - $skippedExistingCount) {
                $status = 3; // Partial success
                $message = "Partial success in processing entries. Processed $totalEntries entries: $successCount successful, $skippedAllCount skipped (Shop Name 'All'), $skippedExistingCount skipped (Existing data), " . ($totalEntries - $successCount - $skippedAllCount - $skippedExistingCount) . " failed.";
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
