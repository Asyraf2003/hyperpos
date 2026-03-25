<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_debt_adjustments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_debt_id')->constrained('employee_debts')->restrictOnDelete();
            $table->string('adjustment_type', 20);
            $table->bigInteger('amount');
            $table->text('reason');
            $table->string('performed_by_actor_id', 64);
            $table->bigInteger('before_total_debt');
            $table->bigInteger('after_total_debt');
            $table->bigInteger('before_remaining_balance');
            $table->bigInteger('after_remaining_balance');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_debt_adjustments');
    }
};
