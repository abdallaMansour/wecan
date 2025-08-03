<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\CancerScreeningCenterListResource;
use App\Models\CancerScreeningCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CancerScreeningCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = request()->all();

        try {

            $query = CancerScreeningCenter::query();

             if (isset($filters['country_code'])) {
                $query->whereHas('country', function ($query) use ($filters) {
                    $query->where('country_code', $filters['country_code']);
                });
            }


            $centers = $query->where('visible', true)
                ->paginate($filters['per_page'] ?? 10);

            $centers = new CancerScreeningCenterListResource($centers);

            if ($centers->isEmpty()) {
                return ResponseHelper::error('No centers found', 404, []);
            }

            return ResponseHelper::success('centers retrieved successfully', $centers);
        } catch (\Exception $e) {
            Log::error('Error retrieving centers: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving centers');
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
