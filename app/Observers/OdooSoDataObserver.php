<?php

namespace App\Observers;

use App\Models\OdooSoData;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;

class OdooSoDataObserver
{
    public function created(RawData $rawData)
    {
        if ($rawData->type === 'odoo_so') {
            $jsonData = json_decode($rawData->data, true);
            
            $totalEntries = count($jsonData);
            $successCount = 0;
            $skipCount = 0;
            $errorDetails = [];
            $skippedDetails = [];
            $failedDetails = [];
            $updatedDetails = [];
            $successfulEntries = [];

            foreach ($jsonData as $dataItem) {
                // Mengubah nilai false menjadi string 'false' untuk jubelio_so_no, jubelio_status, dan source_name
                $dataItem['jubelio_so_no'] = $dataItem['jubelio_so_no'] === false ? 'false' : $dataItem['jubelio_so_no'];
                $dataItem['jubelio_status'] = $dataItem['jubelio_status'] === false ? 'false' : $dataItem['jubelio_status'];
                $dataItem['source_name'] = $dataItem['source_name'] === false ? 'false' : $dataItem['source_name'];
                $dataItem['ns_shipping_info_provider'] = $dataItem['ns_shipping_info_provider'] === false ? 'false' : $dataItem['ns_shipping_info_provider'];

                // Step 1: Check existing data based on `odoo_id`
                $existingRecords = OdooSoData::where('odoo_id', $dataItem['id'])->get();

                if ($existingRecords->isEmpty()) {
                    // If no existing data, create new entries for each order line
                    foreach ($dataItem['order_lines'] as $orderLine) {
                        OdooSoData::create($this->prepareOrderLineData($dataItem, $orderLine, $rawData));
                    }
                    $successCount++;
                    $successfulEntries[] = $dataItem['id'];
                } else {
                    // Step 2: Synchronize data if `odoo_id` exists
                    $existingOrderLines = $existingRecords->keyBy('ol_id');
                    $hasUpdates = false;
                    $isSkipped = true;
                    $hasNewOrderLine = false;

                    foreach ($dataItem['order_lines'] as $orderLine) {
                        if (isset($existingOrderLines[$orderLine['id']])) {
                            // Existing order line found, compare and update if necessary
                            $existingRecord = $existingOrderLines[$orderLine['id']];
                            $updatedData = $this->prepareOrderLineData($dataItem, $orderLine, $rawData);

                            // Compare each field and update if different
                            $isUpdated = false;
                            $updatedFields = [];
                            foreach ($updatedData as $key => $value) {
                                if (in_array($key, ['retrieved_at', 'raw_data_id'])) {
                                    continue; // Skip retrieved_at and raw_data_id for comparison
                                }
                                if ($existingRecord->$key !== $value) {
                                    $updatedFields[$key] = [
                                        'before' => $existingRecord->$key,
                                        'after' => $value
                                    ];
                                    $existingRecord->$key = $value;
                                    $isUpdated = true;
                                }
                            }
                            if ($isUpdated) {
                                // Update retrieved_at and raw_data_id if other fields are updated
                                $existingRecord->retrieved_at = $rawData->retrieved_at;
                                $existingRecord->raw_data_id = $rawData->id;
                                $existingRecord->save();
                                $updatedDetails[] = [
                                    'odoo_id' => $dataItem['id'],
                                    'order_line_id' => $orderLine['id'],
                                    'updated_fields' => $updatedFields
                                ];
                                $hasUpdates = true;
                                $isSkipped = false;
                            }

                            // Remove this order line from existingOrderLines to keep track of processed lines
                            $existingOrderLines->forget($orderLine['id']);
                        } else {
                            // New order line, create a new entry
                            OdooSoData::create($this->prepareOrderLineData($dataItem, $orderLine, $rawData));
                            $hasUpdates = true;
                            $isSkipped = false;
                            $hasNewOrderLine = true;
                        }
                    }

                    // Step 3: Delete order lines from the database that are not in the new JSON data
                    foreach ($existingOrderLines as $remainingOrderLine) {
                        $remainingOrderLine->delete();
                        $hasUpdates = true;
                        $isSkipped = false;
                    }

                    // Increment success count if there were updates or new order lines, otherwise consider it skipped
                    if ($hasUpdates || $hasNewOrderLine) {
                        $successCount++;
                        $successfulEntries[] = $dataItem['id'];
                    } else {
                        $skipCount++;
                        $skippedDetails[] = [
                            'odoo_id' => $dataItem['id'],
                            'reason' => 'No changes detected'
                        ];
                    }
                }
            }

            // Determine the status and message details
            $status = 2; // Assume success by default
            $messageDetails = [
                'total_entries' => $totalEntries,
                'successful' => $successCount,
                'skipped' => $skipCount,
                'failed' => $totalEntries - $successCount - $skipCount,
                'errors' => $errorDetails,
                'skipped_details' => $skippedDetails,
                'failed_details' => $failedDetails,
                'successful_entries' => $successfulEntries,
                'updated_details' => $updatedDetails,
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

    private function prepareOrderLineData($dataItem, $orderLine, $rawData)
    {
        return [
            'odoo_id' => $dataItem['id'],
            'so_number' => $dataItem['name'],
            'odoo_company_id' => $dataItem['company_id'][0] ?? null,
            'odoo_company_name' => $dataItem['company_id'][1] ?? null,
            'date_order' => $dataItem['date_order'],
            'state' => $dataItem['state'],
            'amount_total' => $dataItem['amount_total'],
            'odoo_user_id' => $dataItem['user_id'][0] ?? null,
            'odoo_user_name' => $dataItem['user_id'][1] ?? null,
            'invoice_status' => $dataItem['invoice_status'],
            'delivery_status' => $dataItem['delivery_status'],
            'jubelio_so_no' => $dataItem['jubelio_so_no'],
            'jubelio_status' => $dataItem['jubelio_status'],
            'odoo_partner_id' => $dataItem['partner_id'][0] ?? null,
            'odoo_partner_name' => $dataItem['partner_id'][1] ?? null,
            'odoo_channel_id' => $dataItem['channel'][0] ?? null,
            'odoo_channel_name' => $dataItem['channel'][1] ?? null,
            'team_id' => $dataItem['team_id'][0] ?? null,
            'team_name' => $dataItem['team_id'][1] ?? null,
            'ns_shipping_info_provider' => $dataItem['ns_shipping_info_provider'],
            'amount_untaxed' => $dataItem['amount_untaxed'],
            'amount_tax' => $dataItem['amount_tax'],
            'service_fee' => $dataItem['service_fee'],
            'insurance_cost' => $dataItem['insurance_cost'],
            'nrs_add_disc' => $dataItem['nrs_add_disc'],
            'nrs_add_fee' => $dataItem['nrs_add_fee'],
            'nrs_add_escrow' => $dataItem['nrs_add_escrow'],
            'source_name' => $dataItem['source_name'],
            'odoo_partner_cust_rank_id' => $dataItem['partner_cust_rank'][0] ?? null,
            'odoo_partner_cust_rank_name' => $dataItem['partner_cust_rank'][1] ?? null,
            'ol_id' => $orderLine['id'],
            'ol_name' => $orderLine['name'],
            'ol_product_uom_qty' => $orderLine['product_uom_qty'],
            'ol_price_unit' => $orderLine['price_unit'],
            'ol_discount' => $orderLine['discount'],
            'ol_discount_fixed' => $orderLine['discount_fixed'],
            'ol_price_subtotal' => $orderLine['price_subtotal'],
            'ol_price_total' => $orderLine['price_total'],
            'retrieved_at' => $rawData->retrieved_at,
            'file_name' => $rawData->file_name,
            'brand_id' => $rawData->brand_id,
            'raw_data_id' => $rawData->id
        ];
    }
}
