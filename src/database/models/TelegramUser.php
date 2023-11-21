<?php

namespace Pishgaman\CyberspaceMonitoring\Database\models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

class TelegramUser extends Model
{
    protected $table = 'telegram_users';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'username',
        'phone',
        'tag',
    ];     
}

