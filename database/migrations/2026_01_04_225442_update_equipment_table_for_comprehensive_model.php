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
        Schema::table('equipment', function (Blueprint $table) {
            // Handle organization_id -> customer_id
            if (Schema::hasColumn('equipment', 'organization_id')) {
                // Drop FK if exists (assuming strict checking or ignore error if not exists difficult in migration without specific name)
                // We'll try to rename.
                $table->renameColumn('organization_id', 'customer_id');
            } else {
                if (!Schema::hasColumn('equipment', 'customer_id')) {
                    $table->foreignId('customer_id')->nullable()->constrained('organizations');
                }
            }

            // Handle equipment_category_id -> equipment_type_id
            if (Schema::hasColumn('equipment', 'equipment_category_id')) {
                $table->renameColumn('equipment_category_id', 'equipment_type_id');
            } else {
                if (!Schema::hasColumn('equipment', 'equipment_type_id')) {
                    $table->foreignId('equipment_type_id')->nullable()->constrained('equipment_types');
                }
            }

            if (!Schema::hasColumn('equipment', 'warranty_expiry')) {
                $table->date('warranty_expiry')->nullable();
            }

            if (!Schema::hasColumn('equipment', 'location')) {
                $table->string('location')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['warranty_expiry', 'location']);

            if (Schema::hasColumn('equipment', 'customer_id')) {
                $table->renameColumn('customer_id', 'organization_id');
            }
            if (Schema::hasColumn('equipment', 'equipment_type_id')) {
                $table->renameColumn('equipment_type_id', 'equipment_category_id');
            }
        });
    }
};
