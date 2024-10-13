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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('company_address');
            $table->string('company_logo')->nullable(); // Nullable logo
            $table->string('company_vat_number')->unique(); // Unique VAT number
            $table->string('company_kvk_number')->unique(); // Unique KVK number
            $table->string('company_iban_number')->unique()->nullable(); // Unique KVK number
            $table->boolean('status')->default(false);
            $table->string('client_remotik_id');
            $table->foreignId('client_id')->unique()->constrained('clients')->onDelete('cascade'); // Foreign key to clients table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
