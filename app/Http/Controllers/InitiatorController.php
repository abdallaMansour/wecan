<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\InitiatorListResource;
use App\Models\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InitiatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = request()->all();

        try {
            $initiators = Administrator::query()
                ->where('visible', true)
                ->paginate($filters['per_page'] ?? 10);

            $initiators = new InitiatorListResource($initiators);

            if ($initiators->isEmpty()) {
                return ResponseHelper::error('No initiators found', 404, []);
            }

            return ResponseHelper::success('initiators retrieved successfully', $initiators);
        } catch (\Exception $e) {
            Log::error('Error retrieving initiators: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving initiators');
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
