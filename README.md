Notification Processing System

Simple Laravel project that demonstrates how notifications can be processed using queues and background jobs.

The project also shows a clean structure using services, repositories, and DTOs.

Features

Process notifications using Laravel Queue

Retry failed notifications

Basic rate limiting (10 notifications per user per hour)

Caching for recent notifications

Simple admin APIs for checking notification status

Bulk retry and cancel options

Project Structure

Controllers
Handle API requests and responses.

Services
Contain the main business logic and dispatch queue jobs.

Repositories
Handle database queries.

DTOs
Used to pass structured data between layers.

Jobs
Process notifications in the background.

Installation

Clone the project

git clone https://github.com/scoder2022/demo-task.git

Go into the project folder

cd demo-task

Install dependencies

composer install

Copy environment file

cp .env.example .env

Generate app key

php artisan key:generate

Run migrations

php artisan migrate
Run Queue Worker

Start the queue worker:

php artisan queue:work redis
API Example 

Notification API Routes Notes

1. Create Notification
POST /api/notifications

Used to create a new notification.

Example request body:
{
  "user_id": 1,
  "tenant_id": 1,
  "type": "email",
  "message": "Test notification"
}


2. Get Recent Notifications
GET /api/notifications/recent

Example:
http://127.0.0.1:8000/api/notifications/recent?user_id=1&tenant_id=1&status=processed&limit=2

Parameters:
user_id   -> filter by user
tenant_id -> filter by tenant
status    -> pending / processed / failed
limit     -> number of results


3. Get Notification Summary
GET /api/notifications/summary
Returns total notifications by status.


Authentication

Login
POST /api/login

Request fields:
- email     -> required, must be a valid email
- password  -> required

Logout
POST /api/logout
Requires authentication token (Sanctum).

Admin Routes (Requires login + admin role)

4. Notification Stats
GET /api/admin/notifications/stats


5. Bulk Retry Notifications
POST /api/admin/notifications/bulk-retry

Retries failed notifications.


6. Bulk Cancel Notifications
POST /api/admin/notifications/bulk-cancel

Cancels pending notifications.

Webhook Testing

POST /api/webhook-test

Used for local webhook testing.
Logs incoming data and returns a simple response.
