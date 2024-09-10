<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{

    public function index()
    {
        $data = Brand::all();
        return response()->json($data);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'user_data_dir' => 'required|string',
                'profile_dir' => 'required|string',
                'download_directory' => 'required|string',
                'fast_api_url' => 'required|string',
                'description' => 'nullable|string',
            ]);

            $store = new Brand;
            $store->name = $validatedData['name'];
            $store->user_data_dir = $validatedData['user_data_dir'] ; 
            $store->profile_dir = $validatedData['profile_dir'] ;
            $store->download_directory = $validatedData['download_directory'] ;  
            $store->fast_api_url = $validatedData['fast_api_url'] ; 
            $store->description = $validatedData['description'] ; 
            $store->save();

            return response()->json(['message' => 'Brand Added Successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed : ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function edit(string $id)
    {
        $edit = Brand::findOrFail($id);

        $data = [
            'name' => $edit->name,
            'user_data_dir' => $edit->user_data_dir,
            'profile_dir' => $edit->profile_dir,
            'download_directory' => $edit->download_directory,
            'fast_api_url' => $edit->fast_api_url,
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
                'user_data_dir' => 'required|string',
                'profile_dir' => 'required|string',
                'download_directory' => 'required|string',
                'fast_api_url' => 'required|string',
                'description' => 'nullable|string',
            ]);

            $update = Brand::findOrFail($id);

            $update->fill($validatedData);

            if ($update->isDirty()) {
                $update->save();
                return response()->json(['message' => 'Brand Updated Successfully', 'type' => 'success']);
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
            $destroy = Brand::findOrFail($id);
            $destroy->delete();

            return response()->json(['message' => 'Brand Deleted Sucessfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed : ' . $e->getMessage(), 'type' => 'error']);
        }
    }
}
