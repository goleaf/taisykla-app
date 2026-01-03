<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('knowledge_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('knowledge_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('usage_count')->default(0);
            $table->timestamps();
        });

        Schema::create('knowledge_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('content_type')->default('how_to');
            $table->text('description')->nullable();
            $table->json('sections')->nullable();
            $table->longText('body')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->text('summary')->nullable()->after('title');
            $table->string('content_type')->default('how_to')->after('summary');
            $table->string('content_format')->default('html')->after('content');
            $table->foreignId('category_id')->nullable()->after('slug')->constrained('knowledge_categories')->nullOnDelete();
            $table->string('visibility')->default('public')->after('content_format');
            $table->json('visibility_roles')->nullable()->after('visibility');
            $table->string('status')->default('draft')->after('is_published');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->boolean('is_promoted')->default(false)->after('is_featured');
            $table->timestamp('featured_at')->nullable()->after('is_promoted');
            $table->unsignedInteger('featured_order')->nullable()->after('featured_at');
            $table->string('author_name')->nullable()->after('published_at');
            $table->string('author_title')->nullable()->after('author_name');
            $table->unsignedInteger('reading_time_minutes')->nullable()->after('author_title');
            $table->string('language')->default('en')->after('reading_time_minutes');
            $table->string('translation_status')->default('published')->after('language');
            $table->boolean('is_machine_translated')->default(false)->after('translation_status');
            $table->foreignId('translation_of_id')->nullable()->after('is_machine_translated')->constrained('knowledge_articles')->nullOnDelete();
            $table->string('seo_title')->nullable()->after('translation_of_id');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->string('template_key')->nullable()->after('seo_description');
            $table->boolean('allow_comments')->default(true)->after('template_key');
            $table->timestamp('review_due_at')->nullable()->after('allow_comments');
            $table->timestamp('reviewed_at')->nullable()->after('review_due_at');
            $table->foreignId('reviewed_by_user_id')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable()->after('reviewed_by_user_id');
            $table->timestamp('expires_at')->nullable()->after('review_notes');
            $table->timestamp('archived_at')->nullable()->after('expires_at');
            $table->timestamp('scheduled_for')->nullable()->after('archived_at');
            $table->unsignedBigInteger('view_count')->default(0)->after('scheduled_for');
            $table->unsignedBigInteger('helpful_count')->default(0)->after('view_count');
            $table->unsignedBigInteger('unhelpful_count')->default(0)->after('helpful_count');
            $table->unsignedBigInteger('rating_count')->default(0)->after('unhelpful_count');
            $table->decimal('rating_avg', 4, 2)->default(0)->after('rating_count');
            $table->unsignedBigInteger('comment_count')->default(0)->after('rating_avg');
            $table->unsignedBigInteger('share_count')->default(0)->after('comment_count');
            $table->unsignedBigInteger('download_count')->default(0)->after('share_count');
            $table->unsignedInteger('current_version')->default(1)->after('download_count');
            $table->json('meta')->nullable()->after('current_version');
        });

        Schema::create('knowledge_article_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('knowledge_tag_id')->constrained('knowledge_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['knowledge_article_id', 'knowledge_tag_id']);
        });

        Schema::create('knowledge_article_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('related_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->string('relation_type')->default('related');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['knowledge_article_id', 'related_article_id', 'relation_type']);
        });

        Schema::create('knowledge_article_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('content_format')->default('html');
            $table->json('meta')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('knowledge_article_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->string('label');
            $table->string('resource_type')->default('file');
            $table->string('url')->nullable();
            $table->string('file_type')->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->boolean('is_downloadable')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_article_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('feedback_type')->default('general');
            $table->boolean('is_helpful')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('knowledge_article_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->boolean('is_helpful')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason')->nullable();
            $table->unsignedInteger('upvotes')->default(0);
            $table->unsignedInteger('downvotes')->default(0);
            $table->timestamps();
        });

        Schema::create('knowledge_article_comment_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_article_comment_id')->constrained('knowledge_article_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->smallInteger('value')->default(1);
            $table->timestamps();

            $table->unique(['knowledge_article_comment_id', 'user_id']);
        });

        Schema::create('knowledge_article_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_search_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('query');
            $table->json('filters')->nullable();
            $table->unsignedInteger('results_count')->default(0);
            $table->boolean('had_click')->default(false);
            $table->foreignId('clicked_article_id')->nullable()->constrained('knowledge_articles')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('knowledge_article_support_ticket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('added_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('context')->default('suggested');
            $table->timestamps();

            $table->unique(['knowledge_article_id', 'support_ticket_id', 'context']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_article_support_ticket');
        Schema::dropIfExists('knowledge_search_logs');
        Schema::dropIfExists('knowledge_article_views');
        Schema::dropIfExists('knowledge_article_comment_votes');
        Schema::dropIfExists('knowledge_article_comments');
        Schema::dropIfExists('knowledge_article_feedback');
        Schema::dropIfExists('knowledge_article_resources');
        Schema::dropIfExists('knowledge_article_versions');
        Schema::dropIfExists('knowledge_article_relations');
        Schema::dropIfExists('knowledge_article_tag');

        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['translation_of_id']);
            $table->dropForeign(['reviewed_by_user_id']);
            $table->dropColumn([
                'summary',
                'content_type',
                'content_format',
                'category_id',
                'visibility',
                'visibility_roles',
                'status',
                'is_featured',
                'is_promoted',
                'featured_at',
                'featured_order',
                'author_name',
                'author_title',
                'reading_time_minutes',
                'language',
                'translation_status',
                'is_machine_translated',
                'translation_of_id',
                'seo_title',
                'seo_description',
                'template_key',
                'allow_comments',
                'review_due_at',
                'reviewed_at',
                'reviewed_by_user_id',
                'review_notes',
                'expires_at',
                'archived_at',
                'scheduled_for',
                'view_count',
                'helpful_count',
                'unhelpful_count',
                'rating_count',
                'rating_avg',
                'comment_count',
                'share_count',
                'download_count',
                'current_version',
                'meta',
            ]);
        });

        Schema::dropIfExists('knowledge_templates');
        Schema::dropIfExists('knowledge_tags');
        Schema::dropIfExists('knowledge_categories');
    }
};
