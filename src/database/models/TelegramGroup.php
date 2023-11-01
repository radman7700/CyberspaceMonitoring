<?php

namespace Pishgaman\CyberspaceMonitoring\Database\models;

use Illuminate\Database\Eloquent\Model;

class TelegramGroup extends Model
{
    public $timestamps = false;

    protected $table = 'telegram_groups';

    protected $fillable = ['gid', 'name', 'username', 'participants_count', 'description'];
}

