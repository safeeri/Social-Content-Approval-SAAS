<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('icon_url')->nullable();
            $table->timestamps();
        });

        Schema::create('client_platform', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
            $table->unique(['client_id', 'platform_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_platform');
        Schema::dropIfExists('platforms');
    }
};
