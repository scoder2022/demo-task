<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Admin\NotificationController as AdminNotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::post('notifications', [NotificationController::class, 'store']);
Route::get('notifications/recent', [NotificationController::class, 'recent']);
Route::get('notifications/summary', [NotificationController::class, 'summary']);


Route::prefix('admin')->middleware(['auth:sanctum','admin'])->group(function () {
    Route::get('/notifications/stats', [AdminNotificationController::class, 'stats']);
    Route::post('/notifications/bulk-retry', [AdminNotificationController::class, 'bulkRetry']);
    Route::post('/notifications/bulk-cancel', [AdminNotificationController::class, 'bulkCancel']);
});

// making a local testing env ro test inside the projects.
Route::post('/webhook-test', function (\Illuminate\Http\Request $request) {

    \Log::info('Webhook received', $request->all());

    return response()->json([
        'message' => 'Webhook received'
    ]);
});

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
