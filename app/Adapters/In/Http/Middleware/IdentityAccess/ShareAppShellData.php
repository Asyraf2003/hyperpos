<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\IdentityAccess;

use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use App\Ports\Out\IdentityAccess\AdminCashierAreaAccessStatePort;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\Response;

final class ShareAppShellData
{
    public function __construct(
        private readonly ActorAccessReaderPort $actors,
        private readonly AdminCashierAreaAccessStatePort $cashierAreaAccessStates,
    ) {
    }

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appShell = [
            'user_email' => null,
            'actor_label' => null,
            'is_admin_actor' => false,
            'can_access_cashier_area' => false,
        ];

        $user = $request->user();

        if ($user !== null) {
            $actorId = (string) $user->getAuthIdentifier();
            $actor = $this->actors->findByActorId($actorId);

            $appShell['user_email'] = $user->email;

            if ($actor !== null) {
                $appShell['actor_label'] = ucfirst($actor->role()->value());
                $appShell['is_admin_actor'] = $actor->isAdmin();

                if ($actor->isAdmin()) {
                    $capability = $this->cashierAreaAccessStates->getByActorId($actorId);
                    $appShell['can_access_cashier_area'] = $capability->isActive();
                }
            }
        }

        $uiFeedback = $this->buildUiFeedback($request);
        $uiFeedbackJson = $uiFeedback === null
            ? 'null'
            : (string) json_encode(
                $uiFeedback,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT,
            );

        view()->share('appShell', $appShell);
        view()->share('uiFeedback', $uiFeedback);
        view()->share('uiFeedbackJson', $uiFeedbackJson);

        return $next($request);
    }

    /**
     * @return array{type:string,title:string,message:?string,messages:array<int,string>}|null
     */
    private function buildUiFeedback(Request $request): ?array
    {
        $validationMessages = $this->extractValidationMessages($request);

        if ($validationMessages !== []) {
            return [
                'type' => 'error',
                'title' => 'Terjadi Kesalahan',
                'message' => null,
                'messages' => $validationMessages,
            ];
        }

        $sessionMap = [
            'error' => 'Terjadi Kesalahan',
            'success' => 'Berhasil',
            'warning' => 'Peringatan',
            'info' => 'Informasi',
        ];

        foreach ($sessionMap as $type => $title) {
            $value = $request->session()->get($type);

            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                $messages = $this->normalizeMessages($value);

                return [
                    'type' => $type,
                    'title' => $title,
                    'message' => null,
                    'messages' => $messages,
                ];
            }

            $message = $this->normalizeMessage($value);

            if ($message === null) {
                continue;
            }

            return [
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'messages' => [],
            ];
        }

        return null;
    }

    /**
     * @return array<int,string>
     */
    private function extractValidationMessages(Request $request): array
    {
        $errorBag = $request->session()->get('errors');

        if (!$errorBag instanceof ViewErrorBag) {
            return [];
        }

        return $this->normalizeMessages($errorBag->all());
    }

    /**
     * @param array<mixed> $messages
     * @return array<int,string>
     */
    private function normalizeMessages(array $messages): array
    {
        $normalized = [];

        array_walk_recursive($messages, function (mixed $value) use (&$normalized): void {
            $message = $this->normalizeMessage($value);

            if ($message !== null) {
                $normalized[] = $message;
            }
        });

        return array_values(array_unique($normalized));
    }

    private function normalizeMessage(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $message = trim((string) $value);

        return $message === '' ? null : $message;
    }
}
