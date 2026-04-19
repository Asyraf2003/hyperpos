<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_mutation_events', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('note_id');
            $table->string('mutation_type');
            $table->string('actor_id');
            $table->string('actor_role');
            $table->text('reason');
            $table->dateTime('occurred_at');
            $table->string('related_customer_payment_id')->nullable();
            $table->string('related_customer_refund_id')->nullable();

            $table->index('note_id');
            $table->index('mutation_type');
            $table->index('actor_id');
            $table->index('actor_role');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_mutation_events');
    }
};
