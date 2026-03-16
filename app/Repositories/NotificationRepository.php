<?php
namespace App\Repositories;

use App\Models\Notification;

class NotificationRepository
{
    public function create(array $data): Notification
    {
        return Notification::create($data);
    }


    public function markAsProcessed(Notification $notification): void
    {
        $notification->update([
            'status' => 'processed',
            'processed_at' => now()
        ]);
    }


    public function markAsFailed(Notification $notification): void
    {
        $notification->increment('attempts');

        $notification->update([
            'status' => 'failed'
        ]);
    }


    public function getRecent(array $filters = [], int $limit = 10)
    {
        $query = Notification::query();

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->limit($limit)->get();
    }


    public function getSummary()
    {
        return [
            'processed' => Notification::where('status', 'processed')->count(),
            'failed' => Notification::where('status', 'failed')->count(),
            'pending' => Notification::where('status', 'pending')->count(),
        ];
    }
}
