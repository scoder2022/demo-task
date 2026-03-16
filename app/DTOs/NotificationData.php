<?php
namespace App\DTOs;

class NotificationData
{
    public int $user_id;
    public ?int $tenant_id;
    public string $type;
    public string $message;

    public function __construct(array $data)
    {
        $this->user_id = $data['user_id'];
        $this->tenant_id = $data['tenant_id'] ?? null;
        $this->type = $data['type'] ?? 'system';
        $this->message = $data['message'];
    }
}
