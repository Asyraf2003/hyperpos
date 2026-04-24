<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Adapters\Out\Note\Mappers\NoteMapper;
use App\Adapters\Out\Note\Mappers\WorkItemMapper;
use App\Core\Note\Note\Note;
use App\Ports\Out\Note\NoteReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseNoteReaderAdapter implements NoteReaderPort
{
    public function __construct(
        private readonly DatabaseNoteActiveWorkItemFilter $activeRows,
        private readonly DatabaseNoteWorkItemDetailLoader $details,
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

        $workItems = array_map(
            fn ($row) => WorkItemMapper::map(
                $row,
                $this->details->loadDetails($ids),
                $this->details->loadExternals($ids),
                $this->details->loadStocks($ids),
            ),
            $activeItemRows
        );

        return NoteMapper::map($noteRow, $workItems);
    }
}
