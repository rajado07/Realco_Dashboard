<?php

namespace App\Http\Controllers;

use App\Models\MarketPlace;
use Illuminate\Http\Request;

class MarketPlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = MarketPlace::all();
        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(MarketPlace $marketPlace)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MarketPlace $marketPlace)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MarketPlace $marketPlace)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MarketPlace $marketPlace)
    {
        //
    }
}
