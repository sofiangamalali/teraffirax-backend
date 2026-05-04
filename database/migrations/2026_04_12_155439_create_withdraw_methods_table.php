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
        Schema::create('withdraw_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->json('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('withdraw_method_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdraw_method_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->json('label');
            $table->string('type'); // text, number, etc.
            $table->json('validation'); // JSON rules
            $table->json('options')->nullable(); // For dropdowns/radio
            $table->json('placeholder')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('creator_withdraw_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->onDelete('cascade');
            $table->foreignId('withdraw_method_id')->constrained()->onDelete('cascade');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('creator_withdraw_method_values', function (Blueprint $table) {
            $table->id();
            // Shortened FK name to avoid MySQL limits
            $table->foreignId('creator_withdraw_method_id')
                  ->constrained('creator_withdraw_methods', 'id', 'cwmv_cwm_id_foreign')
                  ->onDelete('cascade');
            
            $table->string('field_name');
            $table->text('field_value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creator_withdraw_method_values');
        Schema::dropIfExists('creator_withdraw_methods');
        Schema::dropIfExists('withdraw_method_fields');
        Schema::dropIfExists('withdraw_methods');
    }
};
