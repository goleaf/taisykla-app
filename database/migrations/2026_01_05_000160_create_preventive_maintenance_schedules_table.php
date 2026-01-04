<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('preventive_maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('equipment_category_id')->nullable()->constrained('equipment_categories')->nullOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            // Schedule frequency
            $table->string('frequency_type'); // days, weeks, months, hours_of_use
            $table->unsignedInteger('frequency_value');

            // Tracking
            $table->timestamp('next_due_at')->nullable();
            $table->timestamp('last_performed_at')->nullable();

            // Checklist template
            $table->json('checklist_template')->nullable();

            // Notifications
            $table->unsignedInteger('reminder_days_before')->default(7);
            $table->boolean('auto_create_work_order')->default(true);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['equipment_id', 'is_active']);
            $table->index('next_due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenance_schedules');
    }
};
