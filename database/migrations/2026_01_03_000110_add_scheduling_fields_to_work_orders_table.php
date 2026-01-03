<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->unsignedTinyInteger('required_skill_level')->nullable()->after('category_id');
            $table->json('required_skills')->nullable()->after('required_skill_level');
            $table->json('required_certifications')->nullable()->after('required_skills');
            $table->foreignId('preferred_technician_id')->nullable()->after('assigned_to_user_id')
                ->constrained('users')->nullOnDelete();
            $table->json('preferred_technician_ids')->nullable()->after('preferred_technician_id');
            $table->string('service_territory')->nullable()->after('time_window');
            $table->timestamp('customer_time_window_start')->nullable()->after('time_window');
            $table->timestamp('customer_time_window_end')->nullable()->after('customer_time_window_start');
            $table->text('customer_preference_notes')->nullable()->after('customer_time_window_end');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['preferred_technician_id']);
            $table->dropColumn([
                'required_skill_level',
                'required_skills',
                'required_certifications',
                'preferred_technician_id',
                'preferred_technician_ids',
                'service_territory',
                'customer_time_window_start',
                'customer_time_window_end',
                'customer_preference_notes',
            ]);
        });
    }
};
