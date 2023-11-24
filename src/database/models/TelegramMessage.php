<?php

namespace Pishgaman\CyberspaceMonitoring\Database\models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

class TelegramMessage extends Model
{
    use HasTags;
    
    public $timestamps = false;

    protected $table = 'telegram_messages';

    protected $fillable = ['mid', 'gid', 'user_id', 'date', 'message'];

    protected $dates = ['date'];

    public function TelegramGroup()
    {
        return $this->belongsTo(TelegramGroup::class, 'gid', 'gid');
    }   
    public function TelegramUser()
    {
        return $this->belongsTo(TelegramUser::class, 'user_id', 'user_id');
    }       
}

