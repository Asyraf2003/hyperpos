<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_disbursement_reversals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_disbursement_id')->constrained('payroll_disbursements')->restrictOnDelete();
            $table->text('reason');
            $table->string('performed_by_actor_id', 64);
            $table->timestamps();

            $table->unique('payroll_disbursement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_disbursement_reversals');
    }
};
