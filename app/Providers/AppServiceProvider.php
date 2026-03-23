<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- 1. Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 2. Tambahkan kode ini agar CSS tidak diblokir browser
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
