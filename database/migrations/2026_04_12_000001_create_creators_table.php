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
        Schema::create('creators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->decimal('pending_balance', 15, 2)->default(0);
            $table->decimal('available_balance', 15, 2)->default(0);
            $table->decimal('withdrawn_balance', 15, 2)->default(0);

            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_manager_id')->nullable()->constrained('account_managers')->onDelete('set null');

            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_active')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creators');
    }
};
