<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\PatientAppointmentListResource;
use App\Http\Resources\PatientAppointmentResource;
use App\Models\PatientAppointments;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use App\Models\User;
use App\Models\Hospital;


use Illuminate\Support\Facades\Validator;
class PatientAppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = request()->all();

        try {
            $appointments = PatientAppointments::query()
                ->where('user_id', Auth::id())
                ->paginate($filters['per_page'] ?? 10);

            $appointments = new PatientAppointmentListResource($appointments);

            if ($appointments->isEmpty()) {
                return ResponseHelper::error('No appointments found', 404, []);
            }

            return ResponseHelper::success('appointments retrieved successfully', $appointments);
        } catch (\Exception $e) {
            Log::error('Error retrieving appointments: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving appointments');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
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
            'doctor_name' => 'required|string',
            'datetime' => 'required|date_format:Y-m-d H:i:s',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'show' => 'nullable|in:true,false,0,1',
        ]);

        try {
            $appointment = new PatientAppointments();
            $appointment->fill(Arr::except($validatedData, ['show']));

            $appointment->user_id = auth()->id();
            $appointment->show = $this->parseBoolean($request->input('show'));

            $appointment->save();

            return ResponseHelper::success('appointment created successfully', new PatientAppointmentResource($appointment));
        } catch (\Exception $e) {
            Log::error('Error creating appointment: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while creating appointment');
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
            'doctor_name' => 'required|string',
            'datetime' => 'required|date_format:Y-m-d H:i:s',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'show' => 'nullable|in:true,false,0,1',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed in storeByHospital: ' . json_encode($validator->errors()));
            return ResponseHelper::error($validator->errors(), 422);
        }

        $validatedData = $validator->validated();

        try {
            $appointment = new PatientAppointments();
            $appointment->fill(Arr::except($validatedData, ['show']));
            $appointment->user_id = $validatedData['user_id'];
            $appointment->is_hospital = true;
            $appointment->hospital_id = $hospitalId;
            $appointment->hospital_name = $hospital->hospital_name;
            $appointment->show = $this->parseBoolean($request->input('show', true)); // Default to true if not provided

            $appointment->save();

            Log::info('Appointment created successfully by hospital. Appointment ID: ' . $appointment->id);
            return ResponseHelper::success('Appointment created successfully by hospital', new PatientAppointmentResource($appointment));
        } catch (\Exception $e) {
            Log::error('Error creating appointment by hospital: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while creating appointment: ' . $e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'doctor_name' => 'required|string',
            'datetime' => 'required|date_format:Y-m-d H:i:s',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'show' => 'nullable|in:true,false,0,1',
        ]);

        try {
            $appointment = PatientAppointments::findOrFail($id);

            if ($appointment->user_id != auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $appointment->fill(Arr::except($validatedData, ['show']));
            if ($request->has('show')) {
                $appointment->show = $this->parseBoolean($request->input('show'));
            }

            $appointment->save();

            return ResponseHelper::success('appointment updated successfully', new PatientAppointmentResource($appointment));
        } catch (\Exception $e) {
            Log::error('Error updating appointment: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while updating appointments');
        }
    }
    public function updateByHospital(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'doctor_name' => 'required|string',
            'datetime' => 'required|date_format:Y-m-d H:i:s',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'show' => 'nullable|in:true,false,0,1',
            'hospital_id' => 'required|exists:hospitals,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors());
        }

        try {
            $appointment = PatientAppointments::findOrFail($id);

            // Check if the appointment belongs to the specified hospital
            if (!$appointment->is_hospital || $appointment->hospital_id != $request->hospital_id) {
                return ResponseHelper::error('Unauthorized. This appointment does not belong to your hospital.', 403);
            }

            // Check if the authenticated user belongs to the specified hospital
            $hospital = Hospital::findOrFail($request->hospital_id);
            // Uncomment the following lines if you want to check user's association with the hospital
            // if ($hospital->user_id !== Auth::id()) {
            //     return ResponseHelper::error('Unauthorized. You do not belong to this hospital.', 403);
            // }

            $appointment->fill($request->only(['doctor_name', 'datetime', 'instructions', 'notes', 'show']));
            if ($request->has('show')) {
                $appointment->show = $this->parseBoolean($request->input('show'));
            }

            $appointment->save();

            return ResponseHelper::success('Appointment updated successfully by hospital', new PatientAppointmentResource($appointment));
        } catch (\Exception $e) {
            Log::error('Error updating appointment by hospital: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while updating appointment');
        }
    }
    public function getPatientAppointments(string $email)
    {
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return ResponseHelper::error('User not found', 404);
            }

            $appointments = PatientAppointments::where('user_id', $user->id)
                ->where('show', true)
                ->get();

            if ($appointments->isEmpty()) {
                return ResponseHelper::error('No visible appointments found for this patient', 404);
            }

            return ResponseHelper::success('Patient appointments retrieved successfully', PatientAppointmentResource::collection($appointments));
        } catch (\Exception $e) {
            Log::error('Error retrieving patient appointments: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while retrieving patient appointments');
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $appointment = PatientAppointments::findOrFail($id);

            if ($appointment->user_id != auth()->id()) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $appointment->delete();

            return ResponseHelper::success('appointment deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting appointment: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while deleting appointment');
        }
    }
}
