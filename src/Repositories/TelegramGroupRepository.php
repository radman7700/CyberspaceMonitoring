<?php

namespace Pishgaman\CyberspaceMonitoring\Repositories;

use Pishgaman\CyberspaceMonitoring\Database\models\TelegramGroup;

class TelegramGroupRepository
{
    public function groupCount($type='group')
    {
        return TelegramGroup::select('gid')->where('type',$type)->distinct()->pluck('gid')->count();
    }

    public function sessionCount()
    {
        return TelegramGroup::select('session')->distinct()->pluck('session')->count();
    }

}