<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Jobs\ProcessNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller{
    // Get statistics for admin dashboard
    public function stats()
    {
        $data = [
            'total' => Notification::count(),
            'processed' => Notification::where('status', 'processed')->count(),
            'pending' => Notification::where('status', 'pending')->count(),
            'failed' => Notification::where('status', 'failed')->count(),
        ];

        return response()->json($data);
    }

    // Bulk retry failed notifications
    public function bulkRetry(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array'
        ]);

        $notifications = Notification::whereIn('id', $request->notification_ids)
            ->where('status', 'failed')
            ->get();

        foreach ($notifications as $notification) {
            $notification->update([
                'status' => 'pending'
            ]);

            // Dispatch to queue
            ProcessNotificationJob::dispatch($notification);
        }

        return response()->json([
            'message' => 'Bulk retry queued successfully',
            'count' => $notifications->count()
        ]);
    }


    public function bulkCancel(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:notifications,id'
        ]);

        $updated = Notification::whereIn('id', $request->ids)
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled'
            ]);

        return response()->json([
            'message' => 'Notifications cancelled successfully',
            'cancelled_count' => $updated
        ]);
    }
}
