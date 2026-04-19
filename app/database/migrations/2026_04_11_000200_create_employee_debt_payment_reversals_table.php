<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_debt_payment_reversals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_debt_payment_id')->constrained('employee_debt_payments')->restrictOnDelete();
            $table->text('reason');
            $table->string('performed_by_actor_id', 64);
            $table->timestamps();

            $table->unique('employee_debt_payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_debt_payment_reversals');
    }
};
