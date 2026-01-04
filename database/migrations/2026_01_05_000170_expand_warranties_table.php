<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            $table->string('document_path')->nullable()->after('claim_instructions');
            $table->text('terms_conditions')->nullable()->after('document_path');
            $table->decimal('renewal_cost', 10, 2)->nullable()->after('terms_conditions');
            $table->boolean('is_renewable')->default(false)->after('renewal_cost');
            $table->string('warranty_number')->nullable()->after('provider_name');
            $table->string('contact_phone')->nullable()->after('is_renewable');
            $table->string('contact_email')->nullable()->after('contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            $table->dropColumn([
                'document_path',
                'terms_conditions',
                'renewal_cost',
                'is_renewable',
                'warranty_number',
                'contact_phone',
                'contact_email',
            ]);
        });
    }
};
