<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Note\Policies\CashierNoteAccessGuard;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteReaderPort;

final readonly class CashierNoteRouteAccessData
{
    public function __construct(
        private NoteReaderPort $notes,
        private CashierNoteAccessGuard $guard,
        private ClockPort $clock,
    ) {
    }

    public function ensureCanView(string $noteId): bool
    {
        $note = $this->notes->getById($noteId);

        if ($note === null) {
            return false;
        }

        $this->guard->assertCanView($note, $this->clock->now());

        return true;
    }

    public function ensureCanMutateOpenNote(string $noteId): bool
    {
        $note = $this->notes->getById($noteId);

        if ($note === null) {
            return false;
        }

        $this->guard->assertCanMutateOpenNote($note, $this->clock->now());

        return true;
    }
}
