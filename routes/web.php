<?php

use Illuminate\Support\Facades\Route;
use App\Services\FCMService;

Route::get('test-fcm/{id}', function ($id) {
    $fcmService = new FCMService();
    $fcmService->sendTopicNotification($id, 'Test Title', 'Test Message');
    return 'Test FCM';
});
