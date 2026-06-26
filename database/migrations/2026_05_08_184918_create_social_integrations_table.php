<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PlatformEnum;
use App\Enums\IntegrationTypeEnum;
use App\Enums\SocialIntegrationStatusEnum;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->onDelete('cascade');
            $table->enum('platform', array_column(PlatformEnum::cases(), 'value'))->default(PlatformEnum::TELEGRAM->value);
            $table->enum('type', array_column(IntegrationTypeEnum::cases(), 'value'));
            $table->enum('status', array_column(SocialIntegrationStatusEnum::cases(), 'value'))->default(SocialIntegrationStatusEnum::PENDING->value);
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
