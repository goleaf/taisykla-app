<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'previous_hash')) {
                $table->string('previous_hash')->nullable()->after('user_agent');
            }
            if (! Schema::hasColumn('audit_logs', 'hash')) {
                $table->string('hash')->nullable()->after('previous_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'hash')) {
                $table->dropColumn('hash');
            }
            if (Schema::hasColumn('audit_logs', 'previous_hash')) {
                $table->dropColumn('previous_hash');
            }
        });
    }
};
