<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\RawData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessFastApiTaskJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function handle()
    {
        // Segera tandai running
        $task = Task::with('brand')->find($this->task->id);
        $task->update(['status' => 3]);

        $baseUrl  = rtrim($task->brand->fast_api_url, '/');
        $endpoint = "{$baseUrl}/run-script";

        try {
            $response = Http::timeout(900)->post($endpoint, [
                'id'                 => $task->id,
                'brand_id'           => $task->brand_id,
                'market_place_id'    => $task->market_place_id,
                'type'               => $task->type,
                'link'               => $task->link,
                'scheduled_to_run'   => $task->scheduled_to_run,
                'status'             => 3,
                'task_generator_id'  => $task->task_generator_id,
                'message'            => $task->message,
                'user_data_dir'      => $task->brand->user_data_dir,
                'profile_dir'        => $task->brand->profile_dir,
                'download_directory' => $task->brand->download_directory,
            ]);

            if (! $response->successful()) {
                throw new \Exception('HTTP error: ' . $response->body());
            }

            $data = $response->json();

            if (isset($data['status']) && $data['status'] === 'success') {
                RawData::create([
                    'type'             => $task->type,
                    'data'             => json_encode($data['data']),
                    'retrieved_at'     => Carbon::now(),
                    'data_date'        => Carbon::parse($task->scheduled_to_run)->toDateString(),
                    'file_name'        => $data['file_name'],
                    'brand_id'         => $task->brand_id,
                    'market_place_id'  => $task->market_place_id,
                    'task_id'          => $task->id,
                ]);

                $task->update([
                    'status'  => 5, // success
                    'message' => null,
                ]);

                Log::info("Task [{$task->id}] succeeded (URL={$baseUrl}).");
            } else {
                $errorMsg = $data['error'] ?? $data['message'] ?? 'Unknown error';
                $task->update([
                    'status'  => 4,
                    'message' => $errorMsg,
                ]);

                Log::error("Task [{$task->id}] failed: {$errorMsg}");
            }
        } catch (\Exception $e) {
            $task->update([
                'status'  => 4,
                'message' => $e->getMessage(),
            ]);

            Log::error("Task [{$task->id}] exception: {$e->getMessage()}");
        }
    }
}
