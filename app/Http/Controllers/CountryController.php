<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $countries = Country::where('visible', true)->get();

            $countries = CountryResource::collection($countries);
            if ($countries->isEmpty()) {
                return ResponseHelper::error('No countries found', 404, []);
            }

            return ResponseHelper::success('Countries retrieved successfully', $countries);
        } catch (\Exception $e) {
            Log::error('Error retrieving countries: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving countries');
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
