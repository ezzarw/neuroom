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
        Schema::table('pomodoro_histories', function (Blueprint $table) {
            $table->string('pomodoro_uid')->after('id');
            $table->string('status')->after('user_id');
            $table->unsignedInteger('actual_seconds')->after('duration_seconds');
            $table->integer('remaining_seconds')->nullable()->after('actual_seconds');
            $table->dateTime('started_at')->nullable()->after('remaining_seconds');
            $table->dateTime('finished_at')->nullable()->after('started_at');
            $table->dateTime('stopped_at')->nullable()->after('finished_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pomodoro_histories', function (Blueprint $table) {
            $table->dropColumn([
                'pomodoro_uid',
                'status',
                'actual_seconds',
                'remaining_seconds',
                'started_at',
                'finished_at',
                'stopped_at',
            ]);
        });
    }
};
