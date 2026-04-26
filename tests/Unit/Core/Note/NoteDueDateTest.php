<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Note;

use App\Core\Note\Note\Note;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class NoteDueDateTest extends TestCase
{
    public function test_create_calculates_due_date_one_month_later(): void
    {
        $note = Note::create('note-1', 'Budi', null, new DateTimeImmutable('2026-04-23'));

        self::assertSame('2026-05-23', $note->dueDate()->format('Y-m-d'));
    }

    public function test_create_calculates_due_date_without_month_overflow(): void
    {
        $note = Note::create('note-1', 'Budi', null, new DateTimeImmutable('2026-01-31'));

        self::assertSame('2026-02-28', $note->dueDate()->format('Y-m-d'));
    }

    public function test_update_header_recalculates_due_date_from_new_transaction_date(): void
    {
        $note = Note::create('note-1', 'Budi', null, new DateTimeImmutable('2026-04-23'));

        $note->updateHeader('Sari', null, new DateTimeImmutable('2026-01-31'));

        self::assertSame('2026-01-31', $note->transactionDate()->format('Y-m-d'));
        self::assertSame('2026-02-28', $note->dueDate()->format('Y-m-d'));
    }
}
