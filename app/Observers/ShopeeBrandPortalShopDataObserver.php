<?php

namespace App\Observers;

use App\Models\RawData;
use App\Models\ShopeeBrandPortalShopData;
use App\Models\DataGroup;
use Illuminate\Support\Facades\Log;

class ShopeeBrandPortalShopDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'shopee_brand_portal_shop') {
            $jsonData = json_decode($rawData->data, true);

            $totalEntries = count($jsonData);
            $successCount = 0;
            $skipCount = 0; // Initialize skipped count if needed
            $errorDetails = [];
            $failedDetails = [];

            foreach ($jsonData as $dataItem) {
                try {
                    // Tentukan group_id berdasarkan product_id
                    $groupId = null;
                    $dataGroups = DataGroup::all();

                    foreach ($dataGroups as $dataGroup) {
                        $idMapping = json_decode($dataGroup->id_mapping, true);
                        if (in_array($dataItem['Product ID'], $idMapping)) {
                            $groupId = $dataGroup->id;
                            break;
                        }
                    }

                    ShopeeBrandPortalShopData::create([
                        'product_name' => $dataItem['Product Name'],
                        'product_id' => $dataItem['Product ID'],
                        'gross_sales' => $dataItem['Gross Sales(Rp)'],
                        'gross_orders' => $dataItem['Gross Orders'],
                        'gross_units_sold' => $dataItem['Gross Units Sold'],
                        'product_views' => $dataItem['Product Views'],
                        'product_visitors' => $dataItem['Product Visitors'],
                        'retrieved_at' => $rawData->retrieved_at,
                        'data_date' => $rawData->data_date,
                        'file_name' => $rawData->file_name,
                        'brand_id' => $rawData->brand_id,
                        'raw_data_id' => $rawData->id,
                        'data_group_id' => $groupId, // Menetapkan group_id jika ada, jika tidak null
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem;
                    $errorDetails[] = [
                        'product_id' => $dataItem['Product ID'],
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
