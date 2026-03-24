<?php

$dir = __DIR__ . '/../vendor';
if (!is_dir($dir)) {
    echo "Vendor directory not found at $dir\n";
    exit(1);
}

$items = scandir($dir);
$sizes = [];

function getDirectorySize($path)
{
    if (!file_exists($path)) return 0;
    $size = 0;
    if (is_file($path)) return filesize($path);

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

foreach ($items as $item) {
    if ($item == '.' || $item == '..') continue;
    $path = $dir . '/' . $item;
    $sizes[$item] = getDirectorySize($path);
}

arsort($sizes);

$totalSize = 0;
echo "\nTop Vendor Directories:\n";
foreach ($sizes as $name => $bytes) {
    $totalSize += $bytes;
    if ($bytes > 1000000) { // Only show > 1MB
        echo str_pad($name, 30) . ": " . number_format($bytes / 1024 / 1024, 2) . " MB\n";
    }
}

echo "\nTotal Vendor Size: " . number_format($totalSize / 1024 / 1024, 2) . " MB\n";
