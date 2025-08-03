<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\CharityListResource;
use App\Models\Charity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CharityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = request()->all();

        try {
               $query = Charity::query();

            if (isset($filters['country_code'])) {
                $query->whereHas('country', function ($query) use ($filters) {
                    $query->where('country_code', $filters['country_code']);
                });
            }

            $charities = $query->paginate($filters['per_page'] ?? 10);


            $charities = new CharityListResource($charities);

            if ($charities->isEmpty()) {
                return ResponseHelper::error('No charities found', 404, []);
            }

            return ResponseHelper::success('charities retrieved successfully', $charities);
        } catch (\Exception $e) {
            Log::error('Error retrieving charities: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving charities');
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
