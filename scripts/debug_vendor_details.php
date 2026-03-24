<?php

function dirSize($dir)
{
    if (!is_dir($dir)) return filesize($dir) ?: 0;
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

echo "--- VENDOR/GOOGLE ---\n";
$path = __DIR__ . '/../vendor/google';
if (is_dir($path)) {
    foreach (scandir($path) as $item) {
        if ($item == '.' || $item == '..') continue;
        $size = dirSize($path . '/' . $item);
        echo str_pad($item, 30) . ": " . number_format($size / 1024 / 1024, 2) . " MB\n";
    }
}

echo "\n--- VENDOR/COMPOSER ---\n";
$path = __DIR__ . '/../vendor/composer';
if (is_dir($path)) {
    foreach (scandir($path) as $item) {
        if ($item == '.' || $item == '..') continue;
        $size = dirSize($path . '/' . $item);
        if ($size > 100000) { // Only show > 100KB
            echo str_pad($item, 30) . ": " . number_format($size / 1024 / 1024, 2) . " MB\n";
        }
    }
}
