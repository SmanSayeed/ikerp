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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('client_remotik_id')->nullable()->unique();
            $table->boolean('is_seller')->default(false);
            $table->date('payment_due_date')->nullable();
            $table->string('vat_slab')->nullable();
            $table->text('gbs_information')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->decimal('vip_discount', 8, 2)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamp('last_synced')->nullable();
            $table->boolean('is_parent')->default(true); // If true, the client is a parent, unless false, false does not mean client is a child
            $table->boolean('is_child')->default(false); // true means client is a child, false means, client is not a child
            $table->string('parent_client_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
