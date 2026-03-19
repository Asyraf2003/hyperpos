<?php

declare(strict_types=1);

$root = realpath(__DIR__ . '/../');
$viewsPath = $root . '/resources/views';

if ($root === false || ! is_dir($viewsPath)) {
    fwrite(STDERR, "ERROR: resources/views tidak ditemukan.\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath, FilesystemIterator::SKIP_DOTS)
);

$rules = [
    [
        'label' => 'raw php tag',
        'regex' => '/<\?(?:php|=)?/i',
    ],
    [
        'label' => 'blade @php directive',
        'regex' => '/@php\b/',
    ],
    [
        'label' => 'blade @endphp directive',
        'regex' => '/@endphp\b/',
    ],
];

$errors = [];

foreach ($iterator as $file) {
    if (! $file->isFile()) {
        continue;
    }

    $filename = $file->getFilename();

    if (! str_ends_with($filename, '.blade.php')) {
        continue;
    }

    $filePath = $file->getRealPath();
    if ($filePath === false) {
        continue;
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        $errors[] = sprintf('[unreadable] %s', str_replace($root . '/', '', $filePath));
        continue;
    }

    foreach ($rules as $rule) {
        if (! preg_match($rule['regex'], $content, $matches, PREG_OFFSET_CAPTURE)) {
            continue;
        }

        $offset = $matches[0][1];
        $line = substr_count(substr($content, 0, $offset), "\n") + 1;
        $relativePath = str_replace($root . '/', '', $filePath);

        $errors[] = sprintf(
            '[%s] %s:%d',
            $rule['label'],
            $relativePath,
            $line
        );
    }
}

if ($errors !== []) {
    echo "\e[31mERROR: Ditemukan PHP/directive PHP di Blade. Ini melanggar boundary presentational:\e[0m\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}

echo "\e[32mSUCCESS: Tidak ditemukan PHP/directive PHP di Blade resources/views.\e[0m\n";
exit(0);
