<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Inventory\Services\IssueInventoryOperation;
use App\Application\Note\Services\WorkItemFactory;
use App\Application\Shared\DTO\Result;
use App\Core\Note\Note\Note;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Note\NoteWriterPort;
use App\Ports\Out\Note\WorkItemWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class CreateTransactionWorkspaceHandler
{
    public function __construct(
        private readonly NoteWriterPort $notes,
        private readonly WorkItemWriterPort $workItems,
        private readonly IssueInventoryOperation $issueInventory,
        private readonly TransactionManagerPort $transactions,
        private readonly WorkItemFactory $factory,
        private readonly AuditLogPort $audit,
        private readonly UuidPort $uuid,
    ) {
    }

    /**
     * @param array{
     * note: array<string, mixed>,
     * items: list<array<string, mixed>>,
     * inline_payment: array<string, mixed>
     * } $payload
     */
    public function handle(array $payload): Result
    {
        $started = false;

        try {
            $decision = (string) ($payload['inline_payment']['decision'] ?? 'skip');

            if ($decision !== 'skip') {
                return Result::failure(
                    'Inline payment workspace belum aktif pada step ini. Gunakan Skip dulu.',
                    ['inline_payment' => ['WORKSPACE_PAYMENT_NOT_READY']]
                );
            }

            $this->transactions->begin();
            $started = true;

            $note = Note::create(
                $this->uuid->generate(),
                $this->requiredString($payload['note']['customer_name'] ?? null, 'Nama customer wajib diisi.'),
                $this->parseTransactionDate($payload['note']['transaction_date'] ?? null),
            );

            $this->notes->create($note);

            $lineNo = 1;

            foreach (($payload['items'] ?? []) as $item) {
                [$type, $sd, $ext, $sto] = $this->mapItemPayload($item);

                $workItem = $this->factory->build($note->id(), $lineNo, $type, $sd, $ext, $sto);
                $note->addWorkItem($workItem);
                $this->workItems->create($workItem);

                foreach ($workItem->storeStockLines() as $line) {
                    $this->issueInventory->execute(
                        $line->productId(),
                        $line->qty(),
                        $note->transactionDate(),
                        'work_item_store_stock_line',
                        $line->id()
                    );
                }

                $lineNo++;
            }

            $this->notes->updateTotal($note);

            $this->audit->record('transaction_workspace_created', [
                'note_id' => $note->id(),
                'customer_name' => $note->customerName(),
                'items_count' => count($payload['items'] ?? []),
                'total_rupiah' => $note->totalRupiah()->amount(),
                'payment_decision' => $decision,
            ]);

            $this->transactions->commit();

            return Result::success(
                [
                    'note' => [
                        'id' => $note->id(),
                        'customer_name' => $note->customerName(),
                        'transaction_date' => $note->transactionDate()->format('Y-m-d'),
                        'total_rupiah' => $note->totalRupiah()->amount(),
                    ],
                ],
                'Nota workspace berhasil dibuat.'
            );
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure($e->getMessage(), ['note' => ['INVALID_WORKSPACE']]);
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $item
     * @return array{0:string,1:array<string, mixed>,2:list<array<string, mixed>>,3:list<array<string, mixed>>}
     */
    private function mapItemPayload(array $item): array
    {
        $entryMode = (string) ($item['entry_mode'] ?? '');
        $partSource = (string) ($item['part_source'] ?? 'none');

        if ($entryMode === 'product') {
            return [
                WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
                [],
                [],
                [$this->mapStoreStockLine($item)],
            ];
        }

        if ($entryMode !== 'service') {
            throw new DomainException('Tipe item workspace tidak didukung.');
        }

        $sd = [
            'service_name' => $this->requiredString($item['service']['name'] ?? null, 'Nama servis wajib diisi.'),
            'service_price_rupiah' => $this->requiredInt($item['service']['price_rupiah'] ?? null, 'Harga servis wajib diisi.'),
            'part_source' => $partSource,
        ];

        return match ($partSource) {
            'none', 'customer_owned' => [
                WorkItem::TYPE_SERVICE_ONLY,
                $sd,
                [],
                [],
            ],
            'store_stock' => [
                WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART,
                $sd,
                [],
                [$this->mapStoreStockLine($item)],
            ],
            'external_purchase' => [
                WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE,
                $sd,
                [$this->mapExternalPurchaseLine($item)],
                [],
            ],
            default => throw new DomainException('Sumber part service tidak valid.'),
        };
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function mapStoreStockLine(array $item): array
    {
        $line = $this->firstLine($item['product_lines'] ?? []);

        $qty = $this->requiredInt($line['qty'] ?? null, 'Qty produk wajib diisi.');
        $unitPrice = $this->requiredInt($line['unit_price_rupiah'] ?? null, 'Harga satuan produk wajib diisi.');

        return [
            'product_id' => $this->requiredString($line['product_id'] ?? null, 'Product wajib dipilih.'),
            'qty' => $qty,
            'line_total_rupiah' => $qty * $unitPrice,
        ];
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function mapExternalPurchaseLine(array $item): array
    {
        $line = $this->firstLine($item['external_purchase_lines'] ?? []);

        return [
            'cost_description' => $this->requiredString($line['label'] ?? null, 'Label pembelian luar wajib diisi.'),
            'qty' => $this->requiredInt($line['qty'] ?? null, 'Qty pembelian luar wajib diisi.'),
            'unit_cost_rupiah' => $this->requiredInt($line['unit_cost_rupiah'] ?? null, 'Biaya pembelian luar wajib diisi.'),
        ];
    }

    /**
     * @param mixed $value
     * @return array<string, mixed>
     */
    private function firstLine(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $first = array_values($value)[0] ?? [];

        return is_array($first) ? $first : [];
    }

    private function requiredString(mixed $value, string $message): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new DomainException($message);
        }

        return trim($value);
    }

    private function requiredInt(mixed $value, string $message): int
    {
        if (! is_int($value) || $value <= 0) {
            throw new DomainException($message);
        }

        return $value;
    }

    private function parseTransactionDate(mixed $value): DateTimeImmutable
    {
        if (! is_string($value)) {
            throw new DomainException('Tanggal nota wajib diisi.');
        }

        $normalized = trim($value);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $normalized);

        if ($parsed === false || $parsed->format('Y-m-d') !== $normalized) {
            throw new DomainException('Tanggal nota wajib valid dengan format Y-m-d.');
        }

        return $parsed;
    }
}
