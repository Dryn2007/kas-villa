# 🚀 Setup Cloudinary untuk Kas Villa

## Langkah-langkah Setup Cloudinary

### 1. Buat Akun Cloudinary

- Kunjungi https://cloudinary.com/
- Daftar akun gratis
- Verifikasi email Anda

### 2. Dapatkan API Credentials

Di Dashboard Cloudinary Anda, temukan:

- **Cloud Name** - Nama unik untuk cloud Anda
- **API Key** - Kunci API publik
- **API Secret** - Kunci API rahasia (JANGAN SHARE!)

### 3. Update .env File

Buka file `.env` di root project dan cari bagian CLOUDINARY:

```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

Ganti dengan nilai actual Anda dari Cloudinary Dashboard.

### 4. Testing Upload

Akses halaman Profile di aplikasi (`/profile`) dan coba upload foto.

## 📁 Folder Structure di Cloudinary

Berdasarkan konfigurasi, file akan tersimpan di:

- **Profile Pictures**: `kas-villa/profiles/`
- **Documents**: `kas-villa/documents/`
- **General**: `kas-villa/`

## 🔐 Security Best Practices

1. **JANGAN** commit `.env` file ke Git
2. **JANGAN** share `API_SECRET` di public
3. Gunakan environment variables untuk production
4. Rotate API keys secara berkala

## 📤 Contoh Upload di Controller

```php
use App\Services\CloudinaryService;

// Inject service
public function __construct(CloudinaryService $cloudinaryService)
{
    $this->cloudinaryService = $cloudinaryService;
}

// Upload Profile Image
$result = $this->cloudinaryService->uploadProfileImage($request->file('avatar'));
if ($result['success']) {
    $user->avatar = $result['url'];
    $user->save();
}

// Upload File Generic
$result = $this->cloudinaryService->upload($request->file('document'), 'kas-villa/documents');
```

## 🎨 Transformation Options

CloudinaryService sudah include beberapa transformasi:

```php
// Profile Picture - Auto crop ke wajah
uploadProfileImage() // width: 200, height: 200, gravity: face

// Custom URL transformation
getUrl('public_id', ['width' => 400, 'height' => 300, 'crop' => 'fill'])
```

## 🗑️ Delete File

```php
$this->cloudinaryService->delete('public_id');
```

## ❓ Troubleshooting

### Upload gagal dengan error "Undefined property"

- Pastikan `.env` file sudah diupdate dengan credentials yang benar
- Jalankan `php artisan config:cache`

### API Credentials tidak terbaca

- Jalankan `php artisan config:clear`
- Restart application
- Verify `.env` file dengan format yang benar

### File tidak terupload

- Cek ukuran file (Max 5MB untuk profile, 10MB untuk documents)
- Cek format file (jpeg, png, jpg, gif untuk image)
- Cek permission folder `storage`

## 📞 Docs Referensi

- Cloudinary PHP SDK: https://cloudinary.com/documentation/php_integration
- API Reference: https://cloudinary.com/documentation/image_upload_api_reference

---

**Selamat! Cloudinary sudah terintegrasi dengan Kas Villa! ☁️**
