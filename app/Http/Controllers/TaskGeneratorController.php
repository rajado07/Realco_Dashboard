<?php

namespace App\Http\Controllers;

use App\Models\TaskGenerator;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class TaskGeneratorController extends Controller
{
    public function index()
    {
        $data = TaskGenerator::all();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'brand_id' => 'required|integer',
                'type' => 'required|string',
                'market_place_id' => 'required|integer',
                'frequency' => 'required|string',
                'link' => 'required|string',
                'run_at' => 'required|date_format:H:i:s',
            ]);

            $store = new TaskGenerator;
            $store->brand_id = $validatedData['brand_id'];
            $store->type = $validatedData['type'] ?? null;
            $store->market_place_id = $validatedData['market_place_id'];
            $store->frequency = $validatedData['frequency'];
            $store->link = $validatedData['link'];
            $store->run_at = $validatedData['run_at'];
            $store->save();

            return response()->json(['message' => 'Task Added Successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed : ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function edit(string $id)
    {
        $edit = TaskGenerator::findOrFail($id);

        $data = [
            'brand_id' => $edit->brand_id,
            'market_place_id' => $edit->market_place_id,
            'type' => $edit->type,
            'frequency' => $edit->frequency,
            'link' => $edit->link,
            'run_at' => $edit->run_at,

        ];
        return response()->json($data);
    }


    public function update(Request $request)
    {
        $id = $request->input('id');

        try {
            // Validasi input
            $validatedData = $request->validate([
                'brand_id' => 'required|integer',
                'type' => 'required|string',
                'market_place_id' => 'required|integer',
                'frequency' => 'required|string',
                'link' => 'required|string',
                'run_at' => 'required|date_format:H:i:s',
            ]);

            $taskGenerator = TaskGenerator::findOrFail($id);

            $taskGenerator->fill($validatedData);

            if ($taskGenerator->isDirty()) {
                $taskGenerator->save();

                $tasks = Task::where('task_generator_id', $taskGenerator->id)
                    ->whereIn('status', [1, 4]) // Filter berdasarkan status 1 dan 4
                    ->get();

                // Loop melalui masing-masing Task dan update sesuai dengan TaskGenerator
                foreach ($tasks as $task) {
                    $task->update([
                        'brand_id' => $taskGenerator->brand_id,
                        'type' => $taskGenerator->type,
                        'market_place_id' => $taskGenerator->market_place_id,
                        'link' => $taskGenerator->link,
                    ]);
                }

                return response()->json(['message' => 'Task Generator and related Tasks with status 1 or 4 updated successfully', 'type' => 'success']);
            } else {
                return response()->json(['message' => 'No changes made', 'type' => 'warning']);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed : ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function destroy(string $id)
    {
        try {
            $taskGenerator = TaskGenerator::findOrFail($id);

            // Hapus seluruh Task yang berhubungan dengan TaskGenerator dan memiliki status 1
            Task::where('task_generator_id', $taskGenerator->id)
                ->where('status', 1)
                ->delete();

            $taskGenerator->delete();

            return response()->json(['message' => 'Task Generator and related Tasks Deleted Successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed : ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function getScript()
    {
        try {
            // Kirim request GET ke FastAPI menggunakan Http Facade
            $response = Http::get('http://192.168.20.245:8001/get-script');

            // Cek apakah request berhasil
            if ($response->successful()) {
                // Kembalikan data sebagai response JSON
                return response()->json($response->json());
            } else {
                return response()->json([
                    'error' => 'Failed to fetch scripts',
                    'status' => $response->status()
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Tangani error jika ada
            return response()->json([
                'error' => 'Failed to fetch scripts',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function generateTask(Request $request)
    {
        $id = $request->input('id');
        $dateRange = $request->input('date_range');

        try {
            // Validasi request
            $validatedData = $request->validate([
                'id' => 'required|exists:task_generators,id',
                'date_range' => 'required|string',
            ]);

            $taskGenerator = TaskGenerator::find($id);

            // Cek apakah TaskGenerator ditemukan
            if (!$taskGenerator) {
                return response()->json(['message' => 'Task Generator not found', 'type' => 'error']);
            }

            // Pastikan hanya task dengan type 'daily' yang diperbolehkan
            if (strtolower($taskGenerator->frequency) !== 'daily') {
                return response()->json(['message' => 'Only Task Generators with frequency "daily" are allowed.', 'type' => 'error']);
            }

            $runAtTime = Carbon::parse($taskGenerator->run_at)->format('H:i:s');

            [$startDate, $endDate] = explode(',', $dateRange);
            $startDate = Carbon::parse($startDate);
            $endDate = Carbon::parse($endDate);

            $skippedTasks = 0;
            $createdTasks = 0;

            while ($startDate->lte($endDate)) {
                $scheduledToRun = Carbon::parse($startDate->format('Y-m-d') . ' ' . $runAtTime);

                // Cek apakah task sudah ada di database
                $existingTask = Task::where('brand_id', $taskGenerator->brand_id)
                    ->where('market_place_id', $taskGenerator->market_place_id)
                    ->where('type', $taskGenerator->type)
                    ->where('scheduled_to_run', $scheduledToRun)
                    ->exists();

                if (!$existingTask) {
                    Task::create([
                        'brand_id' => $taskGenerator->brand_id,
                        'market_place_id' => $taskGenerator->market_place_id,
                        'type' => $taskGenerator->type,
                        'link' => $taskGenerator->link,
                        'scheduled_to_run' => $scheduledToRun,
                        'status' => 10,
                        'task_generator_id' => $taskGenerator->id,
                    ]);
                    $createdTasks++;
                } else {
                    $skippedTasks++;
                }

                $startDate->addDay();
            }

            return response()->json([
                'message' => "Task Generated Successfully. Created: $createdTasks, Skipped: $skippedTasks",
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

}
