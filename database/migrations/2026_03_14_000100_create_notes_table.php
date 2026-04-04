<?php

declare(strict_types=1);

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
        Schema::create('notes', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->date('transaction_date');
 
            $table->string('note_state')->default('open');
            $table->dateTime('closed_at')->nullable();
            $table->string('closed_by_actor_id')->nullable();
            $table->dateTime('reopened_at')->nullable();
            $table->string('reopened_by_actor_id')->nullable();
 
            $table->integer('total_rupiah');

            $table->index('transaction_date');
            $table->index('customer_name');
            $table->index('note_state');
            $table->index('closed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};