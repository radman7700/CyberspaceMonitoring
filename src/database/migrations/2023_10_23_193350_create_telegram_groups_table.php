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
        Schema::create('telegram_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gid')->index();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->integer('participants_count')->nullable();
            $table->text('description')->nullable();
            $table->text('session')->nullable();
            $table->timestamps();

            // افزودن ایندکس ترکیبی برای gid و participants_count
            $table->index(['gid', 'participants_count']);
            
            // افزودن ایندکس ترتیبی برای name به ترتیب نزولی
            $table->index('name', 'name_index_desc')->desc();            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_groups');
    }
};
