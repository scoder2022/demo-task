<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\DTOs\NotificationData;
use App\Http\Requests\StoreNotificationRequest;

class NotificationController extends Controller
{
    protected NotificationService $service;

    public function __construct(NotificationService $service)
    {
        $this->service = $service;
    }

    
    public function store(StoreNotificationRequest $request)
    {
        $validated = $request->validated();
        // DTO
        $data = new NotificationData($validated);
        // Publish via service
        try {
            $notification = $this->service->publish($data);
            return response()->json([
                'success' => true,
                'notification_id' => $notification->id,
                'status' => $notification->status,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                429,
            ); // 429 toi many requests
        }
    }


    public function recent(Request $request)
    {
        $request->validate([
            'user_id' => 'sometimes|integer',
            'tenant_id' => 'sometimes|integer',
            'status' => 'sometimes|string|in:pending,processed,failed',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        $filters = $request->only(['user_id', 'tenant_id', 'status']);
        $limit = $request->input('limit', 10);

        $notifications = $this->service->getRecentNotifications($filters, $limit);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }


    public function summary()
    {
        $summary = $this->service->getSummary();

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }
}
