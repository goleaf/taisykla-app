<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /*
        Schema::table('work_orders', function (Blueprint $table) {
            $table->index('priority');
            $table->index('status');
            $table->index('requested_at');
            $table->index('scheduled_start_at');
            $table->index('scheduled_end_at');
            $table->index('started_at');
            $table->index('completed_at');
            $table->index('canceled_at');
        });

        Schema::table('work_order_feedback', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('status');
            $table->index('sent_at');
            $table->index('paid_at');
            $table->index('due_date');
            $table->index('created_at');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->index('type');
            $table->index('manufacturer');
            $table->index('purchase_date');
            $table->index('status');
            $table->index('lifecycle_status');
        });
        */
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['manufacturer']);
            $table->dropIndex(['purchase_date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['lifecycle_status']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['sent_at']);
            $table->dropIndex(['paid_at']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('work_order_feedback', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex(['priority']);
            $table->dropIndex(['status']);
            $table->dropIndex(['requested_at']);
            $table->dropIndex(['scheduled_start_at']);
            $table->dropIndex(['scheduled_end_at']);
            $table->dropIndex(['started_at']);
            $table->dropIndex(['completed_at']);
            $table->dropIndex(['canceled_at']);
        });
    }
};
