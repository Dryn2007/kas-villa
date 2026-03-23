<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Exception;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // 1. Cek apakah Google ID ini sudah pernah login
            $user = User::where('google_id', $googleUser->id)->first();
            if ($user) {
                Auth::login($user);
                return redirect()->intended('/dashboard');
            }

            // 2. Cek apakah emailnya cocok dengan email dummy dari Seeder
            $userByEmail = User::where('email', $googleUser->email)->first();
            if ($userByEmail && !$userByEmail->google_id) {
                $userByEmail->update(['google_id' => $googleUser->id]);
                Auth::login($userByEmail);
                return redirect()->intended('/dashboard');
            }

            // 3. JIKA AKUN BARU: Simpan data sementara, arahkan ke halaman Pilih KK
            session([
                'temp_google_id' => $googleUser->id,
                'temp_google_email' => $googleUser->email,
                'temp_google_avatar' => $googleUser->avatar,
            ]);

            return redirect()->route('pilih-kk');
        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Gagal login menggunakan Google.');
        }
    }

    // Fungsi untuk menampilkan halaman pilihan KK
    public function showPilihKk()
    {
        // Tolak jika diakses langsung tanpa login Google dulu
        if (!session('temp_google_id')) {
            return redirect('/login');
        }

        $semuaKk = User::all();
        return view('auth.pilih-kk', compact('semuaKk'));
    }

    // Fungsi untuk memproses pilihan KK
    public function claimKk(Request $request)
    {
        if (!session('temp_google_id')) {
            return redirect('/login');
        }

        $request->validate(['kk_id' => 'required|exists:users,id']);

        $user = User::findOrFail($request->kk_id);

        // Keamanan ekstra: Pastikan belum diklaim
        if ($user->google_id != null) {
            return back()->with('error', 'Wah, KK ini sudah terdaftar oleh orang lain! Coba pilih yang lain.');
        }

        // ==========================================
        // CARA BARU: ANTI GAGAL & BYPASS FILLABLE
        // ==========================================
        $user->google_id = session('temp_google_id');
        $user->email = session('temp_google_email');
        $user->avatar = session('temp_google_avatar');
        $user->save(); // Simpan paksa ke database

        // Hapus session sementara, lalu login-kan!
        session()->forget(['temp_google_id', 'temp_google_email']);
        Auth::login($user);

        return redirect()->intended('/dashboard');
    }
}
