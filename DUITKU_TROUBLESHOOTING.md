# 🚀 Duitku Unauthorized - Step-by-Step Troubleshooting

## ⚡ Quick Diagnosis

Gunakan script ini untuk diagnosis cepat:

```bash
php test_duitku.php
```

Script ini akan:

1. ✅ Cek credentials di .env
2. ✅ Generate test signature
3. ✅ Test koneksi ke API Duitku
4. ✅ Show response dari server

---

## 🔍 Langkah Debugging (Urutan Penting)

### **Step 1: Verify .env Credentials**

```bash
# Terminal
cat .env | grep DUITKU
```

Output harus seperti ini:

```properties
DUITKU_MERCHANT_CODE=DS29069
DUITKU_MERCHANT_KEY=9347e1c55b2b3fdbe1a01e10ea63c0ea
DUITKU_ENV=sandbox
```

⚠️ **CRITICAL CHECKS:**

- ❌ Jangan ada spasi di awal/akhir nilai
- ❌ Jangan ada tanda kutip: `"DS29069"` (salah!)
- ✅ Format: `DUITKU_MERCHANT_CODE=DS29069` (benar!)

---

### **Step 2: Clear Laravel Cache**

```bash
# Very important! Config cache bisa outdated
php artisan config:clear
php artisan cache:clear

# Restart server
php artisan serve --port=8000
```

---

### **Step 3: Verify Credentials Loaded in Application**

```bash
# Open Laravel Tinker
php artisan tinker

# Check apakah credentials terbaca
>>> env('DUITKU_MERCHANT_CODE')
=> "DS29069"

>>> env('DUITKU_MERCHANT_KEY')
=> "9347e1c55b2b3fdbe1a01e10ea63c0ea"

# Exit tinker
>>> exit
```

Jika hasil null atau kosong → **PROBLEM FOUND!**

- Clear cache dan restart server
- Atau restart VSCode

---

### **Step 4: Verify Signature Generation**

```bash
php artisan tinker

# Generate test signature
>>> $merchantCode = 'DS29069'
>>> $orderId = 'KAS-TEST-123'
>>> $amount = 65000
>>> $merchantKey = '9347e1c55b2b3fdbe1a01e10ea63c0ea'

>>> $signature = md5($merchantCode . $orderId . $amount . $merchantKey)
=> "a1b2c3d4e5f6..."

# Copy signature dan verify di https://www.md5online.org/
# Paste: DS290691KAS-TEST-123650009347e1c55b2b3fdbe1a01e10ea63c0ea
# Hasilnya harus sama dengan $signature di atas
```

---

### **Step 5: Test API Endpoint dengan Curl**

Jika punya Git Bash atau WSL di Windows:

```bash
# Copet credentials dari .env
MERCHANT_CODE="DS29069"
MERCHANT_KEY="9347e1c55b2b3fdbe1a01e10ea63c0ea"

# Generate signature
MERCHANT_ORDER_ID="KAS-TEST-$(date +%s)"
AMOUNT=65000
SIGNATURE=$(echo -n "$MERCHANT_CODE$MERCHANT_ORDER_ID$AMOUNT$MERCHANT_KEY" | md5sum | awk '{print $1}')

# Test API
curl -X POST https://api-sandbox.duitku.com/api/merchant/createInvoice \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{
    \"merchantCode\": \"$MERCHANT_CODE\",
    \"paymentAmount\": $AMOUNT,
    \"merchantOrderId\": \"$MERCHANT_ORDER_ID\",
    \"productDetails\": \"Test Payment\",
    \"email\": \"test@example.com\",
    \"customerVaName\": \"Test User\",
    \"phoneNumber\": \"62812345678\",
    \"returnUrl\": \"http://localhost:8000/dashboard\",
    \"callbackUrl\": \"http://localhost:8000/api/duitku/callback\",
    \"signature\": \"$SIGNATURE\",
    \"expiryPeriod\": 60
  }"
```

---

### **Step 6: Check Application Logs**

Pastikan sudah ada logging di code (sudah saya update):

```bash
# Terminal
tail -f storage/logs/laravel.log

# Di browser, klik "Bayar Online"
# Di terminal, lihat output logs

# Harus ada sesuatu seperti:
# [2026-03-24 10:30:45] local.INFO: Duitku CreateInvoice Request
# [2026-03-24 10:30:46] local.INFO: Duitku CreateInvoice Response
```

