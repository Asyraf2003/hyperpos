<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_allocations', function (Blueprint $table): void {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::table('payment_component_allocations', function (Blueprint $table): void {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::table('refund_component_allocations', function (Blueprint $table): void {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        $migrationTimestamp = now()->format('Y-m-d H:i:s');

        DB::table('payment_allocations')
            ->whereNull('created_at')
            ->update([
                'created_at' => $migrationTimestamp,
                'updated_at' => $migrationTimestamp,
            ]);

        DB::table('payment_component_allocations')
            ->whereNull('created_at')
            ->update([
                'created_at' => $migrationTimestamp,
                'updated_at' => $migrationTimestamp,
            ]);

        DB::table('refund_component_allocations')
            ->whereNull('created_at')
            ->update([
                'created_at' => $migrationTimestamp,
                'updated_at' => $migrationTimestamp,
            ]);
    }

    public function down(): void
    {
        Schema::table('refund_component_allocations', function (Blueprint $table): void {
            $table->dropColumn(['created_at', 'updated_at']);
        });

        Schema::table('payment_component_allocations', function (Blueprint $table): void {
            $table->dropColumn(['created_at', 'updated_at']);
        });

        Schema::table('payment_allocations', function (Blueprint $table): void {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};
