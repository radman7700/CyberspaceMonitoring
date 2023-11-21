<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mid');
            $table->unsignedBigInteger('gid');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('date');
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->index('gid');
            $table->index('user_id');
            $table->index('date');
            $table->index('message');            
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
