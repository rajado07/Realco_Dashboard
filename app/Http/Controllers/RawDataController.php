<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RawData;
use Illuminate\Support\Facades\DB;



class RawDataController extends Controller
{
    public function index()
    {
        $data = RawData::all();
        return response()->json($data);
    }
    
    public function getRawDataStatusCount()
    {
        // Mengambil jumlah uploads berdasarkan status yang ditentukan
        $statusCounts = RawData::select('status', DB::raw('count(*) as total'))
            ->whereIn('status', [1, 2, 3, 4, 5])
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->map(function ($item) {
                return $item->total;
            });

        $allStatusCounts = collect([1, 2, 3, 4, 5])->mapWithKeys(function ($status) use ($statusCounts) {
            return [$status => $statusCounts->get($status, 0)];
        });

        return response()->json($allStatusCounts);
    }
}
