<?php

namespace App\Http\Controllers;

use App\Models\TaskGenerator;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
            $response = Http::get('http://127.0.0.1:8001/get-script');

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
}
