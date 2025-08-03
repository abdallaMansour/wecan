<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ChemotherapySessionListResource;
use App\Http\Resources\ChemotherapySessionResource;
use App\Models\ChemotherapySession;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class ChemotherapySessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = request()->all();

        try {
            $sessions = ChemotherapySession::query()
                ->where('user_id', Auth::id())
                ->paginate($filters['per_page'] ?? 10);

            $sessions = new ChemotherapySessionListResource($sessions);

            if ($sessions->isEmpty()) {
                return ResponseHelper::error('No sessions found', 404, []);
            }

            return ResponseHelper::success('sessions retrieved successfully', $sessions);
        } catch (\Exception $e) {
            Log::error('Error retrieving sessions: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving sessions');
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
            'session_number' => 'required|string',
            'session_datetime' => 'required|date_format:Y-m-d H:i:s',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'show' => 'nullable|in:true,false,0,1',
        ]);

        try {
            $sessionDateTime = Carbon::parse($validatedData['session_datetime']);

            $session = new ChemotherapySession();
            $session->fill(Arr::except($validatedData, ['show']));

            $session->user_id = auth()->id();
            $session->show = $this->parseBoolean($request->input('show'));

            $session->save();

            return ResponseHelper::success('Chemotherapy session created successfully', new ChemotherapySessionResource($session));
        } catch (\Exception $e) {
            Log::error('Error creating chemotherapy session: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while creating sessions');
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
            'session_number' => 'required|string',
            'session_datetime' => 'required|date_format:Y-m-d H:i:s',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'show' => 'nullable|in:true,false,0,1',
        ]);

        try {
            $session = ChemotherapySession::findOrFail($id);

            if ($session->user_id != auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $session->fill(Arr::except($validatedData, ['show']));
            if ($request->has('show')) {
                $session->show = $this->parseBoolean($request->input('show'));
            }

            $session->save();

            return ResponseHelper::success('Chemotherapy session updated successfully', new ChemotherapySessionResource($session));
        } catch (\Exception $e) {
            Log::error('Error updating chemotherapy session: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while updating sessions');
        }
    }

    public function getPatientChemoSessions(string $email)
    {
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return ResponseHelper::error('User not found', 404);
            }

            $sessions = ChemotherapySession::where('user_id', $user->id)
                ->where('show', true)
                ->get();

            if ($sessions->isEmpty()) {
                return ResponseHelper::error('No visible chemotherapy sessions found for this patient', 404);
            }

            return ResponseHelper::success('Chemotherapy sessions retrieved successfully', ChemotherapySessionResource::collection($sessions));
        } catch (\Exception $e) {
            Log::error('Error retrieving patient chemotherapy sessions: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while retrieving chemotherapy sessions');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $medication = ChemotherapySession::findOrFail($id);

            if ($medication->user_id != auth()->id()) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $medication->delete();

            return ResponseHelper::success('Chemotherapy Session deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting chemotherapy session: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while deleting session');
        }
    }
    
       public function storeByHospital(Request $request)
    {
        Log::info('Incoming request data for storeByHospital: ' . json_encode($request->all()));

        // Get the hospital ID from the authenticated user
        $hospitalId = Auth::user()->hospital_id;

        if (!$hospitalId) {
            Log::error('Authenticated user does not have an associated hospital. User ID: ' . Auth::id());
            return ResponseHelper::error('User is not associated with any hospital.', 403);
        }

        $validator = Validator::make($request->all(), [
            'session_number' => 'required|string',
            'session_datetime' => 'required|date_format:Y-m-d H:i:s',
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
            $session = new ChemotherapySession();
            $session->fill(Arr::except($validatedData, ['show']));
            $session->user_id = $validatedData['user_id'];
            $session->is_hospital = true;
            $session->hospital_id = $hospitalId;
            $session->show = $this->parseBoolean($request->input('show', true)); // Default to true if not provided

            $session->save();

            Log::info('Chemotherapy session created successfully by hospital. Session ID: ' . $session->id);
            return ResponseHelper::success('Chemotherapy session created successfully by hospital', new ChemotherapySessionResource($session));
        } catch (\Exception $e) {
            Log::error('Error creating chemotherapy session by hospital: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while creating chemotherapy session: ' . $e->getMessage());
        }
    }

    public function updateByHospital(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'session_number' => 'required|string',
            'session_datetime' => 'required|date_format:Y-m-d H:i:s',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'show' => 'nullable|in:true,false,0,1',
            'hospital_id' => 'required|exists:hospitals,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors());
        }

        try {
            $session = ChemotherapySession::findOrFail($id);

            // Check if the session belongs to the specified hospital
            if (!$session->is_hospital || $session->hospital_id != $request->hospital_id) {
                return ResponseHelper::error('Unauthorized. This chemotherapy session does not belong to your hospital.', 403);
            }

            // Check if the authenticated user belongs to the specified hospital
            $hospital = Hospital::findOrFail($request->hospital_id);
            // Uncomment the following lines if you want to check user's association with the hospital
            // if ($hospital->user_id !== Auth::id()) {
            //     return ResponseHelper::error('Unauthorized. You do not belong to this hospital.', 403);
            // }

            $session->fill($request->only(['session_number', 'session_datetime', 'instructions', 'notes']));
            if ($request->has('show')) {
                $session->show = $this->parseBoolean($request->input('show'));
            }

            $session->save();

            return ResponseHelper::success('Chemotherapy session updated successfully by hospital', new ChemotherapySessionResource($session));
        } catch (\Exception $e) {
            Log::error('Error updating chemotherapy session by hospital: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while updating chemotherapy session');
        }
    }
}