---

## 🎯 Common Issues & Quick Fixes

### Issue #1: "Unauthorized" / 401 Status

**Kemungkinan:**

1. Merchant Code salah
2. Merchant Key salah
3. Credentials tidak terbaca dari .env

**Quick Fix:**

```bash
# 1. Verify .env
cat .env | grep DUITKU

# 2. Clear cache
php artisan config:clear
php artisan cache:clear

# 3. Restart server
php artisan serve --port=8000

# 4. Test lagi
```

---

### Issue #2: "400 Bad Request"

**Kemungkinan:**

1. Amount bukan integer
2. Email format salah
3. Phone number format salah
4. Parameter missing

**Quick Fix:**

```php
// Check di code
var_dump(gettype($amount)); // harus 'integer', bukan 'string'
var_dump($userPhone);        // harus format 62812345678
var_dump($email);            // harus format valid@email.com
```

---

### Issue #3: "Invalid Signature" / 422 Status

**Kemungkinan:**

1. Signature formula salah
2. Karakter hilang/lebih
3. Character encoding issue

**Quick Fix:**

```php
// Check order yang benar
$signature = md5($merchantCode . $orderId . $amount . $merchantKey);
//              ^^^^^^^^^^^^^^^^   ^^^^^^^^   ^^^^^^   ^^^^^^^^^^^
//              [1]                [2]        [3]      [4]
// Order PENTING!
```

---

### Issue #4: "Connection Timeout"

**Kemungkinan:**

1. Internet offline
2. Firewall block HTTPS
3. DNS issue
4. Server Duitku down

**Quick Fix:**

```bash
# Test internet
ping google.com

# Test DNS
nslookup api-sandbox.duitku.com

# Test HTTPS connection
curl -v https://api-sandbox.duitku.com/
```

---

## 🧪 Manual Testing Steps

Jika script test_duitku.php berhasil tapi aplikasi masih error:

### 1. Buka Dashboard

```
http://localhost:8000/dashboard
```

### 2. Pilih 1 bulan untuk dibayar

### 3. Klik "Bayar Online" (GREEN button)

### 4. Buka Developer Console (F12 → Network tab)

### 5. Cari request ke API

```
POST https://api-sandbox.duitku.com/api/merchant/createInvoice
```

### 6. Cek response:

- **Status 200** → ✅ Berhasil, ambil `paymentUrl`
- **Status 401** → ❌ Unauthorized, check credentials
- **Status 400** → ❌ Bad Request, check parameters
- **Status 422** → ❌ Invalid Signature

### 7. Buka Laravel logs

```bash
tail -f storage/logs/laravel.log
```

---

## 🔐 Signature Generator (Online)

Untuk verify signature manual:

1. Buka: https://www.md5online.org/
2. Paste: `DS290691KAS-TEST-123650009347e1c55b2b3fdbe1a01e10ea63c0ea`
3. Click "Generate"
4. Output harus match dengan signature di log

---

## 📋 Checklist Sebelum Deploy ke Production

- [ ] .env credentials benar
- [ ] Config cache cleared
- [ ] Server restarted
- [ ] test_duitku.php berhasil 100%
- [ ] Manual test di aplikasi berhasil
- [ ] Logs menunjukkan success message
- [ ] Phone number format: 62812345678
- [ ] Email format valid
- [ ] Amount harus integer
- [ ] returnUrl & callbackUrl valid URLs

---

## 📞 Jika Masih Error

### Gather Information:

1. Copy full error message
2. Copy logs dari storage/logs/laravel.log
3. Copy request/response dari browser console (F12)
4. Run `test_duitku.php` dan copy hasilnya
5. Cek `php artisan tinker` output untuk credentials

### Kemudian Debug Poin Mana yang Gagal:

- [ ] .env credentials?
- [ ] Config loading?
- [ ] Signature generation?
- [ ] API endpoint?
- [ ] Network connection?

---

## 🚀 Success Indicators

Jika sudah OK, harusnya:

1. ✅ Form pembayaran bisa disubmit
2. ✅ Redirect ke halaman Duitku (URL berisi duitku.com)
3. ✅ Logs menunjukkan "Success" message
4. ✅ Database status berubah ke `proses_online`
5. ✅ Order ID tersimpan di database

---

**Last Updated:** March 24, 2026  
**Quick Test:** `php test_duitku.php`
