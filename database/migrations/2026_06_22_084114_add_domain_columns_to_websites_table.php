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
            $table->string('domain')->nullable()->change();
            $table->string('subdomain')->nullable()->after('name');
            $table->string('base_domain')->nullable()->after('subdomain');
            $table->boolean('auto_attach_domain')->default(true)->after('domain');
            $table->boolean('redirect_subdomain')->default(true)->after('auto_attach_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->string('domain')->nullable(false)->change();
            $table->dropColumn([
                'subdomain',
                'base_domain',
                'auto_attach_domain',
                'redirect_subdomain'
            ]);
        });
    }
};
