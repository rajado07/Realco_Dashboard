<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\TaskGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateTaskStatus extends Command
{
    protected $signature = 'update:task-status';
    protected $description = 'Update task status when the scheduled time has arrived';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Ambil task yang sedang dijalankan (status 1) dan waktunya sudah tiba atau terlewat
        $tasks = Task::where('status', 1)
            ->where('scheduled_to_run', '<=', Carbon::now())
            ->get();

        foreach ($tasks as $task) {
            $taskGenerator = TaskGenerator::find($task->task_generator_id);

            if ($taskGenerator && $taskGenerator->frequency === 'daily') {
                $scheduledDate = Carbon::parse($task->scheduled_to_run);
                if ($scheduledDate->addDays(2)->lte(Carbon::now())) {
                    // Update status task menjadi 2 (sudah dijalankan) jika sudah H+2
                    $task->status = 2;
                    $task->save();

                    Log::info('Daily task status updated to 2', [
                        'task_id' => $task->id,
                        'brand_id' => $task->brand_id,
                        'market_place_id' => $task->market_place_id,
                        'type' => $task->type,
                        'scheduled_to_run' => $task->scheduled_to_run,
                    ]);
                }
            } else {
                // Untuk frequency selain daily, update langsung jika waktunya sudah tiba
                $task->status = 2;
                $task->save();

                Log::info('Task status updated to 2', [
                    'task_id' => $task->id,
                    'brand_id' => $task->brand_id,
                    'market_place_id' => $task->market_place_id,
                    'type' => $task->type,
                    'scheduled_to_run' => $task->scheduled_to_run,
                ]);
            }
        }
    }
}