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
        Schema::create('social_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->onDelete('cascade');
            $table->string('platform')->default('telegram');
            $table->string('type'); // channel, bot
            $table->string('status')->default('pending'); // active, pending, revoked
            $table->string('external_id')->nullable();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('access_token')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_integrations');
    }
};
