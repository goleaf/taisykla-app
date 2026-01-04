<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            // Manufacturer relationship
            $table->foreignId('manufacturer_id')->nullable()->after('equipment_category_id')
                ->constrained('manufacturers')->nullOnDelete();

            // Lifecycle management
            $table->unsignedInteger('expected_lifespan_months')->nullable()->after('status');
            $table->unsignedTinyInteger('health_score')->nullable()->after('expected_lifespan_months');
            $table->string('lifecycle_status')->default('operational')->after('health_score');

            // Parent-child hierarchy
            $table->foreignId('parent_equipment_id')->nullable()->after('organization_id')
                ->constrained('equipment')->nullOnDelete();

            // Network information
            $table->string('ip_address')->nullable()->after('location_room');
            $table->string('mac_address')->nullable()->after('ip_address');

            // Physical dimensions
            $table->string('dimensions')->nullable()->after('mac_address');
            $table->decimal('weight', 8, 2)->nullable()->after('dimensions');

            // QR/Barcode
            $table->string('qr_code')->nullable()->unique()->after('asset_tag');
            $table->string('barcode')->nullable()->unique()->after('qr_code');

            // Maintenance tracking
            $table->timestamp('last_maintenance_at')->nullable()->after('notes');
            $table->timestamp('next_maintenance_due_at')->nullable()->after('last_maintenance_at');

            // Add indexes for common queries
            $table->index('lifecycle_status');
            $table->index('health_score');
            $table->index(['location_building', 'location_floor', 'location_room']);
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropForeign(['manufacturer_id']);
            $table->dropForeign(['parent_equipment_id']);
            $table->dropIndex(['lifecycle_status']);
            $table->dropIndex(['health_score']);
            $table->dropIndex(['location_building', 'location_floor', 'location_room']);
            $table->dropColumn([
                'manufacturer_id',
                'expected_lifespan_months',
                'health_score',
                'lifecycle_status',
                'parent_equipment_id',
                'ip_address',
                'mac_address',
                'dimensions',
                'weight',
                'qr_code',
                'barcode',
                'last_maintenance_at',
                'next_maintenance_due_at',
            ]);
        });
    }
};
