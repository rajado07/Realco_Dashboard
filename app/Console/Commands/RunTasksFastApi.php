<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Task;
use App\Jobs\ProcessFastApiTaskJob;

class RunTasksFastApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:fastapi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch FastAPI tasks: one per URL at a time, concurrent across different URLs';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 1) Ambil semua task waiting (status = 2), urut by updated_at
        $tasks = Task::with('brand')
            ->where('status', 2)
            ->orderBy('updated_at', 'asc')
            ->get();

        if ($tasks->isEmpty()) {
            Log::info('No tasks with status=2 found.');
            return 0;
        }

        // 2) Kumpulkan list base URLs dari task yang sedang running (status = 3)
        $runningUrls = Task::where('status', 3)
            ->with('brand')
            ->get()
            ->pluck('brand.fast_api_url')
            ->map(fn($url) => rtrim($url, '/'))
            ->unique()
            ->toArray();

        // 3) Track URL yang sudah kita dispatch di loop ini
        $dispatchedUrls = [];

        // 4) Iterasi tiap waiting task
        foreach ($tasks as $task) {
            $baseUrl = rtrim($task->brand->fast_api_url, '/');

            // Jika URL ini sedang running atau sudah kita dispatch di loop ini → skip
            if (in_array($baseUrl, $runningUrls) || in_array($baseUrl, $dispatchedUrls)) {
                Log::info("Task {$task->id} is waiting because a task with the same URL is already running or queued: {$baseUrl}");
                continue;
            }

            // Dispatch job; status tetap 2 sampai job benar-benar mulai
            dispatch(new ProcessFastApiTaskJob($task));

            // Tandai URL ini sudah di-dispatch, supaya tidak dispatch dua kali per run
            $dispatchedUrls[] = $baseUrl;

            Log::info("Queued Task {$task->id} for FastAPI ({$baseUrl})");
        }

        return 0;
    }
}
