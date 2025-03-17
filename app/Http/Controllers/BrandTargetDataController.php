<?php

namespace App\Http\Controllers;
use App\Models\BrandTargetData;
use Illuminate\Http\Request;

class BrandTargetDataController extends Controller
{
    public function index()
    {
        $data = BrandTargetData::all();
        return response()->json($data);
    }

    public function edit(string $id)
    {
        $edit = BrandTargetData::findOrFail($id);

        $data = [
            'sub_brand_name' => $edit->sub_brand_name,
            'target_nmv' => $edit->target_nmv,
            'target_ads_to_nmv' => $edit->target_ads_to_nmv,
            'composition_cpas' => $edit->target_ads_to_nmv,
            'composition_iklanku' => $edit->target_ads_to_nmv,
            'data_date' => $edit->data_date,
            'brand_id' => $edit->brand_id,
        ];
        return response()->json($data);
    }

    public function update(Request $request)
    {
        $id = $request->input('id');

        try {
            $validatedData = $request->validate([
                'id' => 'required|exists:brand_target_data,id', // Pastikan ID valid
                'sub_brand_name' => 'required|string',
                'target_nmv' => 'required|integer',
                'target_ads_to_nmv' => 'required|integer',
                'composition_cpas' => 'required|integer',
                'composition_iklanku' => 'required|integer',
                'data_date' => 'required|date',
                'brand_id' => 'required|exists:brands,id',
            ]);

            $update = BrandTargetData::findOrFail($id);

            $update->fill($validatedData);

            if ($update->isDirty()) {
                $update->save();
                return response()->json(['message' => 'Brand Target Updated Successfully', 'type' => 'success']);
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
            $destroy = BrandTargetData::findOrFail($id);
            $destroy->delete();

            return response()->json(['message' => 'Brand Target Deleted Sucessfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed : ' . $e->getMessage(), 'type' => 'error']);
        }
    }
}
