<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Adapters\Out\Note\DatabaseNoteRevisionSettlementAdapter;
use App\Application\Note\DTO\NoteRevisionSettlement;
use DateTimeImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DatabaseNoteRevisionSettlementAdapterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_and_reads_revision_settlement(): void
    {
        $adapter = new DatabaseNoteRevisionSettlementAdapter();
        $adapter->create($this->settlement('set-1', 'rev-1', 'note-1'));

        $found = $adapter->findByRevisionId('rev-1');

        $this->assertNotNull($found);
        $this->assertSame('note-1', $found->noteRootId);
        $this->assertSame(NoteRevisionSettlement::STATUS_OVERPAID_PENDING, $found->settlementStatus);
        $this->assertSame(50000, $found->surplusRupiah);
    }

    public function test_it_lists_settlements_by_note_root_id(): void
    {
        $adapter = new DatabaseNoteRevisionSettlementAdapter();
        $adapter->create($this->settlement('set-1', 'rev-1', 'note-1'));
        $adapter->create($this->settlement('set-2', 'rev-2', 'note-1'));

        $items = $adapter->listByNoteRootId('note-1');

        $this->assertCount(2, $items);
        $this->assertSame(['rev-1', 'rev-2'], array_map(
            static fn (NoteRevisionSettlement $item): string => $item->noteRevisionId,
            $items,
        ));
    }

    public function test_it_rejects_duplicate_revision_settlement(): void
    {
        $adapter = new DatabaseNoteRevisionSettlementAdapter();
        $adapter->create($this->settlement('set-1', 'rev-1', 'note-1'));

        $this->expectException(QueryException::class);

        $adapter->create($this->settlement('set-duplicate', 'rev-1', 'note-1'));
    }

    private function settlement(string $id, string $revisionId, string $noteId): NoteRevisionSettlement
    {
        return NoteRevisionSettlement::create(
            $id,
            $revisionId,
            $noteId,
            150000,
            200000,
            0,
            200000,
            0,
            50000,
            NoteRevisionSettlement::STATUS_OVERPAID_PENDING,
            new DateTimeImmutable('2026-05-13 10:00:00'),
        );
    }
}
