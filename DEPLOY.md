# Deployment Guide

This document provides instructions and configurations for deploying the CashyTok API, specifically focusing on the OTP (One-Time Password) system and background job processing using Redis and Supervisor.

## Table of Contents
1. [OTP Architecture](#1-otp-architecture)
2. [Queue Configuration](#2-queue-configuration)
3. [Redis Persistence (AOF)](#3-redis-persistence-aof)
4. [Supervisor Configuration](#4-supervisor-configuration)
5. [Standard Deployment Checklist](#5-standard-deployment-checklist)

---

## 1. OTP Architecture

The OTP system is designed to be asynchronous to ensure fast response times for the user. Here is the flow:

1.  **Event Trigger**: When a user registers, the `App\Services\AuthService` triggers the `App\Events\CreatorRegistered` event.
2.  **Listener**: The `App\Listeners\SendEmailVerification` listener catches the event.
3.  **Job Dispatch**: The listener calls `OtpService`, which generates the OTP and dispatches the `App\Jobs\SendOtpEmailJob`.
4.  **Queue**: The job is sent to the `redis` queue for processing by a worker.

---

## 2. Queue Configuration

Ensure your `.env` file is configured to use Redis for queues:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 3. Redis Persistence (AOF)

To ensure that jobs in the queue are not lost if the Redis server or the entire server crashes, we use **Append Only File (AOF)** persistence.

### Configuration
Edit your `redis.conf` (usually located at `/etc/redis/redis.conf`):

1.  Find the `appendonly` setting and set it to `yes`:
    ```conf
    appendonly yes
    ```
2.  (Optional) Ensure `appendfsync` is set to `everysec` for a good balance between performance and durability:
    ```conf
    appendfsync everysec
    ```

### Restart Redis
```bash
sudo systemctl restart redis-server
```

---

## 4. Supervisor Configuration

Supervisor is used to keep the Laravel queue worker running in the background and to restart it automatically if it fails or if the server reboots.

### Create Configuration File
Create a new file at `/etc/supervisor/conf.d/cashytok-worker.conf`:

```conf
[program:cashytok-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/cashy-tok/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/cashy-tok/storage/logs/worker.log
stopwaitsecs=3600
```

> [!IMPORTANT]
> - Change `/var/www/cashy-tok/` to the actual path of your project.
> - Change `user=www-data` to the user that owns the project files (e.g., `ubuntu` or `www-data`).

### Apply Changes
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start cashytok-worker:*
```

---

## 5. Standard Deployment Checklist

Whenever you deploy new code, follow these steps:

1.  **Pull latest code**: `git pull origin main`
2.  **Install dependencies**: `composer install --no-dev --optimize-autoloader`
3.  **Run migrations**: `php artisan migrate --force`
4.  **Clear/Rebuild cache**:
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```
5.  **Restart Workers**:
    ```bash
    php artisan queue:restart
    ```
    *(Note: `queue:restart` tells Supervisor workers to gracefully finish their current job and then restart).*
