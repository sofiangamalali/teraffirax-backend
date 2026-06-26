<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ProductStatusEnum;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('vendor_category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('external_id');
            $table->string('name');
            $table->text('url');
            $table->decimal('price_before', 12, 2)->nullable();
            $table->decimal('price_after', 12, 2);
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->integer('rank_in_vendor_category')->nullable();
            $table->decimal('commission', 12, 2)->nullable();
            $table->integer('score')->default(0);
            $table->enum('status', array_column(ProductStatusEnum::cases(), 'value'))->default(ProductStatusEnum::ACTIVE->value);
            $table->timestamp('flagged_at')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['vendor_id', 'external_id']);

            // Indexes
            $table->index(['category_id', 'status', 'score']);
            $table->index(['vendor_id', 'status']);
            $table->index(['status', 'flagged_at']);
            $table->index('last_validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
