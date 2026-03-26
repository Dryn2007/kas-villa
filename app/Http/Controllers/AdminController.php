<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            ->get()
            ->groupBy(function ($item) {
                // Kelompokkan per user dan per submit barengan (via updated_at)
                return $item->user_id . '_' . $item->updated_at->format('Y-m-d H:i:s');
            });

        // Ambil seluruh warga beserta data pembayarannya untuk fitur Kelola KK dan Pelunasan Manual
        $users = User::with('pembayarans')->where('role', 'warga')->get();

        return view('admin', compact('pendingPayments', 'users'));
    }

    // 2. Fungsi untuk Menyetujui (ACC)
    public function approve($ids)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $idArray = explode(',', $ids);
        $pembayarans = Pembayaran::with('user')->whereIn('id', $idArray)->get();
        if ($pembayarans->isEmpty()) return back();

        Pembayaran::whereIn('id', $idArray)->update(['status' => 'lunas', 'updated_at' => now()]);

        $user = $pembayarans->first()->user;
        $namaUser = $user ? $user->name : '';

        return back()->with('success', 'Tagihan ' . $namaUser . ' (' . count($idArray) . ' bulan) berhasil di-ACC! Uang sudah masuk kas. ✅');
    }

    // 3. Fungsi untuk Menolak (Reject)
    public function reject($ids)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $idArray = explode(',', $ids);
        $pembayarans = Pembayaran::with('user')->whereIn('id', $idArray)->get();
        if ($pembayarans->isEmpty()) return back();

        // Ubah statusnya jadi 'belum' agar kembali jadi kartu merah di dashboard warga
        Pembayaran::whereIn('id', $idArray)->update(['status' => 'belum', 'updated_at' => now()]);

        $user = $pembayarans->first()->user;
        $namaUser = $user ? $user->name : '';

        return back()->with('error', 'Tagihan ' . $namaUser . ' (' . count($idArray) . ' bulan) dikembalikan/ditolak. ❌');
    }

    // 4. Fungsi Menambah Kepala Keluarga Baru
    public function addKk(Request $request)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('password123'), // password acak aja, karena pakai google SSO
            'role' => 'warga',
        ]);

        // Buatkan juga langsung 14 bulan tagihannya status: 'belum'
        for ($i = 1; $i <= 14; $i++) {
            Pembayaran::create([
                'user_id' => $user->id,
                'bulan_ke' => $i,
                'jumlah' => 65000,
                'status' => 'belum',
            ]);
        }

        return back()->with('success', 'Keluarga baru (' . $user->name . ') berhasil ditambahkan! Total iuran siap ditagih.');
    }

    // 5. Fungsi Menghapus Kepala Keluarga
    public function deleteKk($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $user = User::findOrFail($id);

        // Hapus juga secara otomatis seluruh riwayat tagihan dan laporannya (bisa pakai softdelete, tp ini langsung delete aja)
        $user->pembayarans()->delete();

        $name = $user->name;
        $user->delete();

        return back()->with('success', 'Keluarga (' . $name . ') beserta riwayat iurannya berhasil dihapus.');
    }

    // 6. Fungsi Pelunasan Manual (Bypass tanpa rikues warga)
    public function manualPay($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $pembayaran = Pembayaran::findOrFail($id);

        // Langsung hajar jadi lunas
        $pembayaran->update([
            'status' => 'lunas',
            'updated_at' => now()
        ]);

        return back()->with('success', 'Tagihan ' . $pembayaran->user->name . ' bulan ke-' . $pembayaran->bulan_ke . ' BERHASIL dilunasi secara manual oleh Admin. ✅');
    }
}
