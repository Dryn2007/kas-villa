<?php

function getDirSize($dir)
{
    if (!is_dir($dir)) return 0;
    $size = 0;
    try {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $size += $file->getSize();
        }
    } catch (Exception $e) {
        // ignore errors
    }
    return $size;
}

echo "Vendor: " . number_format(getDirSize('vendor') / 1024 / 1024, 2) . " MB\n";
echo "Node Modules: " . number_format(getDirSize('node_modules') / 1024 / 1024, 2) . " MB\n";
echo "Public: " . number_format(getDirSize('public') / 1024 / 1024, 2) . " MB\n";
echo "Storage: " . number_format(getDirSize('storage') / 1024 / 1024, 2) . " MB\n";
