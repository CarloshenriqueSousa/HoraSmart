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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_type')->default('CLT')->after('position'); // Estagiário, Trainee, CLT
            $table->string('shift')->default('morning')->after('employee_type'); // morning, afternoon, night
            $table->integer('daily_workload')->default(480)->after('shift'); // in minutes
            $table->integer('overtime_tolerance')->default(10)->after('daily_workload'); // in minutes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['employee_type', 'shift', 'daily_workload', 'overtime_tolerance']);
        });
    }
};
