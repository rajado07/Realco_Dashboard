<?php

namespace App\Observers;

use App\Models\TiktokLiveStreamingData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TiktokLiveStreamingDataObserver
{
    public function created(RawData $rawData)
    {
        if (in_array($rawData->type, ['tiktok_live_streaming_own', 'tiktok_live_streaming_affiliate'])) {
            $jsonData = json_decode($rawData->data, true);

            $totalEntries = count($jsonData);
            $successCount = 0;
            $skipCount = 0;
            $errorDetails = [];
            $skippedDetails = [];
            $failedDetails = [];

            foreach ($jsonData as $dataItem) {
                try {

                    $durationInMinutes = $this->convertDurationToMinutes($dataItem['Duration']);

                    $formattedLaunchedTime = $this->convertLaunchedTimeToTimestamp($dataItem['Launched Time']);

                    // Check for existing data
                    $existingData = TiktokLiveStreamingData::where('creator_id', $dataItem['Creator ID'])
                        ->where('launched_time', $formattedLaunchedTime)
                        ->where('data_date', $rawData->data_date)
                        ->where('brand_id', $rawData->brand_id)
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    $percentageFieldsToReplace = [
                        'co_rate' => 'CO rate',
                        'ctr' => 'CTR',
                    ];

                    $cleanedPercentageData = [];
                    foreach ($percentageFieldsToReplace as $key => $field) {
                        // Cek jika datanya adalah '--', ubah menjadi 0.00
                        if ($dataItem[$field] === '--') {
                            $cleanedPercentageData[$key] = '0.00';
                        } else {
                            // Hapus tanda persen dan simpan data sebagai angka
                            $cleanedPercentageData[$key] = str_replace('%', '', $dataItem[$field]);
                        }
                    }

                    // Tentukan type berdasarkan rawData->type
                    $type = $rawData->type === 'tiktok_live_streaming_affiliate' ? 'affiliate' : ($rawData->type === 'tiktok_live_streaming_own' ? 'own' : 'unknown');

                    TiktokLiveStreamingData::create([
                        'data_date' =>  $rawData->data_date,

                        'creator_id' => $dataItem['Creator ID'],
                        'creator_name' => $dataItem['Creator'],
                        'nickname' => $dataItem['Nickname'],
                        'launched_time' => $formattedLaunchedTime,
                        'duration' => $durationInMinutes,
                        'revenue' => $dataItem['Revenue (Rp)'],
                        'products' => $dataItem['Products'],
                        'different_products_sold' => $dataItem['Different Products Sold'],
                        'orders_created' => $dataItem['Orders Created'],
                        'orders_paid' => $dataItem['Orders Paid'],
                        'unit_sales' => $dataItem['Unit Sales'],
                        'buyers' => $dataItem['Buyers'],
                        'average_price' => $dataItem['Average Price (Rp)'],
                        'co_rate' =>  $cleanedPercentageData['co_rate'],
                        'live_attributed_gmv' => $dataItem['LIVE attributed GMV (Rp)'],
                        'viewers' => $dataItem['Viewers'],
                        'views' => $dataItem['Views'],
                        'avg_viewing_duration' => $dataItem['Avg. Viewing Duration'],
                        'comments' => $dataItem['Comments'],
                        'shares' => $dataItem['Shares'],
                        'likes' => $dataItem['Likes'],
                        'new_followers' => $dataItem['New Followers'],
                        'product_impressions' => $dataItem['Product Impressions'],
                        'product_clicks' => $dataItem['Product Clicks'],
                        'ctr' =>  $cleanedPercentageData['ctr'],

                        // Set the type based on rawData->type
                        'type' => $type,
                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                        'brand_id' => $rawData->brand_id,
                        'raw_data_id' => $rawData->id,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'creator_id' => $dataItem['Creator ID'],
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

    private function convertDurationToMinutes($duration)
    {
        $totalMinutes = 0;

        // Check if the duration contains hours (e.g., "1h")
        if (preg_match('/(\d+)h/', $duration, $hoursMatch)) {
            $totalMinutes += (int)$hoursMatch[1] * 60; // Convert hours to minutes
        }

        // Check if the duration contains minutes (e.g., "15min")
        if (preg_match('/(\d+)min/', $duration, $minutesMatch)) {
            $totalMinutes += (int)$minutesMatch[1]; // Add minutes directly
        }

        return $totalMinutes; // Return the total minutes as an integer
    }

    private function convertLaunchedTimeToTimestamp($launchedTime)
    {
        // Mengonversi launched time ke format datetime yang dapat disimpan di database
        return Carbon::createFromFormat('Y/m/d/ H:i', $launchedTime)->format('Y-m-d H:i:s');
    }
}
