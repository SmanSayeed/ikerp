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
        Schema::create('power_data_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('client_remotik_id');

            $table->integer('synced_count')->default(0);  // How many records synced
            $table->enum('status', ['new_data', 'no_new_data', 'error'])->default('new_data');  // Status of sync
            $table->string('message')->nullable();
            $table->timestamps();  // When sync occurred
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('power_data_sync_log');
    }
};
