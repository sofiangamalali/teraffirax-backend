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
        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('creator_id')->constrained()->onDelete('cascade');
            $table->foreignId('creator_withdraw_method_id')->constrained('creator_withdraw_methods')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, approved, completed, rejected
            
            // Audit fields
            $table->timestamp('requested_at')->useCurrent();
            $table->foreignId('reviewed_by')->nullable()->constrained('account_managers')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            
            // Transfer details
            $table->string('transfer_reference')->nullable();
            $table->string('receipt_url')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Rejection
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();

            $table->index(['creator_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
    }
};
