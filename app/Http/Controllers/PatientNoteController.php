<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\PatientNoteListResource;
use App\Http\Resources\PatientNoteResource;
use App\Models\PatientNote;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PatientNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = request()->all();

        try {
            $notes = PatientNote::query()
                ->where('user_id', Auth::id())
                ->paginate($filters['per_page'] ?? 10);

            $notes = new PatientNoteListResource($notes);

            if ($notes->isEmpty()) {
                return ResponseHelper::error('No notes found', 404, []);
            }

            return ResponseHelper::success('notes retrieved successfully', $notes);
        } catch (\Exception $e) {
            Log::error('Error retrieving notes: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while retrieving notes');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'datetime' => 'required|string',
            'notes' => 'nullable|string',
            'attachments.*' => 'nullable|file',
        ]);

        try {

            $note = new PatientNote();

            $note->fill($validatedData);

            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $attachment) {
                    $attachmentName = time() . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                    $attachment->storeAs('public/patient_note_attachments', $attachmentName);
                    $attachments[] = 'patient_note_attachments/' . $attachmentName;
                }
                $note->attachments = $attachments;
            }

            $note->user_id = auth()->id();

            $note->save();

            return ResponseHelper::success('note created successfully', new PatientNoteResource($note));
        } catch (\Exception $e) {
            Log::error('Error creating note: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while creating note');
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
            'title' => 'required|string',
            'datetime' => 'required|string',
            'notes' => 'nullable|string',
            'attachments.*' => 'nullable|file',
            'remove_attachments.*' => 'string', // Array of URLs of attachments to remove
        ]);

        try {
            $note = PatientNote::findOrFail($id);

            if ($note->user_id != auth()->id()) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $note->fill(Arr::except($validatedData, ['attachments', 'remove_attachments']));

            // Define the base URL for attachments
            $baseUrl = config('app.url') . '/storage/';
            
            // Remove specified attachments
            if (!empty($validatedData['remove_attachments'])) {
                foreach ($validatedData['remove_attachments'] as $attachmentToRemove) {
                    // Extract relative path by removing the base URL if present
                    $relativePath = str_replace($baseUrl, '', $attachmentToRemove);

                    if (in_array($relativePath, $note->attachments)) {
                        // Delete the file from storage
                        Storage::delete('public/' . $relativePath);
                        // Remove the attachment path from the attachments array
                        $note->attachments = array_filter($note->attachments, function ($attachment) use ($relativePath) {
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
                    $attachment->storeAs('public/patient_note_attachments', $attachmentName);
                    $newAttachments[] = 'patient_note_attachments/' . $attachmentName;
                }
            }

            // Merge existing and new attachments, ensuring uniqueness
            $note->attachments = array_unique(array_merge($note->attachments, $newAttachments));

            $note->save();
            return ResponseHelper::success('Note record updated successfully', new PatientNoteResource($note));
        } catch (\Exception $e) {
            Log::error('Error updating note record: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while updating note record');
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $note = PatientNote::findOrFail($id);

            if ($note->user_id != auth()->id()) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $note->delete();

            return ResponseHelper::success('note deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting note: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while deleting note');
        }
    }
}
