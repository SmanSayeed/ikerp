<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodesTable extends Migration
{
    public function up()
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('meshid');
            $table->string('nodeid')->unique();
            $table->string('mesh_name');
            $table->string('node_name');
            $table->string('client_remotik_id');
            $table->boolean('is_child_node')->default(false);
            $table->string('child_client_remotik_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nodes');
    }
}
