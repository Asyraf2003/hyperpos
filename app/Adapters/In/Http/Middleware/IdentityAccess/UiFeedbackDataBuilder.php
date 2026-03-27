<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\IdentityAccess;

use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;

final class UiFeedbackDataBuilder
{
    public function __construct(
        private readonly UiFeedbackMessageNormalizer $messages,
    ) {
    }

    /**
     * @return array{type:string,title:string,message:?string,messages:array<int,string>}|null
     */
    public function build(Request $request): ?array
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

        return $this->buildFromSession($request);
    }

    /**
     * @return array<int,string>
     */
    private function extractValidationMessages(Request $request): array
    {
        $errorBag = $request->session()->get('errors');

        if (! $errorBag instanceof ViewErrorBag) {
            return [];
        }

        return $this->messages->normalizeMany($errorBag->all());
    }

    /**
     * @return array{type:string,title:string,message:?string,messages:array<int,string>}|null
     */
    private function buildFromSession(Request $request): ?array
    {
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
                return [
                    'type' => $type,
                    'title' => $title,
                    'message' => null,
                    'messages' => $this->messages->normalizeMany($value),
                ];
            }

            $message = $this->messages->normalizeOne($value);

            if ($message !== null) {
                return [
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'messages' => [],
                ];
            }
        }

        return null;
    }
}
