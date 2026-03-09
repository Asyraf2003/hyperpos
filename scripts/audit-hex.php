#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$rules = [
    [
        'name' => 'Core must not depend on framework/application/adapters/ports',
        'path' => $root . '/app/Core',
        'forbidden' => [
            'Illuminate\\',
            'App\\Application\\',
            'App\\Adapters\\',
            'App\\Ports\\',
        ],
    ],
    [
        'name' => 'Application must not depend on framework/adapters',
        'path' => $root . '/app/Application',
        'forbidden' => [
            'Illuminate\\',
            'App\\Adapters\\',
        ],
    ],
    [
        'name' => 'Ports must not depend on framework/adapters',
        'path' => $root . '/app/Ports',
        'forbidden' => [
            'Illuminate\\',
            'App\\Adapters\\',
        ],
    ],
];

$violations = [];

function phpFiles(string $dir): array
{
    if (!is_dir($dir)) {
        return [];
    }

    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );

    $files = [];
    foreach ($rii as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    sort($files);
    return $files;
}

function normalizePath(string $path): string
{
    return str_replace(dirname(__DIR__) . '/', '', $path);
}

foreach ($rules as $rule) {
    $files = phpFiles($rule['path']);

    foreach ($files as $file) {
        $content = file_get_contents($file);
        if ($content === false) {
            $violations[] = [
                'rule' => $rule['name'],
                'file' => normalizePath($file),
                'line' => 0,
                'matched' => 'Unable to read file',
            ];
            continue;
        }

        $lines = preg_split("/\\R/", $content) ?: [];

        foreach ($lines as $index => $line) {
            $trimmed = trim($line);

            if (
                str_starts_with($trimmed, 'use ') ||
                str_starts_with($trimmed, '\\') ||
                str_contains($trimmed, ' new ') ||
                str_contains($trimmed, 'implements ') ||
                str_contains($trimmed, 'extends ')
            ) {
                foreach ($rule['forbidden'] as $forbidden) {
                    if (str_contains($line, $forbidden)) {
                        $violations[] = [
                            'rule' => $rule['name'],
                            'file' => normalizePath($file),
                            'line' => $index + 1,
                            'matched' => trim($line),
                        ];
                    }
                }
            }
        }
    }
}

if ($violations !== []) {
    fwrite(STDERR, "HEXAGONAL AUDIT: FAILED\n\n");
    foreach ($violations as $violation) {
        fwrite(
            STDERR,
            sprintf(
                "[%s]\n- File : %s\n- Line : %d\n- Code : %s\n\n",
                $violation['rule'],
                $violation['file'],
                $violation['line'],
                $violation['matched']
            )
        );
    }
    exit(1);
}

fwrite(STDOUT, "HEXAGONAL AUDIT: OK\n");
exit(0);
