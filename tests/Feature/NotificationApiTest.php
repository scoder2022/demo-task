<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear Redis to reset rate limits
        Redis::flushall();
    }

    public function api_rate_limit()
    {
        $user = User::factory()->create();

        $userId = $user->id;

        // First 10 requests must succeed
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/notifications', [
                'user_id' => $userId,
                'type' => 'email',
                'message' => "test msg $i"
            ]);

            $response->assertStatus(200)
                    ->assertJson(['success' => true]);
        }

        // 11th request need to fail
        $response = $this->postJson('/api/notifications', [
            'user_id' => $userId,
            'type' => 'email',
            'message' => "Test 11"
        ]);

        $response->assertStatus(429)
        ->assertJson([
            'success' => false,
            'message' => 'Rate limit exceeded. Max 10 notifications count per hour is exceeded.'
        ]);
    }
}
