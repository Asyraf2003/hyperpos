<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payment_reversals', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('supplier_payment_id');
            $table->text('reason');
            $table->string('performed_by_actor_id', 64);
            $table->timestamps();

            $table->unique('supplier_payment_id', 'uq_spr_payment');
            $table->foreign('supplier_payment_id', 'fk_spr_payment')
                ->references('id')
                ->on('supplier_payments')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payment_reversals', function (Blueprint $table): void {
            $table->dropForeign('fk_spr_payment');
            $table->dropUnique('uq_spr_payment');
        });

        Schema::dropIfExists('supplier_payment_reversals');
    }
};
