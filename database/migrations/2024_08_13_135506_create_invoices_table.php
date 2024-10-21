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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id');
            $table->string('client_remotik_id');
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('client_address')->nullable();
            $table->string('client_is_vip')->nullable();
            $table->string('client_vip_discount')->nullable();
            $table->string('client_vat_slab')->nullable();
            $table->string('date_range');
            $table->string('due_date')->nullable();
            $table->enum('invoice_status', ['paid', 'unpaid', 'cancelled']);
            $table->text('address')->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('vat_slab_amount', 10, 2)->nullable();
            $table->text('device_usage_details')->nullable(); // array of objects json string
            $table->decimal('total_cost', 10, 2);
            $table->decimal('original_cost', 10, 2);
            $table->text('notes')->nullable();

            $table->string('invoice_generated_by_user_type')->enum('admin', 'client')->default('admin');

            $table->string('invoice_generated_by_id'); // if admin get from admin table, if client get from client table
            $table->string('invoice_generated_by_name'); // if admin get from users name, if client get from client table client_remotik_id

            $table->string('for_child_client_remotik_id')->nullable();

            $table->integer('seller_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
