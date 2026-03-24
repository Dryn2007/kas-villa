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

// Generate test data for Pop API
$orderId = 'KAS-TEST-' . time();
$amount = 65000;
$timestamp = round(microtime(true) * 1000);
$signature = hash('sha256', $merchantCode . $timestamp . $merchantKey);

$payload = [
    'paymentAmount' => $amount,
    'merchantOrderId' => $orderId,
    'productDetails' => 'Test Pop API',
    'email' => 'test@example.com',
    'phoneNumber' => '081234567890',
    'customerVaName' => 'John Doe',
    'callbackUrl' => trim($env['DUITKU_CALLBACK_URL'] ?? 'http://example.com/callback'),
    'returnUrl' => 'http://example.com/return',
    'expiryPeriod' => 10
];

echo "📡 TEST 1: CREATE INVOICE (POP API)\n";
echo "───────────────────────────────────────────────────────────────\n";

$apiUrl = $duitkuEnv === 'production'
    ? 'https://api-prod.duitku.com/api/merchant/createInvoice'
    : 'https://api-sandbox.duitku.com/api/merchant/createInvoice';
echo "Endpoint : " . $apiUrl . "\n";
echo "Order ID : " . $orderId . "\n";
echo "Amount   : Rp " . number_format($amount, 0, ',', '.') . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($payload)),
    'x-duitku-signature: ' . $signature,
    'x-duitku-timestamp: ' . $timestamp,
    'x-duitku-merchantcode: ' . $merchantCode
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

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

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                    Test Complete                             ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
?>