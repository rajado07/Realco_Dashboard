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
            $skipCount = 0;
            $errorDetails = [];
            $skippedDetails = [];
            $failedDetails = [];

            // Pre-fetch all data groups and decode their ID mappings for quick lookups
            $dataGroups = DataGroup::all()->mapWithKeys(function ($item) {
                return [$item->id => json_decode($item->id_mapping, true) ?? []];
            });

            foreach ($jsonData as $dataItem) {
                try {
                    // Check if the data already exists
                    $existingData = ShopeeBrandPortalShopData::where('product_id', $dataItem['Product ID'])
                        ->where('data_date', $rawData->data_date)
                        ->where('brand_id', $rawData->brand_id)
                        ->exists();

                    if ($existingData) {
                        $skipCount++;
                        $skippedDetails[] = $dataItem; // Collect details of skipped items
                        continue; // Skip this iteration as the entry already exists
                    }

                    // Determine group_id based on product_id
                    $groupId = null;
                    foreach ($dataGroups as $groupIdKey => $idMapping) {
                        if (in_array($dataItem['Product ID'], $idMapping)) {
                            $groupId = $groupIdKey;
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
                        'data_group_id' => $groupId,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedDetails[] = $dataItem; // Collect details of failed items
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

            $rawData->update([
                'status' => $status,
                'message' => json_encode($messageDetails),
            ]);
        }
    }
}
