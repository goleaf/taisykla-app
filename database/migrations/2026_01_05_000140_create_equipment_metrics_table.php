<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->string('metric_type'); // tco, mtbf, mttr, downtime_hours, repair_cost
            $table->decimal('value', 12, 2);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->json('breakdown')->nullable(); // Detailed cost/time breakdown
            $table->timestamps();

            $table->index(['equipment_id', 'metric_type']);
            $table->index('period_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_metrics');
    }
};
