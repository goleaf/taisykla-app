<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type'); // manual, warranty_doc, receipt, config, training, service_manual
            $table->string('title');
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('version')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_documents');
    }
};
