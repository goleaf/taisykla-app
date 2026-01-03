<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->decimal('purchase_price', 10, 2)->nullable()->after('purchase_date');
            $table->string('purchase_vendor')->nullable()->after('purchase_price');
            $table->string('location_building')->nullable()->after('location_name');
            $table->string('location_floor')->nullable()->after('location_building');
            $table->string('location_room')->nullable()->after('location_floor');
            $table->json('specifications')->nullable()->after('notes');
            $table->json('custom_fields')->nullable()->after('specifications');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_price',
                'purchase_vendor',
                'location_building',
                'location_floor',
                'location_room',
                'specifications',
                'custom_fields',
            ]);
        });
    }
};
