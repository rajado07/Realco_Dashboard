<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RawData;


class RawDataController extends Controller
{
    public function index()
    {
        $data = RawData::all();
        return response()->json($data);
    }
}
