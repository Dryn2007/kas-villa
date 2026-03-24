<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pembayaran;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Data global untuk Progress Bar di Header
        $totalTerkumpul = Pembayaran::where('status', 'lunas')->sum('nominal');
        $targetDana = 10985000;
        $persentase = ($totalTerkumpul / $targetDana) * 100;

        // KONDISI 1: Jika salah satu kotak KK diklik (URL: ?kk_id=...)
        if ($request->has('kk_id')) {
            $selectedKk = User::findOrFail($request->kk_id);
            $tagihanKk = Pembayaran::where('user_id', $selectedKk->id)
                ->orderBy('bulan_ke', 'asc')
                ->get();

            return view('dashboard', compact('selectedKk', 'tagihanKk', 'totalTerkumpul', 'targetDana', 'persentase'));
        }

        // KONDISI 2: Halaman Utama (Grid, Leaderboard, History)

        // Ambil semua KK untuk Grid
        $semuaKk = User::all();

        // Hitung Leaderboard: 3 KK dengan jumlah bulan "Lunas" terbanyak
        $leaderboard = User::withCount(['pembayarans as lunas_count' => function ($query) {
            $query->where('status', 'lunas');
        }])
            ->orderByDesc('lunas_count')
            ->take(3)
            ->get();

        // Ambil History: 5 pembayaran terakhir yang lunas DI BULAN INI
        $historyTerbaru = Pembayaran::with('user')
            ->where('status', 'lunas')
            ->whereMonth('updated_at', date('m'))
            ->whereYear('updated_at', date('Y'))
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact('semuaKk', 'leaderboard', 'historyTerbaru', 'totalTerkumpul', 'targetDana', 'persentase'));
    }

    // Fungsi untuk Bayar Sekaligus (Duitku & Titip Admin)
    public function dummyPayBulk(Request $request)
    {
        $ids = $request->input('tagihan_ids', []);
        $metode = $request->input('metode', 'online'); // Menangkap metode dari tombol

        if (empty($ids)) {
            return back()->with('error', 'Pilih minimal satu bulan untuk dibayar.');
        }

        $firstTagihan = Pembayaran::findOrFail($ids[0]);
        $userId = $firstTagihan->user_id;

        // Ambil SEMUA tagihan yang murni belum dibayar (abaikan yang lunas & yang sedang proses)
        $allUnpaid = Pembayaran::where('user_id', $userId)
            ->whereNotIn('status', ['lunas', 'proses'])
            ->orderBy('bulan_ke', 'asc')
            ->get();

        $jumlahBulanYgMauDibayar = count($ids);
        // Ambil ID yang SEHARUSNYA dibayar (harus berurutan dari yang paling awal)
        $idYangSah = $allUnpaid->take($jumlahBulanYgMauDibayar)->pluck('id')->toArray();

        $submittedIds = array_map('intval', $ids);
        sort($submittedIds);
        sort($idYangSah);

        // Validasi: Apakah ID yang dikirim sesuai dengan urutan dari bulan paling awal?
        // Relax validasi untuk pembayaran online (boleh 1 bulan), tapi tetap enforce sequential untuk tunai
        if ($metode === 'tunai' && $submittedIds !== $idYangSah) {
            return back()->with('error', 'Pembayaran tunai harus berurutan dari bulan tertua! Jangan nge-cheat ya 😉');
        }

        // Untuk online, validasi hanya untuk 2+ bulan
        if ($metode === 'online' && count($submittedIds) >= 2 && $submittedIds !== $idYangSah) {
            return back()->with('error', 'Pembayaran online untuk 2+ bulan harus berurutan dari bulan tertua!');
        }

        // Tentukan status dan pesan berdasarkan tombol yang diklik
        $statusBaru = $metode === 'tunai' ? 'proses' : 'lunas';
        $pesan = $metode === 'tunai'
            ? 'Sip! ' . count($submittedIds) . ' bulan tagihan sedang menunggu konfirmasi Admin. ⏳'
            : 'Hore! ' . count($submittedIds) . ' bulan tagihan berhasil dilunasi via Online! 🎉';

        // Update ke database
        Pembayaran::whereIn('id', $submittedIds)->update([
            'status' => $statusBaru,
            'updated_at' => now()
        ]);

        return back()->with('success', $pesan);
    }

    // Fungsi untuk Halaman Riwayat Semua Pembayaran
    public function history()
    {
        $semuaHistory = Pembayaran::with('user')
            ->where('status', 'lunas')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('history', compact('semuaHistory'));
    }
}
