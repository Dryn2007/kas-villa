<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Service untuk integrasi Duitku Payment Gateway
 * 
 * Handles creating payment invoice dan processing callbacks dari Duitku
 */
class DuitkuService
{
    private string $merchantCode;
    private string $merchantKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->merchantCode = env('DUITKU_MERCHANT_CODE', '');
        $this->merchantKey = env('DUITKU_MERCHANT_KEY', '');

        // Gunakan sandbox atau production sesuai env
        $env = env('DUITKU_ENV', 'sandbox');
        $this->apiUrl = $env === 'production'
            ? 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry'
            : 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry';
    }

    /**
     * Generate signature untuk Duitku API request
     * 
     * Rumus: MD5(merchantCode + merchantOrderId + amount + merchantKey)
     */
    public function generateSignature(string $orderId, int $amount): string
    {
        return md5($this->merchantCode . $orderId . $amount . $this->merchantKey);
    }

    /**
     * Validate signature dari Duitku callback
     * 
     * @param string $orderId - Order ID dari Duitku
     * @param int $amount - Amount yang dibayarkan
     * @param string $signature - Signature dari Duitku callback
     * @return bool - True jika signature valid
     */
    public function validateSignature(string $orderId, int $amount, string $signature): bool
    {
        $expectedSignature = $this->generateSignature($orderId, $amount);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Buat payment invoice di Duitku
     * 
     * @param array $payload - Data pembayaran
     * @return array - Response dari Duitku API
     */
    public function createInvoice(array $payload): array
    {
        try {
            // Tambahkan merchant credentials
            $payload['merchantCode'] = $this->merchantCode;
            $payload['signature'] = $this->generateSignature(
                $payload['merchantOrderId'],
                $payload['paymentAmount']
            );

            // Tembak API Duitku
            $response = Http::timeout(30)
                ->post($this->apiUrl, $payload);

            $result = $response->json();

            // Log request
            try {
                Log::info('Duitku Create Invoice Request', [
                    'order_id' => $payload['merchantOrderId'],
                    'amount' => $payload['paymentAmount'],
                    'status_code' => $response->status()
                ]);
            } catch (\Throwable $err) {}

            return $result;
        } catch (\Exception $e) {
            try {
                Log::error('Duitku API Error', [
                    'message' => $e->getMessage(),
                    'order_id' => $payload['merchantOrderId'] ?? 'unknown'
                ]);
            } catch (\Throwable $err) {}

            return [
                'statusCode' => '01',
                'statusMessage' => 'API Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check status pembayaran di Duitku
     * 
     * Berguna untuk manual check tanpa menunggu callback
     */
    public function checkPaymentStatus(string $orderId): array
    {
        try {
            $params = [
                'merchantCode' => $this->merchantCode,
                'merchantOrderId' => $orderId,
                'signature' => md5($this->merchantCode . $orderId . $this->merchantKey)
            ];

            $response = Http::timeout(30)
                ->post(str_replace('/inquiry', '/checkstatus', $this->apiUrl), $params);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Duitku Check Status Error', [
                'message' => $e->getMessage(),
                'order_id' => $orderId
            ]);

            return [
                'statusCode' => '01',
                'statusMessage' => 'Check Status Error'
            ];
        }
    }

    /**
     * Generate unique order ID
     * 
     * Format: KAS-{timestamp}-{userId}
     * Contoh: KAS-1711270000-5
     */
    public static function generateOrderId(?int $userId = null): string
    {
        if (!$userId) {
            // Fallback jika user tidak login
            $userId = 'anon';
        }
        return 'KAS-' . time() . '-' . $userId;
    }

    /**
     * Get payment method display name
     */
    public static function getPaymentMethodName(string $paymentMethod): string
    {
        $methods = [
            'VA' => 'Virtual Account',
            'OVO' => 'OVO',
            'DANA' => 'DANA',
            'GOPAY' => 'GoPay',
            'LINKAJA' => 'LinkAja',
            'CC' => 'Credit Card',
            'MANDIRI_CLICK' => 'Mandiri Click',
            'BCA_CLICK' => 'BCA ClickPay',
        ];

        return $methods[$paymentMethod] ?? $paymentMethod;
    }

    /**
     * Get status description
     */
    public static function getStatusDescription(string $statusCode): string
    {
        $statuses = [
            '00' => 'Pembayaran Berhasil',
            '01' => 'Pembayaran Gagal / Ditolak',
            '02' => 'Pembayaran Expired',
            '03' => 'Pembayaran Menunggu',
            '04' => 'Pembayaran Dibatalkan',
            '05' => 'Pembayaran Dalam Proses',
        ];

        return $statuses[$statusCode] ?? 'Status Tidak Diketahui';
    }

    /**
     * Buat payment invoice di Duitku Pop
     * 
     * @param array $payload - Data pembayaran
     * @return array - Response dari Duitku API Pop
     */
    public function createInvoicePop(array $payload): array
    {
        try {
            $timestamp = round(microtime(true) * 1000); // Milisecond
            $signature = hash('sha256', $this->merchantCode . $timestamp . $this->merchantKey);

            $env = env('DUITKU_ENV', 'sandbox');
            $apiUrl = $env === 'production'
                ? 'https://api-prod.duitku.com/api/merchant/createInvoice'
                : 'https://api-sandbox.duitku.com/api/merchant/createInvoice';

            $payloadJson = json_encode($payload);
            if ($payloadJson === false) {
                throw new \Exception('JSON Encode Error: ' . json_last_error_msg());
            }

            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Content-Length' => (string) strlen($payloadJson),
                'x-duitku-signature' => $signature,
                'x-duitku-timestamp' => $timestamp,
                'x-duitku-merchantcode' => $this->merchantCode
            ];

            // Tembak API Duitku Pop
            $response = Http::withHeaders($headers)->timeout(30)->post($apiUrl, $payload);

            $result = $response->json();

            // Log request safely
            try {
                Log::info('Duitku Create Invoice Pop Request', [
                    'order_id' => $payload['merchantOrderId'] ?? null,
                    'amount' => $payload['paymentAmount'] ?? null,
                    'status_code' => $response->status(),
                    'response' => $result
                ]);
            } catch (\Throwable $loggingError) {
                // Ignore if log fails on Vercel
            }

            return $result;
        } catch (\Throwable $e) {
            try {
                Log::error('Duitku API Pop Error', [
                    'message' => $e->getMessage(),
                    'order_id' => $payload['merchantOrderId'] ?? 'unknown'
                ]);
            } catch (\Throwable $loggingError) {
                // If logging fails (e.g. read-only filesystem on Vercel), ignore so it doesn't crash the request
            }

            return [
                'statusCode' => '01',
                'statusMessage' => 'API Error: ' . $e->getMessage()
            ];
        }
    }
}
