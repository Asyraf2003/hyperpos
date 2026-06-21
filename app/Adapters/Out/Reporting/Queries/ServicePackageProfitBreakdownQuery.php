<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use Illuminate\Support\Facades\DB;

final class ServicePackageProfitBreakdownQuery
{
    /**
     * @return list<array<string, int|string|null>>
     */
    public function rows(string $fromTransactionDate, string $toTransactionDate): array
    {
        $partsTotals = DB::table('work_item_store_stock_lines')
            ->selectRaw('work_item_id, SUM(line_total_rupiah) as parts_total_rupiah')
            ->groupBy('work_item_id');

        $issuedCogs = DB::table('work_item_store_stock_lines')
            ->join('inventory_movements', static function ($join): void {
                $join->on('inventory_movements.source_id', '=', 'work_item_store_stock_lines.id')
                    ->where('inventory_movements.source_type', '=', 'work_item_store_stock_line')
                    ->where('inventory_movements.movement_type', '=', 'stock_out');
            })
            ->selectRaw('work_item_store_stock_lines.work_item_id, SUM(ABS(inventory_movements.total_cost_rupiah)) as issued_cogs_rupiah')
            ->groupBy('work_item_store_stock_lines.work_item_id');

        $returnedCogs = DB::table('work_item_store_stock_lines')
            ->join('inventory_movements', static function ($join): void {
                $join->on('inventory_movements.source_id', '=', 'work_item_store_stock_lines.id')
                    ->where('inventory_movements.source_type', '=', 'work_item_store_stock_line_reversal')
                    ->where('inventory_movements.movement_type', '=', 'stock_in');
            })
            ->selectRaw('work_item_store_stock_lines.work_item_id, SUM(inventory_movements.total_cost_rupiah) as returned_cogs_rupiah')
            ->groupBy('work_item_store_stock_lines.work_item_id');

        $refundedProductComponents = DB::table('refund_component_allocations')
            ->whereIn('component_type', [
                'product_only_work_item',
                'service_store_stock_part',
            ])
            ->selectRaw('work_item_id, SUM(refunded_amount_rupiah) as refunded_product_component_rupiah')
            ->groupBy('work_item_id');

        $refundedServiceComponents = DB::table('refund_component_allocations')
            ->where('component_type', 'service_fee')
            ->selectRaw('work_item_id, SUM(refunded_amount_rupiah) as refunded_service_component_rupiah')
            ->groupBy('work_item_id');

        return DB::table('work_items')
            ->join('notes', 'notes.id', '=', 'work_items.note_id')
            ->join('work_item_service_details', 'work_item_service_details.work_item_id', '=', 'work_items.id')
            ->leftJoinSub($partsTotals, 'parts_totals', static fn ($join) => $join->on('parts_totals.work_item_id', '=', 'work_items.id'))
            ->leftJoinSub($issuedCogs, 'issued_cogs', static fn ($join) => $join->on('issued_cogs.work_item_id', '=', 'work_items.id'))
            ->leftJoinSub($returnedCogs, 'returned_cogs', static fn ($join) => $join->on('returned_cogs.work_item_id', '=', 'work_items.id'))
            ->leftJoinSub($refundedProductComponents, 'refunded_product_components', static fn ($join) => $join->on('refunded_product_components.work_item_id', '=', 'work_items.id'))
            ->leftJoinSub($refundedServiceComponents, 'refunded_service_components', static fn ($join) => $join->on('refunded_service_components.work_item_id', '=', 'work_items.id'))
            ->where('work_items.transaction_type', 'service_with_store_stock_part')
            ->where('work_items.status', '<>', 'canceled')
            ->whereBetween('notes.transaction_date', [$fromTransactionDate, $toTransactionDate])
            ->orderBy('notes.transaction_date')
            ->orderBy('notes.id')
            ->orderBy('work_items.line_no')
            ->get([
                'notes.id as note_id',
                'work_items.id as work_item_id',
                'notes.transaction_date',
                'notes.customer_name',
                'work_items.subtotal_rupiah as package_sold_amount_rupiah',
                DB::raw('COALESCE(parts_totals.parts_total_rupiah, 0) as parts_total_rupiah'),
                'work_item_service_details.service_price_rupiah',
                'work_item_service_details.package_base_service_price_rupiah',
                DB::raw('COALESCE(work_item_service_details.package_service_extra_rupiah, 0) as package_service_extra_rupiah'),
                DB::raw('COALESCE(work_item_service_details.package_profit_rupiah, 0) as package_profit_rupiah'),
                DB::raw('COALESCE(refunded_product_components.refunded_product_component_rupiah, 0) as refunded_product_component_rupiah'),
                DB::raw('COALESCE(refunded_service_components.refunded_service_component_rupiah, 0) as refunded_service_component_rupiah'),
                DB::raw('(COALESCE(issued_cogs.issued_cogs_rupiah, 0) - COALESCE(returned_cogs.returned_cogs_rupiah, 0)) as sparepart_cogs_rupiah'),
            ])
            ->map(static function (object $row): array {
                $partsTotal = (int) $row->parts_total_rupiah;
                $servicePrice = (int) $row->service_price_rupiah;
                $packageProfit = (int) $row->package_profit_rupiah;
                $totalServiceComponent = $servicePrice + $packageProfit;
                $sparepartCogs = (int) $row->sparepart_cogs_rupiah;
                $sparepartMargin = $partsTotal - $sparepartCogs;

                return [
                    'note_id' => (string) $row->note_id,
                    'work_item_id' => (string) $row->work_item_id,
                    'transaction_date' => (string) $row->transaction_date,
                    'customer_name' => (string) $row->customer_name,
                    'package_sold_amount_rupiah' => (int) $row->package_sold_amount_rupiah,
                    'parts_total_rupiah' => $partsTotal,
                    'service_price_rupiah' => $servicePrice,
                    'package_base_service_price_rupiah' => $row->package_base_service_price_rupiah === null
                        ? null
                        : (int) $row->package_base_service_price_rupiah,
                    'package_service_extra_rupiah' => (int) $row->package_service_extra_rupiah,
                    'package_profit_rupiah' => $packageProfit,
                    'total_service_component_rupiah' => $totalServiceComponent,
                    'refunded_product_component_rupiah' => (int) $row->refunded_product_component_rupiah,
                    'refunded_service_component_rupiah' => (int) $row->refunded_service_component_rupiah,
                    'sparepart_cogs_rupiah' => $sparepartCogs,
                    'sparepart_margin_rupiah' => $sparepartMargin,
                    'total_package_gross_profit_rupiah' => $sparepartMargin + $totalServiceComponent,
                ];
            })
            ->all();
    }
}
