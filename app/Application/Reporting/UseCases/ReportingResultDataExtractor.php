<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

final class ReportingResultDataExtractor
{
    public static function summary(object $result): array
    {
        return self::section($result, 'summary');
    }

    public static function row(object $result): array
    {
        return self::section($result, 'row');
    }

    public static function rows(object $result): array
    {
        return self::section($result, 'rows');
    }

    public static function snapshotRows(object $result): array
    {
        return self::section($result, 'snapshot_rows');
    }

    private static function section(object $result, string $key): array
    {
        $data = method_exists($result, 'data') ? $result->data() : null;

        return is_array($data) && is_array($data[$key] ?? null)
            ? $data[$key]
            : [];
    }
}
