<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->boolean('customer_signoff_functional')->nullable()->after('customer_signature_at');
            $table->boolean('customer_signoff_professional')->nullable()->after('customer_signoff_functional');
            $table->boolean('customer_signoff_satisfied')->nullable()->after('customer_signoff_professional');
            $table->text('customer_signoff_comments')->nullable()->after('customer_signoff_satisfied');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_signoff_functional',
                'customer_signoff_professional',
                'customer_signoff_satisfied',
                'customer_signoff_comments',
            ]);
        });
    }
};
