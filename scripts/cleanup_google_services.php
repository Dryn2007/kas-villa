<?php

$vendorDir = __DIR__ . '/../vendor';
$servicesDir = $vendorDir . '/google/apiclient-services/src';

if (!is_dir($servicesDir)) {
    echo "Google API Services directory not found at " . realpath($servicesDir) . "\n";
    return;
}

$services = scandir($servicesDir);

foreach ($services as $service) {
    if (in_array($service, ['.', '..', 'Drive', 'Drive.php'])) {
        echo "Keeping: $service\n";
        continue;
    }

    $path = $servicesDir . '/' . $service;

    if (is_dir($path)) {
        deleteDirectory($path);
    } else {
        try {
            unlink($path);
        } catch (Exception $e) {
            echo "Failed to delete file: $path. Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "Cleaned up Google API Services.\n";

function deleteDirectory($dir)
{
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        try {
            return unlink($dir);
        } catch (Exception $e) {
            echo "Failed to delete file: $dir. Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    try {
        return rmdir($dir);
    } catch (Exception $e) {
        echo "Failed to remove directory: $dir. Error: " . $e->getMessage() . "\n";
        return false;
    }
}
