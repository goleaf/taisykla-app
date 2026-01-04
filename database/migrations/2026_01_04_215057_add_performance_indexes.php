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
        // Work Orders
        Schema::table('work_orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('priority');
            $table->index('organization_id');
            $table->index('assigned_to_user_id'); // Technician ID
            $table->index('requested_by_user_id'); // Customer User ID
            $table->index('equipment_id');

            // Date ranges
            $table->index('requested_at');
            $table->index('scheduled_start_at');
            $table->index('completed_at');
            $table->index('created_at');
        });

        // Organizations
        Schema::table('organizations', function (Blueprint $table) {
            $table->index('type');
            $table->index('status');
            $table->index('service_agreement_id');
        });

        // Equipment
        Schema::table('equipment', function (Blueprint $table) {
            $table->index('organization_id');
            $table->index('assigned_user_id');
            $table->index('serial_number');
            $table->index('asset_tag');
            $table->index('status');
            $table->index('model');
            $table->index('manufacturer');
        });

        // Invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('status');
            $table->index('due_date');
            $table->index('organization_id');
            $table->index('work_order_id');
            $table->index('sent_at');
            $table->index('paid_at');
        });

        // Appointments
        Schema::table('appointments', function (Blueprint $table) {
            $table->index('assigned_to_user_id'); // Technician ID
            $table->index('work_order_id');
            $table->index('scheduled_start_at');
            $table->index('status');
        });

        // Parts
        Schema::table('parts', function (Blueprint $table) {
            $table->index('sku');
            $table->index('name'); // Searchable
        });

        // Users
        Schema::table('users', function (Blueprint $table) {
            $table->index('job_title'); // For filtering by role/type if no explicit role column
            $table->index('is_active');
            $table->index('organization_id');
        });

        // Support Tickets
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->index('status');
            $table->index('priority');
            $table->index('assigned_to_user_id');
            $table->index('submitted_by_user_id');
            $table->index('organization_id');
        });

        // Inventory
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->index('part_id');
            $table->index('location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['assigned_to_user_id']);
            $table->dropIndex(['requested_by_user_id']);
            $table->dropIndex(['equipment_id']);
            $table->dropIndex(['requested_at']);
            $table->dropIndex(['scheduled_start_at']);
            $table->dropIndex(['completed_at']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['status']);
            $table->dropIndex(['service_agreement_id']);
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['assigned_user_id']);
            $table->dropIndex(['serial_number']);
            $table->dropIndex(['asset_tag']);
            $table->dropIndex(['status']);
            $table->dropIndex(['model']);
            $table->dropIndex(['manufacturer']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['work_order_id']);
            $table->dropIndex(['sent_at']);
            $table->dropIndex(['paid_at']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['assigned_to_user_id']);
            $table->dropIndex(['work_order_id']);
            $table->dropIndex(['scheduled_start_at']);
            $table->dropIndex(['status']);
        });

        Schema::table('parts', function (Blueprint $table) {
            $table->dropIndex(['sku']);
            $table->dropIndex(['name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['job_title']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['organization_id']);
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['assigned_to_user_id']);
            $table->dropIndex(['submitted_by_user_id']);
            $table->dropIndex(['organization_id']);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropIndex(['part_id']);
            $table->dropIndex(['location_id']);
        });
    }
};
