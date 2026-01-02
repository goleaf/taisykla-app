<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('diagnosis_summary')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('test_results')->nullable();
            $table->text('recommendations')->nullable();
            $table->unsignedInteger('diagnostic_minutes')->nullable();
            $table->unsignedInteger('repair_minutes')->nullable();
            $table->unsignedInteger('testing_minutes')->nullable();
            $table->timestamps();

            $table->unique('work_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_reports');
    }
};
