<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\HealthTipListResource;
use App\Http\Resources\HealthTipResource;
use App\Models\HealthTip;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HealthTipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = request()->all();

        try {
            $query = HealthTip::query();

            if (isset($filters['my_tips'])) {
                $query->where('user_id', Auth::id());
            }

            if (isset($filters['tip_type'])) {
                $query->where('tip_type', $filters['tip_type']);
            }

            $tips = $query->orderBy('publish_datetime', 'desc')->paginate($filters['per_page'] ?? 10);
            $tips = new HealthTipListResource($tips);

            if ($tips->isEmpty()) {
                return ResponseHelper::error('No tips found', 404, []);
            }

            return ResponseHelper::success('tips retrieved successfully', $tips);
        } catch (\Exception $e) {
            Log::error('Error retrieving tips: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving tips');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'publish_datetime' => 'required|date_format:Y-m-d H:i:s',
            'title_ar' => 'nullable|string',
            'title_en' => 'nullable|string',
            'details_ar' => 'nullable|string',
            'details_en' => 'nullable|string',
            'attachments.*' => 'nullable|file',
            'link' => 'nullable',
            'tip_type' => 'nullable|string|in:Medication Tips,General Tips,Nutrition Tips,Dosage Tips,Other',
            'visible' => 'nullable',
        ]);

        try {
            $healthTip = new HealthTip();

            $healthTip->fill($validatedData);

            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $attachment) {
                    $attachmentName = time() . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                    $attachment->storeAs('public/health_tip_attachments', $attachmentName);
                    $attachments[] = 'health_tip_attachments/' . $attachmentName;
                }
                $healthTip->attachments = $attachments;
            }

            $healthTip->user_id = auth()->id();

            $healthTip->save();

            return ResponseHelper::success('Health tip created successfully', new HealthTipResource($healthTip));
        } catch (\Exception $e) {
            Log::error('Error creating health tip: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while creating health tip');
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
        $validatedData = $request->validate([
            'publish_datetime' => 'required|date_format:Y-m-d H:i:s',
            'title_ar' => 'nullable|string',
            'title_en' => 'nullable|string',
            'details_ar' => 'nullable|string',
            'details_en' => 'nullable|string',
            'attachments.*' => 'nullable|file',
            'link' => 'nullable',
            'tip_type' => 'nullable|string|in:Medication Tips,General Tips,Nutrition Tips,Dosage Tips,Other',
            'visible' => 'nullable',
        ]);


        try {
            // Find the health tip by ID
            $healthTip = HealthTip::findOrFail($id);

            if ($healthTip->user_id != auth()->id()) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $healthTip->fill(Arr::except($validatedData, ['attachments']));

            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $attachment) {
                    $attachmentName = time() . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                    $attachment->storeAs('public/health_tip_attachments', $attachmentName);
                    $attachments[] = 'health_tip_attachments/' . $attachmentName;
                }
                $healthTip->attachments = $attachments;
            }

            $healthTip->save();

            return ResponseHelper::success('Health tip updated successfully', new HealthTipResource($healthTip));
        } catch (\Exception $e) {
            Log::error('Error updating health tip: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while updating health tip');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $tip = HealthTip::findOrFail($id);

            if ($tip->user_id != auth()->id()) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $tip->delete();

            return ResponseHelper::success('tip deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting tip: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while deleting tip');
        }
    }
}
