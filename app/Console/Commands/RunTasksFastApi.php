<?php

namespace App\Console\Commands;

use App\Jobs\ProcessFastApiTaskJob;
use Illuminate\Console\Command;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class RunTasksFastApi extends Command
{
    protected $signature = 'run:fastapi';
    protected $description = 'Find eligible tasks and dispatch them to the queue to be run in parallel.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // 1. Identifikasi URL yang slotnya sedang terpakai (status 3 = Running)
        $runningUrls = Task::where('status', 3)
            ->whereHas('brand', fn($query) => $query->whereNotNull('fast_api_url'))
            ->with('brand:id,fast_api_url')
            ->get()
            ->map(fn($task) => $task->brand->fast_api_url)
            ->unique()
            ->all();

        if (count($runningUrls) > 0) {
            Log::info('URLs currently in use', ['urls' => $runningUrls]);
        }

        // 2. Cari kandidat task (status 2) yang URL-nya tidak sedang terpakai
        $pendingTasks = Task::with('brand')
            ->where('status', 2)
            ->whereHas('brand', function ($query) use ($runningUrls) {
                $query->whereNotIn('fast_api_url', $runningUrls)
                      ->whereNotNull('fast_api_url');
            })
            ->orderBy('updated_at', 'asc')
            ->get();

        if ($pendingTasks->isEmpty()) {
            Log::info('No eligible tasks to run in this cycle.');
            $this->info('No eligible tasks to run in this cycle.');
            return;
        }

        // 3. Pilih satu perwakilan dari setiap URL unik
        $tasksToDispatch = $pendingTasks
            ->groupBy('brand.fast_api_url')
            ->map(fn($group) => $group->first());
            
        $this->info("Found {$tasksToDispatch->count()} tasks to dispatch to the queue.");

        // 4. Kunci statusnya dan delegasikan ke Job
        foreach ($tasksToDispatch as $task) {
            // Kunci task dengan mengubah statusnya menjadi 3 (Running)
            $task->status = 3;
            $task->message = 'Dispatched to queue for processing.';
            $task->save();
            
            // Kirim ke antrian untuk diproses di latar belakang
            ProcessFastApiTaskJob::dispatch($task);

            Log::info('Dispatched task to queue.', [
                'task_id' => $task->id,
                'fast_api_url' => $task->brand->fast_api_url
            ]);
            $this->info(" -> Dispatched Task ID: {$task->id} for URL: {$task->brand->fast_api_url}");
        }
        
        $this->info('All eligible tasks have been dispatched.');
    }
}