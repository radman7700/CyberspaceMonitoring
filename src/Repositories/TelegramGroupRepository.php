<?php

namespace Pishgaman\CyberspaceMonitoring\Repositories;

use Pishgaman\CyberspaceMonitoring\Database\models\TelegramGroup;

class TelegramGroupRepository
{
    public function groupCount()
    {
        return TelegramGroup::select('gid')->distinct()->pluck('gid')->count();
    }

    public function sessionCount()
    {
        return TelegramGroup::select('session')->distinct()->pluck('session')->count();
    }

}