<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('part_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('part_categories')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('payment_terms')->nullable();
            $table->unsignedInteger('lead_time_days')->default(0);
            $table->unsignedInteger('min_order_quantity')->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('performance_rating', 4, 2)->default(0);
            $table->boolean('is_preferred')->default(false);
            $table->string('external_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('part_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('supplier_part_number')->nullable();
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->unsignedInteger('lead_time_days')->default(0);
            $table->unsignedInteger('min_order_quantity')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamp('last_cost_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['part_id', 'supplier_id']);
        });

        Schema::create('part_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('url');
            $table->string('link_type')->default('datasheet');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('part_substitutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->foreignId('substitute_part_id')->constrained('parts')->cascadeOnDelete();
            $table->boolean('is_preferred')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['part_id', 'substitute_part_id']);
        });

        Schema::create('part_compatibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['part_id', 'equipment_id']);
        });

        Schema::create('part_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->unsignedInteger('min_quantity')->default(1);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->string('currency')->default('USD');
            $table->timestamps();
        });

        Schema::create('part_cost_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('unit_cost', 10, 2);
            $table->string('currency')->default('USD');
            $table->timestamp('effective_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('part_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->decimal('unit_price', 10, 2);
            $table->string('currency')->default('USD');
            $table->timestamp('effective_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('reference_number')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('ordered_at')->nullable();
            $table->date('needed_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('part_id')->nullable()->constrained('parts')->nullOnDelete();
            $table->string('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->unsignedInteger('received_quantity')->default(0);
            $table->string('status')->default('open');
            $table->date('expected_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->foreignId('to_location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->text('note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->string('status')->default('scheduled');
            $table->date('scheduled_for')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('counted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('counted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_count_id')->constrained('inventory_counts')->cascadeOnDelete();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->integer('expected_quantity')->default(0);
            $table->integer('counted_quantity')->nullable();
            $table->integer('variance')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['inventory_count_id', 'part_id']);
        });

        Schema::table('parts', function (Blueprint $table) {
            $table->foreignId('part_category_id')->nullable()->constrained('part_categories')->nullOnDelete();
            $table->string('manufacturer')->nullable();
            $table->string('manufacturer_part_number')->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->string('barcode')->nullable();
            $table->string('rfid_tag')->nullable();
            $table->json('specifications')->nullable();
            $table->text('compatibility_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_obsolete')->default(false);
            $table->text('obsolete_reason')->nullable();
            $table->timestamp('obsoleted_at')->nullable();
            $table->unsignedInteger('reorder_point')->default(0);
            $table->unsignedInteger('reorder_quantity')->default(0);
            $table->decimal('average_cost', 10, 2)->default(0);
            $table->string('costing_method')->default('weighted_average');
            $table->decimal('markup_percentage', 6, 2)->default(0);
            $table->unsignedInteger('annual_demand')->default(0);
            $table->decimal('ordering_cost', 10, 2)->default(0);
            $table->decimal('holding_cost', 10, 2)->default(0);
            $table->foreignId('primary_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->timestamp('last_received_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
        });

        Schema::table('inventory_locations', function (Blueprint $table) {
            $table->string('code')->nullable();
            $table->string('type')->default('warehouse');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('bin_location')->nullable();
            $table->string('shelf_location')->nullable();
            $table->unsignedInteger('min_quantity')->default(0);
            $table->unsignedInteger('reorder_quantity')->default(0);
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->string('reason')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('document_number')->nullable();
            $table->foreignId('source_location_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            $table->foreignId('destination_location_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            $table->foreignId('transfer_id')->nullable()->constrained('inventory_transfers')->nullOnDelete();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropIndex(['reference_type', 'reference_id']);
            $table->dropConstrainedForeignId('source_location_id');
            $table->dropConstrainedForeignId('destination_location_id');
            $table->dropConstrainedForeignId('transfer_id');
            $table->dropColumn(['reason', 'reference_type', 'reference_id', 'document_number']);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['bin_location', 'shelf_location', 'min_quantity', 'reorder_quantity']);
        });

        Schema::table('inventory_locations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_user_id');
            $table->dropColumn(['code', 'type', 'is_active']);
        });

        Schema::table('parts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('part_category_id');
            $table->dropConstrainedForeignId('primary_supplier_id');
            $table->dropColumn([
                'manufacturer',
                'manufacturer_part_number',
                'unit_of_measure',
                'barcode',
                'rfid_tag',
                'specifications',
                'compatibility_notes',
                'is_active',
                'is_obsolete',
                'obsolete_reason',
                'obsoleted_at',
                'reorder_point',
                'reorder_quantity',
                'average_cost',
                'costing_method',
                'markup_percentage',
                'annual_demand',
                'ordering_cost',
                'holding_cost',
                'last_received_at',
                'last_used_at',
            ]);
        });

        Schema::dropIfExists('inventory_count_items');
        Schema::dropIfExists('inventory_counts');
        Schema::dropIfExists('inventory_transfers');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('part_price_histories');
        Schema::dropIfExists('part_cost_histories');
        Schema::dropIfExists('part_price_tiers');
        Schema::dropIfExists('part_compatibilities');
        Schema::dropIfExists('part_substitutions');
        Schema::dropIfExists('part_links');
        Schema::dropIfExists('part_suppliers');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('part_categories');
    }
};
