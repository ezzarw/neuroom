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
        Schema::create('pomodoro_histories', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100);
            $table->foreignId('user_id')
                ->references('id')
                ->on('authentications')
                ->cascadeOnDelete();
            $table->integer('session');
            $table->date('date');
            $table->timestamps();


            $table->foreign('username')
                ->references('username')
                ->on('authentications')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pomodoro_histories');
    }
};
