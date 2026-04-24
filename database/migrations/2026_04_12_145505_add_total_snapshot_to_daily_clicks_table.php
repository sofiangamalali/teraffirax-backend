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
        Schema::table('daily_clicks', function (Blueprint $table) {
            $table->bigInteger('total_snapshot')->default(0)->after('clicks_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_clicks', function (Blueprint $table) {
            $table->dropColumn('total_snapshot');
        });
    }
};
