<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('child_equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->string('relationship_type'); // contains, depends_on, powers, connects_to
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['parent_equipment_id', 'child_equipment_id', 'relationship_type'], 'equipment_rel_unique');
            $table->index('relationship_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_relationships');
    }
};
