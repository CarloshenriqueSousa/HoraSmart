<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->timestamp('clock_in')->nullable();
            $table->timestamp('clock_out')->nullable();
            $table->timestamp('lunch_in')->nullable();
            $table->timestamp('launch_out')->nullable();
            $table->timestamp('minutes_worked')->nullable();
            $table->enum('status', [
                'in_progress',
                'on_launch',
                'back_from_launch',
                'completed',
            ]) ->default('in_progress');
            $table->timestamps();

            $table->unique(['employee_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_logs');
    }
};
