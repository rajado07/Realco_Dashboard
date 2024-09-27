<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class RunTasksFastApi extends Command
{
    protected $signature = 'run:fastapi';
    protected $description = 'Run tasks based on status using FastAPI';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Log::info('RunFastAPITasks command started.');

        // Check if any task is already running
        $runningTask = Task::where('status', 3)->first();
        if ($runningTask) {
            Log::info('A task is already running. Waiting for it to finish.');
            return;
        }

        $task = Task::with('brand') // Use with() to eager load the Brand relation
            ->where('status', 2)
            ->orderBy('updated_at', 'asc')
            ->first();

        if ($task) {
            $task->status = 3;
            $task->save();

            // Get the fast_api_url from the brand associated with this task
            $baseFastApiUrl = $task->brand->fast_api_url ?? 'http://127.0.0.1:8001';
            $fastApiUrl = rtrim($baseFastApiUrl, '/') . '/run-script';

            // Merge Task attributes with specific Brand attributes
            $mergedData = [
                'id' => $task->id,
                'brand_id' => $task->brand_id,
                'market_place_id' => $task->market_place_id,
                'type' => $task->type,
                'link' => $task->link,
                'scheduled_to_run' => $task->scheduled_to_run,
                'status' => $task->status,
                'task_generator_id' => $task->task_generator_id,
                'message' => $task->message,
                'user_data_dir' => $task->brand->user_data_dir,  
                'profile_dir' => $task->brand->profile_dir,     
                'download_directory' => $task->brand->download_directory, 
            ];

            try {
                // Make the POST request using the combined fast_api_url and merged data
                $response = Http::timeout(120)->post($fastApiUrl, $mergedData);

                if ($response->successful()) {
                    $responseData = $response->json();
                    if ($responseData['status'] == 'success') {
                        // Save RawData
                        $rawData = new RawData();
                        $rawData->type = $task->type;
                        $rawData->data = json_encode($responseData['data']);
                        $rawData->retrieved_at = Carbon::now();
                        $rawData->data_date = Carbon::parse($task->scheduled_to_run)->toDateString();
                        $rawData->file_name = $responseData['file_name'];
                        $rawData->brand_id = $task->brand_id;
                        $rawData->market_place_id = $task->market_place_id;
                        $rawData->task_id = $task->id;

                        // Update task status to success (5)
                        $task->status = 5;
                        $task->message = null;
                        Log::info('Task executed and status updated to 5 (success)', [
                            'task_id' => $task->id,
                            'type' => $task->type,
                            'scheduled_to_run' => $task->scheduled_to_run,
                        ]);

                        // Simpan task dan rawData setelah berhasil
                        $task->save();
                        $rawData->save();

                    } elseif ($responseData['status'] == 'error') {
                        // Update task status to 4 (exception) if an error occurred
                        $task->status = 4;
                        $task->message = $responseData['error'];
                        Log::error('Task execution failed with error and status updated to 4 (exception)', [
                            'task_id' => $task->id,
                            'error_message' => $responseData['error'],
                        ]);
                    } elseif ($responseData['status'] == 'exception') {
                        // Update task status to 4 (exception) if an exception occurred
                        $task->status = 4;
                        $task->message = $responseData['message'];
                        Log::error('Task execution encountered an exception and status updated to 4 (exception)', [
                            'task_id' => $task->id,
                            'exception_message' => $responseData['message'],
                        ]);
                    }
                } else {
                    // Update task status to 4 (exception) if request failed
                    $task->status = 4;
                    $task->message = 'Failed to call FastAPI';
                    Log::error('Failed to call FastAPI and status updated to 4 (exception)', [
                        'task_id' => $task->id,
                        'fast_api_url' => $fastApiUrl, // Log the URL
                        'error_message' => $response->body(),
                    ]);
                }

                $task->save(); 

            } catch (\Exception $e) {
                // Update task status to 4 (exception) if an error occurred
                $task->status = 4;
                $task->message = $e->getMessage();
                $task->save();

                Log::error('Task execution failed and status updated to 4 (exception)', [
                    'task_id' => $task->id,
                    'fast_api_url' => $fastApiUrl, // Log the URL
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::info('No tasks with status 2 found.');
        }
    }
}
