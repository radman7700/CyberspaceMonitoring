<?php 

namespace Pishgaman\CyberspaceMonitoring\Providers;

use Pishgaman\CyberspaceMonitoring\Repositories\TelegramGroupRepository;
use Pishgaman\CyberspaceMonitoring\Repositories\TelegramMessageRepository;
use Illuminate\Support\ServiceProvider;

class StatisticsProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(TelegramGroupRepository::class);
        $this->app->bind(TelegramMessageRepository::class);
    }
}
