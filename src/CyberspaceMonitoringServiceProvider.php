<?php

namespace Pishgaman\CyberspaceMonitoring;

use Illuminate\Support\ServiceProvider;

class CyberspaceMonitoringServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // اینجا می‌توانید افزونه‌ها و کدهای مرتبط با پکیج را ثبت کنید.
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // تنظیم مسیرهای (روت‌ها) مرتبط با پکیج CyberspaceMonitoring
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        // Load views
        $this->loadViewsFrom(__DIR__.'/Resources/views', 'CyberspaceMonitoringView');
        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/Resources/lang', 'CyberspaceMonitoringLang');

        // تنظیم فایل‌های مشخصات پکیج GymManagement
        $this->publishes([
            __DIR__ . '/config/club-management.php' => config_path('club-management.php'),
        ], 'config');

        // تنظیم فایل‌های دیتابیس مرتبط با پکیج GymManagement
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
