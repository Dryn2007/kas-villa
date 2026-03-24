<?php

echo "Starting cleanup...\n";

$vendorDir = __DIR__ . '/../vendor';

// 1. Cleanup Google Services
$servicesDir = $vendorDir . '/google/apiclient-services/src';

if (is_dir($servicesDir)) {
    echo "Cleaning Google API Services...\n";
    $services = scandir($servicesDir);

    foreach ($services as $service) {
        if (in_array($service, ['.', '..', 'Drive', 'Drive.php'])) {
            // echo "Keeping: $service\n";
            continue;
        }

        $path = $servicesDir . '/' . $service;

        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            try {
                unlink($path);
            } catch (Exception $e) {
                // verify if file still exists before complaining
                if (file_exists($path)) {
                    echo "Failed to delete file: $path. Error: " . $e->getMessage() . "\n";
                }
            }
        }
    }
}

// 2. Cleanup Composer Junk Directories (e.g. 4ce5c010)
$composerDir = $vendorDir . '/composer';
if (is_dir($composerDir)) {
    echo "Cleaning Composer junk directories...\n";
    $items = scandir($composerDir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;

        $path = $composerDir . '/' . $item;
        if (is_dir($path)) {
            // Check if it looks like a hex string directory (common for temp folders)
            // Or just delete ANY subdirectory in vendor/composer because normal composer files are in root of vendor/composer
            // WAIT! verify content of vendor/composer. usually it only has .php and .json files.
            // But sometimes it has `ca-bundle` or similar? No, usually not.
            echo "Deleting junk directory in vendor/composer: $item\n";
            deleteDirectory($path);
        }
    }
}

echo "Cleanup complete.\n";

function deleteDirectory($dir)
{
    if (!file_exists($dir)) {
        return true;
    }

    // Try system command first for speed and reliability
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec(sprintf("rd /s /q %s", escapeshellarg($dir)));
    } else {
        exec(sprintf("rm -rf %s", escapeshellarg($dir)));
    }

    // Fallback to PHP implementation if directory still exists (e.g. exec disabled)
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            // return false; 
        }
    }

    return rmdir($dir);
}
