<?php

use App\Models\Hospital;
use Illuminate\Support\Facades\Route;

Route::get('email', function () {
    return view('emails.hospital-status-changed', ['hospital' => Hospital::find(1)]);
});
