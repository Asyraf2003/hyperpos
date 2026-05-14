<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_payments', function (Blueprint $table): void {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::table('customer_refunds', function (Blueprint $table): void {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::table('customer_payment_cash_details', function (Blueprint $table): void {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        $migrationTimestamp = now()->format('Y-m-d H:i:s');

        DB::table('customer_payments')
            ->whereNull('created_at')
            ->update([
                'created_at' => $migrationTimestamp,
                'updated_at' => $migrationTimestamp,
            ]);

        DB::table('customer_refunds')
            ->whereNull('created_at')
            ->update([
                'created_at' => $migrationTimestamp,
                'updated_at' => $migrationTimestamp,
            ]);

        DB::table('customer_payment_cash_details')
            ->whereNull('created_at')
            ->update([
                'created_at' => $migrationTimestamp,
                'updated_at' => $migrationTimestamp,
            ]);
    }

    public function down(): void
    {
        Schema::table('customer_payment_cash_details', function (Blueprint $table): void {
            $table->dropColumn(['created_at', 'updated_at']);
        });

        Schema::table('customer_refunds', function (Blueprint $table): void {
            $table->dropColumn(['created_at', 'updated_at']);
        });

        Schema::table('customer_payments', function (Blueprint $table): void {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};
