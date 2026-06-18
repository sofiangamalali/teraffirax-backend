<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendor_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('external_id');
            $table->string('external_name')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->decimal('commission', 5, 2)->nullable();
            $table->foreignId('parent_vendor_category_id')
                ->nullable()
                ->constrained('vendor_categories')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(['vendor_id', 'category_id']);

            $table->unique(['vendor_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_categories');
    }
};
