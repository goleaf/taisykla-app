<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('completed');
            $table->string('format')->default('csv');
            $table->string('file_path')->nullable();
            $table->unsignedInteger('row_count')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('run_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_runs');
    }
};
