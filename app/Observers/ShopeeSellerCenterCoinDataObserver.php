<?php

namespace App\Observers;

use App\Models\ShopeeSellerCenterCoinData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShopeeSellerCenterCoinDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'shopee_seller_center_coins') {
            $jsonData = json_decode($rawData->data, true);

            $totalEntries = count($jsonData);
            $successCount = 0;
            $skipCount = 0;
            $errorDetails = [];
            $skippedDetails = [];
            $failedDetails = [];

            foreach ($jsonData as $dataItem) {
                try {

                    // Determine the format of the date
                    $date = null;
                    $time = null;
                    if (preg_match('/\d{1,2}\/\d{1,2}\/\d{4} \d{1,2}:\d{2}/', $dataItem['Tanggal'])) {
                        $dateTime = Carbon::createFromFormat('d/m/Y H:i', $dataItem['Tanggal']);
                    } else {
                        $dateTime = Carbon::createFromFormat('d-m-Y H:i:s', $dataItem['Tanggal']);
                    }

                    $date = $dateTime->format('Y-m-d');
                    $time = $dateTime->format('H:i:s');
                    
                    // Check for existing data
                    $existingData = ShopeeSellerCenterCoinData::where('data_date', $date)
                        ->where('time', $time)
                        ->where('coins_amount', $dataItem['Jumlah Koin Penjual'])
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    ShopeeSellerCenterCoinData::create([
                        'data_date' => $date,
                        'time' => $time,
                        'name' => $dataItem['Nama Promosi / Tugas Berhadiah'],
                        'coins_amount' => $dataItem['Jumlah Koin Penjual'],
                       
                        'raw_data_id' => $rawData->id,
                        'brand_id' => $rawData->brand_id,
                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'name' => $dataItem['Nama Promosi / Tugas Berhadiah'],
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
