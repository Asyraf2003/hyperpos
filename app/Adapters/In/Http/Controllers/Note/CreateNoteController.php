<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Adapters\In\Http\Requests\Note\CreateNoteRequest;
use App\Application\Note\UseCases\AddWorkItemHandler;
use App\Application\Note\UseCases\CreateNoteHandler;
use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class CreateNoteController extends Controller
{
    public function __invoke(
        CreateNoteRequest $request,
        CreateNoteHandler $createNote,
        AddWorkItemHandler $addWorkItem,
    ): RedirectResponse {
        $data = $request->validated();

        $createResult = $createNote->handle(
            (string) $data['customer_name'],
            (string) $data['transaction_date'],
        );

        if ($createResult->isFailure()) {
            return back()
                ->withErrors([
                    'note' => $createResult->message() ?? 'Nota gagal dibuat.',
                ])
                ->withInput();
        }

        /** @var array<string, mixed> $payload */
        $payload = $createResult->data();
        /** @var string $noteId */
        $noteId = (string) ($payload['id'] ?? '');

        if ($noteId === '') {
            return back()
                ->withErrors([
                    'note' => 'ID nota tidak ditemukan setelah create.',
                ])
                ->withInput();
        }

        $rows = $data['rows'] ?? [];
        $lineNo = 1;

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $addResult = $this->addRow(
                $addWorkItem,
                $noteId,
                $lineNo,
                $row,
            );

            if ($addResult->isFailure()) {
                return back()
                    ->withErrors([
                        'note' => $addResult->message() ?? "Baris nota ke-{$lineNo} gagal ditambahkan.",
                    ])
                    ->withInput();
            }

            $lineNo++;
        }

        return redirect()
            ->route('cashier.notes.show', ['noteId' => $noteId])
            ->with('success', 'Nota berhasil dibuat.');
    }

    /**
     * @param array<string, mixed> $row
     */
    private function addRow(
        AddWorkItemHandler $addWorkItem,
        string $noteId,
        int $lineNo,
        array $row,
    ): \App\Application\Shared\DTO\Result {
        $lineType = (string) ($row['line_type'] ?? '');

        if ($lineType === 'product') {
            return $addWorkItem->handle(
                $noteId,
                $lineNo,
                WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
                [],
                [],
                [[
                    'product_id' => (string) ($row['product_id'] ?? ''),
                    'qty' => (int) ($row['qty'] ?? 0),
                    'line_total_rupiah' => 0,
                ]]
            );
        }

        return $addWorkItem->handle(
            $noteId,
            $lineNo,
            WorkItem::TYPE_SERVICE_ONLY,
            [
                'service_name' => (string) ($row['service_name'] ?? ''),
                'service_price_rupiah' => (int) ($row['service_price_rupiah'] ?? 0),
            ],
            [],
            []
        );
    }
}
