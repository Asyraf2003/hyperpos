<?php

declare(strict_types=1);

namespace Database\Seeders\Transaction;

use Carbon\CarbonImmutable;
use Database\Seeders\Support\SeedDensity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CustomerPaymentBaselineSeeder extends Seeder
{
    public function run(): void
    {
        $density = SeedDensity::baseline();

        $notes = DB::table('notes')
            ->select('id', 'transaction_date', 'total_rupiah')
            ->where('id', 'like', 'seed-note-bl-%')
            ->where('total_rupiah', '>', 0)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get()
            ->values();

        if ($notes->isEmpty()) {
            $this->command?->warn('CustomerPaymentBaselineSeeder dilewati: baseline notes belum tersedia.');
            return;
        }

        $this->purgeSeededPayments();

        $distribution = $density['payment_distribution'];

        foreach ($notes as $index => $note) {
            $pattern = $index % 20;
            $noteId = (string) $note->id;
            $noteDate = CarbonImmutable::parse((string) $note->transaction_date);
            $total = (int) $note->total_rupiah;

            $fullThreshold = $distribution['full'];
            $partialThreshold = $distribution['full'] + $distribution['partial'];

            if ($pattern < $fullThreshold / 5) {
                if ($pattern === 0) {
                    $first = max(1000, intdiv($total * 40, 100));
                    $second = $total - $first;

                    $this->createPayment(
                        paymentId: $this->paymentId($noteId, 1),
                        noteId: $noteId,
                        allocationId: $this->allocationId($noteId, 1),
                        paidAt: $noteDate->format('Y-m-d'),
                        amount: $first,
                    );

                    $this->createPayment(
                        paymentId: $this->paymentId($noteId, 2),
                        noteId: $noteId,
                        allocationId: $this->allocationId($noteId, 2),
                        paidAt: $noteDate->addDay()->format('Y-m-d'),
                        amount: $second,
                    );

                    continue;
                }

                $this->createPayment(
                    paymentId: $this->paymentId($noteId, 1),
                    noteId: $noteId,
                    allocationId: $this->allocationId($noteId, 1),
                    paidAt: $noteDate->format('Y-m-d'),
                    amount: $total,
                );

                continue;
            }

            if ($pattern < ($partialThreshold / 5)) {
                $partial = max(1000, intdiv($total * 55, 100));

                if ($partial >= $total) {
                    $partial = max(1000, $total - 1000);
                }

                $this->createPayment(
                    paymentId: $this->paymentId($noteId, 1),
                    noteId: $noteId,
                    allocationId: $this->allocationId($noteId, 1),
                    paidAt: $noteDate->format('Y-m-d'),
                    amount: $partial,
                );

                continue;
            }

            // unpaid: sengaja tidak dibuat payment
        }

        $this->command?->info('CustomerPaymentBaselineSeeder selesai: baseline payment/allocation 7 hari dibuat.');
    }

    private function purgeSeededPayments(): void
    {
        DB::table('refund_component_allocations')
            ->where('customer_refund_id', 'like', 'seed-ref-bl-%')
            ->delete();

        DB::table('customer_refunds')
            ->where('id', 'like', 'seed-ref-bl-%')
            ->orWhere('customer_payment_id', 'like', 'seed-pay-bl-%')
            ->delete();

        DB::table('payment_component_allocations')
            ->where('customer_payment_id', 'like', 'seed-pay-bl-%')
            ->delete();

        DB::table('payment_allocations')
            ->where('customer_payment_id', 'like', 'seed-pay-bl-%')
            ->delete();

        DB::table('customer_payments')
            ->where('id', 'like', 'seed-pay-bl-%')
            ->delete();

        DB::table('audit_logs')
            ->where('event', 'payment_allocated')
            ->where('context', 'like', '%seed-pay-bl-%')
            ->delete();
    }

    private function createPayment(
        string $paymentId,
        string $noteId,
        string $allocationId,
        string $paidAt,
        int $amount,
    ): void {
        DB::table('customer_payments')->updateOrInsert(
            ['id' => $paymentId],
            [
                'amount_rupiah' => $amount,
                'paid_at' => $paidAt,
            ]
        );

        DB::table('payment_allocations')->updateOrInsert(
            ['id' => $allocationId],
            [
                'customer_payment_id' => $paymentId,
                'note_id' => $noteId,
                'amount_rupiah' => $amount,
            ]
        );

        DB::table('audit_logs')->insert([
            'event' => 'payment_allocated',
            'context' => json_encode([
                'payment_id' => $paymentId,
                'note_id' => $noteId,
                'amount' => $amount,
                'seed_source' => self::class,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function paymentId(string $noteId, int $sequence): string
    {
        return sprintf('seed-pay-bl-%s-%02d', str_replace('seed-note-bl-', '', $noteId), $sequence);
    }

    private function allocationId(string $noteId, int $sequence): string
    {
        return sprintf('seed-pay-alloc-bl-%s-%02d', str_replace('seed-note-bl-', '', $noteId), $sequence);
    }
}
