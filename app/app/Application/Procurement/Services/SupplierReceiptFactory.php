<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Inventory\Movement\InventoryMovement;
use App\Core\Procurement\SupplierReceipt\{SupplierReceipt, SupplierReceiptLine};
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Procurement\SupplierReceiptLineReaderPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;

final class SupplierReceiptFactory
{
    public function __construct(private UuidPort $uuid, private SupplierReceiptLineReaderPort $receiptLines) {}

    public function build(string $invoiceId, DateTimeImmutable $date, array $lines, array $invoiceLinesById): array
    {
        if ($lines === []) throw new DomainException('Supplier receipt minimal harus memiliki satu line.');
        
        $rLines = []; $movements = [];
        foreach ($lines as $l) {
            $invLineId = (string)($l['supplier_invoice_line_id'] ?? throw new DomainException('Invoice line id wajib ada.'));
            $qty = (int)($l['qty_diterima'] ?? throw new DomainException('Qty diterima wajib ada.'));
            $invLine = $invoiceLinesById[$invLineId] ?? throw new DomainException('Invoice line tidak ditemukan.');

            $received = $this->receiptLines->getReceivedQtyBySupplierInvoiceLineId($invLineId);
            if (($received + $qty) > $invLine['qty_pcs']) throw new DomainException('Qty diterima melebihi invoice.');

            $rl = SupplierReceiptLine::create($this->uuid->generate(), $invLineId, $qty);
            $movements[] = InventoryMovement::create($this->uuid->generate(), $invLine['product_id'], 'stock_in', 'supplier_receipt_line', $rl->id(), $date, $qty, Money::fromInt($invLine['unit_cost_rupiah']));
            $rLines[] = $rl;
        }

        return [SupplierReceipt::create($this->uuid->generate(), $invoiceId, $date, $rLines), $movements];
    }
}
