<?php

namespace Pishgaman\CyberspaceMonitoring\Database\models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

class TelegramUser extends Model
{
    protected $table = 'telegram_users';
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'username',
        'phone',
        'tag',
    ];    
    
    public function messages()
    {
        return $this->hasMany(TelegramMessage::class, 'user_id', 'user_id');
    }    
}

