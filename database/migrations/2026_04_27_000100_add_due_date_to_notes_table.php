<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notes')) {
            return;
        }

        if (! Schema::hasColumn('notes', 'due_date')) {
            Schema::table('notes', function (Blueprint $table): void {
                $table->date('due_date')->nullable();
                $table->index('due_date');
            });
        }

        DB::table('notes')
            ->select(['id', 'transaction_date'])
            ->whereNull('due_date')
            ->orderBy('id')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('notes')
                        ->where('id', (string) $row->id)
                        ->update([
                            'due_date' => $this->calculateDueDate(
                                new DateTimeImmutable((string) $row->transaction_date)
                            )->format('Y-m-d'),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('notes') || ! Schema::hasColumn('notes', 'due_date')) {
            return;
        }

        Schema::table('notes', function (Blueprint $table): void {
            $table->dropIndex(['due_date']);
            $table->dropColumn('due_date');
        });
    }

    private function calculateDueDate(DateTimeImmutable $transactionDate): DateTimeImmutable
    {
        $month = (int) $transactionDate->format('n') + 1;
        $year = (int) $transactionDate->format('Y');

        if ($month > 12) {
            $month = 1;
            $year++;
        }

        $lastDay = (int) (new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))
            ->modify('last day of this month')
            ->format('j');

        return $transactionDate->setDate($year, $month, min((int) $transactionDate->format('j'), $lastDay));
    }
};
