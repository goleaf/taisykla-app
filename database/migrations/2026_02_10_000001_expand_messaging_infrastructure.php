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
        Schema::table('message_threads', function (Blueprint $table) {
            $table->string('type')->default('direct')->after('subject');
            $table->string('status')->default('open')->after('type');
        });

        Schema::table('message_thread_participants', function (Blueprint $table) {
            $table->string('folder')->default('inbox')->after('user_id');
            $table->boolean('is_starred')->default(false)->after('folder');
            $table->boolean('is_archived')->default(false)->after('is_starred');
            $table->boolean('is_muted')->default(false)->after('is_archived');
            $table->timestamp('deleted_at')->nullable()->after('last_read_at');

            $table->index(['folder', 'is_archived']);
            $table->index(['is_starred']);
            $table->index(['deleted_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('sender_id')->nullable()->after('thread_id')->constrained('users')->nullOnDelete();
            $table->foreignId('recipient_id')->nullable()->after('sender_id')->constrained('users')->nullOnDelete();
            $table->string('subject')->nullable()->after('recipient_id');
            $table->timestamp('timestamp')->nullable()->after('subject');
            $table->boolean('is_read')->default(false)->after('timestamp');
            $table->foreignId('related_work_order_id')->nullable()->after('is_read')->constrained('work_orders')->nullOnDelete();
            $table->foreignId('parent_message_id')->nullable()->after('related_work_order_id')->constrained('messages')->nullOnDelete();
            $table->string('message_type')->default('direct')->after('parent_message_id');
            $table->string('channel')->default('in_app')->after('message_type');
            $table->json('metadata')->nullable()->after('channel');

            $table->index(['message_type', 'channel']);
            $table->index(['timestamp']);
        });

        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('message_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('to');
            $table->string('delivery_status')->default('pending');
            $table->timestamp('read_at')->nullable();
            $table->string('channel')->default('in_app');
            $table->timestamps();

            $table->unique(['message_id', 'user_id', 'role']);
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('channel');
            $table->string('frequency')->default('immediate');
            $table->json('message_types')->nullable();
            $table->json('vip_senders')->nullable();
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'channel']);
        });

        Schema::create('message_automations', function (Blueprint $table) {
            $table->id();
            $table->string('trigger');
            $table->string('name');
            $table->boolean('is_enabled')->default(true);
            $table->json('channels')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('communication_templates')->nullOnDelete();
            $table->integer('schedule_offset_minutes')->nullable();
            $table->json('conditions')->nullable();
            $table->timestamps();
        });

        Schema::create('message_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
        });

        Schema::table('communication_templates', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
            $table->boolean('is_shared')->default(false)->after('is_active');
            $table->json('merge_fields')->nullable()->after('is_shared');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communication_templates', function (Blueprint $table) {
            $table->dropColumn(['category', 'is_shared', 'merge_fields']);
        });

        Schema::dropIfExists('message_folders');
        Schema::dropIfExists('message_automations');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('message_participants');
        Schema::dropIfExists('message_attachments');

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['message_type', 'channel']);
            $table->dropIndex(['timestamp']);
            $table->dropConstrainedForeignId('recipient_id');
            $table->dropConstrainedForeignId('sender_id');
            $table->dropConstrainedForeignId('parent_message_id');
            $table->dropConstrainedForeignId('related_work_order_id');
            $table->dropColumn([
                'subject',
                'timestamp',
                'is_read',
                'message_type',
                'channel',
                'metadata',
            ]);
        });

        Schema::table('message_thread_participants', function (Blueprint $table) {
            $table->dropIndex(['folder', 'is_archived']);
            $table->dropIndex(['is_starred']);
            $table->dropIndex(['deleted_at']);
            $table->dropColumn([
                'folder',
                'is_starred',
                'is_archived',
                'is_muted',
                'deleted_at',
            ]);
        });

        Schema::table('message_threads', function (Blueprint $table) {
            $table->dropColumn(['type', 'status']);
        });
    }
};
