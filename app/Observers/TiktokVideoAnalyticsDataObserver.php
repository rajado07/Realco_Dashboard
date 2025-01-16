<?php

namespace App\Observers;

use App\Models\TiktokVideoAnalyticsData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TiktokVideoAnalyticsDataObserver
{
    public function created(RawData $rawData)
    {
        if (in_array($rawData->type, ['tiktok_video_analytics_affiliate', 'tiktok_video_analytics_own'])) {
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
                    $existingData = TiktokVideoAnalyticsData::where('creator_id', $dataItem['Creator ID'])
                        ->where('video_id', $dataItem['Video ID'])
                        ->where('data_date', $rawData->data_date)
                        ->where('brand_id', $rawData->brand_id)
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    $percentageFieldsToReplace = [
                        'ctr' => 'CTR',
                        'v_to_l_rate' => 'V-to-L rate',
                        'video_finish_rate' => 'Video Finish Rate',
                        'ctor' => 'Click-to-Order Rate',
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

                    // Konversi Time ke timestamp
                    $timeAsFormatted = Carbon::createFromFormat('Y/m/d H:i:s', $dataItem['Time'])->format('Y-m-d H:i:s');

                    // Tentukan type berdasarkan rawData->type
                    $type = $rawData->type === 'tiktok_video_analytics_affiliate' ? 'affiliate' : ($rawData->type === 'tiktok_video_analytics_own' ? 'own' : 'unknown');

                    TiktokVideoAnalyticsData::create([
                        'data_date' =>  $rawData->data_date,
                        'creator_id' => $dataItem['Creator ID'],
                        'creator_name' => $dataItem['Creator name'],
                        'video_info' => $dataItem['Video Info'],
                        'video_id' => $dataItem['Video ID'],
                        'time' => $timeAsFormatted, // Gunakan timestamp
                        'products' => $dataItem['Products'],
                        'vv' => $dataItem['VV'],
                        'likes' => $dataItem['Likes'],
                        'comments' => $dataItem['Comments'],
                        'shares' => $dataItem['Shares'],
                        'new_followers' => $dataItem['New followers'],
                        'v_to_l_clicks' => $dataItem['V-to-L clicks'],
                        'product_impressions' => $dataItem['Product Impressions'],
                        'product_clicks' => $dataItem['Product Clicks'],
                        'customers' => $dataItem['Customers'] ?? null, // perlu dirubah jadi Customer tetapi jika tidak ada null saja
                        'orders' => $dataItem['Orders'],
                        'unit_sales' => $dataItem['Unit Sales'],
                        'video_revenue' => $dataItem['Video Revenue (Rp)'],
                        'gpm' => $dataItem['GPM (Rp)'],
                        'shoppable_video_attributed_gmv' => $dataItem['Shoppable video attributed GMV (Rp)'],
                        'ctr' => $cleanedPercentageData['ctr'],
                        'v_to_l_rate' => $cleanedPercentageData['v_to_l_rate'],
                        'video_finish_rate' => $cleanedPercentageData['video_finish_rate'],
                        'ctor' => $cleanedPercentageData['ctor'],

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
                        'creator_name' => $dataItem['Creator name'],
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