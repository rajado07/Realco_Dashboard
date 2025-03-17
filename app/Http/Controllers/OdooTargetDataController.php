<?php

namespace App\Http\Controllers;

use App\Models\OdooTargetData;
use Illuminate\Http\Request;

class OdooTargetDataController extends Controller
{
    public function index()
    {
        $data = OdooTargetData::all();
        return response()->json($data);
    }

    public function edit(string $id)
    {
        $edit = OdooTargetData::findOrFail($id);

        $data = [
            'odoo_user' => $edit->odoo_user,
            'target' => $edit->target,
            'type' => $edit->type,
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
                'id' => 'required|exists:odoo_target_data,id', // Pastikan ID valid
                'odoo_user' => 'required|string',
                'target' => 'required|integer',
                'type' => 'required|string',
                'data_date' => 'required|date',
                'brand_id' => 'required|exists:brands,id',
            ]);

            $update = OdooTargetData::findOrFail($id);

            $update->fill($validatedData);

            if ($update->isDirty()) {
                $update->save();
                return response()->json(['message' => 'Odoo Target Updated Successfully', 'type' => 'success']);
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
            $destroy = OdooTargetData::findOrFail($id);
            $destroy->delete();

            return response()->json(['message' => 'Odoo Target Deleted Sucessfully', 'type' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed : ' . $e->getMessage(), 'type' => 'error']);
        }
    }
}
