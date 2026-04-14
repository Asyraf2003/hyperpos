<?php
// make_storage_link.php
// Jalankan sekali, lalu HAPUS filenya.

$token = $_GET['token'] ?? '';
$expected = 'ueshdf7uinie4hfyusfhueisgbfewj';

if (!hash_equals($expected, $token)) {
    http_response_code(403);
    exit('Forbidden');
}

$publicPath = __DIR__; // public_html
// Sesuaikan ini kalau app kamu bukan /home/asyraf/app2
$target = realpath(__DIR__ . '/../app/storage/app/private');
$link   = $publicPath . '/storage';

if ($target === false) {
    http_response_code(500);
    exit('Target storage/app/private not found');
}

if (file_exists($link)) {
    // Kalau sudah ada folder/file "storage", jangan ditimpa
    exit('Link/folder already exists at: ' . $link);
}

if (!function_exists('symlink')) {
    http_response_code(500);
    exit('symlink() is disabled on this server');
}

$ok = @symlink($target, $link);

if (!$ok) {
    $err = error_get_last();
    http_response_code(500);
    exit('Failed to create symlink. ' . ($err['message'] ?? ''));
}

echo "OK: created symlink\n$link -> $target\n";