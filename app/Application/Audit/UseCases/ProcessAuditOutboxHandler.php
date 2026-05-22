<?php

declare(strict_types=1);

namespace App\Application\Audit\UseCases;

use App\Adapters\Out\Audit\DatabaseAuditEventWriterAdapter;
use App\Application\Audit\Services\AuditOutboxEventHydrator;
use App\Application\Audit\Services\AuditOutboxFailureRecorder;
use App\Application\Audit\Support\AuditOutboxStatus;
use App\Ports\Out\ClockPort;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class ProcessAuditOutboxHandler
{
    public function __construct(
        private readonly DatabaseAuditEventWriterAdapter $materializer,
        private readonly AuditOutboxEventHydrator $hydrator,
        private readonly AuditOutboxFailureRecorder $failures,
        private readonly ClockPort $clock,
    ) {
    }

    public function handle(int $limit, bool $retryFailed, int $maxAttempts): array
    {
        $summary = ['processed' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($this->eligibleRows($limit, $retryFailed, $maxAttempts) as $row) {
            try {
                $result = $this->processRow($row);
            } catch (Throwable $e) {
                $this->failures->record((string) $row->id, $e, max(1, $maxAttempts));
                $summary['failed']++;

                continue;
            }

            $summary[$result]++;
        }

        return $summary;
    }

    private function eligibleRows(int $limit, bool $retryFailed, int $maxAttempts): Collection
    {
        $now = $this->clock->now();

        return DB::table('audit_outbox')
            ->where(static function ($query) use ($retryFailed): void {
                $query->where('status', AuditOutboxStatus::PENDING);

                if ($retryFailed) {
                    $query->orWhere('status', AuditOutboxStatus::FAILED);
                }
            })
            ->where('attempts', '<', max(1, $maxAttempts))
            ->where(static function ($query) use ($now): void {
                $query->whereNull('available_at')->orWhere('available_at', '<=', $now);
            })
            ->orderBy('created_at')
            ->limit(max(1, $limit))
            ->get();
    }

    private function processRow(object $row): string
    {
        return DB::transaction(function () use ($row): string {
            $now = $this->clock->now();
            $claimed = DB::table('audit_outbox')
                ->where('id', (string) $row->id)
                ->where('status', (string) $row->status)
                ->update(['status' => AuditOutboxStatus::PROCESSING, 'locked_at' => $now, 'updated_at' => $now]);

            if ($claimed !== 1) {
                return 'skipped';
            }

            $fresh = DB::table('audit_outbox')->where('id', (string) $row->id)->first();

            if ($fresh === null) {
                throw new RuntimeException('audit_outbox row disappeared during processing.');
            }

            $this->materializer->write($this->hydrator->fromRow($fresh));

            DB::table('audit_outbox')->where('id', (string) $fresh->id)->update([
                'status' => AuditOutboxStatus::PROCESSED,
                'locked_at' => null,
                'processed_at' => $now,
                'updated_at' => $now,
            ]);

            return 'processed';
        });
    }
}
