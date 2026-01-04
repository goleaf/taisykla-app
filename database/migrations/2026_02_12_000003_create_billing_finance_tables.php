<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('manual');
            $table->string('method_type')->default('card');
            $table->string('token')->nullable();
            $table->string('brand')->nullable();
            $table->string('last4')->nullable();
            $table->unsignedTinyInteger('exp_month')->nullable();
            $table->unsignedSmallInteger('exp_year')->nullable();
            $table->text('billing_address')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payment_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('applied_amount', 10, 2)->default(0);
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->default('active');
            $table->string('frequency')->default('monthly');
            $table->unsignedInteger('interval_count')->default(1);
            $table->unsignedInteger('installment_count')->nullable();
            $table->date('start_date')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->boolean('auto_charge')->default(false);
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_plan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_plan_id')->constrained('payment_plans')->cascadeOnDelete();
            $table->date('due_date')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status')->default('scheduled');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('recurring_billing_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_agreement_id')->nullable()->constrained('service_agreements')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->string('frequency')->default('monthly');
            $table->unsignedTinyInteger('bill_day')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('auto_charge')->default(false);
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->timestamp('next_invoice_at')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('recurring_billing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('recurring_billing_plans')->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('tax_rate', 7, 4)->default(0);
            $table->boolean('is_taxable')->default(true);
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('billing_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reminder_type')->default('due');
            $table->string('channel')->default('email');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->unsignedInteger('attempts')->default(0);
            $table->foreignId('template_id')->nullable()->constrained('communication_templates')->nullOnDelete();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        Schema::create('credit_memos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status')->default('issued');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('accounting_exports', function (Blueprint $table) {
            $table->id();
            $table->string('integration')->default('general_ledger');
            $table->string('export_type')->default('invoices');
            $table->string('status')->default('queued');
            $table->json('payload')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_exports');
        Schema::dropIfExists('credit_memos');
        Schema::dropIfExists('billing_reminders');
        Schema::dropIfExists('recurring_billing_items');
        Schema::dropIfExists('recurring_billing_plans');
        Schema::dropIfExists('payment_plan_installments');
        Schema::dropIfExists('payment_plans');
        Schema::dropIfExists('payment_applications');
        Schema::dropIfExists('payment_methods');
    }
};
