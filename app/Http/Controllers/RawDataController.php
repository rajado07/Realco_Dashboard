<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RawData;


class RawDataController extends Controller
{
    public function create(Request $request)
    {
        try {
            $rawData = new RawData;
            $rawData->type = $request->type;
            $rawData->data = json_encode($request->data);
            $rawData->retrieved_at = $request->retrieved_at;
            $rawData->data_date = $request->data_date;
            $rawData->file_name = $request->file_name;
            $rawData->brand_id = $request->brand_id;
            $rawData->market_place_id = $request->market_place_id;
            $rawData->save();

            return response()->json(['message' => 'Data inserted successfully', 'data' => $rawData], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 400);
        }
    }
}
