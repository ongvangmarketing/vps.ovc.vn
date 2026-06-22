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
        Schema::table('websites', function (Blueprint $table) {
            $table->string('environment')->default('Production')->nullable();
            $table->string('deploy_path')->default('/')->nullable();
            $table->string('public_dir')->default('public')->nullable();
            $table->boolean('auto_install_deps')->default(true);
            $table->boolean('auto_migrate')->default(true);
            $table->boolean('auto_nginx_config')->default(true);
            $table->boolean('auto_reload_nginx')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn([
                'environment',
                'deploy_path',
                'public_dir',
                'auto_install_deps',
                'auto_migrate',
                'auto_nginx_config',
                'auto_reload_nginx',
            ]);
        });
    }
};
