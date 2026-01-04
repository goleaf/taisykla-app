<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Quotes
        Schema::table('quotes', function (Blueprint $table) {
            $table->index('status');
            $table->index('organization_id');
            $table->index('work_order_id');
            $table->index('valid_until');
        });

        // Warranty Claims
        Schema::table('warranty_claims', function (Blueprint $table) {
            $table->index('status');
            $table->index('work_order_id');
            $table->index('equipment_id');
            $table->index('warranty_id');
            $table->index('submitted_at');
        });

        // Payments
        Schema::table('payments', function (Blueprint $table) {
            $table->index('invoice_id');
            $table->index('method');
            $table->index('paid_at');
        });

        // Inventory Transactions
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->index('type');
            $table->index('part_id');
            $table->index('location_id');
            $table->index('work_order_id');
            $table->index('user_id');
            $table->index('created_at');
        });

        // Work Order Parts
        Schema::table('work_order_parts', function (Blueprint $table) {
            $table->index('work_order_id');
            $table->index('part_id');
        });

        // Work Order Events
        Schema::table('work_order_events', function (Blueprint $table) {
            $table->index('work_order_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
        });

        // Message Threads
        Schema::table('message_threads', function (Blueprint $table) {
            $table->index('organization_id');
            $table->index('work_order_id');
            $table->index('created_by_user_id');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['work_order_id']);
            $table->dropIndex(['valid_until']);
        });

        Schema::table('warranty_claims', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['work_order_id']);
            $table->dropIndex(['equipment_id']);
            $table->dropIndex(['warranty_id']);
            $table->dropIndex(['submitted_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['method']);
            $table->dropIndex(['paid_at']);
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['part_id']);
            $table->dropIndex(['location_id']);
            $table->dropIndex(['work_order_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('work_order_parts', function (Blueprint $table) {
            $table->dropIndex(['work_order_id']);
            $table->dropIndex(['part_id']);
        });

        Schema::table('work_order_events', function (Blueprint $table) {
            $table->dropIndex(['work_order_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('message_threads', function (Blueprint $table) {
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['work_order_id']);
            $table->dropIndex(['created_by_user_id']);
            $table->dropIndex(['updated_at']);
        });
    }
};
