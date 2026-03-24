# Kas Villa Management System

A simple web application for managing monthly contributions (kas) for a villa community.

## Feature Overview

*   **Dashboard**: View payments status for all residents.
*   **Monthly Payments**: Track payments by month/year.
*   **Progress Bar**: Visual representation of total collected funds vs target.
*   **Payment Submission (Manual)**: Residents can upload proof of payment (image).
*   **Cloudinary Integration**: Automatic image upload for payment proofs.
*   **Google Drive Backup**: Automatic backup of payment proofs to a designated Google Drive folder.
*   **Admin Panel**: Approve/Reject payments.

## Setup & Configuration

### Prerequisites
*   PHP 8.2+
*   Composer
*   Node.js & NPM
*   Database (MySQL/SQLite)

### Installation
1.  Clone the repository.
2.  `composer install`
3.  `npm install && npm run build`
4.  Copy `.env.example` to `.env` and configure database.
5.  `php artisan migrate --seed`
6.  `php artisan key:generate`

### Environment Variables
Configure the following in your `.env` file:

```ini
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kas_villa
DB_USERNAME=root
DB_PASSWORD=

# Cloudinary (Image Hosting)
CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
CLOUDINARY_UPLOAD_PRESET=kas_villa_unsigned

# Google Drive (Backup) - See GOOGLE_DRIVE_SETUP.md for details
GOOGLE_DRIVE_CLIENT_ID=
GOOGLE_DRIVE_CLIENT_SECRET=
GOOGLE_DRIVE_REFRESH_TOKEN=
GOOGLE_DRIVE_FOLDER_ID=
```

## Google Drive Setup Guide
For step-by-step instructions on obtaining the Google Drive credentials, please refer to [GOOGLE_DRIVE_SETUP.md](./GOOGLE_DRIVE_SETUP.md).

## Deployment
This project is configured for deployment on Vercel. Ensure all environment variables are added to the Vercel project settings.
