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
        // Daftar 13 Kepala Keluarga / Perwakilan
        $daftarKk = [
            'Adrian Adiputra', // Data pertama kita jadikan Admin
            'Dhani',
            'Apip',
            'Reni',
            'Sopi',
            'Antonius Iwayan',
            'Aziz Khasyi',
            'Juan Farrel',
            'Rezezi Axcel',
            'Keluarga Pak Budi',
            'Keluarga Pak Andi',
            'Keluarga Bu Siti',
            'Keluarga Pak Joko'
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

            // 2. Langsung buatkan tagihan 13 bulan untuk tiap KK yang baru dibuat
            for ($bulan = 1; $bulan <= 13; $bulan++) {
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
