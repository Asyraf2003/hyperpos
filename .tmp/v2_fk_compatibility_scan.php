<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$sourcePath = __DIR__ . '/v2_fk_orphan_scan_report.json';

if (! is_file($sourcePath)) {
    fwrite(STDERR, "missing source report: {$sourcePath}" . PHP_EOL);
    exit(1);
}

$source = json_decode((string) file_get_contents($sourcePath), true, 512, JSON_THROW_ON_ERROR);
$checks = $source['results'] ?? [];

$defaultConnection = (string) config('database.default');
$databaseName = (string) config("database.connections.{$defaultConnection}.database");

function columnMeta(string $databaseName, string $tableName, string $columnName): ?array
{
    $row = DB::table('information_schema.COLUMNS')
        ->select(
            'TABLE_NAME',
            'COLUMN_NAME',
            'DATA_TYPE',
            'COLUMN_TYPE',
            'IS_NULLABLE',
            'CHARACTER_SET_NAME',
            'COLLATION_NAME'
        )
        ->where('TABLE_SCHEMA', $databaseName)
        ->where('TABLE_NAME', $tableName)
        ->where('COLUMN_NAME', $columnName)
        ->first();

    return $row ? (array) $row : null;
}

function childIndexNames(string $databaseName, string $tableName, string $columnName): array
{
    return DB::table('information_schema.STATISTICS')
        ->where('TABLE_SCHEMA', $databaseName)
        ->where('TABLE_NAME', $tableName)
        ->where('COLUMN_NAME', $columnName)
        ->orderBy('INDEX_NAME')
        ->pluck('INDEX_NAME')
        ->unique()
        ->values()
        ->all();
}

$results = [];
$failedChecks = 0;

foreach ($checks as $check) {
    $child = columnMeta($databaseName, $check['child_table'], $check['child_column']);
    $parent = columnMeta($databaseName, $check['parent_table'], $check['parent_pk']);

    $compatible =
        $child !== null &&
        $parent !== null &&
        (string) $child['COLUMN_TYPE'] === (string) $parent['COLUMN_TYPE'] &&
        (string) ($child['CHARACTER_SET_NAME'] ?? '') === (string) ($parent['CHARACTER_SET_NAME'] ?? '') &&
        (string) ($child['COLLATION_NAME'] ?? '') === (string) ($parent['COLLATION_NAME'] ?? '');

    if (! $compatible) {
        $failedChecks++;
    }

    $results[] = [
        'name' => $check['name'],
        'compatible' => $compatible,
        'child' => $child,
        'parent' => $parent,
        'child_index_names' => childIndexNames($databaseName, $check['child_table'], $check['child_column']),
    ];
}

$output = [
    'generated_at' => date(DATE_ATOM),
    'connection' => $defaultConnection,
    'driver' => DB::connection()->getDriverName(),
    'database' => $databaseName,
    'total_checks' => count($checks),
    'failed_checks' => $failedChecks,
    'overall_status' => $failedChecks === 0 ? 'PASS' : 'FAIL',
    'results' => $results,
];

$reportPath = __DIR__ . '/v2_fk_compatibility_scan_report.json';

file_put_contents(
    $reportPath,
    json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo 'overall_status=' . $output['overall_status'] . PHP_EOL;
echo 'failed_checks=' . $failedChecks . PHP_EOL;
echo 'report=' . $reportPath . PHP_EOL;

foreach ($results as $row) {
    if ($row['compatible'] !== true) {
        echo $row['name'] . ' => incompatible' . PHP_EOL;
    }
}
