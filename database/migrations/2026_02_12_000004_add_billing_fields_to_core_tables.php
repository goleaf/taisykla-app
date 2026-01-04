<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (! Schema::hasColumn('organizations', 'billing_contact_name')) {
                $table->string('billing_contact_name')->nullable()->after('primary_contact_phone');
            }
            if (! Schema::hasColumn('organizations', 'billing_phone')) {
                $table->string('billing_phone')->nullable()->after('billing_contact_name');
            }
            if (! Schema::hasColumn('organizations', 'billing_currency')) {
                $table->string('billing_currency')->default('USD')->after('billing_address');
            }
            if (! Schema::hasColumn('organizations', 'payment_terms')) {
                $table->string('payment_terms')->nullable()->after('billing_currency');
            }
            if (! Schema::hasColumn('organizations', 'credit_limit')) {
                $table->decimal('credit_limit', 12, 2)->default(0)->after('payment_terms');
            }
            if (! Schema::hasColumn('organizations', 'credit_balance')) {
                $table->decimal('credit_balance', 12, 2)->default(0)->after('credit_limit');
            }
            if (! Schema::hasColumn('organizations', 'allow_over_limit')) {
                $table->boolean('allow_over_limit')->default(false)->after('credit_balance');
            }
            if (! Schema::hasColumn('organizations', 'is_tax_exempt')) {
                $table->boolean('is_tax_exempt')->default(false)->after('allow_over_limit');
            }
            if (! Schema::hasColumn('organizations', 'tax_exempt_reason')) {
                $table->string('tax_exempt_reason')->nullable()->after('is_tax_exempt');
            }
            if (! Schema::hasColumn('organizations', 'tax_exempt_valid_until')) {
                $table->date('tax_exempt_valid_until')->nullable()->after('tax_exempt_reason');
            }
            if (! Schema::hasColumn('organizations', 'default_labor_rate_tier_id')) {
                $table->foreignId('default_labor_rate_tier_id')->nullable()->constrained('labor_rate_tiers')->nullOnDelete()->after('tax_exempt_valid_until');
            }
            if (! Schema::hasColumn('organizations', 'pricing_contract_id')) {
                $table->foreignId('pricing_contract_id')->nullable()->constrained('pricing_contracts')->nullOnDelete()->after('default_labor_rate_tier_id');
            }
        });

        Schema::table('quotes', function (Blueprint $table) {
            if (! Schema::hasColumn('quotes', 'quote_number')) {
                $table->string('quote_number')->nullable()->after('id');
                $table->unique('quote_number');
            }
            if (! Schema::hasColumn('quotes', 'quote_type')) {
                $table->string('quote_type')->default('standard')->after('status');
            }
            if (! Schema::hasColumn('quotes', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('quote_type');
            }
            if (! Schema::hasColumn('quotes', 'terms')) {
                $table->text('terms')->nullable()->after('total');
            }
            if (! Schema::hasColumn('quotes', 'labor_subtotal')) {
                $table->decimal('labor_subtotal', 10, 2)->default(0)->after('total');
            }
            if (! Schema::hasColumn('quotes', 'parts_subtotal')) {
                $table->decimal('parts_subtotal', 10, 2)->default(0)->after('labor_subtotal');
            }
            if (! Schema::hasColumn('quotes', 'fees_subtotal')) {
                $table->decimal('fees_subtotal', 10, 2)->default(0)->after('parts_subtotal');
            }
            if (! Schema::hasColumn('quotes', 'discount_total')) {
                $table->decimal('discount_total', 10, 2)->default(0)->after('fees_subtotal');
            }
            if (! Schema::hasColumn('quotes', 'tax_total')) {
                $table->decimal('tax_total', 10, 2)->default(0)->after('discount_total');
            }
            if (! Schema::hasColumn('quotes', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('valid_until');
            }
            if (! Schema::hasColumn('quotes', 'approved_by_user_id')) {
                $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
            }
            if (! Schema::hasColumn('quotes', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_by_user_id');
            }
            if (! Schema::hasColumn('quotes', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
            if (! Schema::hasColumn('quotes', 'signature_name')) {
                $table->string('signature_name')->nullable()->after('rejection_reason');
            }
            if (! Schema::hasColumn('quotes', 'signature_data')) {
                $table->text('signature_data')->nullable()->after('signature_name');
            }
            if (! Schema::hasColumn('quotes', 'signature_ip')) {
                $table->string('signature_ip')->nullable()->after('signature_data');
            }
            if (! Schema::hasColumn('quotes', 'revision_of_quote_id')) {
                $table->foreignId('revision_of_quote_id')->nullable()->constrained('quotes')->nullOnDelete()->after('signature_ip');
            }
            if (! Schema::hasColumn('quotes', 'currency')) {
                $table->string('currency')->default('USD')->after('revision_of_quote_id');
            }
        });

        Schema::table('quote_items', function (Blueprint $table) {
            if (! Schema::hasColumn('quote_items', 'item_type')) {
                $table->string('item_type')->default('service')->after('quote_id');
            }
            if (! Schema::hasColumn('quote_items', 'part_id')) {
                $table->foreignId('part_id')->nullable()->constrained('parts')->nullOnDelete()->after('item_type');
            }
            if (! Schema::hasColumn('quote_items', 'service_type')) {
                $table->string('service_type')->nullable()->after('part_id');
            }
            if (! Schema::hasColumn('quote_items', 'labor_minutes')) {
                $table->unsignedInteger('labor_minutes')->nullable()->after('service_type');
            }
            if (! Schema::hasColumn('quote_items', 'unit_cost')) {
                $table->decimal('unit_cost', 10, 2)->nullable()->after('unit_price');
            }
            if (! Schema::hasColumn('quote_items', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('unit_cost');
            }
            if (! Schema::hasColumn('quote_items', 'tax_rate')) {
                $table->decimal('tax_rate', 7, 4)->default(0)->after('discount_amount');
            }
            if (! Schema::hasColumn('quote_items', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate');
            }
            if (! Schema::hasColumn('quote_items', 'is_taxable')) {
                $table->boolean('is_taxable')->default(true)->after('tax_amount');
            }
            if (! Schema::hasColumn('quote_items', 'position')) {
                $table->unsignedInteger('position')->default(0)->after('is_taxable');
            }
            if (! Schema::hasColumn('quote_items', 'metadata')) {
                $table->json('metadata')->nullable()->after('position');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('id');
                $table->unique('invoice_number');
            }
            if (! Schema::hasColumn('invoices', 'invoice_type')) {
                $table->string('invoice_type')->default('standard')->after('status');
            }
            if (! Schema::hasColumn('invoices', 'terms')) {
                $table->text('terms')->nullable()->after('total');
            }
            if (! Schema::hasColumn('invoices', 'issued_at')) {
                $table->timestamp('issued_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('invoices', 'labor_subtotal')) {
                $table->decimal('labor_subtotal', 10, 2)->default(0)->after('total');
            }
            if (! Schema::hasColumn('invoices', 'parts_subtotal')) {
                $table->decimal('parts_subtotal', 10, 2)->default(0)->after('labor_subtotal');
            }
            if (! Schema::hasColumn('invoices', 'fees_subtotal')) {
                $table->decimal('fees_subtotal', 10, 2)->default(0)->after('parts_subtotal');
            }
            if (! Schema::hasColumn('invoices', 'discount_total')) {
                $table->decimal('discount_total', 10, 2)->default(0)->after('fees_subtotal');
            }
            if (! Schema::hasColumn('invoices', 'adjustment_total')) {
                $table->decimal('adjustment_total', 10, 2)->default(0)->after('discount_total');
            }
            if (! Schema::hasColumn('invoices', 'tax_total')) {
                $table->decimal('tax_total', 10, 2)->default(0)->after('adjustment_total');
            }
            if (! Schema::hasColumn('invoices', 'balance_due')) {
                $table->decimal('balance_due', 10, 2)->default(0)->after('tax_total');
            }
            if (! Schema::hasColumn('invoices', 'currency')) {
                $table->string('currency')->default('USD')->after('balance_due');
            }
            if (! Schema::hasColumn('invoices', 'parent_invoice_id')) {
                $table->foreignId('parent_invoice_id')->nullable()->constrained('invoices')->nullOnDelete()->after('currency');
            }
            if (! Schema::hasColumn('invoices', 'credit_applied')) {
                $table->decimal('credit_applied', 10, 2)->default(0)->after('parent_invoice_id');
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            if (! Schema::hasColumn('invoice_items', 'item_type')) {
                $table->string('item_type')->default('service')->after('invoice_id');
            }
            if (! Schema::hasColumn('invoice_items', 'part_id')) {
                $table->foreignId('part_id')->nullable()->constrained('parts')->nullOnDelete()->after('item_type');
            }
            if (! Schema::hasColumn('invoice_items', 'service_type')) {
                $table->string('service_type')->nullable()->after('part_id');
            }
            if (! Schema::hasColumn('invoice_items', 'labor_minutes')) {
                $table->unsignedInteger('labor_minutes')->nullable()->after('service_type');
            }
            if (! Schema::hasColumn('invoice_items', 'unit_cost')) {
                $table->decimal('unit_cost', 10, 2)->nullable()->after('unit_price');
            }
            if (! Schema::hasColumn('invoice_items', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('unit_cost');
            }
            if (! Schema::hasColumn('invoice_items', 'tax_rate')) {
                $table->decimal('tax_rate', 7, 4)->default(0)->after('discount_amount');
            }
            if (! Schema::hasColumn('invoice_items', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate');
            }
            if (! Schema::hasColumn('invoice_items', 'is_taxable')) {
                $table->boolean('is_taxable')->default(true)->after('tax_amount');
            }
            if (! Schema::hasColumn('invoice_items', 'position')) {
                $table->unsignedInteger('position')->default(0)->after('is_taxable');
            }
            if (! Schema::hasColumn('invoice_items', 'metadata')) {
                $table->json('metadata')->nullable()->after('position');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'status')) {
                $table->string('status')->default('completed')->after('reference');
            }
            if (! Schema::hasColumn('payments', 'gateway')) {
                $table->string('gateway')->nullable()->after('status');
            }
            if (! Schema::hasColumn('payments', 'fee_amount')) {
                $table->decimal('fee_amount', 10, 2)->default(0)->after('gateway');
            }
            if (! Schema::hasColumn('payments', 'currency')) {
                $table->string('currency')->default('USD')->after('fee_amount');
            }
            if (! Schema::hasColumn('payments', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('paid_at');
            }
            if (! Schema::hasColumn('payments', 'refund_amount')) {
                $table->decimal('refund_amount', 10, 2)->default(0)->after('processed_at');
            }
            if (! Schema::hasColumn('payments', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            }
            if (! Schema::hasColumn('payments', 'payment_method_id')) {
                $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete()->after('refunded_at');
            }
            if (! Schema::hasColumn('payments', 'check_number')) {
                $table->string('check_number')->nullable()->after('payment_method_id');
            }
            if (! Schema::hasColumn('payments', 'deposited_at')) {
                $table->timestamp('deposited_at')->nullable()->after('check_number');
            }
            if (! Schema::hasColumn('payments', 'cleared_at')) {
                $table->timestamp('cleared_at')->nullable()->after('deposited_at');
            }
            if (! Schema::hasColumn('payments', 'bounced_at')) {
                $table->timestamp('bounced_at')->nullable()->after('cleared_at');
            }
            if (! Schema::hasColumn('payments', 'bounce_reason')) {
                $table->text('bounce_reason')->nullable()->after('bounced_at');
            }
            if (! Schema::hasColumn('payments', 'overpayment_amount')) {
                $table->decimal('overpayment_amount', 10, 2)->default(0)->after('bounce_reason');
            }
            if (! Schema::hasColumn('payments', 'applied_amount')) {
                $table->decimal('applied_amount', 10, 2)->default(0)->after('overpayment_amount');
            }
            if (! Schema::hasColumn('payments', 'notes')) {
                $table->text('notes')->nullable()->after('applied_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $columns = [
                'status',
                'gateway',
                'fee_amount',
                'currency',
                'processed_at',
                'refund_amount',
                'refunded_at',
                'payment_method_id',
                'check_number',
                'deposited_at',
                'cleared_at',
                'bounced_at',
                'bounce_reason',
                'overpayment_amount',
                'applied_amount',
                'notes',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $columns = [
                'item_type',
                'part_id',
                'service_type',
                'labor_minutes',
                'unit_cost',
                'discount_amount',
                'tax_rate',
                'tax_amount',
                'is_taxable',
                'position',
                'metadata',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('invoice_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            $columns = [
                'invoice_number',
                'invoice_type',
                'terms',
                'issued_at',
                'labor_subtotal',
                'parts_subtotal',
                'fees_subtotal',
                'discount_total',
                'adjustment_total',
                'tax_total',
                'balance_due',
                'currency',
                'parent_invoice_id',
                'credit_applied',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('quote_items', function (Blueprint $table) {
            $columns = [
                'item_type',
                'part_id',
                'service_type',
                'labor_minutes',
                'unit_cost',
                'discount_amount',
                'tax_rate',
                'tax_amount',
                'is_taxable',
                'position',
                'metadata',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('quote_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('quotes', function (Blueprint $table) {
            $columns = [
                'quote_number',
                'quote_type',
                'version',
                'terms',
                'labor_subtotal',
                'parts_subtotal',
                'fees_subtotal',
                'discount_total',
                'tax_total',
                'expires_at',
                'approved_by_user_id',
                'rejected_at',
                'rejection_reason',
                'signature_name',
                'signature_data',
                'signature_ip',
                'revision_of_quote_id',
                'currency',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('quotes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('organizations', function (Blueprint $table) {
            $columns = [
                'billing_contact_name',
                'billing_phone',
                'billing_currency',
                'payment_terms',
                'credit_limit',
                'credit_balance',
                'allow_over_limit',
                'is_tax_exempt',
                'tax_exempt_reason',
                'tax_exempt_valid_until',
                'default_labor_rate_tier_id',
                'pricing_contract_id',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('organizations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
