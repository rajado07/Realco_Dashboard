<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\RawData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessFastApiTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function handle()
    {
        // Eager load relasi brand jika belum ter-load
        $this->task->loadMissing('brand');

        $baseFastApiUrl = $this->task->brand->fast_api_url ?? 'http://127.0.0.1:8001';
        $fastApiUrl = rtrim($baseFastApiUrl, '/') . '/run-script';

        // Gabungkan atribut Task dengan atribut spesifik dari Brand
        $mergedData = [
            'id' => $this->task->id,
            'brand_id' => $this->task->brand_id,
            'market_place_id' => $this->task->market_place_id,
            'type' => $this->task->type,
            'link' => $this->task->link,
            'scheduled_to_run' => $this->task->scheduled_to_run,
            'status' => $this->task->status,
            'task_generator_id' => $this->task->task_generator_id,
            'message' => $this->task->message,
            'user_data_dir' => $this->task->brand->user_data_dir,
            'profile_dir' => $this->task->brand->profile_dir,
            'download_directory' => $this->task->brand->download_directory,
        ];

        try {
            // 1. Panggil FastAPI
            $response = Http::timeout(300)->post($fastApiUrl, $mergedData);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['status']) && $responseData['status'] === 'success') {
                    
                    $rawData                     = new RawData();
                    $rawData->type               = $this->task->type;
                    $rawData->data               = json_encode($responseData['data']);
                    $rawData->retrieved_at       = Carbon::now();
                    $rawData->data_date          = Carbon::parse($this->task->scheduled_to_run)->toDateString();
                    $rawData->file_name          = $responseData['file_name'] ?? null;
                    $rawData->brand_id           = $this->task->brand_id;
                    $rawData->market_place_id    = $this->task->market_place_id;
                    $rawData->task_id            = $this->task->id;

                    $this->task->status  = 5;
                    $this->task->message = null;
                    
                    Log::info('Task executed and status updated to 5 (success)', [
                        'task_id'          => $this->task->id,
                        'type'             => $this->task->type,
                        'scheduled_to_run' => $this->task->scheduled_to_run,
                    ]);

                    $this->task->save();
                    $rawData->save();

                } elseif ($responseData['status'] === 'error') {
                    $this->task->status  = 4;
                    $this->task->message = $responseData['error'] ?? 'Unknown error';
                    $this->task->save();

                    Log::error('Task execution failed with error and status updated to 4 (exception)', [
                        'task_id'       => $this->task->id,
                        'error_message' => $this->task->message,
                    ]);

                } elseif ($responseData['status'] === 'exception') {
                    $this->task->status  = 4;
                    $this->task->message = $responseData['message'] ?? 'Exception from FastAPI';
                    $this->task->save();

                    Log::error('Task execution encountered an exception and status updated to 4 (exception)', [
                        'task_id'            => $this->task->id,
                        'exception_message'  => $this->task->message,
                    ]);
                }

            } else {
                $this->task->status  = 4;
                $this->task->message = 'Failed to call FastAPI';
                $this->task->save();

                Log::error('Failed to call FastAPI and status updated to 4 (exception)', [
                    'task_id'       => $this->task->id,
                    'fast_api_url'  => $fastApiUrl,
                    'error_message' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            $this->task->status  = 4;
            $this->task->message = $e->getMessage();
            $this->task->save();

            Log::error('Task execution failed and status updated to 4 (exception)', [
                'task_id'      => $this->task->id,
                'fast_api_url' => $fastApiUrl,
                'error'        => $e->getMessage(),
            ]);
        }
    }
}
