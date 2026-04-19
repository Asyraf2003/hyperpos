<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Note;

use App\Core\Note\Note\Note;
use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class NoteOperationalStateTransitionsTest extends TestCase
{
    public function test_new_note_is_open_by_default(): void
    {
        $note = Note::create('note-1', 'Budi', null, new DateTimeImmutable('2026-04-03'));

        $this->assertSame(Note::STATE_OPEN, $note->noteState());
        $this->assertTrue($note->isOpen());
        $this->assertFalse($note->isClosed());
    }

    public function test_it_can_close_open_note(): void
    {
        $note = Note::create('note-1', 'Budi', null, new DateTimeImmutable('2026-04-03'));

        $note->close('admin-1', new DateTimeImmutable('2026-04-03 10:00:00'));

        $this->assertSame(Note::STATE_CLOSED, $note->noteState());
        $this->assertTrue($note->isClosed());
        $this->assertSame('admin-1', $note->closedByActorId());
        $this->assertSame('2026-04-03 10:00:00', $note->closedAt()?->format('Y-m-d H:i:s'));
    }

    public function test_it_can_reopen_closed_note(): void
    {
        $note = Note::create('note-1', 'Budi', null, new DateTimeImmutable('2026-04-03'));
        $note->close('admin-1', new DateTimeImmutable('2026-04-03 10:00:00'));

        $note->reopen('admin-2', new DateTimeImmutable('2026-04-03 11:00:00'));

        $this->assertSame(Note::STATE_OPEN, $note->noteState());
        $this->assertTrue($note->isOpen());
        $this->assertSame('admin-2', $note->reopenedByActorId());
        $this->assertSame('2026-04-03 11:00:00', $note->reopenedAt()?->format('Y-m-d H:i:s'));
    }

    public function test_it_rejects_closing_closed_note(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Hanya note open yang boleh ditutup.');

        $note = Note::create('note-1', 'Budi', null, new DateTimeImmutable('2026-04-03'));
        $note->close('admin-1', new DateTimeImmutable('2026-04-03 10:00:00'));
        $note->close('admin-2', new DateTimeImmutable('2026-04-03 11:00:00'));
    }

    public function test_it_rejects_reopening_open_note(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Hanya note closed yang boleh dibuka kembali.');

        $note = Note::create('note-1', 'Budi', null, new DateTimeImmutable('2026-04-03'));
        $note->reopen('admin-1', new DateTimeImmutable('2026-04-03 11:00:00'));
    }
}
