<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Adapters\Out\Note\Mappers\NoteMapper;
use App\Adapters\Out\Note\Mappers\WorkItemMapper;
use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\StoreStockLine;
use App\Core\Note\Note\Note;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Note\NoteReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseNoteReaderAdapter implements NoteReaderPort
{
    public function __construct(
        private readonly DatabaseNoteActiveWorkItemFilter $activeRows,
    ) {
    }

    public function getById(string $id): ?Note
    {
        $noteRow = DB::table('notes')->where('id', trim($id))->first();
        if (!$noteRow) {
            return null;
        }

        $itemRows = DB::table('work_items')
            ->where('note_id', $noteRow->id)
            ->orderBy('line_no')
            ->get()
            ->all();

        $activeItemRows = $this->activeRows->filter($itemRows);
        $ids = array_map(fn ($row) => (string) $row->id, $activeItemRows);

        $details = $this->loadDetails($ids);
        $externals = $this->loadExternals($ids);
        $stocks = $this->loadStocks($ids);

        $workItems = array_map(
            fn ($row) => WorkItemMapper::map($row, $details, $externals, $stocks),
            $activeItemRows
        );

        return NoteMapper::map($noteRow, $workItems);
    }

    private function loadDetails(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $result = [];

        foreach (DB::table('work_item_service_details')->whereIn('work_item_id', $ids)->get() as $row) {
            $result[(string) $row->work_item_id] = ServiceDetail::rehydrate(
                (string) $row->service_name,
                Money::fromInt((int) $row->service_price_rupiah),
                (string) $row->part_source,
            );
        }

        return $result;
    }

    private function loadExternals(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $result = [];

        foreach (DB::table('work_item_external_purchase_lines')->whereIn('work_item_id', $ids)->orderBy('id')->get() as $row) {
            $result[(string) $row->work_item_id][] = ExternalPurchaseLine::rehydrate(
                (string) $row->id,
                (string) $row->cost_description,
                Money::fromInt((int) $row->unit_cost_rupiah),
                (int) $row->qty,
            );
        }

        return $result;
    }

    private function loadStocks(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $result = [];

        foreach (DB::table('work_item_store_stock_lines')->whereIn('work_item_id', $ids)->orderBy('id')->get() as $row) {
            $result[(string) $row->work_item_id][] = StoreStockLine::rehydrate(
                (string) $row->id,
                (string) $row->product_id,
                (int) $row->qty,
                Money::fromInt((int) $row->line_total_rupiah),
            );
        }

        return $result;
    }
}
