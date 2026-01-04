<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'recurring_schedule_id')) {
                $table->foreignId('recurring_schedule_id')->nullable()->after('assigned_to_user_id')
                    ->constrained('recurring_schedules')->nullOnDelete();
            }
            if (!Schema::hasColumn('appointments', 'is_exception')) {
                $table->boolean('is_exception')->default(false)->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'recurring_schedule_id')) {
                $table->dropForeign(['recurring_schedule_id']);
                $table->dropColumn('recurring_schedule_id');
            }
            if (Schema::hasColumn('appointments', 'is_exception')) {
                $table->dropColumn('is_exception');
            }
        });
    }
};
