<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\Note;

use App\Application\Note\Policies\CashierNoteAccessGuard;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteReaderPort;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCashierNoteAccess
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly CashierNoteAccessGuard $guard,
        private readonly ClockPort $clock,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $noteId = $request->route('noteId');

        if (!is_string($noteId) || trim($noteId) === '') {
            return $next($request);
        }

        $note = $this->notes->getById($noteId);
        abort_if($note === null, 404);

        try {
            $this->guard->assertCanAccess($note, $this->clock->now());
        } catch (DomainException $e) {
            abort(403, $e->getMessage());
        }

        return $next($request);
    }
}
