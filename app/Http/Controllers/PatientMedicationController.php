<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\PatientMedicationListResource;
use App\Http\Resources\PatientMedicationResource;
use App\Models\PatientMedications;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PatientMedicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = request()->all();

        try {
            $medications = PatientMedications::query()
                ->where('user_id', Auth::id())
                ->paginate($filters['per_page'] ?? 10);

            $medications = new PatientMedicationListResource($medications);

            if ($medications->isEmpty()) {
                return ResponseHelper::error('No medications found', 404, []);
            }

            return ResponseHelper::success('medications retrieved successfully', $medications);
        } catch (\Exception $e) {
            Log::error('Error retrieving medications: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving medications');
        }
    }
   private function parseBoolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'drug_image' => 'nullable|image',
            'drug_name' => 'required|string',
            'frequency' => 'nullable|integer',
            'frequency_per' => 'nullable|string|in:day,week,month',
            'instructions' => 'nullable|string',
            'duration' => 'nullable|integer',
            'show' => 'nullable|in:true,false,0,1',
        ]);

        try {
            $medication = new PatientMedications();
            $medication->user_id = auth()->id();
            $medication->fill(Arr::except($validatedData, ['drug_image', 'show']));

            // Handle the 'show' field
            $medication->show = $this->parseBoolean($request->input('show'));

            if ($request->hasFile('drug_image')) {
                $image = $request->file('drug_image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/drug_images', $imageName);
                $medication->drug_image = 'drug_images/' . $imageName;
            }

            $medication->save();

            return ResponseHelper::success('Medication created successfully', new PatientMedicationResource($medication));
        } catch (\Exception $e) {
            Log::error('Error creating medication: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while creating medication');
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
            'drug_image' => 'nullable|image',
            'drug_name' => 'required|string',
            'frequency' => 'nullable|integer',
            'frequency_per' => 'nullable|string|in:day,week,month',
            'instructions' => 'nullable|string',
            'duration' => 'nullable|integer',
            'show' => 'nullable|in:true,false,0,1',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed in storeByHospital: ' . json_encode($validator->errors()));
            return ResponseHelper::error($validator->errors(), 422);
        }

        $validatedData = $validator->validated();

        try {
            $medication = new PatientMedications();
            $medication->fill(Arr::except($validatedData, ['drug_image', 'show']));
            $medication->user_id = $validatedData['user_id'];
            $medication->is_hospital = true;
            $medication->hospital_id = $hospitalId;
            $medication->hospital_name = $hospital->hospital_name; // Store the hospital name
            $medication->show = $this->parseBoolean($request->input('show', true)); // Default to true if not provided

            if ($request->hasFile('drug_image')) {
                $image = $request->file('drug_image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/drug_images', $imageName);
                $medication->drug_image = 'drug_images/' . $imageName;
            }

            $medication->save();

            Log::info('Medication created successfully by hospital. Medication ID: ' . $medication->id);
            return ResponseHelper::success('Medication created successfully by hospital', new PatientMedicationResource($medication));
        } catch (\Exception $e) {
            Log::error('Error creating medication by hospital: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while creating medication: ' . $e->getMessage());
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
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'drug_image' => 'nullable|image',
            'drug_name' => 'required|string',
            'frequency' => 'nullable|integer',
            'frequency_per' => 'nullable|string|in:day,week,month',
            'instructions' => 'nullable|string',
            'remove_image' => 'nullable',
            'duration' => 'nullable|integer',
            'show' => 'nullable|in:true,false,0,1',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors());
        }

        try {
            $medication = PatientMedications::findOrFail($id);

            if ($medication->user_id != auth()->id()) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $medication->fill($request->only(['drug_name', 'frequency', 'frequency_per', 'instructions', 'duration', 'show']));
            if ($request->has('show')) {
                $medication->show = $this->parseBoolean($request->input('show'));
            }
            if ($request->has('remove_image') && $request->remove_image) {
                if ($medication->drug_image) {
                    Storage::delete('public/' . $medication->drug_image);
                    $medication->drug_image = null;
                }
            } else if ($request->hasFile('drug_image')) {
                if ($medication->drug_image) {
                    Storage::delete('public/' . $medication->drug_image);
                }

                $image = $request->file('drug_image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/drug_images', $imageName);
                $medication->drug_image = 'drug_images/' . $imageName;
            }

            $medication->save();

            return ResponseHelper::success('Medication updated successfully', new PatientMedicationResource($medication));
        } catch (\Exception $e) {
            Log::error('Error updating medication: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while updating medication');
        }
    }
    public function updateByHospital(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'drug_image' => 'nullable|image',
            'drug_name' => 'required|string',
            'frequency' => 'nullable|integer',
            'frequency_per' => 'nullable|string|in:day,week,month',
            'instructions' => 'nullable|string',
            'remove_image' => 'nullable',
            'duration' => 'nullable|integer',
            'show' => 'nullable|in:true,false,0,1',
            'hospital_id' => 'required|exists:hospitals,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors());
        }

        try {
            $medication = PatientMedications::findOrFail($id);

            // Check if the medication belongs to the specified hospital
            if (!$medication->is_hospital || $medication->hospital_id != $request->hospital_id) {
                return ResponseHelper::error('Unauthorized. This medication does not belong to your hospital.', 403);
            }

            // Check if the authenticated user belongs to the specified hospital
            $hospital = Hospital::findOrFail($request->hospital_id);
          //  if ($hospital->user_id !== Auth::id()) {
             //   return ResponseHelper::error('Unauthorized. You do not belong to this hospital.', 403);
            //}

            $medication->fill($request->only(['drug_name', 'frequency', 'frequency_per', 'instructions', 'duration', 'show']));
            if ($request->has('show')) {
                $medication->show = $this->parseBoolean($request->input('show'));
            }

            if ($request->has('remove_image') && $request->remove_image) {
                if ($medication->drug_image) {
                    Storage::delete('public/' . $medication->drug_image);
                    $medication->drug_image = null;
                }
            } else if ($request->hasFile('drug_image')) {
                if ($medication->drug_image) {
                    Storage::delete('public/' . $medication->drug_image);
                }

                $image = $request->file('drug_image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/drug_images', $imageName);
                $medication->drug_image = 'drug_images/' . $imageName;
            }

            $medication->save();

            return ResponseHelper::success('Medication updated successfully by hospital', new PatientMedicationResource($medication));
        } catch (\Exception $e) {
            Log::error('Error updating medication by hospital: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while updating medication');
        }
    }
    public function getPatientMedications(string $email)
    {
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return ResponseHelper::error('User not found', 404);
            }

            $medications = PatientMedications::where('user_id', $user->id)
                ->where('show', true)
                ->get();

            if ($medications->isEmpty()) {
                return ResponseHelper::error('No visible medications found for this patient', 404);
            }

            return ResponseHelper::success('Patient medications retrieved successfully', PatientMedicationResource::collection($medications));
        } catch (\Exception $e) {
            Log::error('Error retrieving patient medications: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while retrieving patient medications');
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $medication = PatientMedications::findOrFail($id);

            if ($medication->user_id != auth()->id()) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $medication->delete();

            return ResponseHelper::success('Medication deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting medication: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while deleting medication');
        }
    }
}
