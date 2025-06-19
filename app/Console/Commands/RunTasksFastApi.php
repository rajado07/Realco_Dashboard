<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Task;
use App\Jobs\ProcessFastApiTaskJob;

class RunTasksFastApi extends Command
{
    protected $signature = 'run:fastapi';

    protected $description = 'Dispatch FastAPI tasks into the queue, per-URL concurrency control';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Ambil semua task yang statusnya 2 (waiting), urut by updated_at
        $tasks = Task::with('brand')
            ->where('status', 2)
            ->orderBy('updated_at', 'asc')
            ->get();

        if ($tasks->isEmpty()) {
            Log::info('No tasks with status=2 found.');
            return 0;
        }

        foreach ($tasks as $task) {
            // Dispatch job; status tetap 2 sampai job benar-benar mulai
            dispatch(new ProcessFastApiTaskJob($task));

            Log::info(sprintf(
                'Task %d queued for FastAPI (%s)',
                $task->id,
                $task->brand->fast_api_url
            ));
        }

        return 0;
    }
}
