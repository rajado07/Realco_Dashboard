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

}
