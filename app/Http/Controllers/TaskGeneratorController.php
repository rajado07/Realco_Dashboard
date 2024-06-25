<?php

namespace App\Http\Controllers;

use App\Models\TaskGenerator;


class TaskGeneratorController extends Controller
{
    public function index()
    {
        $data = TaskGenerator::all();
        return response()->json($data);
    }
}