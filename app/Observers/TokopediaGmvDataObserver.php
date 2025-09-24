<?php

namespace App\Observers;

use App\Models\TokopediaGmvData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;

class TokopediaGmvDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'tokopedia_gmv') {
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
                    $existingData = TokopediaGmvData::where('data_date', $dataItem['Date'])
                        ->where('brand_id', $rawData->brand_id)
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem;
                        continue;
                    }

                    $conversionRate = isset($dataItem['Conversion rate']) ? str_replace('%', '', $dataItem['Conversion rate']) : null;

                    TokopediaGmvData::create([
                        'data_date' => $dataItem['Date'],
                        'gmv' => $dataItem['Gross merchandise value (Rp)'],
                        'refunds' => $dataItem['Refunds (Rp)'],
                        // 'gross_revenue' => $dataItem['Gross merchandise value (with TikTok co-funding)'], Sudah tidak digunakan lagii !
                        'items_sold' => $dataItem['Items sold'],
                        'customers' => $dataItem['Customers'], # dirubah dari sebelum nya penamaan nya buyers
                        'page_views' => $dataItem['Page views'],
                        'visitors' => $dataItem['Shop page visits'],
                        'sku_orders' => $dataItem['SKU orders'],
                        'orders' => $dataItem['Orders'],
                        'conversion_rate' => $conversionRate,
                        
                        'retrieved_at' => $rawData->retrieved_at,
                        'file_name' => $rawData->file_name,
                        'brand_id' => $rawData->brand_id,
                        'raw_data_id' => $rawData->id,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'data_date' => $dataItem['Date'],
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
