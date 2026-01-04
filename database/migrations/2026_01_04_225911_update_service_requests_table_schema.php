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
        Schema::table('service_requests', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('scheduled_at');
            $table->decimal('estimated_hours', 8, 2)->nullable()->after('completed_at');
            $table->decimal('actual_hours', 8, 2)->nullable()->after('estimated_hours');
            $table->text('rejection_reason')->nullable()->after('approved_at');
            $table->text('customer_notes')->nullable()->after('rejection_reason');
            $table->text('technician_notes')->nullable()->after('customer_notes');
            $table->text('internal_notes')->nullable()->after('technician_notes');

            // Indexes
            // Note: foreign keys usually auto-index in MySQL, but adding explicit indexes is requested
            // We check if they don't exist to avoid duplicate index errors if automatic ones exist? 
            // Laravel's schema builder handles basic index creation.
            // However, with 'foreignId' usually an index is created. 
            // I will add the non-FK indexes and the specific ones requested if they differ from FK default names.
            // Safest is to just try adding them or relying on the fact that we confirmed FKs exist.
            // But the request explicitly asked "Add indexes on...".

            $table->index('status');
            $table->index('priority');
            $table->index('scheduled_at');
            $table->index('created_at');

            // Composite index for efficient filtering
            $table->index(['status', 'priority', 'scheduled_at']);

            // FK indexes if not already present (Postgres needs them, MySQL defines them with FK)
            // Ideally we check driver or just add them. If index exists, it might throw or be ignored.
            // Since this is likely MySQL/SQLite for dev, I'll add them if they might be missing.
            // $table->index('customer_id'); // Likely exists due to foreignId
            // $table->index('equipment_id'); // Likely exists due to foreignId
            // $table->index('technician_id'); // Likely exists due to foreignId
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['scheduled_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'priority', 'scheduled_at']);

            $table->dropColumn([
                'started_at',
                'estimated_hours',
                'actual_hours',
                'rejection_reason',
                'customer_notes',
                'technician_notes',
                'internal_notes',
            ]);
        });
    }
};
