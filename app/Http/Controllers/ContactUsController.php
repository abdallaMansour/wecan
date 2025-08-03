<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\ContactUs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactUsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required'
        ]);

        try {
            $form = new ContactUs();
            $form->name = $validatedData['name'];
            $form->email = $validatedData['email'];
            $form->subject = $validatedData['subject'];
            $form->datetime = Carbon::now();

            $form->save();

            return ResponseHelper::success('Form created successfully');
        } catch (\Exception $e) {
            Log::error('Error creating form: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while creating form');
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
