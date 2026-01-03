<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('skill_level')->nullable()->after('job_title');
            $table->json('skills')->nullable()->after('skill_level');
            $table->json('certifications')->nullable()->after('skills');
            $table->string('territory')->nullable()->after('certifications');
            $table->unsignedInteger('max_daily_minutes')->default(480)->after('territory');
            $table->unsignedInteger('max_weekly_minutes')->default(2400)->after('max_daily_minutes');
            $table->boolean('overtime_allowed')->default(false)->after('max_weekly_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'skill_level',
                'skills',
                'certifications',
                'territory',
                'max_daily_minutes',
                'max_weekly_minutes',
                'overtime_allowed',
            ]);
        });
    }
};
