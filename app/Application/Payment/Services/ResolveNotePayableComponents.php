<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\PayableNoteComponent;
use App\Core\Note\Note\Note;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use App\Core\Shared\Exceptions\DomainException;

final class ResolveNotePayableComponents
{
    /**
     * @return list<PayableNoteComponent>
     */
    public function fromNote(Note $note): array
    {
        $components = [];
        $order = 1;

        foreach ($note->workItems() as $item) {
            $components = [...$components, ...$this->fromWorkItem($item, $order)];
            $order += count($components) - ($order - 1);
        }

        return $components;
    }

    /**
     * @return list<PayableNoteComponent>
     */
    private function fromWorkItem(WorkItem $item, int $startOrder): array
    {
        $components = [];
        $order = $startOrder;

        switch ($item->transactionType()) {
            case WorkItem::TYPE_STORE_STOCK_SALE_ONLY:
                $components[] = new PayableNoteComponent(
                    $item->id(),
                    PaymentComponentType::PRODUCT_ONLY_WORK_ITEM,
                    $item->id(),
                    $item->subtotalRupiah(),
                    $order++,
                );
                break;

            case WorkItem::TYPE_SERVICE_ONLY:
                $components[] = $this->serviceFee($item, $order++);
                break;

            case WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART:
                foreach ($item->storeStockLines() as $line) {
                    $components[] = new PayableNoteComponent(
                        $item->id(),
                        PaymentComponentType::SERVICE_STORE_STOCK_PART,
                        $line->id(),
                        $line->lineTotalRupiah(),
                        $order++,
                    );
                }

                $components[] = $this->serviceFee($item, $order++);
                break;

            case WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE:
                foreach ($item->externalPurchaseLines() as $line) {
                    $components[] = new PayableNoteComponent(
                        $item->id(),
                        PaymentComponentType::SERVICE_EXTERNAL_PURCHASE_PART,
                        $line->id(),
                        $line->lineTotalRupiah(),
                        $order++,
                    );
                }

                $components[] = $this->serviceFee($item, $order++);
                break;

            default:
                throw new DomainException('Transaction type work item belum didukung untuk payable component.');
        }

        return $components;
    }

    private function serviceFee(WorkItem $item, int $order): PayableNoteComponent
    {
        $detail = $item->serviceDetail();

        if ($detail === null) {
            throw new DomainException('Service detail wajib ada untuk service fee component.');
        }

        return new PayableNoteComponent(
            $item->id(),
            PaymentComponentType::SERVICE_FEE,
            $item->id(),
            $detail->servicePriceRupiah(),
            $order,
        );
    }
}
