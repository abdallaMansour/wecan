<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $regions = Region::where('visible', true)->get();

            $regions = RegionResource::collection($regions);
            if ($regions->isEmpty()) {
                return ResponseHelper::error('No regions found', 404, []);
            }

            return ResponseHelper::success('regions retrieved successfully', $regions);
        } catch (\Exception $e) {
            Log::error('Error retrieving regions: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving regions');
        }
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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
