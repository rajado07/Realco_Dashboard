<?php

namespace App\Observers;

use App\Models\RawData;
use Illuminate\Support\Facades\Log;

class RawDataStatusObserver
{
    public function updated(RawData $rawData)
    {
        if ($rawData->isDirty('status')) {
            $task = $rawData->task;

            if ($task) {
                $task->status = $this->mapStatus($rawData->status);
                $task->save();

                Log::info("Task ID: {$task->id} diupdate ke status: {$task->status} berdasarkan perubahan status RawData.");
            } else {
                Log::error("Task dengan ID {$rawData->task_id} tidak ditemukan.");
            }
        }
    }

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
