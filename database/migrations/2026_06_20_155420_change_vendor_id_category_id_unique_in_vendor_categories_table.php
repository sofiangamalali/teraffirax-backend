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
        Schema::table('vendor_categories', function (Blueprint $table) {
            $table->dropUnique(['vendor_id', 'category_id']);
            $table->index(['vendor_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_categories', function (Blueprint $table) {
            $table->dropIndex(['vendor_id', 'category_id']);
            $table->unique(['vendor_id', 'category_id']);
        });
    }
};
