<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // 1. Menampilkan Halaman Panel Admin
    public function index()
    {
        // Keamanan: Tendang keluar kalau yang akses bukan Admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Ups! Hanya Admin yang boleh masuk ke ruangan ini 🛑');
        }

        // Ambil semua pembayaran yang statusnya 'proses' (Titip Tunai)
        $pendingPayments = Pembayaran::with('user')
            ->where('status', 'proses')
            ->orderBy('updated_at', 'asc')
            ->get();

        return view('admin', compact('pendingPayments'));
    }

    // 2. Fungsi untuk Menyetujui (ACC)
    public function approve($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->update(['status' => 'lunas', 'updated_at' => now()]);

        return back()->with('success', 'Tagihan ' . $pembayaran->user->name . ' bulan ke-' . $pembayaran->bulan_ke . ' berhasil di-ACC! Uang sudah masuk kas. ✅');
    }

    // 3. Fungsi untuk Menolak (Reject)
    public function reject($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $pembayaran = Pembayaran::findOrFail($id);
        // Ubah statusnya jadi 'belum' agar kembali jadi kartu merah di dashboard warga
        $pembayaran->update(['status' => 'belum', 'updated_at' => now()]);

        return back()->with('error', 'Tagihan ' . $pembayaran->user->name . ' bulan ke-' . $pembayaran->bulan_ke . ' dikembalikan/ditolak. ❌');
    }
}
