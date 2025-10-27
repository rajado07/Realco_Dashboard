<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MetaCpasData;
use App\Models\DataGroup;
use Illuminate\Support\Facades\Log;

class CreateMetaCpasDataGroups extends Command
{
    protected $signature = 'create:meta-cpas-groups';
    protected $description = 'Update or Confirm group_id in MetaCpasData based on DataGroups with type meta_cpas and group name matching ad_set_name, prioritizing first match';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Ambil semua data grup dengan tipe meta_cpas dan urutkan berdasarkan nama
        $dataGroups = DataGroup::where('type', 'meta_cpas')->orderBy('name')->get();

        // Proses MetaCpasData yang diurutkan berdasarkan ad_set_name
        MetaCpasData::orderBy('ad_set_name')->chunk(100, function ($metaCpasEntries) use ($dataGroups) {
            foreach ($metaCpasEntries as $entry) {
                $groupId = null;

                // Cari group_id dengan membandingkan ad_set_name dengan nama grup
                foreach ($dataGroups as $group) {
                    if (stripos($entry->ad_set_name, $group->name) !== false) {
                        $groupId = $group->id;
                        break;  // Hentikan pencarian setelah cocok pertama
                    }
                }

                // Update group_id jika ditemukan
                if ($groupId !== null) {
                    $entry->data_group_id = $groupId;
                    $entry->save();

                    // Update id_mapping pada DataGroup
                    $this->updateIdMapping($groupId, (string) $entry->ad_set_id);
                }
            }
        });

        $this->info('MetaCpasData group_id updated or confirmed successfully based on DataGroups with type meta_cpas.');
    }

    private function updateIdMapping($groupId, $adSetId)
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

            // Tambahkan adSetId ke id_mapping jika belum ada
            if (!in_array($adSetId, $idMapping)) {
                $idMapping[] = $adSetId;
            }

            // Update id_mapping di DataGroup
            $group->id_mapping = json_encode($idMapping);
            $group->save();
        }
    }
}