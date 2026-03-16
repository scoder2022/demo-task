<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\NotificationService;
use App\DTOs\NotificationData;
use App\Jobs\ProcessNotificationJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushall();
        Queue::fake();
    }

    public function publish_notification_creates_record()
    {
        $service = $this->app->make(NotificationService::class);

        $data = new NotificationData([
            'user_id' => 1,
            'tenant_id' => 1,
            'type' => 'email',
            'message' => 'Test notification'
        ]);

        $notification = $service->publish($data);

        // Assert DB has pending record
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => 'pending'
        ]);

        Queue::assertPushed(ProcessNotificationJob::class, function ($job) use ($notification) {
            return $job->notification->id === $notification->id;
        });
    }
}
