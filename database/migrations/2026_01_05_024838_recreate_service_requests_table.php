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
        Schema::dropIfExists('service_requests');

        Schema::create('service_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('equipment_id')->nullable()->constrained('equipment');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');

            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('description');

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();

            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->text('customer_notes')->nullable();
            $table->text('technician_notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            // Note: foreign keys (customer_id, equipment_id, technician_id) are indexed by default.
            $table->index('status');
            $table->index('priority');
            $table->index('scheduled_at');
            $table->index('created_at');

            // Composite index
            $table->index(['status', 'priority', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('service_requests')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropForeign(['equipment_id']);
                $table->dropForeign(['technician_id']);
                $table->dropForeign(['approved_by']);

                $table->dropIndex(['status']);
                $table->dropIndex(['priority']);
                $table->dropIndex(['scheduled_at']);
                $table->dropIndex(['created_at']);
                $table->dropIndex(['status', 'priority', 'scheduled_at']);
            });

            Schema::dropIfExists('service_requests');
        }
    }
};
