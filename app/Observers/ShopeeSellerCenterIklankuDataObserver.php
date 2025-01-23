<?php

namespace App\Observers;

use App\Models\ShopeeSellerCenterIklankuData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShopeeSellerCenterIklankuDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'shopee_seller_center_iklanku') {
            $jsonData = json_decode($rawData->data, true);

            $totalEntries = count($jsonData);
            $successCount = 0;
            $skipCount = 0;
            $errorDetails = [];
            $skippedDetails = [];
            $failedDetails = [];

            foreach ($jsonData as $dataItem) {
                try {

                    // Convert Acos
                    $acos = isset($dataItem['Persentase Biaya Iklan terhadap Penjualan dari Iklan (ACOS)'])? str_replace('%', '', $dataItem['Persentase Biaya Iklan terhadap Penjualan dari Iklan (ACOS)']): null;

                    // Check for existing data
                    $existingData = ShopeeSellerCenterIklankuData::where('data_date', $rawData->data_date)
                        ->where('name', $dataItem['Nama Iklan'])
                        ->where('brand_id', $rawData->brand_id)
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    ShopeeSellerCenterIklankuData::create([
                        'data_date' => $rawData->data_date,
                        'name' => $dataItem['Nama Iklan'],
                        'status' => $dataItem['Status'],
                        'ad_type' => $dataItem['Jenis Iklan'],
                        'product_code' => $dataItem['Kode Produk'],
                        'display_type' => $dataItem['Tampilan Iklan'],
                        'bidding_type' => $dataItem['Mode Bidding'],
                        'ad_placement' => $dataItem['Penempatan Iklan'],
                        'start_date' => $dataItem['Tanggal Mulai'],
                        'end_date' => $dataItem['Tanggal Selesai'],
                        'impressions' => $dataItem['Dilihat'],
                        'clicks' => $dataItem['Jumlah Klik'],
                        'items_sold' => $dataItem['Produk Terjual'],
                        'gmv' => $dataItem['Omzet Penjualan'],
                        'expense' => $dataItem['Biaya'],
                        'roas' => $dataItem['Efektifitas Iklan'],
                        'acos' => $acos,
  
                        'raw_data_id' => $rawData->id,
                        'brand_id' => $rawData->brand_id,
                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'name' => $dataItem['Nama Iklan'],
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
