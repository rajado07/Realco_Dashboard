<?php

namespace App\Observers;

use App\Models\TiktokProductAnalyticsData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;

class TiktokProductAnalyticsDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'tiktok_product_analytics') {
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
                    $existingData = TiktokProductAnalyticsData::where('product_id', $dataItem['ID'])
                        ->where('data_date', $rawData->data_date)
                        ->where('brand_id', $rawData->brand_id)
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    // Percentage data that need to be cleaned
                    $percentageFieldsToReplace = [
                        'shop_tab_clickthrough_rate' => 'Shop tab clickthrough rate',
                        'shop_tab_conversion_rate' => 'Shop tab conversion rate',
                        'live_clickthrough_rate' => 'LIVE click-through rate',
                        'live_conversion_rate' => 'LIVE conversion rate',
                        'video_clickthrough_rate' => 'Video click-through rate',
                        'video_conversion_rate' => 'Video conversion rate',
                        'product_card_clickthrough_rate' => 'Product card click-through rate',
                        'product_card_conversion_rate' => 'Product card conversion rate',
                    ];

                    $cleanedPercentageData = [];
                    foreach ($percentageFieldsToReplace as $key => $field) {
                        $cleanedPercentageData[$key] = str_replace('%', '', $dataItem[$field]);
                    }

                    // Currency data that need to be cleaned
                    $currencyFieldsToReplace = [
                        'gmv' => $dataItem['GMV'],
                        'shop_tab_gmv' => $dataItem['Shop tab GMV'],
                        'live_gmv' => $dataItem['LIVE GMV'],
                        'video_gmv' => $dataItem['Video GMV'],
                        'product_card_gmv' => $dataItem['Product card GMV'],
                    ];

                    $cleanedCurrencyData = [];
                    foreach ($currencyFieldsToReplace as $key => $value) {
                        // Remove 'Rp' and '.' to convert to integer
                        $cleanedCurrencyData[$key] = (int) str_replace(['Rp', '.'], '', $value);
                    }

                    TiktokProductAnalyticsData::create([

                        'data_date' => $rawData->data_date,
                        'product_id' => $dataItem['ID'],
                        'product_name' => $dataItem['Product'],
                        'status' => $dataItem['Status'],

                        // Shop Tab
                        'gmv' => $cleanedCurrencyData['gmv'],
                        'units_sold' => $dataItem['Units sold'],
                        'orders' => $dataItem['Orders'],
                        'shop_tab_gmv' => $cleanedCurrencyData['shop_tab_gmv'],
                        'shop_tab_units_sold' => $dataItem['Shop Tab units sold'],
                        'shop_tab_listing_impressions' => $dataItem['Shop tab listing impressions'],
                        'shop_tab_page_views' => $dataItem['Shop tab page views'],
                        'shop_tab_unique_page_views' => $dataItem['Shop tab unique page views'],
                        'shop_tab_unique_product_buyers' => $dataItem['Shop tab unique product buyers'],
                        'shop_tab_clickthrough_rate' => $cleanedPercentageData['shop_tab_clickthrough_rate'],
                        'shop_tab_conversion_rate' => $cleanedPercentageData['shop_tab_conversion_rate'],

                        // LIVE
                        'live_gmv' => $cleanedCurrencyData['live_gmv'],
                        'live_units_sold' => $dataItem['LIVE units sold'],
                        'live_impressions' => $dataItem['LIVE impressions'],
                        'page_views_from_live' => $dataItem['Page views from LIVE'],
                        'unique_page_views_from_live' => $dataItem['Unique page views from LIVE'],
                        'live_unique_product_buyers' => $dataItem['LIVE unique product buyers'],
                        'live_clickthrough_rate' => $cleanedPercentageData['live_clickthrough_rate'],
                        'live_conversion_rate' => $cleanedPercentageData['live_conversion_rate'],

                        // Video
                        'video_gmv' => $cleanedCurrencyData['video_gmv'],
                        'video_units_sold' => $dataItem['Video units sold'],
                        'video_impressions' => $dataItem['Video impressions'],
                        'page_views_from_video' => $dataItem['Page views from video'],
                        'unique_page_views_from_video' => $dataItem['Unique page views from video'],
                        'video_unique_product_buyers' => $dataItem['Video unique product buyers'],
                        'video_clickthrough_rate' => $cleanedPercentageData['video_clickthrough_rate'],
                        'video_conversion_rate' => $cleanedPercentageData['video_conversion_rate'],

                        // Product Card
                        'product_card_gmv' => $cleanedCurrencyData['product_card_gmv'],
                        'product_card_units_sold' => $dataItem['Product  card units sold'],
                        'product_card_impressions' => $dataItem['Product card impressions'],
                        'page_views_from_product_card' => $dataItem['Page views from product card'],
                        'unique_page_views_from_product_card' => $dataItem['Unique page views from product card'],
                        'product_card_unique_buyers' => $dataItem['Product card unique buyers'],
                        'product_card_clickthrough_rate' => $cleanedPercentageData['product_card_clickthrough_rate'],
                        'product_card_conversion_rate' => $cleanedPercentageData['product_card_conversion_rate'],

                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                        'brand_id' => $rawData->brand_id,
                        'raw_data_id' => $rawData->id,

                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'product_id' => $dataItem['ID'],
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
