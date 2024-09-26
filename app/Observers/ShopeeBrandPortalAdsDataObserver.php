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
            $skipCount = 0;
            $errorDetails = [];
            $skippedDetails = [];
            $failedDetails = [];

            foreach ($jsonData as $dataItem) {
                // Convert the date format from dd/MM/yyyy to yyyy-MM-dd
                $dataDate = \DateTime::createFromFormat('d/m/Y', $dataItem['Date']);
                if (!$dataDate) {
                    $errorDetails[] = [
                        'data_item' => $dataItem,
                        'error' => 'Invalid date format for Date: ' . $dataItem['Date'],
                    ];
                    $failedDetails[] = $dataItem;
                    continue;
                }
                $formattedDate = $dataDate->format('Y-m-d');

                // Skip data if shop_id and data_date already exist
                $existingData = ShopeeBrandPortalAdsData::where('shop_id', $dataItem['Shop ID'])
                    ->where('data_date', $formattedDate)
                    ->exists();
                if ($existingData) {
                    $skipCount++;
                    $skippedDetails[] = $dataItem;
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
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'data_item' => $dataItem,
                        'error' => 'Failed to insert data for shop ID: ' . $dataItem['Shop ID'] . ' on date: ' . $dataItem['Date'] . ' with error: ' . $e->getMessage(),
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
