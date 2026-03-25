<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_refunds', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('customer_payment_id');
            $table->string('note_id');
            $table->integer('amount_rupiah');
            $table->date('refunded_at');
            $table->text('reason');

            $table->index('customer_payment_id');
            $table->index('note_id');
            $table->index('refunded_at');
            $table->index(['customer_payment_id', 'note_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_refunds');
    }
};
