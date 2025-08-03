<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\DoctorListResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = request()->all();

        try {
            $doctors = User::query()
                ->where('show_info_to_patients', true)
                ->where('account_type', 'doctor')
                ->paginate($filters['per_page'] ?? 10);

            $doctors = new DoctorListResource($doctors);

            if ($doctors->isEmpty()) {
                return ResponseHelper::error('No doctors found', 404, []);
            }

            return ResponseHelper::success('doctors retrieved successfully', $doctors);
        } catch (\Exception $e) {
            Log::error('Error retrieving doctors: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving doctors');
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
