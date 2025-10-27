<?php

namespace App\Observers;

use App\Models\ShopeeSellerCenterLiveStreamingData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShopeeSellerCenterLiveStreamingDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'shopee_seller_center_live_streaming') {
            $jsonData = json_decode($rawData->data, true);

            $totalEntries = count($jsonData);
            $successCount = 0;
            $skipCount = 0;
            $errorDetails = [];
            $skippedDetails = [];
            $failedDetails = [];

            foreach ($jsonData as $dataItem) {
                try {
                    $infoParts = explode("\n", $dataItem['Informasi Streaming']);
                    $duration = $infoParts[0];
                    $name = $infoParts[1];
                    $datetime = str_replace('Dimulai pada ', '', $infoParts[2]);

                    $datetimeParts = explode(' ', $datetime);
                    $data_date = Carbon::createFromFormat('d-m-Y', $datetimeParts[0])->toDateString();
                    $start_at = $datetimeParts[1];

                    // Check for existing data
                    $existingData = ShopeeSellerCenterLiveStreamingData::where('data_date', $data_date)
                        ->where('start_at', $start_at)
                        ->where('name', $name)
                        ->where('brand_id', $rawData->brand_id)
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    // Convert necessary fields
                    $durationParts = explode(':', $duration);
                    $durationInSeconds = $durationParts[0] * 3600 + $durationParts[1] * 60 + $durationParts[2];
                    $durationInMinutes = $durationInSeconds / 60;


                    $averageWatchDurationParts = explode(':', $dataItem['Rata-rata Durasi Menonton']);
                    $averageWatchDurationInSeconds = $averageWatchDurationParts[0] * 3600 + $averageWatchDurationParts[1] * 60 + $averageWatchDurationParts[2];

                    $salesAmount = str_replace(['Rp', '.', 'K'], '', $dataItem['Penjualan']);
                    $salesAmount = str_replace(',', '.', $salesAmount) * 1000;

                    // Calculate sales_per_hour
                    $salesPerHour = $salesAmount / $durationInMinutes;

                    ShopeeSellerCenterLiveStreamingData::create([
                        'duration' => $durationInSeconds,
                        'name' => $name,
                        'data_date' => $data_date,
                        'start_at' => $start_at,
                        'unique_viewers' => str_replace('.', '', $dataItem['Pengunjung']),
                        'peak_viewers' => str_replace('.', '', $dataItem['Penonton Terbanyak']),
                        'avg_watch_time' => $averageWatchDurationInSeconds,
                        'orders' => $dataItem['Pesanan'],
                        'sales' => $salesAmount,
                        'sales_per_hour' => $salesPerHour,
                        'raw_data_id' => $rawData->id,
                        'brand_id' => $rawData->brand_id,
                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'stream_info' => $dataItem['Informasi Streaming'],
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
