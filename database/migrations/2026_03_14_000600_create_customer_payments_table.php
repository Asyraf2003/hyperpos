<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_payments', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->integer('amount_rupiah');
            $table->date('paid_at');

            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
