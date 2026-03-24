# 🔴 Unauthorized 401 - Merchant Code/Key Invalid

## 📋 Diagnosis

**Test Script Result:**

```
HTTP Status: 401
Status: UNAUTHORIZED
Response Body: "Unauthorized"
```

**Artinya:** Merchant Code (`DS29069`) atau Merchant Key (`9347e1c55b2b3fdbe1a01e10ea63c0ea`) tidak valid untuk Duitku Sandbox API.

---

## ✅ Solusi

### **Option 1: Get Real Credentials dari Duitku (Recommended)**

1. **Login ke Duitku Sandbox**

    ```
    https://sandbox.duitku.com/
    ```

2. **Navigate ke API Settings**
    - Dashboard → API Key / Merchant Settings
    - Atau cari di bagian Developer/Integration

3. **Copy Merchant Code & Merchant Key**
    - Merchant Code: `DS??????` (format berbeda-beda)
    - Merchant Key: `a1b2c3d4e5f6...` (panjang string)

4. **Update .env**

    ```properties
    DUITKU_MERCHANT_CODE=DS??????? (ganti dengan yang di dashboard)
    DUITKU_MERCHANT_KEY=a1b2c3d4e5f6... (ganti dengan yang di dashboard)
    DUITKU_ENV=sandbox
    ```

5. **Clear Cache & Test**
    ```bash
    php artisan config:clear
    php artisan cache:clear
    php test_duitku.php
    ```

---

### **Option 2: Verify Current Credentials**

Jika sudah punya akun Duitku, verify:

1. **Login ke Duitku Dashboard**

    ```
    https://sandbox.duitku.com/
    ```

2. **Check Settings**
    - Cek apakah Merchant Code = `DS29069`
    - Cek apakah Merchant Key = `9347e1c55b2b3fdbe1a01e10ea63c0ea`

3. **Jika tidak sama → Update .env dengan nilai dari dashboard**

4. **Jika tidak ada akun → Create new account (lihat Option 3)**

---

### **Option 3: Create New Sandbox Account**

Jika tidak punya akun Duitku:

1. **Buka Duitku Sandbox**

    ```
    https://sandbox.duitku.com/
    ```

2. **Click "Daftar" atau "Sign Up"**

3. **Isi Form:**
    - Email
    - Password
    - Company Name
    - Phone

4. **Verify Email**

5. **Login & Get API Credentials**
    - Buka Settings → API Keys
    - Copy Merchant Code & Key

6. **Update .env di project Anda**

7. **Test again:**
    ```bash
    php artisan config:clear
    php test_duitku.php
    ```

---

## 📝 What Credentials Are These?

```
DUITKU_MERCHANT_CODE=DS29069
DUITKU_MERCHANT_KEY=9347e1c55b2b3fdbe1a01e10ea63c0ea
```

Ini terlihat seperti **test/dummy credentials** yang:

- ❌ Mungkin tidak valid untuk Duitku Sandbox API
- ❌ Mungkin sudah expired
- ❌ Mungkin milik akun yang sudah dihapus

---

## 🔧 Quick Steps

### 1. **Identify the Issue**

```bash
# Test current credentials
php test_duitku.php

# If HTTP Status: 401 → Merchant credentials invalid
```

### 2. **Get Valid Credentials**

- Login ke https://sandbox.duitku.com/
- Navigate ke API Settings
- Copy actual Merchant Code & Key

### 3. **Update .env**

```properties
DUITKU_MERCHANT_CODE=YOUR_ACTUAL_CODE_HERE
DUITKU_MERCHANT_KEY=YOUR_ACTUAL_KEY_HERE
```

### 4. **Clear Cache**

```bash
php artisan config:clear
php artisan cache:clear
```

### 5. **Test Again**

```bash
php test_duitku.php

# Should return HTTP Status: 200 now
```

---

## 🆘 Jika Masih Error

### Check 1: Pastikan Update .env

```bash
# Verify .env sudah diupdate
cat .env | grep DUITKU
```

Output harus menunjukkan credentials baru Anda.

### Check 2: Clear All Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:cache
```

### Check 3: Restart Laravel Server

```bash
# Stop server (Ctrl+C)
# Start lagi
php artisan serve --port=8000
```

### Check 4: Verify Credentials Loaded

```bash
php artisan tinker
>>> env('DUITKU_MERCHANT_CODE')
=> "YOUR_CODE_HERE" (harus menunjukkan nilai baru)
>>> exit
```

### Check 5: Test API Again

```bash
php test_duitku.php
# Harus return HTTP Status: 200 sekarang
```

---

## 📞 Still Getting 401?

### Possible Causes:

1. ❌ Merchant Code belum diupdate di .env
2. ❌ Cache belum di-clear setelah update .env
3. ❌ Server belum di-restart
4. ❌ Credentials masih salah
5. ❌ Duitku account suspended/disabled

### Debug Steps:

1. Double check .env file directly:

    ```bash
    # Use text editor, bukan terminal
    # File: .env
    # Cari line: DUITKU_MERCHANT_CODE
    # Pastikan value benar tanpa spasi
    ```

2. Verify di Duitku Dashboard:

    ```
    Login ke https://sandbox.duitku.com/
    Navigate ke Settings → API
    Copy value exactly (termasuk spasi jika ada)
    ```

3. Test signature generation:
    ```bash
    php artisan tinker
    >>> $code = 'DS...' // paste merchant code Anda
    >>> $key = '...'    // paste merchant key Anda
    >>> md5($code . 'TEST' . 123 . $key)
    => "a1b2c3d4..."
    ```

---

## 🎯 Expected Result After Fix

```bash
$ php test_duitku.php

╔═══════════════════════════════════════════════════════════════╗
║          Duitku Integration Test Script                      ║
╚═══════════════════════════════════════════════════════════════╝

📋 CONFIGURATION CHECK:
───────────────────────────────────────────────────────────────
Merchant Code: ✅ DS*******
Merchant Key: ✅ a1b2c3****
Environment: ✅ SANDBOX

📝 TEST DATA:
───────────────────────────────────────────────────────────────
Order ID: KAS-TEST-1234567890
Amount: Rp 65.000
Signature: abc123def456...

🔐 SIGNATURE VERIFICATION:
───────────────────────────────────────────────────────────────
Input: DS...TEST...65000...key
Output (MD5): abc123def456...
✅ Signature generated successfully

🌐 TESTING API ENDPOINT:
───────────────────────────────────────────────────────────────
URL: https://api-sandbox.duitku.com/api/merchant/createInvoice

📤 Sending request...
📥 Response received!

HTTP Status: 200  ✅ SUCCESS (not 401!)
✅ Status: SUCCESS
Response Body:
───────────────────────────────────────────────────────────────
{"statusCode":"00","statusMessage":"Success","paymentUrl":"https://..."}

✅ SUCCESS! Payment URL generated:
https://checkout.duitku.com/...
```

---

## 📚 Additional Resources

- **Duitku Sandbox:** https://sandbox.duitku.com/
- **Duitku API Docs:** Available dalam merchant dashboard
- **Verify Credentials:** Login → Settings → API Keys

---

**Current Status:** ❌ Credentials Invalid (401 Unauthorized)  
**Next Step:** Get valid credentials from Duitku Sandbox  
**Action:** Update .env + Clear Cache + Test again

**Last Updated:** March 24, 2026
