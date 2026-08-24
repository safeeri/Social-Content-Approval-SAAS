<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained()->restrictOnDelete();
            $table->text('content');
            $table->enum('status', ['draft', 'internal_review', 'pending_approval', 'approved', 'rejected'])->default('draft');
            $table->enum('post_type', ['feed', 'reel', 'short', 'long_video'])->default('feed');
            $table->dateTime('publish_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'publish_date']);
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
