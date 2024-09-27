<?php

namespace App\Observers;

use App\Models\RawData;
use App\Models\Task; // Pastikan untuk menambahkan use Task
use Illuminate\Support\Facades\Log;

class RawDataStatusObserver
{
    public function updated(RawData $rawData)
    {
        if ($rawData->isDirty('status')) {
            // Cari task berdasarkan task_id yang ada di RawData
            $task = Task::find($rawData->task_id);

            if ($task) {
                // Update status task dengan memetakan dari status RawData
                $task->status = $this->mapStatus($rawData->status);
                $task->save();

                Log::info("Task ID: {$task->id} diupdate ke status: {$task->status} berdasarkan perubahan status RawData.");
            } else {
                Log::error("Task dengan ID {$rawData->task_id} tidak ditemukan.");
            }
        }
    }

    // Fungsi untuk memetakan status RawData ke status Task
    protected function mapStatus($rawDataStatus)
    {
        switch ($rawDataStatus) {
            case 2:
                return 5;
            case 3:
                return 6;
            case 4:
                return 7;
            case 5:
                return 8;
            case 6:
                return 9;
            default:
                return 5;
        }
    }
}
