# 🔴 Duitku Unauthorized Error - Debugging Guide

**Error:** `Duitku Menolak: Unauthorized`

---

## 🔍 Penyebab Umum & Solusi

### 1. **Merchant Code atau Merchant Key Salah**

**Gejala:**

- Error: "Unauthorized"
- Signature validation failed

**Solusi:**

```bash
# 1. Cek di .env apakah credentials benar
cat .env | grep DUITKU

# 2. Pastikan tidak ada spasi di awal/akhir
# ❌ SALAH:
# DUITKU_MERCHANT_CODE= DS29069

# ✅ BENAR:
# DUITKU_MERCHANT_CODE=DS29069

# 3. Cek di Duitku dashboard (login ke https://sandbox.duitku.com/)
# Verifikasi merchant code & key match dengan .env
```

**Di Code:**

```php
// Debug: Cek apakah credentials terbaca
$merchantCode = env('DUITKU_MERCHANT_CODE');
$merchantKey = env('DUITKU_MERCHANT_KEY');

dd($merchantCode, $merchantKey); // Tampilkan ke browser
// Pastikan tidak ada null atau whitespace
```

---

### 2. **Signature MD5 Salah**

**Gejala:**

- Signature validation error dari Duitku

**Rumus yang Benar:**

```
signature = MD5(merchantCode + merchantOrderId + amount + merchantKey)
```

**Debugging:**

```php
// Di dummyPayBulk()
$merchantCode = 'DS29069';
$orderId = 'KAS-1711270000-5';
$amount = 65000;
$merchantKey = '9347e1c55b2b3fdbe1a01e10ea63c0ea';

// Calculate signature
$signature = md5($merchantCode . $orderId . $amount . $merchantKey);
echo "Generated Signature: " . $signature;

// Verify dengan online tool
// https://www.md5online.org/
// Paste: DS290691KAS-1711270000-565000934...
```

**Common Mistakes:**

- ❌ Signature order salah: md5(orderId + merchantCode + amount + key)
- ❌ Amount sebagai string: md5(merchantCode . orderId . '65000' . key)
- ❌ Extra spaces: md5(merchantCode . ' ' . orderId . amount . key)

---

### 3. **API Endpoint Salah**

**Gejala:**

- 404 Not Found atau Unauthorized
- Wrong API version

**Verifikasi Endpoint:**

```php
// ✅ CORRECT (createInvoice endpoint)
$url = 'https://api-sandbox.duitku.com/api/merchant/createInvoice';

// ❌ WRONG (inquiry endpoint - deprecated)
$url = 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry';

// ❌ WRONG (production URL di sandbox)
$url = 'https://api.duitku.com/api/merchant/createInvoice';
```

**Current Implementation:**

```php
$response = Http::timeout(10)->post('https://api-sandbox.duitku.com/api/merchant/createInvoice', $params);
```

---

### 4. **Parameter Format Salah**

**Gejala:**

- 400 Bad Request
- Parameter validation error

**Pastikan Tipe Data Benar:**

```php
$params = [
    'merchantCode' => $merchantCode,              // STRING
    'paymentAmount' => (int) $amount,             // INTEGER ✅ (TIDAK STRING!)
    'merchantOrderId' => $orderId,                // STRING
    'productDetails' => $bulanTeks,               // STRING
    'email' => Auth::user()->email,               // STRING (VALID EMAIL)
    'customerVaName' => Auth::user()->name,       // STRING
    'phoneNumber' => $userPhone,                  // STRING (VALID FORMAT)
    'returnUrl' => route('dashboard'),            // STRING (VALID URL)
    'callbackUrl' => url('/api/duitku/callback'), // STRING (VALID URL)
    'signature' => $signature,                    // STRING (MD5 HASH)
    'expiryPeriod' => 60                          // INTEGER
];
```

**Critical:**

- `paymentAmount` **HARUS INTEGER**, bukan string!
- `phoneNumber` harus format: `62812345678` (tanpa 0 di depan)
- Email harus valid format

---

### 5. **Request Headers Salah**

**Default Headers dari Laravel Http Client:**

```
Content-Type: application/json
Accept: application/json
User-Agent: Laravel HttpClient/8.x
```

**Duitku mungkin membutuhkan:**

```php
$response = Http::timeout(10)
    ->withHeaders([
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ])
    ->post('https://api-sandbox.duitku.com/api/merchant/createInvoice', $params);
```

---

### 6. **Environment Variables Tidak Ter-load**

**Gejala:**

- `env('DUITKU_MERCHANT_CODE')` return NULL
- Credentials tidak terbaca dari .env

**Solusi:**

```bash
# Clear config cache
php artisan config:cache

# OR clear semua cache
php artisan cache:clear
php artisan config:clear

# Restart server
php artisan serve

# Verify credentials terbaca
php artisan tinker
>>> env('DUITKU_MERCHANT_CODE')
=> "DS29069"
```

---

### 7. **Sandbox vs Production URL**

**Sandbox (untuk testing):**

```
https://api-sandbox.duitku.com/api/merchant/createInvoice
```

**Production (live):**

```
https://api.duitku.com/api/merchant/createInvoice
```

**Verifikasi di .env:**

```properties
DUITKU_ENV=sandbox  # Pastikan ini benar!
```

