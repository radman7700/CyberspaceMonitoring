<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWordCountsChannelTelegramTable extends Migration
{
    public function up()
    {
        Schema::create('telegram_channel_word_counts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->string('word');
            $table->integer('word_count');
            $table->timestamp('message_date');            
            $table->foreign('message_id')->references('id')->on('telegram_channel_messages');
        });

        Schema::table('telegram_channel_word_counts', function (Blueprint $table) {
            $table->index('word');
            $table->index('message_date');
        });        
    }

    public function down()
    {
        Schema::dropIfExists('telegram_channel_word_counts');
    }
}
