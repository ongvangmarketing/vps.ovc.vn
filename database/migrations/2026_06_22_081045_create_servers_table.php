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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip');
            $table->string('status')->default('active');
            $table->string('php_version')->nullable();
            $table->string('node_version')->nullable();
            $table->string('mysql_status')->nullable();
            $table->string('nginx_status')->nullable();
            $table->string('cpu_usage')->nullable();
            $table->string('ram_usage')->nullable();
            $table->string('disk_usage')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
