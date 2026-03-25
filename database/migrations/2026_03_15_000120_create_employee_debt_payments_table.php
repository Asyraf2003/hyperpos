<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_debt_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_debt_id')->constrained('employee_debts')->restrictOnDelete();
            $table->bigInteger('amount');
            $table->dateTime('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_debt_payments');
    }
};
