<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();

            // Event details
            $table->string('event_type'); // repair, maintenance, inspection, upgrade, installation
            $table->text('problem_description')->nullable();
            $table->text('resolution_description')->nullable();

            // Timing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();

            // Costs
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('parts_cost', 10, 2)->default(0);
            $table->json('parts_replaced')->nullable();

            // Photos
            $table->json('before_photos')->nullable();
            $table->json('after_photos')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'event_type']);
            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_events');
    }
};
