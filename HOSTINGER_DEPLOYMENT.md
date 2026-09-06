# Hostinger 1-Click Deployment Guide for CGJobs Laravel Backend

This document explains how to deploy this Laravel backend to **Hostinger Web Hosting** (Shared, Cloud, or VPS).

---

## 📁 Project Structure

In this repository, the folder is named **`laravel-backend`**:

```text
laravel-backend/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/                 <--- THIS IS THE PUBLIC DIRECTORY
│   ├── .htaccess           <--- URL rewrite rules for Apache/Hostinger
│   └── index.php           <--- Main application entry point
├── resources/
├── routes/
├── storage/
├── .env.example
├── artisan
└── composer.json
```

---

## 🚀 Easy 3-Step Hostinger Installation

### Step 1: Upload to Hostinger

1. Download or export the project files from AI Studio.
2. Go to Hostinger **hPanel** -> **Websites** -> **Manage** -> **File Manager**.
3. In your home directory (usually `/home/u123456789/`), create a directory named `cgjobs-backend`.
4. Upload all files from `laravel-backend/` into `/home/u123456789/cgjobs-backend/`.

---

### Step 2: Configure Public Directory (`public_html`)

Hostinger serves your domain from `public_html/`. You have two options:

#### Option A (Recommended for Hostinger Shared/Premium Hosting):
1. Copy the contents of `cgjobs-backend/public/` into your `public_html/` folder:
   - `public_html/index.php`
   - `public_html/.htaccess`
2. Open `public_html/index.php` and edit lines 12 and 15 so they point to `cgjobs-backend`:
   ```php
   // Replace:
   require __DIR__.'/../vendor/autoload.php';
   (require_once __DIR__.'/../bootstrap/app.php')

   // With:
   require __DIR__.'/../cgjobs-backend/vendor/autoload.php';
   (require_once __DIR__.'/../cgjobs-backend/bootstrap/app.php')
   ```

#### Option B (For Hostinger Cloud / Business Hosting):
- In hPanel, go to **Websites** -> **Configuration** / **Domain**.
- Change the **Document Root** from `public_html` directly to `cgjobs-backend/public`.

---

### Step 3: Configure Database & `.env`

1. In hPanel -> **Databases** -> **MySQL Databases**, create a new database.
2. In `cgjobs-backend/`, copy `.env.example` to `.env`.
3. Fill in your database details:
   ```env
   APP_NAME="CGJobs Admin"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_hostinger_dbname
   DB_USERNAME=your_hostinger_dbuser
   DB_PASSWORD=your_hostinger_dbpass

   ADMIN_USERNAME=admin
   ADMIN_PASSWORD=admin123
   ```
4. Run migrations via SSH terminal or phpMyAdmin:
   ```bash
   php artisan key:generate
   php artisan migrate --seed --force
   ```
