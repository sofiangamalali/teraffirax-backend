<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TelegramPostStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('telegram_product_posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('social_integration_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();


            // 'pending' = reserved slot before calling Flask.
            // 'posted'  = confirmed delivery after Flask responded 2xx.
            $table->enum('post_status', array_column(TelegramPostStatusEnum::cases(), 'value'))->default(TelegramPostStatusEnum::PENDING->value);

            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            // Composite index used by the idempotency guard SELECT at job start.
            $table->index(
                ['social_integration_id', 'product_id', 'post_status'],
                'idx_tpp_idempotency_guard'
            );

            // Index used by selectProductFromCategory deduplication queries.
            $table->index('posted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_product_posts');
    }
};
