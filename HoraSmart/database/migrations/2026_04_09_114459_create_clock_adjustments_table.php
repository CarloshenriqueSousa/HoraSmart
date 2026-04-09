<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type',
                ['clock_in',
                'clock_out',
                'lunch_in',
                'launch_out']
            );
            $table->timestamp('requested_time');
            $table->enum('status',
                ['pending',
                'approved',
                'rejected']
            )->default('pending');
            $table->text('reviewer_comments')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clock_adjustments');
    }
};
