<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_feedback', function (Blueprint $table) {
            $table->unsignedTinyInteger('professionalism_rating')->nullable()->after('rating');
            $table->unsignedTinyInteger('knowledge_rating')->nullable()->after('professionalism_rating');
            $table->unsignedTinyInteger('communication_rating')->nullable()->after('knowledge_rating');
            $table->unsignedTinyInteger('timeliness_rating')->nullable()->after('communication_rating');
            $table->unsignedTinyInteger('quality_rating')->nullable()->after('timeliness_rating');
            $table->boolean('would_recommend')->nullable()->after('quality_rating');
        });
    }

    public function down(): void
    {
        Schema::table('work_order_feedback', function (Blueprint $table) {
            $table->dropColumn([
                'professionalism_rating',
                'knowledge_rating',
                'communication_rating',
                'timeliness_rating',
                'quality_rating',
                'would_recommend',
            ]);
        });
    }
};
