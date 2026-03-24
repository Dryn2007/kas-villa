#!/usr/bin/env php
<?php

/**
 * Script Testing Duitku Integration
 * Gunakan: php test_duitku.php
 * 
 * Script ini akan test koneksi ke Duitku API langsung tanpa Laravel
 */

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          Duitku Integration Test Script                      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Load .env manually (since no Laravel)
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo "❌ ERROR: .env file not found!\n";
    exit(1);
}

$env = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') === false || strpos($line, '#') === 0) continue;

    list($key, $value) = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value);
    // Remove quotes
    $value = str_replace(['"', "'"], '', $value);
    $env[$key] = $value;
}

$merchantCode = trim($env['DUITKU_MERCHANT_CODE'] ?? '');
$merchantKey = trim($env['DUITKU_MERCHANT_KEY'] ?? '');
$duitkuEnv = trim($env['DUITKU_ENV'] ?? 'sandbox');

echo "📋 CONFIGURATION CHECK:\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "Merchant Code: " . ($merchantCode ? "✅ " . substr($merchantCode, 0, 5) . "***" : "❌ MISSING") . "\n";
echo "Merchant Key: " . ($merchantKey ? "✅ " . substr($merchantKey, 0, 5) . "***" : "❌ MISSING") . "\n";
echo "Environment: " . ($duitkuEnv === 'sandbox' ? "✅ SANDBOX" : "⚠️ PRODUCTION") . "\n";
echo "\n";

if (empty($merchantCode) || empty($merchantKey)) {
    echo "❌ ERROR: Merchant credentials incomplete!\n";
    echo "Please check .env file:\n";
    echo "  - DUITKU_MERCHANT_CODE\n";
    echo "  - DUITKU_MERCHANT_KEY\n";
    exit(1);
}

// Generate test data
$orderId = 'KAS-TEST-' . time();
$amount = 65000;
$signature = md5($merchantCode . $orderId . $amount . $merchantKey);

echo "📝 TEST DATA:\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "Order ID: $orderId\n";
echo "Amount: Rp " . number_format($amount, 0, ',', '.') . "\n";
echo "Signature: " . substr($signature, 0, 10) . "...\n";
echo "\n";

// Verify signature generation
echo "🔐 SIGNATURE VERIFICATION:\n";
echo "───────────────────────────────────────────────────────────────\n";
$input = $merchantCode . $orderId . $amount . $merchantKey;
echo "Input: " . substr($input, 0, 20) . "...\n";
echo "Output (MD5): $signature\n";
echo "✅ Signature generated successfully\n";
echo "\n";

// Test with curl
echo "🌐 TESTING API ENDPOINT:\n";
echo "───────────────────────────────────────────────────────────────\n";

$url = 'https://api-sandbox.duitku.com/api/merchant/createInvoice';
echo "URL: $url\n\n";

$payload = [
    'merchantCode' => $merchantCode,
    'paymentAmount' => (int)$amount,
    'merchantOrderId' => $orderId,
    'productDetails' => 'Test Payment - Kas Villa',
    'email' => 'test@kasvilla.local',
    'customerVaName' => 'Test User',
    'phoneNumber' => '62812345678',
    'returnUrl' => 'http://localhost:8000/dashboard',
    'callbackUrl' => 'http://localhost:8000/api/duitku/callback',
    'signature' => $signature,
    'expiryPeriod' => 60
];

echo "📤 Sending request...\n";

// Use curl if available
if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "\n❌ CURL ERROR: $error\n";
        echo "\nTROUBLESHOOTING:\n";
        echo "1. Check internet connection\n";
        echo "2. Check if firewall blocks HTTPS\n";
        echo "3. Try with different network\n";
        exit(1);
    }

    echo "📥 Response received!\n\n";
    echo "HTTP Status: $httpCode\n";

    if ($httpCode === 200) {
        echo "✅ Status: SUCCESS\n";
    } elseif ($httpCode === 401) {
        echo "❌ Status: UNAUTHORIZED\n";
        echo "Check merchant code and key!\n";
    } elseif ($httpCode === 400) {
        echo "⚠️ Status: BAD REQUEST\n";
        echo "Check parameter format!\n";
    } else {
        echo "⚠️ Status: " . ($httpCode ?: "Unknown") . "\n";
    }

    echo "\nResponse Body:\n";
    echo "───────────────────────────────────────────────────────────────\n";
    echo $response . "\n\n";

    $result = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ Invalid JSON response\n";
        exit(1);
    }

    if (isset($result['paymentUrl'])) {
        echo "✅ SUCCESS! Payment URL generated:\n";
        echo $result['paymentUrl'] . "\n";
    } else {
        echo "❌ FAILED! Response:\n";
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        if (isset($result['statusMessage'])) {
            echo "\nError Message: " . $result['statusMessage'] . "\n";
        }
    }
} else {
    echo "⚠️ cURL not available. Using file_get_contents instead...\n\n";

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => json_encode($payload),
            'timeout' => 10
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        echo "❌ Failed to connect to Duitku API\n";
        echo "Check internet connection and firewall settings\n";
        exit(1);
    }

    echo "✅ Response received:\n";
    echo $response . "\n";
}

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                    Test Complete                             ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
?>