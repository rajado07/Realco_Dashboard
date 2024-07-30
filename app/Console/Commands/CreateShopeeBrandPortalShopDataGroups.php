<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShopeeBrandPortalShopData;
use App\Models\DataGroup;

class CreateShopeeBrandPortalShopDataGroups extends Command
{
    protected $signature = 'create:shopee-brand-portal-groups';
    protected $description = 'Update or Confirm group_id in ShopeeBrandPortalShopData based on DataGroups with type shopee_brand_portal and group name matching product_name, prioritizing first match';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Ambil semua data grup dengan tipe shopee_brand_portal dan urutkan berdasarkan nama
        $dataGroups = DataGroup::where('type', 'shopee_brand_portal_shop')->orderBy('name')->get();

        // Proses ShopeeBrandPortalShopData yang diurutkan berdasarkan product_name
        ShopeeBrandPortalShopData::orderBy('product_name')->chunk(100, function ($shopeeEntries) use ($dataGroups) {
            foreach ($shopeeEntries as $entry) {
                $groupId = null;

                // Cari group_id dengan membandingkan product_name dengan nama grup
                foreach ($dataGroups as $group) {
                    if (stripos($entry->product_name, $group->name) !== false) {
                        $groupId = $group->id;
                        break;  // Hentikan pencarian setelah cocok pertama
                    }
                }

                // Update group_id jika ditemukan
                if ($groupId !== null) {
                    $entry->data_group_id = $groupId;
                    $entry->save();

                    // Update id_mapping pada DataGroup
                    $this->updateIdMapping($groupId, (string) $entry->product_id);
                }
            }
        });

        $this->info('ShopeeBrandPortalShopData group_id updated or confirmed successfully based on DataGroups with type shopee_brand_portal.');
    }

    private function updateIdMapping($groupId, $productId)
    {
        // Temukan DataGroup berdasarkan groupId
        $group = DataGroup::find($groupId);

        if ($group) {
            // Ambil id_mapping dan decode menjadi array
            $idMapping = json_decode($group->id_mapping, true);

            // Pastikan id_mapping adalah array, jika tidak, buat array kosong
            if (!is_array($idMapping)) {
                $idMapping = [];
            }

            // Tambahkan productId ke id_mapping jika belum ada
            if (!in_array($productId, $idMapping)) {
                $idMapping[] = $productId;
            }

            // Update id_mapping di DataGroup
            $group->id_mapping = json_encode($idMapping);
            $group->save();
        }
    }
}