---

## 🧪 Testing Checklist

### Langkah 1: Verify Credentials

```bash
php artisan tinker

>>> env('DUITKU_MERCHANT_CODE')
=> "DS29069"

>>> env('DUITKU_MERCHANT_KEY')
=> "9347e1c55b2b3fdbe1a01e10ea63c0ea"

>>> env('DUITKU_ENV')
=> "sandbox"
```

### Langkah 2: Test Signature Generation

```php
$merchantCode = env('DUITKU_MERCHANT_CODE');
$orderId = 'KAS-1711270000-5';
$amount = 65000;
$merchantKey = env('DUITKU_MERCHANT_KEY');

$signature = md5($merchantCode . $orderId . $amount . $merchantKey);
echo $signature; // Copy dan verify di https://www.md5online.org/
```

### Langkah 3: Verify Endpoint

```bash
# Test endpoint dengan curl
curl -X POST https://api-sandbox.duitku.com/api/merchant/createInvoice \
  -H "Content-Type: application/json" \
  -d '{
    "merchantCode": "DS29069",
    "merchantOrderId": "KAS-TEST-123",
    "paymentAmount": 65000,
    "productDetails": "Test Payment",
    "email": "test@example.com",
    "customerVaName": "Test User",
    "phoneNumber": "62812345678",
    "returnUrl": "http://localhost:8000/dashboard",
    "callbackUrl": "http://localhost:8000/api/duitku/callback",
    "signature": "YOUR_MD5_SIGNATURE_HERE",
    "expiryPeriod": 60
  }'
```

### Langkah 4: Test Pembayaran di Aplikasi

```bash
# 1. Start Laravel server
php artisan serve

# 2. Go to http://localhost:8000/dashboard

# 3. Click "Bayar Online" for 1 bulan

# 4. Check terminal untuk error messages

# 5. Check browser console (F12) untuk network errors
```

---

## 📋 Common Error Messages & Solutions

| Error                   | Penyebab                      | Solusi                                                      |
| ----------------------- | ----------------------------- | ----------------------------------------------------------- |
| `Unauthorized`          | Merchant code/key salah       | Verifikasi di .env & Duitku dashboard                       |
| `Invalid signature`     | MD5 signature salah           | Check order: merchantCode + orderId + amount + key          |
| `400 Bad Request`       | Parameter format salah        | amount harus INTEGER, bukan STRING                          |
| `404 Not Found`         | Endpoint salah                | Gunakan `api-sandbox.duitku.com` (bukan sandbox.duitku.com) |
| `Connection timeout`    | Server tidak respons          | Cek internet connection, timeout value                      |
| `Invalid merchant code` | Merchant code tidak terdaftar | Pastikan merchant code sesuai di Duitku dashboard           |

---

## 🔧 Quick Fix Checklist

- [ ] Credentials di .env benar
- [ ] Tidak ada spasi di awal/akhir DUITKU_MERCHANT_CODE atau DUITKU_MERCHANT_KEY
- [ ] Run `php artisan config:cache` setelah update .env
- [ ] Signature formula: `md5(merchantCode + orderId + amount + merchantKey)`
- [ ] Amount harus INTEGER, bukan STRING
- [ ] Endpoint: `https://api-sandbox.duitku.com/api/merchant/createInvoice`
- [ ] DUITKU_ENV=sandbox (bukan production)
- [ ] Email valid format (contains @)
- [ ] Phone number format: `62812345678` (no leading 0)
- [ ] returnUrl & callbackUrl adalah valid absolute URLs

---

## 📞 Debug Output

Tambahkan temporary logging untuk debugging:

```php
// Di dummyPayBulk() sebelum Http::post()

Log::info('Duitku Request Debug', [
    'merchantCode' => $merchantCode,
    'orderId' => $orderId,
    'amount' => $amount,
    'amount_type' => gettype($amount),
    'signature' => $signature,
    'email' => Auth::user()->email,
    'phoneNumber' => $userPhone,
    'endpoint' => 'https://api-sandbox.duitku.com/api/merchant/createInvoice'
]);

$response = Http::timeout(10)->post('https://api-sandbox.duitku.com/api/merchant/createInvoice', $params);

Log::info('Duitku Response', [
    'status_code' => $response->status(),
    'body' => $response->body(),
    'json' => $response->json()
]);
```

Kemudian cek:

```bash
tail -f storage/logs/laravel.log
```

---

## 🔗 Resources

- **Duitku Sandbox:** https://sandbox.duitku.com/
- **Duitku API Docs:** Check merchant dashboard → API Documentation
- **MD5 Signature Generator:** https://www.md5online.org/
- **URL Encoder:** https://www.urlencoder.org/

---

## 💡 Pro Tips

1. **Test dengan order ID berbeda setiap kali** - Duitku mungkin cache order ID yang sama
2. **Gunakan test card dari Duitku** - Ada list test card numbers di sandbox dashboard
3. **Cek timezone server** - Pastikan time sync untuk signature generation
4. **Monitor response time** - Timeout 10 detik mungkin perlu dikurangi jadi 5-10s
5. **Log semua request/response** - Akan sangat membantu debugging

---

**Last Updated:** March 24, 2026  
**Status:** Debugging Guide for Unauthorized Error
