<?php

namespace App\Http\Controllers;

use App\Models\Task;


class TaskController extends Controller
{
    public function index()
    {
        $data = Task::all();
        return response()->json($data);
    }
}