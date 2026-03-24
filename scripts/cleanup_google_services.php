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
        continue;
    }

    $path = $servicesDir . '/' . $service;

    if (is_dir($path)) {
        deleteDirectory($path);
    } else {
        unlink($path);
    }
}

echo "Cleaned up Google API Services.\n";

function deleteDirectory($dir) {
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
            return false;
        }
    }

    return rmdir($dir);
}
