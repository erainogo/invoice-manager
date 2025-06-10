# Invoice Manager

**Invoice Manager** is a Laravel-based system for managing and processing CSV file uploads containing payment records. It supports both web and API interfaces, handles background jobs using Redis and Horizon, and generates daily payout summaries.

CSV files are uploaded to **Amazon S3 using multipart upload**, making large file handling efficient and reliable.

Tested with 100MB file uploads

## 🔒 User Roles & Authentication

The system supports two user roles:

- **Admin users**: Manage files and users through a web interface powered by [Filament](https://filamentphp.com/).
- **API users**: Upload and manage files via a secure REST API using [Laravel Sanctum](https://laravel.com/docs/sanctum/).

Authentication is required for both user types.

---

## 🧰 Prerequisites

- Docker & Docker Compose
- Laravel (already included in project)
- CSV files containing payment data

---

## 🚀 Setup & Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-org/invoice-manager.git
cd invoice-manager
```

### 2. Start Docker Containers

```bash
docker-compose -f docker-compose.yml up --build -d
```

This spins up the following containers:

- **php** (Laravel backend)
- **webserver** (Nginx)
- **mysql** (MySQL database)
- **redis** (for queueing and caching)
- **horizon** (Laravel Horizon for job management)

### 3. Install Dependencies

```bash
docker exec -it invoice-manager-app composer install
```

### 4. Set File Permissions

```bash
sudo chmod -R 777 storage bootstrap/cache
```

### 5. Environment Setup

Copy `.env.example` to `.env`, then configure DB, Redis, etc.

```bash
cp .env.example .env
docker exec -it invoice-manager-app php artisan key:generate
```

### 6. Run Migrations

```bash
docker exec -it invoice-manager-app php artisan migrate
```

---

## 📬 API Usage

### Login 

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "<your_email>", "password": "<your_password>"}'
```

### Upload a CSV File

```bash
curl -X POST http://localhost:8000/api/upload \
  -H "Authorization: Bearer <your-api-token>" \
  -F "file=@customer_transactions.csv"
```

Replace `<your-api-token>` with your actual token.

---

## ⚙️ Background Jobs

The application uses Redis and Laravel Horizon to handle background processing of uploaded files.

### Start Horizon (already started via Docker)

```bash
# Automatically runs in the 'horizon' service container:
php artisan horizon
```

You can monitor Horizon at:

```
http://localhost:8000/horizon
```

### Jobs:

- **ProcessPaymentFileJob**: Parses and processes uploaded CSV files.
-   queue - payment-file-upload-queue

- **ProcessPaymentRowJob**: Handles individual csv rows.
-   queue - payment-file-read-queue (executed as job batches)

- **ProcessPayoutJob**: handles payouts and send emails.
-   queue - payment-payout-processing-queue

---

## ⏰ Scheduled Task

The app includes a scheduled command to run daily:

```php
$schedule->command('app:daily-payout-command')->dailyAt("01:00");
```

---

## 📂 File Storage

Uploaded files are stored in S3 via multipart uploads.

You can configure your S3 credentials in `.env`.

---

## 📈 Monitoring

- Laravel Horizon is used for monitoring job queues.
- Access it at: http://localhost:8000/horizon


## 🔧 Docker Configuration

The application uses custom Docker configurations for optimal performance:

# Dockerfile Features

dockerfile# FROM php:8.3-fpm

# Large file upload settings in image
- upload_max_filesize=200M
- post_max_size=200M
- memory_limit=512M
- max_execution_time=300

## PHP-FPM Pool Configuration

- pm = dynamic
- pm.max_children = 50
- pm.start_servers = 10
- pm.min_spare_servers = 5
- pm.max_spare_servers = 10

These settings ensure optimal handling of concurrent large file uploads and processing.

## 📧 Email Configuration
The application uses Mailtrap for email testing and notifications.

## Setup Mailtrap

- Sign up for a Mailtrap account
- Create a new inbox in your Mailtrap dashboard
- Copy the SMTP credentials to your .env file
