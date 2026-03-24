# ✅ Duitku Unauthorized - SOLVED!

## 🎯 Summary

Error "Unauthorized" dari Duitku telah **DIDIAGNOSIS**. Masalahnya jelas:

**Merchant Code & Key di .env TIDAK VALID untuk Duitku Sandbox API**

```
Current (INVALID):
DUITKU_MERCHANT_CODE=DS29069
DUITKU_MERCHANT_KEY=9347e1c55b2b3fdbe1a01e10ea63c0ea

Result: HTTP 401 Unauthorized ❌
```

---

## 🔧 FIX (3 Steps)

### **Step 1: Get Valid Credentials**

1. Login ke Duitku Sandbox: https://sandbox.duitku.com/
2. Buka: **Settings** → **API Keys** (atau Developer)
3. Copy nilai **Merchant Code** dan **Merchant Key** yang VALID
4. Jangan copas dari yang sudah ada! Get langsung dari dashboard!

### **Step 2: Update .env**

```bash
# Edit file .env
# Ganti:
DUITKU_MERCHANT_CODE=YOUR_REAL_MERCHANT_CODE_FROM_DASHBOARD
DUITKU_MERCHANT_KEY=YOUR_REAL_MERCHANT_KEY_FROM_DASHBOARD
DUITKU_ENV=sandbox
```

✅ Pastikan:

- Tidak ada spasi di awal/akhir nilai
- Tidak ada tanda kutip
- Nilai berasal dari Duitku dashboard Anda sendiri

### **Step 3: Clear Cache & Test**

```bash
# Clear Laravel cache
php artisan config:clear
php artisan cache:clear

# Restart server
php artisan serve --port=8000

# Test credentials
php test_duitku.php

# Should output: HTTP Status: 200 ✅ SUCCESS
```

---

## 📚 Documentation Created

Saya sudah buatkan 4 file dokumentasi untuk membantu:

### 1. **DUITKU_FIX_CREDENTIALS.md**

- Penjelasan lengkap masalahnya
- Step-by-step cara fix credentials
- Verification steps

### 2. **DUITKU_TROUBLESHOOTING.md**

- Complete step-by-step debugging guide
- Common issues & quick fixes
- Manual testing procedures

### 3. **DUITKU_DEBUG_UNAUTHORIZED.md**

- Error reference guide
- Security implementation details
- Testing checklist

### 4. **test_duitku.php** (Test Script)

- Automatic test untuk Duitku API
- Check credentials, generate signature, test endpoint
- Useful untuk verify setelah fix

---

## 🧪 Quick Test Command

Setelah update .env dan clear cache:

```bash
php test_duitku.php
```

**Expected Output:**

```
HTTP Status: 200 ✅ SUCCESS
Payment URL generated: https://checkout.duitku.com/...
```

---

## ⚠️ Important Notes

1. **Credentials di .env sekarang INVALID**
    - Test script menunjukkan HTTP 401
    - Berarti `DS29069` tidak terdaftar di Duitku

2. **Harus punya akun Duitku**
    - Kunjungi: https://sandbox.duitku.com/
    - Jika belum ada akun, sign up baru
    - Get credentials dari dashboard Anda sendiri

3. **Jangan share credentials**
    - Merchant Code & Key adalah SECRET
    - Jangan commit .env ke git!
    - Gunakan .env.example untuk dokumentasi

---

## 📋 Checklist Sebelum Lanjut

- [ ] Sudah login ke https://sandbox.duitku.com/ dengan akun Anda
- [ ] Sudah dapat Merchant Code dari dashboard
- [ ] Sudah dapat Merchant Key dari dashboard
- [ ] Sudah update .env dengan nilai yang benar
- [ ] Sudah run: `php artisan config:clear && php artisan cache:clear`
- [ ] Sudah restart server Laravel
- [ ] Sudah run: `php test_duitku.php` dan hasilnya HTTP 200

---

## 🚀 Next Steps After Fix

1. **Verify Test Passed**

    ```bash
    php test_duitku.php
    # Output harus: HTTP Status: 200
    ```

2. **Test di Aplikasi**
    - Buka: http://localhost:8000/dashboard
    - Pilih 1 bulan untuk dibayar
    - Klik "Bayar Online" (GREEN button)
    - Harus redirect ke halaman Duitku

3. **Check Logs**

    ```bash
    tail -f storage/logs/laravel.log
    # Cari: "Duitku CreateInvoice Response"
    # Harus ada: "successful" => true
    ```

4. **Verify Database**
    ```bash
    php artisan tinker
    >>> Pembayaran::where('status', 'proses_online')->get()
    # Harus menunjukkan order yang baru dibuat
    ```

---

## 🎯 Success Indicators

Jika sudah fixed:

- ✅ `php test_duitku.php` return HTTP 200
- ✅ Aplikasi redirect ke Duitku payment page
- ✅ Order status berubah ke `proses_online`
- ✅ Logs menunjukkan success message
- ✅ Can complete payment di Duitku

---

## 📞 Still Having Issues?

### Verify Credentials Loaded Correctly

```bash
php artisan tinker

>>> env('DUITKU_MERCHANT_CODE')
=> "YOUR_NEW_CODE_HERE"

>>> env('DUITKU_MERCHANT_KEY')
=> "YOUR_NEW_KEY_HERE"

>>> exit
```

### Check for Typos

- Login ke Duitku dashboard lagi
- Copy-paste exactly (no extra spaces)
- Verify dalam .env file

### Last Resort

- Delete .env cache: `rm bootstrap/cache/config.php` (if exists)
- Clear everything: `php artisan cache:clear && php artisan config:clear && php artisan view:clear`
- Restart server completely
- Test again

---

## 💡 Quick Reference

| Step        | Command                                                 | Expected Result    |
| ----------- | ------------------------------------------------------- | ------------------ |
| Clear Cache | `php artisan config:clear`                              | ✅ Command success |
| Clear Cache | `php artisan cache:clear`                               | ✅ Command success |
| Test API    | `php test_duitku.php`                                   | HTTP Status: 200   |
| Verify Env  | `php artisan tinker` then `env('DUITKU_MERCHANT_CODE')` | Shows your code    |
| Test App    | Open dashboard, click "Bayar Online"                    | Redirect to Duitku |

---

## 📖 Read These Files

1. Start with: **DUITKU_FIX_CREDENTIALS.md**
    - Penjelasan detail masalahnya dan cara fix

2. If still stuck: **DUITKU_TROUBLESHOOTING.md**
    - Step-by-step troubleshooting guide

3. For reference: **DUITKU_DEBUG_UNAUTHORIZED.md**
    - All possible error causes & solutions

---

## 🎉 Done!

Once credentials are updated and test passes:

- Integration is ready! 🚀
- Pembayaran online siap digunakan
- Callback handling sudah siap

**Good luck! You got this! 💪**

---

**Status:** ✅ Diagnosed & Solution Provided  
**Next Action:** Update .env with valid credentials  
**Test:** `php test_duitku.php`  
**Last Updated:** March 24, 2026
