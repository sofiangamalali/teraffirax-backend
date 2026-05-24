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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('creators')->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('set null');
            $table->date('earned_at');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('matured_at')->nullable();
            $table->integer('clicks_count')->default(0);
            $table->integer('items_count')->default(0);
            $table->integer('returned_count')->default(0);
            $table->decimal('revenue_amount', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['creator_id', 'earned_at']);
            $table->index(['calculated_at', 'matured_at']);
            $table->index('matured_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
