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
        Schema::create('power_data', function (Blueprint $table) {
            $table->id();
            $table->integer('remotik_power_id')->unique();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->timestamp('time')->nullable(); // This will hold the timestamp as datetime
            $table->string('nodeid');
            $table->string('node_name');
             // Node ID
            $table->integer('power')->default(0); // Power, defaulting to 0
            $table->timestamps(); // Created_at and Updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('power_data');
    }
};
