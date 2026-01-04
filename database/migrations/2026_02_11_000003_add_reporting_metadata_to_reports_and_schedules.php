<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (! Schema::hasColumn('reports', 'category')) {
                $table->string('category')->default('operational')->after('report_type');
            }
            if (! Schema::hasColumn('reports', 'visualization')) {
                $table->string('visualization')->default('table')->after('description');
            }
        });

        Schema::table('report_schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('report_schedules', 'format')) {
                $table->string('format')->default('csv')->after('time_of_day');
            }
            if (! Schema::hasColumn('report_schedules', 'timezone')) {
                $table->string('timezone')->nullable()->after('format');
            }
            if (! Schema::hasColumn('report_schedules', 'delivery_channels')) {
                $table->json('delivery_channels')->nullable()->after('recipients');
            }
            if (! Schema::hasColumn('report_schedules', 'parameters')) {
                $table->json('parameters')->nullable()->after('delivery_channels');
            }
            if (! Schema::hasColumn('report_schedules', 'conditions')) {
                $table->json('conditions')->nullable()->after('parameters');
            }
            if (! Schema::hasColumn('report_schedules', 'filters')) {
                $table->json('filters')->nullable()->after('conditions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('report_schedules', 'filters')) {
                $table->dropColumn('filters');
            }
            if (Schema::hasColumn('report_schedules', 'conditions')) {
                $table->dropColumn('conditions');
            }
            if (Schema::hasColumn('report_schedules', 'parameters')) {
                $table->dropColumn('parameters');
            }
            if (Schema::hasColumn('report_schedules', 'delivery_channels')) {
                $table->dropColumn('delivery_channels');
            }
            if (Schema::hasColumn('report_schedules', 'timezone')) {
                $table->dropColumn('timezone');
            }
            if (Schema::hasColumn('report_schedules', 'format')) {
                $table->dropColumn('format');
            }
        });

        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'visualization')) {
                $table->dropColumn('visualization');
            }
            if (Schema::hasColumn('reports', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
