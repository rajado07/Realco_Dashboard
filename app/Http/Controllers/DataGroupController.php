<?php

namespace App\Http\Controllers;

use App\Models\DataGroup;


class DataGroupController extends Controller
{
    public function index()
    {
        $data = DataGroup::all();
        return response()->json($data);
    }
}