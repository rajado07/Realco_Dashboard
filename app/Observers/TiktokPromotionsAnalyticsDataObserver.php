<?php

namespace App\Observers;

use App\Models\TiktokPromotionAnalyticsData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;

class TiktokPromotionsAnalyticsDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'tiktok_promotion_analytics') {
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
                    $existingData = TiktokPromotionAnalyticsData::where('promotion_id', $dataItem['ID'])
                        ->where('data_date', $rawData->data_date)
                        ->where('brand_id', $rawData->brand_id)
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    $percentageFieldsToReplace = [
                        'ctor' => 'CTOR',
                        'avg_discount_rate' => 'Avg. discount rate',
                        'roi' => 'ROI',
                    ];

                    $cleanedPercentageData = [];
                    foreach ($percentageFieldsToReplace as $key => $field) {
                        $cleanedPercentageData[$key] = str_replace('%', '', $dataItem[$field]);
                    }

                    TiktokPromotionAnalyticsData::create([
                        'data_date' =>  $rawData->data_date,
                        'promotion_id' => $dataItem['ID'],
                        'promotion_name' => $dataItem['Promotion name'],
                        'promotion_period' => $dataItem['Promotion period'],
                        'status' => $dataItem['Status'],
                        'type' => $dataItem['Type'],
                        'ctor' => $cleanedPercentageData['ctor'],
                        'gmv' => $dataItem['GMV (Rp)'],
                        'orders' => $dataItem['Orders'],
                        'customers' => $dataItem['Customers'], # perlu dirubah jadi customer
                        'products_sold' => $dataItem['Products sold'],
                        'new_customers' => $dataItem['New Customers'], # perlu dirubah menjadi new customer
                        'avg_gmv_per_buyer' => $dataItem['Avg. GMV per customer (Rp)'],
                        'discount_amount' => $dataItem['Discount amount (Rp)'],
                        'avg_discount_rate' => $cleanedPercentageData['avg_discount_rate'],
                        'roi' => $cleanedPercentageData['roi'],
                        
                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                        'brand_id' => $rawData->brand_id,
                        'raw_data_id' => $rawData->id,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'promotion_id' => $dataItem['ID'],
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
