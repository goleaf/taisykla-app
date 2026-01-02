<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('report_type')->default('custom');
            $table->string('data_source')->nullable();
            $table->text('description')->nullable();
            $table->json('definition')->nullable();
            $table->json('filters')->nullable();
            $table->json('group_by')->nullable();
            $table->json('sort_by')->nullable();
            $table->json('compare')->nullable();
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
