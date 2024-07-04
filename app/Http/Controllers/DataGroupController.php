<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataGroup;
use App\Models\TaskGenerator;
use App\Models\ShopeeBrandPortalShopData;




class DataGroupController extends Controller
{
    public function index()
    {
        $data = DataGroup::all();
        return response()->json($data);
    }

    public function create()
    {
        
    }

    public function store()
    {

    }

    public function show()
    {

    }

    public function edit()
    {
        
    }

    public function update()
    {
        
    }

    public function destroy()
    {
        
    }

    public function getDataGroupType()
    {
        $types = TaskGenerator::distinct()->pluck('type');
        return response()->json($types);
    }

    public function getDataGroupByType($type = null)
    {
        if (!$type) {
            return response()->json(['error' => 'Please select type'], 400);
        }
    
        if ($type === 'shopee_brand_portal_shop') {
            $data = ShopeeBrandPortalShopData::select(
                    'product_name as name', 
                    'product_id as id_mapping', 
                    'data_group_id'
                )
                ->groupBy('product_name', 'product_id', 'data_group_id')
                ->get();
            return response()->json($data);
        }
    
        return response()->json([]);
    }

    
}