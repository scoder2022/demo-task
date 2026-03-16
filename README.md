# Notification Processing System

A small Laravel project to show how notifications can be sent and handled in the background using queues. It also uses services, repositories, and DTOs for better structure.

## Requirements

* PHP >= 8.1
* Composer
* MySQL or SQLite (Redis for queues)

## Features

* Send notifications in the background
* Retry failed notifications automatically
* Limit 10 notifications per user per hour
* Cache recent notifications
* Admin APIs to check status
* Bulk retry and cancel

## Project Structure

* **Controllers** – Handle API requests
* **Services** – Business logic and dispatch jobs
* **Repositories** – Database queries
* **DTOs** – Pass data between layers
* **Jobs** – Background processing

## Setup

```bash
git clone https://github.com/your-username/your-repo.git
cd your-repo
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

> Make sure Redis is running if using queues

## Run Queue

```bash
php artisan queue:work redis
```

## API Endpoints

**Create Notification**
POST `/api/notifications`

```json
{
  "user_id": 1,
  "tenant_id": 1,
  "type": "email",
  "message": "Test notification"
}
```

**Get Recent Notifications**
GET `/api/notifications/recent?user_id=1&tenant_id=1&status=processed&limit=2`

**Get Summary**
GET `/api/notifications/summary`

**Authentication**

* Login: POST `/api/login` (email, password)
* Logout: POST `/api/logout` (needs token)

**Admin Routes** (login + admin)

* `/api/admin/notifications/stats` (GET)
* `/api/admin/notifications/bulk-retry` (POST)
* `/api/admin/notifications/bulk-cancel` (POST)
