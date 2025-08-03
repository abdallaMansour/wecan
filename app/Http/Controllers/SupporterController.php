<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\SupporterListResource;
use App\Http\Resources\SupporterResource;
use App\Models\Supporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupporterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = request()->all();

        try {
            $supporters = Supporter::query()
                ->where('visible', true)
                ->paginate($filters['per_page'] ?? 10);

            $supporters = new SupporterListResource($supporters);

            if ($supporters->isEmpty()) {
                return ResponseHelper::error('No supporters found', 404, []);
            }

            return ResponseHelper::success('supporters retrieved successfully', $supporters);
        } catch (\Exception $e) {
            Log::error('Error retrieving supporters: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving supporters');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }
 public function addSupporter(Request $request)
    {
         try {
        $validatedData = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $supporter = new Supporter();
        $supporter->name_ar = $validatedData['name_ar'];
        $supporter->name_en = $validatedData['name_en'];
        $supporter->description = $validatedData['description'] ?? null;
        $supporter->visible = 1;
        
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('supporters', 'public');
            $supporter->image = $imagePath;
        } else {
            $supporter->image = null; // Set to null if no image is provided
        }
        
        $supporter->save();
        
        return ResponseHelper::success('Supporter created successfully', new SupporterResource($supporter), 201);
    } catch (\Exception $e) {
        Log::error('Error creating supporter: ' . $e->getMessage());
        return ResponseHelper::error('An error occurred while creating the supporter: ' . $e->getMessage(), 500);
    }
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
