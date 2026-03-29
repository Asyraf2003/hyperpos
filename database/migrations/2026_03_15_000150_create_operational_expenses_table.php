<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_expenses', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('category_id');
            $table->string('category_code_snapshot')->nullable();
            $table->string('category_name_snapshot')->nullable();
            $table->integer('amount_rupiah');
            $table->date('expense_date');
            $table->string('description');
            $table->string('payment_method');
            $table->string('reference_no')->nullable();
            $table->string('status', 20);
            $table->timestamps();

            $table->foreign('category_id')
                ->references('id')
                ->on('expense_categories')
                ->restrictOnDelete();

            $table->index('expense_date');
            $table->index('status');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_expenses');
    }
};
