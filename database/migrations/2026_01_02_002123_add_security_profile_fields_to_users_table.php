<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->string('employee_id')->nullable();
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_method')->nullable();
            $table->string('mfa_phone')->nullable();
            $table->string('mfa_email')->nullable();
            $table->string('mfa_secret')->nullable();
            $table->timestamp('mfa_confirmed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'department',
                'employee_id',
                'mfa_enabled',
                'mfa_method',
                'mfa_phone',
                'mfa_email',
                'mfa_secret',
                'mfa_confirmed_at',
            ]);
        });
    }
};
