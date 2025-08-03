<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\PatientFoodListResource;
use App\Http\Resources\PatientFoodResource;
use App\Models\PatientFood;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class PatientFoodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = request()->all();

        try {
            $foods = PatientFood::query()
                ->where('user_id', Auth::id())

                ->paginate($filters['per_page'] ?? 10);

            $foods = new PatientFoodListResource($foods);

            if ($foods->isEmpty()) {
                return ResponseHelper::error('No foods found', 404, []);
            }

            return ResponseHelper::success('foods retrieved successfully', $foods);
        } catch (\Exception $e) {
            Log::error('Error retrieving foods: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving foods');
        }
    }
    private function parseBoolean($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'food_name' => 'required|string',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachments.*' => 'nullable|file',
            'show' => 'nullable|in:true,false,0,1',
        ]);

        try {
            $food = new PatientFood();
            $food->fill(Arr::except($validatedData, ['attachments', 'show']));

            $food->user_id = auth()->id();
            $food->show = $this->parseBoolean($request->input('show'));

            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $attachment) {
                    $attachmentName = time() . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                    $attachment->storeAs('public/patient_food_attachments', $attachmentName);
                    $attachments[] = 'patient_food_attachments/' . $attachmentName;
                }
                $food->attachments = $attachments;
            }

            $food->save();

            return ResponseHelper::success('Food created successfully', new PatientFoodResource($food));
        } catch (\Exception $e) {
            Log::error('Error creating food: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while creating food');
        }
    }

  public function storeByHospital(Request $request)
    {
        Log::info('Incoming request data for storeByHospital: ' . json_encode($request->all()));

        // Get the hospital ID from the authenticated user
        $hospitalId = Auth::user()->hospital_id;
        $hospital = Hospital::find(Auth::user()->hospital_id);
        if (!$hospitalId) {
            Log::error('Authenticated user does not have an associated hospital. User ID: ' . Auth::id());
            return ResponseHelper::error('User is not associated with any hospital.', 403);
        }

        $validator = Validator::make($request->all(), [
            'food_name' => 'required|string',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachments.*' => 'nullable|file',
            'show' => 'nullable|in:true,false,0,1',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed in storeByHospital: ' . json_encode($validator->errors()));
            return ResponseHelper::error($validator->errors(), 422);
        }

        $validatedData = $validator->validated();

        try {
            $food = new PatientFood();
            $food->fill(Arr::except($validatedData, ['attachments', 'show']));
            $food->user_id = $validatedData['user_id'];
            $food->is_hospital = true;
            $food->hospital_id = $hospitalId;
            $food->hospital_name = $hospital->hospital_name;
            $food->show = $this->parseBoolean($request->input('show', true)); // Default to true if not provided

            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $attachment) {
                    $attachmentName = time() . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                    $attachment->storeAs('public/patient_food_attachments', $attachmentName);
                    $attachments[] = 'patient_food_attachments/' . $attachmentName;
                }
                $food->attachments = $attachments;
            }

            $food->save();

            Log::info('Food record created successfully by hospital. Food ID: ' . $food->id);
            return ResponseHelper::success('Food record created successfully by hospital', new PatientFoodResource($food));
        } catch (\Exception $e) {
            Log::error('Error creating food record by hospital: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while creating food record: ' . $e->getMessage());
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
            'food_name' => 'required|string',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachments.*' => 'nullable|file',
            'remove_attachments.*' => 'string', // Array of URLs of attachments to remove
            'show' => 'nullable|in:true,false,0,1',
        ]);

        try {
            $food = PatientFood::findOrFail($id);

            if ($food->user_id != auth()->id()) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $food->fill(Arr::except($validatedData, ['attachments', 'remove_attachments', 'show']));

            // Handle the 'show' field
            if ($request->has('show')) {
                $food->show = $this->parseBoolean($request->input('show'));
            }

            // Define the base URL for attachments
            $baseUrl = config('app.url') . '/storage/';

            // Remove specified attachments
            if (!empty($validatedData['remove_attachments'])) {
                foreach ($validatedData['remove_attachments'] as $attachmentToRemove) {
                    // Extract relative path by removing the base URL if present
                    $relativePath = str_replace($baseUrl, '', $attachmentToRemove);

                    if (in_array($relativePath, $food->attachments)) {
                        // Delete the file from storage
                        Storage::delete('public/' . $relativePath);
                        // Remove the attachment path from the attachments array
                        $food->attachments = array_filter($food->attachments, function ($attachment) use ($relativePath) {
                            return $attachment !== $relativePath;
                        });
                    }
                }
            }

            // Handle new attachments
            $newAttachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $attachment) {
                    $attachmentName = time() . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                    $attachment->storeAs('public/patient_food_attachments', $attachmentName);
                    $newAttachments[] = 'patient_food_attachments/' . $attachmentName;
                }
            }

            // Merge existing and new attachments, ensuring uniqueness
            $food->attachments = array_unique(array_merge($food->attachments, $newAttachments));

            $food->save();
            return ResponseHelper::success('Food record updated successfully', new PatientFoodResource($food));
        } catch (\Exception $e) {
            Log::error('Error updating food record: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while updating food record');
        }
    }
    
        public function updateByHospital(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'food_name' => 'required|string',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachments.*' => 'nullable|file',
            'remove_attachments.*' => 'string',
            'show' => 'nullable|in:true,false,0,1',
            'hospital_id' => 'required|exists:hospitals,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors());
        }

        try {
            $food = PatientFood::findOrFail($id);

            // Check if the food record belongs to the specified hospital
            if (!$food->is_hospital || $food->hospital_id != $request->hospital_id) {
                return ResponseHelper::error('Unauthorized. This food record does not belong to your hospital.', 403);
            }

            // Check if the authenticated user belongs to the specified hospital
            $hospital = Hospital::findOrFail($request->hospital_id);
            // Uncomment the following lines if you want to check user's association with the hospital
            // if ($hospital->user_id !== Auth::id()) {
            //     return ResponseHelper::error('Unauthorized. You do not belong to this hospital.', 403);
            // }

            $food->fill($request->only(['food_name', 'instructions', 'notes', 'show']));
            if ($request->has('show')) {
                $food->show = $this->parseBoolean($request->input('show'));
            }

            // Handle attachments
            $baseUrl = config('app.url') . '/storage/';

            // Remove specified attachments
            if (!empty($request->remove_attachments)) {
                foreach ($request->remove_attachments as $attachmentToRemove) {
                    $relativePath = str_replace($baseUrl, '', $attachmentToRemove);
                    if (in_array($relativePath, $food->attachments)) {
                        Storage::delete('public/' . $relativePath);
                        $food->attachments = array_filter($food->attachments, function ($attachment) use ($relativePath) {
                            return $attachment !== $relativePath;
                        });
                    }
                }
            }

            // Add new attachments
            if ($request->hasFile('attachments')) {
                $newAttachments = [];
                foreach ($request->file('attachments') as $attachment) {
                    $attachmentName = time() . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                    $attachment->storeAs('public/patient_food_attachments', $attachmentName);
                    $newAttachments[] = 'patient_food_attachments/' . $attachmentName;
                }
                $food->attachments = array_unique(array_merge($food->attachments, $newAttachments));
            }

            $food->save();

            return ResponseHelper::success('Food record updated successfully by hospital', new PatientFoodResource($food));
        } catch (\Exception $e) {
            Log::error('Error updating food record by hospital: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while updating food record');
        }
    }
    
    public function getPatientFoods(string $email)
    {
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return ResponseHelper::error('User not found', 404);
            }

            $foods = PatientFood::where('user_id', $user->id)
                ->where('show', true)
                ->get();

            if ($foods->isEmpty()) {
                return ResponseHelper::error('No visible foods found for this patient', 404);
            }

            return ResponseHelper::success('Patient foods retrieved successfully', PatientFoodResource::collection($foods));
        } catch (\Exception $e) {
            Log::error('Error retrieving patient foods: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while retrieving patient foods');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $food = PatientFood::findOrFail($id);

            if ($food->user_id != auth()->id()) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $food->delete();

            return ResponseHelper::success('food deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting food: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while deleting food');
        }
    }
}
