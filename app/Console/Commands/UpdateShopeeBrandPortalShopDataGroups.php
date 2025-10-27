<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShopeeBrandPortalShopData;
use App\Models\DataGroup;

class UpdateShopeeBrandPortalShopDataGroups extends Command
{
    protected $signature = 'update:shopee-groups';
    protected $description = 'Update group_id in ShopeeBrandPortalShopData based on new group mappings';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Ambil semua data grup
        $dataGroups = DataGroup::all();

        // Loop melalui setiap entri di ShopeeBrandPortalShopData
        ShopeeBrandPortalShopData::chunk(100, function ($shopeeData) use ($dataGroups) {
            foreach ($shopeeData as $dataItem) {
                $groupId = null;

                // Cari group_id berdasarkan product_id
                foreach ($dataGroups as $dataGroup) {
                    $idMapping = json_decode($dataGroup->id_mapping, true);
                    if (in_array($dataItem->product_id, $idMapping)) {
                        $groupId = $dataGroup->id;
                        break;
                    }
                }

                // Update group_id jika ditemukan
                $dataItem->data_group_id = $groupId;
                $dataItem->save();
            }
        });

        $this->info('ShopeeBrandPortalShopData group_id updated successfully.');
    }
}
