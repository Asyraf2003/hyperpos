<?php

declare(strict_types=1);

namespace App\Adapters\Out\Audit;

final class AuditLogAdminRowMapper
{
    /**
     * @return array{id:int,event:string,reason:string,context:array<string,mixed>,context_json:string,created_at:string}
     */
    public function map(object $row): array
    {
        $context = json_decode((string) $row->context, true);

        if (! is_array($context)) {
            $context = [];
        }

        $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return [
            'id' => (int) $row->id,
            'event' => (string) $row->event,
            'reason' => $this->resolveReason($context),
            'context' => $context,
            'context_json' => is_string($contextJson) ? $contextJson : '{}',
            'created_at' => (string) $row->created_at,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    private function resolveReason(array $context): string
    {
        foreach (['reason', 'alasan', 'void_reason', 'correction_reason', 'note', 'notes'] as $key) {
            $value = $context[$key] ?? null;

            if (is_scalar($value) && trim((string) $value) !== '') {
                return (string) $value;
            }
        }

        return '-';
    }
}
