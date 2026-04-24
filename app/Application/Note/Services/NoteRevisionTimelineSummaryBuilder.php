<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Revision\NoteRevision;
use App\Core\Note\Revision\NoteRevisionLineSnapshot;

final class NoteRevisionTimelineSummaryBuilder
{
    public function build(NoteRevision $revision, ?NoteRevision $parent): array
    {
        if ($parent === null) {
            return ['Ringkasan awal nota.'];
        }

        $current = $this->indexLines($revision);
        $previous = $this->indexLines($parent);
        $changes = [];

        foreach ($current as $lineNo => $line) {
            if (!isset($previous[$lineNo])) {
                $changes[] = 'Line ' . $lineNo . ' ditambahkan: ' . $this->lineLabel($line);
                continue;
            }

            $before = $this->lineSignature($previous[$lineNo]);
            $after = $this->lineSignature($line);

            if ($before !== $after) {
                $changes[] = 'Line ' . $lineNo . ': ' . $before . ' -> ' . $after;
            }
        }

        foreach ($previous as $lineNo => $line) {
            if (!isset($current[$lineNo])) {
                $changes[] = 'Line ' . $lineNo . ' dihapus: ' . $this->lineLabel($line);
            }
        }

        if ($changes === []) {
            return ['Tidak ada perubahan line yang terdeteksi.'];
        }

        return array_slice($changes, 0, 3);
    }

    private function indexLines(NoteRevision $revision): array
    {
        $indexed = [];

        foreach ($revision->lines() as $line) {
            $indexed[$line->lineNo()] = $line;
        }

        ksort($indexed);

        return $indexed;
    }

    private function lineSignature(NoteRevisionLineSnapshot $line): string
    {
        return sprintf(
            '%s [%s • %s]',
            $this->lineLabel($line),
            $line->status(),
            number_format($line->subtotalRupiah(), 0, ',', '.')
        );
    }

    private function lineLabel(NoteRevisionLineSnapshot $line): string
    {
        $service = trim((string) ($line->serviceLabel() ?? ''));
        if ($service !== '') {
            return $service;
        }

        $payload = $line->payload();

        $storeLines = is_array($payload['store_stock_lines'] ?? null)
            ? $payload['store_stock_lines']
            : [];

        if ($storeLines !== []) {
            $first = is_array($storeLines[0]) ? $storeLines[0] : [];
            $label = $this->firstNonEmpty([
                $first['product_label'] ?? null,
                $first['product_name'] ?? null,
                $first['product_id'] ?? null,
            ]);

            if ($label !== null) {
                $remaining = count($storeLines) - 1;
                return $remaining > 0 ? $label . ' +' . $remaining . ' item' : $label;
            }
        }

        $externalLines = is_array($payload['external_purchase_lines'] ?? null)
            ? $payload['external_purchase_lines']
            : [];

        if ($externalLines !== []) {
            $first = is_array($externalLines[0]) ? $externalLines[0] : [];
            $label = $this->firstNonEmpty([
                $first['cost_description'] ?? null,
                $first['description'] ?? null,
            ]);

            if ($label !== null) {
                $remaining = count($externalLines) - 1;
                return $remaining > 0 ? $label . ' +' . $remaining . ' item' : $label;
            }
        }

        return 'Line ' . $line->lineNo();
    }

    private function firstNonEmpty(array $values): ?string
    {
        foreach ($values as $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $text = trim((string) $value);

            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }
}
