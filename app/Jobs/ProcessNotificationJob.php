<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Repositories\NotificationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ProcessNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [10, 30, 60, 120, 300];
    public $timeout = 30;

    public Notification $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }


    public function handle(NotificationRepository $repository): void
    {
        try {
            $this->notification->refresh();

            if ($this->notification->status === 'cancelled') {
                Log::info("Notification {$this->notification->id} is skipped (cancelled)");
                return;
            }

            Log::info("Processing notification: {$this->notification->id}");
            Log::info("Sending notification to user: {$this->notification->user_id}");

            // check case for failed jobs works or not uncomment to make job failed
            // if (rand(1,3) === 1) throw new \Exception("Simulated failure");

            $repository->markAsProcessed($this->notification);

            Log::info("Notification has {$this->notification->id} processed successfully.");

            $this->sendWebhook('processed');

        } catch (\Exception $e) {

            Log::error("Process failed for notification {$this->notification->id}: {$e->getMessage()}");
            $repository->markAsFailed($this->notification);

            throw $e;
        }
    }

    // Send webhook callback
    protected function sendWebhook(string $status): void
    {
        $webhookUrl = config('services.notification_webhook_local_url');

        if (!$webhookUrl) return;

        try {
            Http::timeout(5)->post($webhookUrl, [
                'notification_id' => $this->notification->id,
                'user_id' => $this->notification->user_id,
                'status' => $status,
                'message' => $this->notification->message,
                'processed_at' => $this->notification->processed_at,
                'attempts' => $this->notification->attempts
            ]);
        } catch (\Exception $e) {
            Log::warning("Webhook failed to notification {$this->notification->id}: ".$e->getMessage());
        }
    }

    // Runs after job completely fails
    public function failed(\Throwable $exception): void
    {
        Log::critical("Notification {$this->notification->id} failed after 10 retries");

        $repository = app(NotificationRepository::class);

        $repository->markAsFailed($this->notification);

        $this->sendWebhook('failed');
    }
}
