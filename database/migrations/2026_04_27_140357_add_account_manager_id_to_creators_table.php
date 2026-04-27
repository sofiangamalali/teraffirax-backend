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
        Schema::table('creators', function (Blueprint $table) {
            $table->foreignId('account_manager_id')->nullable()->constrained('account_managers')->nullOnDelete();
            $table->boolean('is_active')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            $table->dropForeign(['account_manager_id']);
            $table->dropColumn(['account_manager_id', 'is_active']);
        });
    }
};
