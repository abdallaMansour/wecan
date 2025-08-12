<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\SelectHospitalResource;

class HospitalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $hospitals = Hospital::where('account_status', 'active')->get();

            $hospitals = SelectHospitalResource::collection($hospitals);
            if ($hospitals->isEmpty()) {
                return ResponseHelper::error('No hospitals found', 404, []);
            }

            return ResponseHelper::success('Hospitals retrieved successfully', $hospitals);
        } catch (\Exception $e) {
            Log::error('Error retrieving hospitals: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving hospitals');
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
