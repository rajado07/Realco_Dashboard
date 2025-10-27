<?php

namespace App\Http\Controllers;

use App\Models\MarketPlace;
use Illuminate\Http\Request;

class MarketPlaceController extends Controller
{

    public function index()
    {
        $data = MarketPlace::all();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
            ]);

            $store = new MarketPlace;
            $store->name = $validatedData['name'];
            $store->description = $validatedData['description'] ?? null;
            $store->save();

            return response()->json(['message' => 'Market Place Added Successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed : ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function edit(string $id)
    {
        $edit = MarketPlace::findOrFail($id);

        $data = [
            'name' => $edit->name,
            'description' => $edit->description,
        ];
        return response()->json($data);
    }

    public function update(Request $request)
    {
        $id = $request->input('id');

        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
            ]);

            $update = MarketPlace::findOrFail($id);

            $update->fill($validatedData);

            if ($update->isDirty()) {
                $update->save();
                return response()->json(['message' => 'Market Place Updated Successfully', 'type' => 'success']);
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
            $destroy = MarketPlace::findOrFail($id);
            $destroy->delete();

            return response()->json(['message' => 'Market Place Deleted Sucessfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed : ' . $e->getMessage(), 'type' => 'error']);
        }
    }
}
