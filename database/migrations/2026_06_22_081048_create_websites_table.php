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
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->enum('type', ['static', 'laravel', 'node', 'next', 'vue']);
            $table->string('repo_url')->nullable();
            $table->string('branch')->default('main');
            $table->string('root_path');
            $table->string('public_path')->nullable();
            $table->string('build_command')->nullable();
            $table->string('start_command')->nullable();
            $table->string('php_version')->nullable();
            $table->string('node_version')->nullable();
            $table->boolean('auto_deploy')->default(false);
            $table->string('webhook_secret')->nullable();
            $table->boolean('ssl_enabled')->default(false);
            $table->enum('status', ['pending', 'online', 'deploying', 'error'])->default('pending');
            $table->timestamp('last_deployed_at')->nullable();
            $table->string('last_commit')->nullable();
            $table->string('last_log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
