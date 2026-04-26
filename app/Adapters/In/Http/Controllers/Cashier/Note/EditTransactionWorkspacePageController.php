<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Application\Note\Services\EditTransactionWorkspacePageDataBuilder;
use App\Application\Note\Services\EnsureInitialNoteRevisionExists;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Note\TransactionWorkspaceDraftReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class EditTransactionWorkspacePageController extends Controller
{
    public function __invoke(
        string $noteId,
        Request $request,
        EditTransactionWorkspacePageDataBuilder $builder,
        TransactionWorkspaceDraftReaderPort $drafts,
        EnsureInitialNoteRevisionExists $ensureInitialRevision,
    ): View {
        $actorId = $request->user()?->getAuthIdentifier();
        $bootstrapRevisionId = trim($noteId) . '-r001';

        try {
            $ensureInitialRevision->handle(
                $noteId,
                $bootstrapRevisionId,
                $actorId !== null ? (string) $actorId : null,
            );
        } catch (DomainException $e) {
            abort(500, $e->getMessage());
        }

        $routeArea = $request->routeIs('admin.notes.*') ? 'admin' : 'cashier';

        $page = $builder->build($noteId, $routeArea);
        $sessionHasOldInput = is_array($request->session()->get('_old_input', [])) && $request->session()->get('_old_input', []) !== [];
        $draftPayload = $this->loadDraftPayload($request, $drafts, $noteId, $sessionHasOldInput);

        $oldNote = old('note');
        $oldItems = old('items');
        $oldInlinePayment = old('inline_payment');

        $noteFromDraft = is_array($draftPayload['note'] ?? null) ? $draftPayload['note'] : [];
        $itemsFromDraft = is_array($draftPayload['items'] ?? null) ? array_values($draftPayload['items']) : [];
        $inlinePaymentFromDraft = is_array($draftPayload['inline_payment'] ?? null) ? $draftPayload['inline_payment'] : [];

        $resolvedNote = is_array($oldNote)
            ? $oldNote
            : (is_array($page['oldNote'] ?? null) ? array_replace($page['oldNote'], $noteFromDraft) : $noteFromDraft);

        $resolvedItems = is_array($oldItems)
            ? array_values($oldItems)
            : ($itemsFromDraft !== [] ? $itemsFromDraft : (is_array($page['oldItems'] ?? null) ? array_values($page['oldItems']) : []));

        $resolvedInlinePayment = is_array($oldInlinePayment)
            ? $oldInlinePayment
            : $inlinePaymentFromDraft;

        return view('cashier.notes.workspace.create', $page + [
            'noteId' => trim($noteId),
            'oldNote' => $resolvedNote,
            'oldItems' => $resolvedItems,
            'oldInlinePayment' => $resolvedInlinePayment,
            'hasOldInput' => $sessionHasOldInput || $draftPayload !== [],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadDraftPayload(
        Request $request,
        TransactionWorkspaceDraftReaderPort $drafts,
        string $noteId,
        bool $sessionHasOldInput,
    ): array {
        if ($sessionHasOldInput) {
            return [];
        }

        $actorId = $request->user()?->getAuthIdentifier();

        if ($actorId === null) {
            return [];
        }

        $draft = $drafts->findByActorAndWorkspaceKey((string) $actorId, 'edit:' . trim($noteId));
        $payload = $draft['payload'] ?? null;

        return is_array($payload) ? $payload : [];
    }
}
