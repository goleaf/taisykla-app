<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_dashboards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('dashboard_type')->default('operations');
            $table->text('description')->nullable();
            $table->json('filters')->nullable();
            $table->json('layout')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('report_dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained('report_dashboards')->cascadeOnDelete();
            $table->string('title');
            $table->string('widget_type')->default('kpi');
            $table->foreignId('report_id')->nullable()->constrained()->nullOnDelete();
            $table->string('data_source')->nullable();
            $table->json('config')->nullable();
            $table->json('position')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['dashboard_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_dashboard_widgets');
        Schema::dropIfExists('report_dashboards');
    }
};
