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
        // Audit Logs Indexes
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['subject_type', 'subject_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });

        // Knowledge Articles Indexes
        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('status');
            $table->index('visibility');
            $table->index('is_published');
            $table->index('is_featured');
            $table->index('language');
            $table->index('translation_of_id');
            $table->index('created_at');
            $table->index('updated_at');
        });

        // Knowledge Search Logs Indexes
        Schema::table('knowledge_search_logs', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('created_at');
        });

        // Knowledge Article Views Indexes
        Schema::table('knowledge_article_views', function (Blueprint $table) {
            $table->index('knowledge_article_id');
            $table->index('user_id');
            $table->index('created_at');
        });

        // Knowledge Article Feedback Indexes
        Schema::table('knowledge_article_feedback', function (Blueprint $table) {
            $table->index('knowledge_article_id');
            $table->index('user_id');
            $table->index('created_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['subject_type', 'subject_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['action']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['visibility']);
            $table->dropIndex(['is_published']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['language']);
            $table->dropIndex(['translation_of_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
        });

        Schema::table('knowledge_search_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('knowledge_article_views', function (Blueprint $table) {
            $table->dropIndex(['knowledge_article_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('knowledge_article_feedback', function (Blueprint $table) {
            $table->dropIndex(['knowledge_article_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status']);
        });
    }
};