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
            // Lakukan POST request dengan data yang sudah digabung
            $response = Http::timeout(300)->post($fastApiUrl, $mergedData);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['status']) && $responseData['status'] == 'success') {
                    // Buat RawData
                    $rawData = new RawData();
                    $rawData->type = $this->task->type;
                    $rawData->data = json_encode($responseData['data']);
                    $rawData->retrieved_at = Carbon::now();
                    $rawData->data_date = Carbon::parse($this->task->scheduled_to_run)->toDateString();
                    $rawData->file_name = $responseData['file_name'] ?? null;
                    $rawData->brand_id = $this->task->brand_id;
                    $rawData->market_place_id = $this->task->market_place_id;
                    $rawData->task_id = $this->task->id;
                    $rawData->save();

                    // Update status task menjadi 5 (success)
                    $this->task->status = 5;
                    $this->task->message = 'Successfully executed by FastAPI.';
                    Log::info('Task successfully executed.', ['task_id' => $this->task->id]);
                } else {
                    // Gagal dieksekusi oleh FastAPI (status error atau exception dari FastAPI)
                    $errorMessage = $responseData['error'] ?? $responseData['message'] ?? 'Unknown error from FastAPI.';
                    $this->task->status = 4;
                    $this->task->message = $errorMessage;
                    Log::error('Task execution failed on FastAPI.', [
                        'task_id' => $this->task->id,
                        'error_message' => $errorMessage,
                    ]);
                }
            } else {
                // Request ke FastAPI gagal (misal: 404 Not Found, 500 Internal Server Error)
                $this->task->status = 4;
                $this->task->message = 'Failed to call FastAPI: ' . $response->reason();
                Log::error('Failed to call FastAPI.', [
                    'task_id' => $this->task->id,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            // Terjadi exception saat melakukan request (misal: timeout, koneksi ditolak)
            $this->task->status = 4;
            $this->task->message = 'Exception occurred: ' . $e->getMessage();
            Log::error('An exception occurred during task execution.', [
                'task_id' => $this->task->id,
                'exception' => $e->getMessage(),
            ]);
        } finally {
            $this->task->save();
        }
    }
}