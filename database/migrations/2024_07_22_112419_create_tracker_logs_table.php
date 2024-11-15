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
        Schema::create('tracker_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('project_name')->nullable();
            $table->string('description')->nullable();
            $table->string('status')->nullable();
            $table->date('date')->nullable();
            $table->timestamp('start_time')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('end_time')->nullable();
            $table->json('tracked_times')->nullable();
            $table->string('elapsed_time')->nullable();
            $table->json('time_logs')->nullable();
            $table->string('current_log_id')->nullable();
            $table->json('screenshots')->nullable();

            $table->timestamp('last_pause')->nullable();
            $table->timestamp('last_active')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracker_logs');
    }
};
