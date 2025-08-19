<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Hospital;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ChatRoom; // Add this line to import ChatRoom
use App\Services\FCMService; // Add this line to import FCMService

class AttachmentController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
 public function attachDoctorToHospital(Request $request)
    {
        $validatedData = $request->validate([
            "hospital_email" => "required|email",
        ]);

        $user = Auth::user();
        $hospital = Hospital::where("email", $validatedData["hospital_email"])->first();

        if (!$hospital) {
            return ResponseHelper::error("Hospital with this email does not exist", 404);
        }

        if ($user->account_type !== "doctor") {
            return ResponseHelper::error("Only doctors can attach to hospitals", 403);
        }

        if ($user->attachedHospitals()->where("hospital_id", $hospital->id)->exists()) {
            return ResponseHelper::error("You are already attached to this hospital", 400);
        }

        $user->attachedHospitals()->attach($hospital->id, [
            "status" => "pending",
            "created_at" => Carbon::now(),
            "sender_id" => $user->id,
        ]);

        $attachmentData = $user->attachedHospitals()->where("hospital_id", $hospital->id)->first();

        // Send FCM notification to the hospital
        if ($hospital->fcm_token) {
            $notificationTitle = "New Doctor Attachment Request";
            $notificationBody = "Doctor {$user->name} has requested to attach to your hospital.";
            $notificationData = [
                'type' => 'doctor_attachment_request',
                'doctor_id' => $user->id,
                'doctor_name' => $user->name,
            ];

            Log::info('Attempting to send FCM notification to hospital', [
                'hospital_id' => $hospital->id,
                'hospital_email' => $hospital->email,
                'fcm_token' => $hospital->fcm_token,
              //  'notification_data' => $notificationData
            ]);

            try {
                $response = $this->fcmService->sendNotification(
                    $hospital->fcm_token,
                    $notificationTitle,
                    $notificationBody,
                  //  $notificationData
                );

                Log::info('FCM notification sent successfully to hospital', [
                    'hospital_id' => $hospital->id,
                    'response' => $response
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send FCM notification to hospital', [
                    'hospital_id' => $hospital->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Log::info('FCM notification not sent to hospital', [
                'reason' => 'No FCM token',
                'hospital_id' => $hospital->id
            ]);
        }

        return ResponseHelper::success("Attachment request sent to hospital", $attachmentData);
    }

    public function attachPatientToHospital(Request $request)
    {
        $validatedData = $request->validate([
            "hospital_email" => "required|email",
        ]);

        $user = Auth::user();
        $hospital = Hospital::where(
            "email",
            $validatedData["hospital_email"]
        )->first();

        if (!$hospital) {
            return ResponseHelper::error(
                "Hospital with this email does not exist",
                404
            );
        }

        if ($user->account_type !== "patient") {
            return ResponseHelper::error(
                "Only patients can attach to hospitals",
                403
            );
        }

        // Check if already attached
        if (
            $user
                ->attachedHospitals()
                ->where("hospital_id", $hospital->id)
                ->exists()
        ) {
            return ResponseHelper::error(
                "You are already attached to this hospital",
                400
            );
        }

        DB::table('hospital_user_attachments')->insert([
            "hospital_id" => $hospital->id,
            "user_id" => $user->id,
            "status" => "pending",
            "account_type" => "patient",
            "created_at" => Carbon::now(),
            "sender_id" => $user->id,
        ]);

        // Retrieve the created attachment
        $attachmentData = DB::table('hospital_user_attachments')->where("hospital_id", $hospital->id)->where("user_id", $user->id)->first();

        return ResponseHelper::success(
            "Attachment request sent to hospital",
            $attachmentData
        );
    }

    public function attachPatientToDoctor(Request $request)
    {
        $validatedData = $request->validate([
            "doctor_email" => "required|email",
        ]);

        $patient = Auth::user();
        $doctor = User::where("email", $validatedData["doctor_email"])->first();

        if (!$doctor) {
            return ResponseHelper::error("Doctor with this email does not exist", 404);
        }

        if ($doctor->account_type !== "doctor") {
            return ResponseHelper::error("The provided email does not belong to a doctor", 400);
        }

        if ($doctor->account_status !== "active") {
            return ResponseHelper::error("Doctor is not active", 400);
        }

        if ($patient->account_type !== "patient") {
            return ResponseHelper::error("Only patients can attach to doctors", 403);
        }

        if (DB::table('hospital_user_attachments')->where("doctor_id", $doctor->id)->where("user_id", $patient->id)->exists()) {
            return ResponseHelper::error("You are already attached to this doctor", 400);
        }

        DB::table('hospital_user_attachments')->insert([
            "doctor_id" => $doctor->id,
            "user_id" => $patient->id,
            "status" => "pending",
            "created_at" => Carbon::now(),
            "sender_id" => $patient->id,
            'account_type' => 'patient'
        ]);

        // $patient->attachedDoctors()->attach($doctor->id, [
        //     "status" => "pending",
        //     "created_at" => Carbon::now(),
        //     "sender_id" => $patient->id,
        // ]);

        $attachmentData = DB::table('hospital_user_attachments')->where("doctor_id", $doctor->id)->where("user_id", $patient->id)->first();

        // Send FCM notification to the doctor
        if ($doctor->fcm_token) {
            $notificationTitle = "New Patient Attachment Request";
            $notificationBody = "Patient {$patient->name} has requested to attach to you.";
            //$notificationData = [
              //  'type' => 'patient_attachment_request',
                //'patient_id' => $patient->id,
                //'patient_name' => $patient->name,
            //];

            Log::info('Attempting to send FCM notification to doctor', [
                'doctor_id' => $doctor->id,
                'doctor_email' => $doctor->email,
                'fcm_token' => $doctor->fcm_token,
               // 'notification_data' => $notificationData
            ]);

            try {
                $response = $this->fcmService->sendNotification(
                    $doctor->fcm_token,
                    $notificationTitle,
                    $notificationBody,
                  //  $notificationData
                );

                Log::info('FCM notification sent successfully to doctor', [
                    'doctor_id' => $doctor->id,
                    'response' => $response
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send FCM notification to doctor', [
                    'doctor_id' => $doctor->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Log::info('FCM notification not sent to doctor', [
                'reason' => 'No FCM token',
                'doctor_id' => $doctor->id
            ]);
        }

        return ResponseHelper::success("Attachment request sent to doctor", $attachmentData);
    }
  public function attachHospitalToPatient(Request $request)
    {
        $validatedData = $request->validate([
            "data_email" => "required|email",
        ]);

        $hospital = Auth::user();
        $patient = User::where("email", $validatedData["data_email"])->first();
         Log::info('Validated data:', $validatedData);
        if (!$patient) {
            return ResponseHelper::error("Patient with this email does not exist", 404);
        }

        if ($hospital->account_type !== "hospital") {
            return ResponseHelper::error("Only hospitals can initiate this attachment", 403);
        }

        if ($patient->account_type !== "patient") {
            return ResponseHelper::error("The provided email does not belong to a patient", 400);
        }

        $hospitalModel = Hospital::where("email", $hospital->email)->first();

        if (!$hospitalModel) {
            return ResponseHelper::error("Hospital not found", 404);
        }

        if ($hospitalModel->attachedPatients()->where("user_id", $patient->id)->exists()) {
            return ResponseHelper::error("You are already attached to this patient", 400);
        }

        $hospitalModel->attachedPatients()->attach($patient->id, [
            "status" => "pending",
            "created_at" => Carbon::now(),
            "sender_id" => $hospitalModel->id,
        ]);

        $attachmentData = $hospitalModel->attachedPatients()
            ->where("user_id", $patient->id)
            ->first();

        // Send FCM notification to the patient
        if ($patient->fcm_token) {
            $notificationTitle = "New Hospital Attachment Request";
            $notificationBody = "Hospital {$hospitalModel->name} has requested to attach to you.";
            $notificationData = [
                'type' => 'hospital_attachment_request',
                'hospital_id' => $hospitalModel->id,
                'hospital_name' => $hospitalModel->name,
            ];

            Log::info('Attempting to send FCM notification to patient', [
                'patient_id' => $patient->id,
                'patient_email' => $patient->email,
                'fcm_token' => $patient->fcm_token,
            ]);

            try {
                $response = $this->fcmService->sendNotification(
                    $patient->fcm_token,
                    $notificationTitle,
                    $notificationBody
                );

                Log::info('FCM notification sent successfully to patient', [
                    'patient_id' => $patient->id,
                    'response' => $response
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send FCM notification to patient', [
                    'patient_id' => $patient->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Log::info('FCM notification not sent to patient', [
                'reason' => 'No FCM token',
                'patient_id' => $patient->id
            ]);
        }

        // Prepare detailed return data
        $returnData = [
            'attachment' => [
                'id' => $attachmentData->pivot->id,
                'status' => $attachmentData->pivot->status,
                'created_at' => $attachmentData->pivot->created_at,
                'sender_id' => $attachmentData->pivot->sender_id,
            ],
            'hospital' => [
                'id' => $hospitalModel->id,
                'name' => $hospitalModel->name,
                'email' => $hospitalModel->email,
            ],
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'email' => $patient->email,
            ],
        ];

        return ResponseHelper::success("Attachment request sent to patient", $returnData);
    }

  public function approveAttachment(Request $request)
{
    $validatedData = $request->validate([
        "user_email" => "required|email|exists:users,email",
        // "type" => "required|in:doctor,patient,hospital", // this is not needed
    ]);
    $user = Auth::user();
    $attachedUser = User::where("email", $validatedData["user_email"])->firstOrFail();

    $chatRoomName = '';
    $notificationRecipient = null;

    if ($user->account_type === "hospital") {
        $hospital = Hospital::where("email", $user->email)->firstOrFail();
        if ($attachedUser->account_type === "doctor") {
            DB::table('hospital_user_attachments')->where("doctor_id", $attachedUser->id)->where("hospital_id", $user->id)->update([
                "status" => "approved",
                "updated_at" => Carbon::now(),
            ]);
            $chatRoomName = "Hospital-Doctor Chat";
            $notificationRecipient = $attachedUser;
        } elseif ($attachedUser->account_type === "patient") {
            DB::table('hospital_user_attachments')->where("hospital_id", $user->id)->where("user_id", $attachedUser->id)->update([
                "status" => "approved",
                "updated_at" => Carbon::now(),
            ]);
            $chatRoomName = "Hospital-Patient Chat";
            $notificationRecipient = $attachedUser;
        }
        ChatRoom::create([
            "name" => $chatRoomName,
            "hospital_id" => $hospital->id,
            $attachedUser->account_type . "_id" => $attachedUser->id,
        ]);
    } elseif ($user->account_type === "doctor" && $attachedUser->account_type === "patient") {
        DB::table('hospital_user_attachments')->where("doctor_id", $user->id)->where("user_id", $attachedUser->id)->update([
            "status" => "approved",
            "updated_at" => Carbon::now(),
        ]);
        $chatRoomName = "Doctor-Patient Chat";
        $notificationRecipient = $attachedUser;
        ChatRoom::create([
            "name" => $chatRoomName,
            "doctor_id" => $user->id,
            "patient_id" => $attachedUser->id,
        ]);
    } elseif ($user->account_type === "patient" && $attachedUser->account_type === "doctor") {
        DB::table('hospital_user_attachments')->where("doctor_id", $attachedUser->id)->where("user_id", $user->id)->update([
            "status" => "approved",
            "updated_at" => Carbon::now(),
        ]);
        $chatRoomName = "Patient-Doctor Chat";
        $notificationRecipient = $attachedUser;
        ChatRoom::create([
            "name" => $chatRoomName,
            "doctor_id" => $attachedUser->id,
            "patient_id" => $user->id,
        ]);
    } elseif ($attachedUser->account_type === "hospital") {
        $hospital = Hospital::where("email", $validatedData["user_email"])->firstOrFail();
        DB::table('hospital_user_attachments')->where("doctor_id", $user->id)->where("hospital_id", $hospital->id)->update([
            "status" => "approved",
            "updated_at" => Carbon::now(),
        ]);
        $chatRoomName = "User-Hospital Chat";
        $notificationRecipient = $user;
        ChatRoom::create([
            "name" => $chatRoomName,
            "hospital_id" => $hospital->id,
            "doctor_id" => $user->id,
        ]);
    } else {
        return ResponseHelper::error("Invalid approval request", 403);
    }

    // Send FCM notification if recipient has an FCM token
    if ($notificationRecipient && $notificationRecipient->fcm_token) {
        $notificationTitle = "Attachment Approved";
        $notificationBody = "Your attachment request has been approved.";
       // $notificationData = [
         //   'type' => 'attachment_approved',
           // 'chat_room_name' => $chatRoomName
        //];
        
        Log::info('Attempting to send FCM notification', [
            'recipient_id' => $notificationRecipient->id,
            'recipient_email' => $notificationRecipient->email,
            'fcm_token' => $notificationRecipient->fcm_token,
          //  'notification_data' => $notificationData
        ]);

        try {
            $response = $this->fcmService->sendNotification(
                $notificationRecipient->fcm_token,
                $notificationTitle,
                $notificationBody,
              //  $notificationData
            );

            Log::info('FCM notification sent successfully', [
                'recipient_id' => $notificationRecipient->id,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send FCM notification', [
                'recipient_id' => $notificationRecipient->id,
                'error' => $e->getMessage()
            ]);
        }
    } else {
        Log::info('FCM notification not sent', [
            'reason' => $notificationRecipient ? 'No FCM token' : 'No notification recipient',
            'recipient_id' => $notificationRecipient ? $notificationRecipient->id : null
        ]);
    }

    // Retrieve the updated attachment
    $updatedAttachment = null;
    if ($user->account_type === "hospital") {
        $relation = $attachedUser->account_type === "doctor" ? "attachedDoctors" : "attachedPatients";
        $updatedAttachment = DB::table('hospital_user_attachments')->where("doctor_id", $user->id)->where("user_id", $attachedUser->id)->first();
    } elseif ($attachedUser->account_type === "hospital") {
        $updatedAttachment = DB::table('hospital_user_attachments')->where("doctor_id", $user->id)->where("user_id", $hospital->id)->first();
    } else {
        $relation = $attachedUser->account_type === "doctor" ? "attachedDoctors" : "attachedPatients";
        $updatedAttachment = $user->$relation()->where($attachedUser->account_type . "_id", $attachedUser->id)->first();
    }

    if (!$updatedAttachment) {
        Log::error('Failed to retrieve updated attachment', [
            'user_id' => $user->id,
            'attached_user_id' => $attachedUser->id,
            'type' => $attachedUser->account_type
        ]);
        return ResponseHelper::error("Failed to retrieve updated attachment", 500);
    }

    Log::info('Attachment approved successfully', [
        'user_id' => $user->id,
        'attached_user_id' => $attachedUser->id,
        'type' => $attachedUser->account_type
    ]);

    return ResponseHelper::success("Attachment approved successfully", $updatedAttachment);
}

public function getAttachments(Request $request)
{
    $user = Auth::user();
    Log::info("user:", ["account_type" => $user->account_type]);

    if ($user->account_type === "hospital") {
        $hospital = Hospital::where("email", $user->email)->first();
        if (!$hospital) {
            return ResponseHelper::error("Hospital not found", 404);
        }

        $doctors = DB::table('hospital_user_attachments')->where("hospital_id", $hospital->id)->whereNotNull("hospital_user_attachments.doctor_id")->join('users', 'hospital_user_attachments.doctor_id', '=', 'users.id')->get();
        $patients = DB::table('hospital_user_attachments')->where("hospital_id", $hospital->id)->whereNotNull("hospital_user_attachments.user_id")->join('users', 'hospital_user_attachments.user_id', '=', 'users.id')->get();

        Log::info("Doctors:", $doctors->toArray());
        Log::info("Patients:", $patients->toArray());

        return ResponseHelper::success(
            "Attachments retrieved successfully",
            [
                "doctors" => $doctors,
                "patients" => $patients,
            ]
        );
    } elseif ($user->account_type === "doctor") {
        $patients = DB::table('hospital_user_attachments')->where("doctor_id", $user->id)->whereNotNull("hospital_user_attachments.user_id")->join('users', 'hospital_user_attachments.user_id', '=', 'users.id')->get();
        $hospitals = DB::table('hospital_user_attachments')->where("doctor_id", $user->id)->whereNotNull("hospital_user_attachments.hospital_id")->join('hospitals', 'hospital_user_attachments.hospital_id', '=', 'hospitals.id')->get();
        return ResponseHelper::success(
            "Attachments retrieved successfully",
            [
                "patients" => $patients,
                "hospitals" => $hospitals,
            ]
        );
    } elseif ($user->account_type === "patient") {
        $doctors = DB::table('hospital_user_attachments')->where("user_id", $user->id)->whereNotNull("hospital_user_attachments.doctor_id")->join('users', 'hospital_user_attachments.doctor_id', '=', 'users.id')->get();
        $hospitals = DB::table('hospital_user_attachments')->where("user_id", $user->id)->whereNotNull("hospital_user_attachments.hospital_id")->join('hospitals', 'hospital_user_attachments.hospital_id', '=', 'hospitals.id')->get();
        return ResponseHelper::success(
            "Attachments retrieved successfully",
            [
                "doctors" => $doctors,
                "hospitals" => $hospitals,
            ]
        );
    }

    return ResponseHelper::error("Invalid user type", 403);
}
public function deleteAttachment(Request $request)
{
    $validatedData = $request->validate([
        'attachment_id' => 'required|integer',
        'type' => 'required|in:doctor,patient,hospital',
    ]);

    $user = Auth::user();
    $attachmentId = $validatedData['attachment_id'];
    $type = $validatedData['type'];

    $deletedAttachment = null;

    if ($user->account_type === 'hospital') {
        $hospital = Hospital::where('email', $user->email)->firstOrFail();
        if ($type === 'doctor') {
            $deletedAttachment = $hospital->attachedDoctors()->wherePivot('id', $attachmentId)->first();
            $hospital->attachedDoctors()->wherePivot('id', $attachmentId)->detach();
        } elseif ($type === 'patient') {
            $deletedAttachment = $hospital->attachedPatients()->wherePivot('id', $attachmentId)->first();
            $hospital->attachedPatients()->wherePivot('id', $attachmentId)->detach();
        }
    } elseif ($user->account_type === 'doctor') {
        if ($type === 'patient') {
            $deletedAttachment = $user->attachedPatients()->wherePivot('id', $attachmentId)->first();
            $user->attachedPatients()->wherePivot('id', $attachmentId)->detach();
        } elseif ($type === 'hospital') {
            $deletedAttachment = $user->attachedHospitals()->wherePivot('id', $attachmentId)->first();
            $user->attachedHospitals()->wherePivot('id', $attachmentId)->detach();
        }
    } elseif ($user->account_type === 'patient') {
        if ($type === 'doctor') {
            $deletedAttachment = $user->attachedDoctors()->wherePivot('id', $attachmentId)->first();
            $user->attachedDoctors()->wherePivot('id', $attachmentId)->detach();
        } elseif ($type === 'hospital') {
            $deletedAttachment = $user->attachedHospitals()->wherePivot('id', $attachmentId)->first();
            $user->attachedHospitals()->wherePivot('id', $attachmentId)->detach();
        }
    }

    if (!$deletedAttachment) {
        return ResponseHelper::error('Attachment not found or you do not have permission to delete it', 404);
    }

    // Delete associated chat room if it exists
    ChatRoom::where(function ($query) use ($user, $deletedAttachment, $type) {
        $query->where($user->account_type . '_id', $user->id)
              ->where($type . '_id', $deletedAttachment->id);
    })->delete();

    return ResponseHelper::success('Attachment deleted successfully', $deletedAttachment);
}
    
}