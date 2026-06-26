<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\CronRunLogStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cron_run_logs', function (Blueprint $table) {
            $table->id();
            $table->string('cron_name');
            $table->uuid('run_id');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->integer('records_processed')->default(0);
            $table->enum('status', array_column(CronRunLogStatusEnum::cases(), 'value'));
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_run_logs');
    }
};
