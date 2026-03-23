<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('prompt_templates')->nullOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->string('tone', 50)->default('professional');
            $table->string('length', 50)->default('medium');
            $table->unsignedInteger('tokens_used')->default(0);
            $table->boolean('is_public')->default(false);
            $table->uuid('share_uuid')->nullable()->unique();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_items');
    }
};