<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Daftar Kepala Keluarga / Perwakilan
        $daftarKk = [
            'Adrian Adiputra', // Admin
            'Heri',
            'Yadi',
            'Citra',
            'Ria',
            'Siti',
            'Sutris',
            'Wulan',
            'Lintang',
            'Lek Sri',
            'Sari',
            'Hadi',
            'Ratih'
        ];

        foreach ($daftarKk as $index => $nama) {
            // Tentukan role: index 0 (Adrian) jadi admin, sisanya warga
            $role = ($index === 0) ? 'admin' : 'warga';

            // Generate email dummy dari nama
            $email = strtolower(str_replace(' ', '', $nama)) . '@gmail.com';

            // 1. Buat Data User (KK)
            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'role' => $role,
                'password' => Hash::make('password123'), // Password default sebelum login Google jalan
                'no_wa' => '08123456789' . $index,
            ]);

            // 2. Langsung buatkan tagihan 14 bulan untuk tiap KK yang baru dibuat
            for ($bulan = 1; $bulan <= 14; $bulan++) {
                Pembayaran::create([
                    'user_id' => $user->id,
                    'bulan_ke' => $bulan,
                    'nominal' => 65000,
                    'status' => 'belum',
                ]);
            }
        }
    }
}
