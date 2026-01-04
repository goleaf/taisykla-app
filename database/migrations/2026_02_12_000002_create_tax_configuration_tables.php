<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_jurisdictions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('jurisdiction_type')->default('state');
            $table->string('code')->nullable();
            $table->decimal('rate', 7, 4)->default(0);
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_jurisdiction_id')->constrained('tax_jurisdictions')->cascadeOnDelete();
            $table->string('applies_to')->default('all');
            $table->string('service_type')->nullable();
            $table->foreignId('part_category_id')->nullable()->constrained('part_categories')->nullOnDelete();
            $table->string('fee_type')->nullable();
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('organization_tax_jurisdictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_jurisdiction_id')->constrained('tax_jurisdictions')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('priority')->default(0);
            $table->timestamps();

            $table->unique(['organization_id', 'tax_jurisdiction_id']);
        });

        Schema::create('tax_exemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->string('certificate_number')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tax_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->decimal('rate', 7, 4)->default(0);
            $table->text('reason')->nullable();
            $table->foreignId('applied_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_overrides');
        Schema::dropIfExists('tax_exemptions');
        Schema::dropIfExists('organization_tax_jurisdictions');
        Schema::dropIfExists('tax_rules');
        Schema::dropIfExists('tax_jurisdictions');
    }
};
