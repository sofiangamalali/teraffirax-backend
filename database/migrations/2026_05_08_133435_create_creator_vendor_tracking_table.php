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
        Schema::create('creator_vendor_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('track_id');
            $table->decimal('platform_commission_percentage', 5, 2)->default(50.00);
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['creator_id', 'vendor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creator_vendor_tracking');
    }
};
