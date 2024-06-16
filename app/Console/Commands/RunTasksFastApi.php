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

        $runningTask = Task::where('status', 3)->first();
        if ($runningTask) {
            Log::info('A task is already running. Waiting for it to finish.');
            return;
        }

        $task = Task::where('status', 2)
            ->orderBy('updated_at', 'asc')
            ->first();

        if ($task) {
            $task->status = 3;
            $task->save();

            try {
                $response = Http::timeout(120)->post('http://127.0.0.1:8001/run-script', $task->toArray());
                // $response = Http::timeout(120)->post('http://192.165.10.20:8000/run-script', $task->toArray());

                if ($response->successful()) {
                    $responseData = $response->json();
                    if ($responseData['status'] == 'success') {
                        $rawData = new RawData();
                        $rawData->type = $task->type;
                        $rawData->data = json_encode($responseData['data']);
                        $rawData->retrieved_at = Carbon::now();
                        $rawData->data_date = Carbon::parse($task->scheduled_to_run)->toDateString();
                        $rawData->file_name = $responseData['file_name'];
                        $rawData->brand_id = $task->brand_id;
                        $rawData->market_place_id = $task->market_place_id;
                        $rawData->task_id = $task->id;
                        $rawData->save();

                        $task->status = 5;
                        $task->message = null;
                        Log::info('Task executed and status updated to 5 (success)', [
                            'task_id' => $task->id,
                            'type' => $task->type,
                            'scheduled_to_run' => $task->scheduled_to_run,
                        ]);
                    } elseif ($responseData['status'] == 'error') {
                        // Update status task menjadi 4 (exception) jika terjadi kesalahan
                        $task->status = 4;
                        $task->message = $responseData['error'];
                        Log::error('Task execution failed with error and status updated to 4 (exception)', [
                            'task_id' => $task->id,
                            'error_message' => $responseData['error'],
                        ]);
                    } elseif ($responseData['status'] == 'exception') {
                        // Update status task menjadi 4 (exception) jika terjadi exception
                        $task->status = 4;
                        $task->message = $responseData['message'];
                        Log::error('Task execution encountered an exception and status updated to 4 (exception)', [
                            'task_id' => $task->id,
                            'exception_message' => $responseData['message'],
                        ]);
                    }
                } else {
                    // Update status task menjadi 4 (exception) jika request gagal
                    $task->status = 4;
                    $task->message = 'Failed to call FastAPI';
                    Log::error('Failed to call FastAPI and status updated to 4 (exception)', [
                        'task_id' => $task->id,
                        'error_message' => $response->body(),
                    ]);
                }

                $task->save();
            } catch (\Exception $e) {
                // Update status task menjadi 4 (exception) jika terjadi kesalahan
                $task->status = 4;
                $task->message = $e->getMessage();
                $task->save();

                Log::error('Task execution failed and status updated to 4 (exception)', [
                    'task_id' => $task->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            // Log::info('No tasks with status 2 found.');
        }

        // Log::info('RunFastAPITasks command completed.');
    }
}
