<?php

namespace App\Http\Controllers;

use App\Models\TaskGenerator;
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
            $validatedData = $request->validate([
                'brand_id' => 'required|integer',
                'type' => 'required|string',
                'market_place_id' => 'required|integer',
                'frequency' => 'required|string',
                'link' => 'required|string',
                'run_at' => 'required|date_format:H:i:s',
            ]);

            $update = TaskGenerator::findOrFail($id);

            $update->fill($validatedData);

            if ($update->isDirty()) {
                $update->save();
                return response()->json(['message' => 'Task Updated Successfully', 'type' => 'success']);
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
            $destroy = TaskGenerator::findOrFail($id);
            $destroy->delete();

            return response()->json(['message' => 'Task Deleted Sucessfully', 'type' => 'success']);
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