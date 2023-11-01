<?php

namespace Pishgaman\CyberspaceMonitoring\Database\models;

use Illuminate\Database\Eloquent\Model;

class TelegramWordCount extends Model
{
    public $timestamps = false;

    protected $table = 'telegram_word_counts';

    protected $fillable = ['message_id', 'word', 'word_count', 'message_date'];
    
    public function message()
    {
        return $this->belongsTo(TelegramMessage::class, 'message_id');
    }
}
