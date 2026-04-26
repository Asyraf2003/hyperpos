<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Note;

use App\Application\Note\UseCases\GetDueNoteRemindersHandler;
use DateTimeImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class DueNoteReminderPageController extends Controller
{
    public function __construct(
        private readonly GetDueNoteRemindersHandler $reminders,
    ) {
    }

    public function __invoke(Request $request): View
    {
        $today = $this->resolveToday($request);

        return view('admin.notes.due-reminders', [
            'pageTitle' => 'Reminder Jatuh Tempo',
            'today' => $today,
            'rows' => $this->reminders->handle($today, 100),
        ]);
    }

    private function resolveToday(Request $request): string
    {
        $value = $request->query('today');

        if (! is_string($value)) {
            return date('Y-m-d');
        }

        $normalized = trim($value);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $normalized);

        if ($parsed === false || $parsed->format('Y-m-d') !== $normalized) {
            return date('Y-m-d');
        }

        return $normalized;
    }
}
