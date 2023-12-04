<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('telegram_channel_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mid');
            $table->unsignedBigInteger('gid');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('date');
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::table('telegram_channel_messages', function (Blueprint $table) {
            $table->index('gid');
            $table->index('user_id');
            $table->index('date');
            // $table->index('message');            
        });       
        
        DB::table('menus')->insert([
            'order' => '3',
            'parent_id' => '1',
            'name' => 'channelHome',
            'packeage' => 'CyberspaceMonitoringLang',
            'route' => 'payesh_telegram_channel_dashboard',
            'icon' => 'fa fa-home',
        ]); 
        DB::table('menus')->insert([
            'order' => '3',
            'parent_id' => '10',
            'name' => 'PayeshTelegramChannelList',
            'packeage' => 'CyberspaceMonitoringLang',
            'route' => 'payesh_telegram_channel_messages_list',
            'icon' => 'fa fa-list',
        ]);  
        DB::table('menus')->insert([
            'order' => '3',
            'parent_id' => '10',
            'name' => 'PayeshTelegramUsersList',
            'packeage' => 'CyberspaceMonitoringLang',
            'route' => 'payesh_telegram_user_list',
            'icon' => 'fa fa-users',
        ]);             
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_channel_messages');
    }
};
 
