<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- 1. Tambahkan ini
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 2. Memaksa HTTPS berjalan di semua environment saat live di Vercel
        // Ini memastikan asset image tidak kena error "Mixed Content"
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        } elseif (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // 3. Register Google Drive Storage Driver
        try {
            Storage::extend('google', function($app, $config) {
                $client = new \Google\Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);
                
                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folder'] ?? '/');
                $driver = new \League\Flysystem\Filesystem($adapter);
    
                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch(\Exception $e) {
            // Fail silently if Google Drive is not configured properly to avoid breaking the app boot
        }
    }
}
