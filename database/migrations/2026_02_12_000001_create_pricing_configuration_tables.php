<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labor_rate_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('labor_rate_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_tier_id')->constrained('labor_rate_tiers')->cascadeOnDelete();
            $table->string('service_type')->nullable();
            $table->string('time_category')->default('regular');
            $table->string('day_type')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('multiplier', 6, 3)->default(1);
            $table->decimal('fixed_rate', 10, 2)->nullable();
            $table->decimal('emergency_premium', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['rate_tier_id', 'service_type']);
        });

        Schema::create('labor_rate_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_tier_id')->nullable()->constrained('labor_rate_tiers')->nullOnDelete();
            $table->string('service_type')->nullable();
            $table->string('time_category')->default('regular');
            $table->decimal('fixed_rate', 10, 2)->nullable();
            $table->decimal('multiplier', 6, 3)->default(1);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'service_type']);
        });

        Schema::create('pricing_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('labor_rate_tier_id')->nullable()->constrained('labor_rate_tiers')->nullOnDelete();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('parts_markup_percent', 5, 2)->default(0);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('terms')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pricing_contract_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_contract_id')->constrained()->cascadeOnDelete();
            $table->string('item_type')->default('labor');
            $table->string('service_type')->nullable();
            $table->foreignId('part_id')->nullable()->constrained('parts')->nullOnDelete();
            $table->foreignId('part_category_id')->nullable()->constrained('part_categories')->nullOnDelete();
            $table->decimal('rate_override', 10, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('fixed_price', 10, 2)->nullable();
            $table->unsignedInteger('min_quantity')->nullable();
            $table->unsignedInteger('max_quantity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pricing_volume_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('applies_to')->default('labor');
            $table->string('threshold_type')->default('amount');
            $table->decimal('threshold_value', 10, 2)->default(0);
            $table->string('discount_type')->default('percent');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->string('service_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('part_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('part_id')->nullable()->constrained('parts')->nullOnDelete();
            $table->foreignId('part_category_id')->nullable()->constrained('part_categories')->nullOnDelete();
            $table->string('cost_basis')->default('average');
            $table->string('markup_type')->default('percent');
            $table->decimal('markup_value', 10, 2)->default(0);
            $table->decimal('fixed_price', 10, 2)->nullable();
            $table->unsignedInteger('min_quantity')->nullable();
            $table->unsignedInteger('max_quantity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('part_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('bundle_price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('part_bundle_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained('part_bundles')->cascadeOnDelete();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['bundle_id', 'part_id']);
        });

        Schema::create('service_fees', function (Blueprint $table) {
            $table->id();
            $table->string('fee_type');
            $table->text('description')->nullable();
            $table->string('rate_type')->default('fixed');
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('minimum_amount', 10, 2)->nullable();
            $table->decimal('maximum_amount', 10, 2)->nullable();
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_fees');
        Schema::dropIfExists('part_bundle_items');
        Schema::dropIfExists('part_bundles');
        Schema::dropIfExists('part_pricing_rules');
        Schema::dropIfExists('pricing_volume_discounts');
        Schema::dropIfExists('pricing_contract_items');
        Schema::dropIfExists('pricing_contracts');
        Schema::dropIfExists('labor_rate_overrides');
        Schema::dropIfExists('labor_rate_rules');
        Schema::dropIfExists('labor_rate_tiers');
    }
};
