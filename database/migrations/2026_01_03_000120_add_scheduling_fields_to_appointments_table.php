<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedInteger('route_order')->nullable()->after('assigned_to_user_id');
            $table->unsignedInteger('estimated_minutes')->nullable()->after('scheduled_end_at');
            $table->unsignedInteger('travel_minutes')->nullable()->after('estimated_minutes');
            $table->string('kind')->default('service')->after('notes');
            $table->string('external_calendar_event_id')->nullable()->after('kind');
            $table->foreignId('recurring_schedule_id')->nullable()->after('external_calendar_event_id')
                ->constrained('recurring_schedules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['recurring_schedule_id']);
            $table->dropColumn([
                'route_order',
                'estimated_minutes',
                'travel_minutes',
                'kind',
                'external_calendar_event_id',
                'recurring_schedule_id',
            ]);
        });
    }
};
