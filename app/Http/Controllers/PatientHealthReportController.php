<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\PatientHealthReportListResource;
use App\Http\Resources\PatientHealthReportResource;
use App\Models\PatientHealthReport;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Hospital;
use Illuminate\Support\Facades\Validator;
class PatientHealthReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = request()->all();

        try {
            $reports = PatientHealthReport::query()
                ->where('user_id', Auth::id())
                ->paginate($filters['per_page'] ?? 10);

            $reports = new PatientHealthReportListResource($reports);

            if ($reports->isEmpty()) {
                return ResponseHelper::error('No reports found', 404, []);
            }

            return ResponseHelper::success('reports retrieved successfully', $reports);
        } catch (\Exception $e) {
            Log::error('Error retrieving reports: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving reports');
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
            'title' => 'required|string',
            'doctor_name' => 'required|string',
            'datetime' => 'required|string',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachments.*' => 'nullable|file',
            'show' => 'nullable|in:true,false,0,1',
        ]);

        try {
            $reports = new PatientHealthReport();
            $reports->fill(Arr::except($validatedData, ['attachments', 'show']));

            // Handle the 'show' field
            $reports->show = $this->parseBoolean($request->input('show'));

            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $attachment) {
                    $attachmentName = time() . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                    $attachment->storeAs('public/patient_reports_attachments', $attachmentName);
                    $attachments[] = 'patient_reports_attachments/' . $attachmentName;
                }
                $reports->attachments = $attachments;
            }

            $reports->user_id = auth()->id();
            $reports->save();

            return ResponseHelper::success('reports created successfully', new PatientHealthReportResource($reports));
        } catch (\Exception $e) {
            Log::error('Error creating reports: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while creating reports');
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
            'title' => 'required|string',
            'doctor_name' => 'required|string',
            'datetime' => 'required|string',
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
            $report = new PatientHealthReport();
            $report->fill(Arr::except($validatedData, ['attachments', 'show']));
            $report->user_id = $validatedData['user_id'];
            $report->is_hospital = true;
            $report->hospital_id = $hospitalId;
            $report->hospital_name = $hospital->hospital_name;
            $report->show = $this->parseBoolean($request->input('show', true)); // Default to true if not provided

            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $attachment) {
                    $attachmentName = time() . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                    $attachment->storeAs('public/patient_reports_attachments', $attachmentName);
                    $attachments[] = 'patient_reports_attachments/' . $attachmentName;
                }
                $report->attachments = $attachments;
            }

            $report->save();

            Log::info('Health report created successfully by hospital. Report ID: ' . $report->id);
            return ResponseHelper::success('Health report created successfully by hospital', new PatientHealthReportResource($report));
        } catch (\Exception $e) {
            Log::error('Error creating health report by hospital: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while creating health report: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'doctor_name' => 'required|string',
            'datetime' => 'required|string',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachments.*' => 'nullable|file',
            'remove_attachments.*' => 'string', // Array of URLs of attachments to remove
            'show' => 'nullable|in:true,false,0,1',
        ]);

        try {
            $report = PatientHealthReport::findOrFail($id);

            if ($report->user_id != auth()->id()) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $report->fill(Arr::except($validatedData, ['attachments', 'remove_attachments', 'show']));

            // Handle the 'show' field
            if ($request->has('show')) {
                $report->show = $this->parseBoolean($request->input('show'));
            }

            // Define the base URL for attachments
            $baseUrl = config('app.url') . '/storage/';

            // Remove specified attachments
            if (!empty($validatedData['remove_attachments'])) {
                foreach ($validatedData['remove_attachments'] as $attachmentToRemove) {
                    // Extract relative path by removing the base URL if present
                    $relativePath = str_replace($baseUrl, '', $attachmentToRemove);

                    if (in_array($relativePath, $report->attachments)) {
                        // Delete the file from storage
                        Storage::delete('public/' . $relativePath);
                        // Remove the attachment path from the attachments array
                        $report->attachments = array_filter($report->attachments, function ($attachment) use ($relativePath) {
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
                    $attachment->storeAs('public/patient_report_attachments', $attachmentName);
                    $newAttachments[] = 'patient_report_attachments/' . $attachmentName;
                }
            }

            // Merge existing and new attachments, ensuring uniqueness
            $report->attachments = array_unique(array_merge($report->attachments, $newAttachments));

            $report->save();
            return ResponseHelper::success('Report record updated successfully', new PatientHealthReportResource($report));
        } catch (\Exception $e) {
            Log::error('Error updating report record: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while updating report record');
        }
    }
  public function updateByHospital(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'doctor_name' => 'required|string',
            'datetime' => 'required|string',
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
            $report = PatientHealthReport::findOrFail($id);

            // Check if the report belongs to the specified hospital
            if (!$report->is_hospital || $report->hospital_id != $request->hospital_id) {
                return ResponseHelper::error('Unauthorized. This report does not belong to your hospital.', 403);
            }

            // Check if the authenticated user belongs to the specified hospital
            $hospital = Hospital::findOrFail($request->hospital_id);

            $report->fill($request->only(['title', 'doctor_name', 'datetime', 'instructions', 'notes', 'show']));
            if ($request->has('show')) {
                $report->show = $this->parseBoolean($request->input('show'));
            }

            // Define the base URL for attachments
            $baseUrl = config('app.url') . '/storage/';

            // Remove specified attachments
            if (!empty($request->remove_attachments)) {
                foreach ($request->remove_attachments as $attachmentToRemove) {
                    // Extract relative path by removing the base URL if present
                    $relativePath = str_replace($baseUrl, '', $attachmentToRemove);

                    if (in_array($relativePath, $report->attachments)) {
                        // Delete the file from storage
                        Storage::delete('public/' . $relativePath);
                        // Remove the attachment path from the attachments array
                        $report->attachments = array_filter($report->attachments, function ($attachment) use ($relativePath) {
                            return $attachment !== $relativePath;
                        });
                    }
                }
            }

            // Handle new attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $attachment) {
                    $attachmentName = time() . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                    $attachment->storeAs('public/patient_report_attachments', $attachmentName);
                    $report->attachments[] = 'patient_report_attachments/' . $attachmentName;
                }
            }

            $report->save();

            return ResponseHelper::success('Health report updated successfully by hospital', new PatientHealthReportResource($report));
        } catch (\Exception $e) {
            Log::error('Error updating health report by hospital: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while updating health report');
        }
    }
    public function getPatientHealthReports(string $email)
    {
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return ResponseHelper::error('User not found', 404);
            }

            $reports = PatientHealthReport::where('user_id', $user->id)
                ->where('show', true)
                ->get();

            if ($reports->isEmpty()) {
                return ResponseHelper::error('No visible health reports found for this patient', 404);
            }

            return ResponseHelper::success('Patient health reports retrieved successfully', PatientHealthReportResource::collection($reports));
        } catch (\Exception $e) {
            Log::error('Error retrieving patient health reports: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while retrieving patient health reports');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $report = PatientHealthReport::findOrFail($id);

            if ($report->user_id != auth()->id()) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $report->delete();

            return ResponseHelper::success('report deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting report: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while deleting report');
        }
    }
}
