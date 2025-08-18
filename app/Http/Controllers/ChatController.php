<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\User; // Assuming User model has the fcm_token field
use App\Services\FCMService; // Import the FCMService
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function createRoom(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'doctor_id' => 'required|exists:users,id',
            'patient_id' => 'required|exists:users,id',
            'hospital_id' => 'nullable|exists:hospitals,id',
        ]);

        $chatRoom = ChatRoom::create($validated);

        return response()->json($chatRoom, 201);
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'chat_room_id' => 'required|exists:chat_rooms,id',
            // 'message_type' => 'required|in:text,media',
            'message' => 'nullable|required_if:attachment_path,null|string',
            'attachment_path' => 'nullable|required_if:message,null|file',
        ]);

        $user = Auth::user();

        if (!in_array($validated['chat_room_id'], $user->chatDoctorRooms()->pluck('id')->toArray()) && !in_array($validated['chat_room_id'], $user->chatPatientRooms()->pluck('id')->toArray())) {
            return response()->json([
                'message' => 'You are not authorized to send message in this chat room',
            ], 401);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment_path')) {
            $attachmentPath = $request->file('attachment_path')->store('chat-attachments', 'public');
        }

        $message = ChatMessage::create([
            'chat_room_id' => $validated['chat_room_id'],
            'user_id' => $user->id,
            'message' => $validated['message'],
            // 'message_type' => $validated['message_type'],
            'attachment_path' => $attachmentPath,
        ]);

        // Fetch the users in the chat room
        $chatRoom = ChatRoom::with(['doctor', 'patient', 'hospital'])->find($validated['chat_room_id']);
        $recipients = [$chatRoom->doctor, $chatRoom->patient];

        if ($chatRoom->hospital) {
            $recipients[] = $chatRoom->hospital;
        }

        try {
            // Send FCM notification to each recipient
            foreach ($recipients as $recipient) {
                if ($recipient->fcm_token && $recipient->id !== $user->id) {
                    $response = $this->fcmService->sendNotification(
                        $recipient->fcm_token,
                        'New Message from ' . $user->name,
                        $validated['message']
                    );

                    if (isset($response['error'])) {
                        Log::error('FCM Notification Error', [
                            'recipient_id' => $recipient->id,
                            'error' => $response['error'],
                        ]);
                    } else {
                        Log::info('FCM Notification Sent', [
                            'recipient_id' => $recipient->id,
                            'message_id' => $response['name'],
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('FCM Notification Exception', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json([
            'message' => $message,
            'user' => $user->only(['id', 'name']),
        ], 201);
    }

    public function getRoomMessages(Request $request)
    {
        $validated = $request->validate([
            'chat_room_id' => 'required|exists:chat_rooms,id',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 50;
        $page = $validated['page'] ?? 1;

        if (!in_array($validated['chat_room_id'], auth()->user()->chatDoctorRooms()->pluck('id')->toArray()) && !in_array($validated['chat_room_id'], auth()->user()->chatPatientRooms()->pluck('id')->toArray())) {
            return response()->json([
                'message' => 'You are not authorized to view this chat room',
            ], 401);
        }

        $messages = ChatMessage::where('chat_room_id', $validated['chat_room_id'])
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        // mark as read
        ChatMessage::where('chat_room_id', $validated['chat_room_id'])->where('user_id', '!=', auth()->user()->id)->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function getUserRooms(Request $request)
    {
        $user = auth()->user();
        $userType = $user->account_type;

        $query = ChatRoom::query();

        if ($userType === 'doctor') {
            $query->where('doctor_id', $user->id);
        } elseif ($userType === 'patient') {
            $query->where('patient_id', $user->id);
        } elseif ($userType === 'hospital') {
            $query->where('hospital_id', $user->hospital_id);
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('doctor_id', $user->id)
                    ->orWhere('patient_id', $user->id)
                    ->orWhere('hospital_id', $user->hospital_id);
            });
        }

        $rooms = $query->with(['doctor', 'patient', 'hospital'])->get();

        return response()->json($rooms);
    }
}
