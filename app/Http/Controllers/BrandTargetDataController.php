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
}
