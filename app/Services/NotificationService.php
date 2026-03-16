<?php
namespace App\Services;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessNotificationJob;
use App\DTOs\NotificationData;
use App\Repositories\NotificationRepository;

class NotificationService
{
    protected NotificationRepository $repository;

    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
    }


    public function publish(NotificationData $data)
    {
        // RATE LIMIT CHECK
        $cacheKey = "notify_rate_limit:{$data->user_id}";

        // current count of user request default is 0
        $count = Cache::get($cacheKey, 0);

        if ($count >= 10) {
            throw new \Exception("Rate limit exceeded. Max 10 notifications per hour.");
        }

        // Increment count
        Cache::put($cacheKey, $count + 1, now()->addHour());

        $notification = $this->repository->create([
            'user_id' => $data->user_id,
            'tenant_id' => $data->tenant_id,
            'type' => $data->type,
            'message' => $data->message,
            'status' => 'pending',
        ]);

        ProcessNotificationJob::dispatch($notification);

        return $notification;
    }


    public function getRecentNotifications(array $filters = [], int $limit = 10)
    {
        // Cache key scoped by filters to avoid stale/wrong data
        $cacheKey = 'recent_notifications_' . md5(json_encode($filters) . "_limit{$limit}");

        // Cache for 20 seconds for near real-time updates
        return Cache::remember($cacheKey, 20, function () use ($filters, $limit) {
            return $this->repository->getRecent($filters, $limit);
        });
    }

    /**
     * Call this when a new notification is created to invalidate cache
     */
    public function invalidateRecentCache(array $filters = [], int $limit = 10)
    {
        $cacheKey = 'recent_notifications_' . md5(json_encode($filters) . "_limit{$limit}");
        Cache::forget($cacheKey);
    }

    public function getSummary(): array
    {
        $cacheKey = "notifications_summary";
        return Cache::remember($cacheKey, 20, function () {
            return $this->repository->getSummary();
        });
    }
}
