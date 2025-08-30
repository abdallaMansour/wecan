<?php

use Illuminate\Support\Facades\Route;
use App\Services\FCMService;

Route::get('test-fcm/{id}', function ($id) {
    $fcmService = new FCMService();
    return $fcmService->sendTopicNotification($id, 'Test Title', 'Test Message');
});
