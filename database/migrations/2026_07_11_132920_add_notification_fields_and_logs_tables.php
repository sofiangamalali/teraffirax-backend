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
        Schema::table('creators', function (Blueprint $table) {
            $table->boolean('needs_manual_reengagement')->default(false)->after('is_active');
            $table->timestamp('milestone_threshold_reached_at')->nullable()->after('needs_manual_reengagement');
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('creators')->onDelete('cascade');
            $table->string('email_type');
            $table->timestamp('sent_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');

        Schema::table('creators', function (Blueprint $table) {
            $table->dropColumn(['needs_manual_reengagement', 'milestone_threshold_reached_at']);
        });
    }
};

