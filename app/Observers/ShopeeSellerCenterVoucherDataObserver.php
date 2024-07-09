<?php

namespace App\Observers;

use App\Models\ShopeeSellerCenterVoucherData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShopeeSellerCenterVoucherDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'shopee_seller_center_voucher') {
            $jsonData = json_decode($rawData->data, true);

            $totalEntries = count($jsonData);
            $successCount = 0;
            $skipCount = 0;
            $errorDetails = [];
            $skippedDetails = [];
            $failedDetails = [];

            foreach ($jsonData as $dataItem) {
                try {
                    // Memisahkan periode klaim menjadi tanggal mulai dan tanggal berakhir
                    $claimPeriod = explode(' - ', $dataItem['Periode Klaim']);

                    if (strpos($claimPeriod[0], ':') !== false) {
                        // Format dengan waktu
                        $claimStart = Carbon::createFromFormat('d-m-Y H:i', trim($claimPeriod[0]));
                        $claimEnd = Carbon::createFromFormat('d-m-Y H:i', trim($claimPeriod[1]));
                    } else {
                        // Format tanpa waktu
                        $claimStart = Carbon::createFromFormat('d-m-Y', trim($claimPeriod[0]));
                        $claimEnd = Carbon::createFromFormat('d-m-Y', trim($claimPeriod[1]));
                    }

                    // Konversi Percentage
                    $percentageString = str_replace('%', '', $dataItem['Tingkat Penggunaan (Pesanan Siap Dikirim)']);
                    $cleanedString = str_replace(',', '.', $percentageString);
                    $usage_rate_cleaned = (float)$cleanedString;

                    // Menghapus titik sebagai pemisah ribuan
                    $cleanedSalesData = str_replace('.', '', $dataItem['Penjualan (Pesanan Siap Dikirim) (IDR)']);
                    $salesInteger = (int)$cleanedSalesData;

                    // Menghapus titik sebagai pemisah ribuan
                    $cleanedCostData = str_replace('.', '', $dataItem['Total Biaya (Pesanan Siap Dikirim) (IDR)']);
                    $costInteger = (int)$cleanedCostData;

                    // Menghapus titik sebagai pemisah ribuan
                    $cleanedSalesPerBuyerData = str_replace('.', '', $dataItem['Penjualan per Pembeli (Pesanan Siap Dikirim) (IDR)']);
                    $salesPerBuyerInteger = (int)$cleanedSalesPerBuyerData;

                    $roi = $salesInteger > 0 ? $salesInteger / $costInteger : 0;

                    // Check for existing data
                    $existingData = ShopeeSellerCenterVoucherData::where('data_date', $rawData->data_date)
                        ->where('voucher_name', $dataItem['Nama Voucher'])
                        ->where('voucher_code', $dataItem['Kode Voucher'])
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    ShopeeSellerCenterVoucherData::create([
                        'voucher_name' => $dataItem['Nama Voucher'],
                        'voucher_code' => $dataItem['Kode Voucher'],
                        'claim_start' => $claimStart,
                        'claim_end' => $claimEnd,
                        'voucher_type' => $dataItem['Tipe Voucher'],
                        'reward_type' => $dataItem['Tipe Hadiah'],
                        'claim' => $dataItem['Klaim'],
                        'order' => $dataItem['Pesanan (Pesanan Siap Dikirim)'],
                        'usage_rate' => $usage_rate_cleaned,
                        'sales' => $salesInteger,
                        'cost' => $costInteger,
                        'units_sold' => $dataItem['Produk Terjual (Pesanan Siap Dikirim)'],
                        'buyers' => $dataItem['Pembeli (Pesanan Siap Dikirim)'],
                        'sales_per_buyer' => $salesPerBuyerInteger,
                        'roi' => $roi,

                        'data_date' => $rawData->data_date,
                        'raw_data_id' => $rawData->id,
                        'brand_id' => $rawData->brand_id,
                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'voucher_name' => $dataItem['Nama Voucher'],
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

            if ($successCount === 0) {
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
