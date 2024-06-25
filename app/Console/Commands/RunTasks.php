<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class RunTasks extends Command
{
    protected $signature = 'run:tasks';
    protected $description = 'Run tasks based on status';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Log::info('RunTasks command started.');

        // Periksa apakah ada task yang sedang berjalan (status 3)
        $runningTask = Task::where('status', 3)->first();
        if ($runningTask) {
            Log::info('A task is already running. Waiting for it to finish.');
            return;
        }

        // Ambil task yang berstatus 2 dan paling awal berubah status
        $task = Task::where('status', 2)
            ->orderBy('updated_at', 'asc')
            ->first();

        if ($task) {
            // Update status task menjadi 3 (sedang dijalankan)
            $task->status = 3;
            $task->save();

            // Menentukan script Python yang akan dijalankan berdasarkan tipe task
            $pythonScript = base_path('scripts/' . $task->type . '.py');

            $output = [];
            $return_var = 0;

            try {
                // Jalankan script Python dengan parameter task
                exec("python3 $pythonScript " . escapeshellarg(json_encode($task->toArray())) . " 2>&1", $output, $return_var);
                $output_string = implode("\n", $output);
                Log::info('Python script output: ' . $output_string);

                if ($return_var === 0) {
                    // Update status task menjadi 5 (sukses) jika berhasil dijalankan
                    $task->status = 5;
                    $task->message = null;
                    Log::info('Task executed and status updated to 5 (success)', [
                        'task_id' => $task->id,
                        'type' => $task->type,
                        'scheduled_to_run' => $task->scheduled_to_run,
                    ]);
                } else {
                    // Update status task menjadi 4 (exception) jika terjadi kesalahan
                    $task->status = 4;
                    $task->message = $output_string; // Save the error message
                    Log::error('Task execution failed with non-zero exit code and status updated to 4 (exception)', [
                        'task_id' => $task->id,
                        'exit_code' => $return_var,
                        'error_message' => $output_string,
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

        // Log::info('RunTasks command completed.');
    }
}
