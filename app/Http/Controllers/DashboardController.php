<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pembayaran;
use App\Models\User;
use App\Services\CloudinaryService;

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

    // Fungsi untuk Bayar Sekaligus
    public function dummyPayBulk(Request $request, CloudinaryService $cloudinaryService)
    {
        $ids = $request->input('tagihan_ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Pilih minimal satu bulan untuk dibayar.');
        }

        $request->validate([
            'bukti_pembayaran' => 'required|image|max:5120'
        ]);

        try {
            $firstTagihan = Pembayaran::findOrFail($ids[0]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Data tagihan tidak ditemukan.');
        }

        $userId = $firstTagihan->user_id;

        // Ambil SEMUA tagihan yang murni belum dibayar (abaikan yang lunas & yang sedang proses)
        $allUnpaid = Pembayaran::where('user_id', $userId)
            ->whereNotIn('status', ['lunas', 'proses', 'proses_online'])
            ->orderBy('bulan_ke', 'asc')
            ->get();

        $jumlahBulanYgMauDibayar = count($ids);
        $idYangSah = $allUnpaid->take($jumlahBulanYgMauDibayar)->pluck('id')->toArray();

        $submittedIds = array_map('intval', $ids);
        sort($submittedIds);
        sort($idYangSah);

        if ($submittedIds !== $idYangSah) {
            return back()->with('error', 'Pembayaran harus berurutan! Jangan nge-cheat ya 😉');
        }

        // --- BUAT PENAMAAN FILE KHUSUS ---
        // Format: Nama keluarga_Detik-Menit-Jam-tanggal-bulan-Tahun_Bayar bulan apa/range bulan
        
        $namaKeluarga = $firstTagihan->user->name;
        // Sanitasi nama keluarga (hapus karakter yang dilarang di storage)
        $namaKeluargaSafe = preg_replace('/[^A-Za-z0-9\s]/', '', $namaKeluarga);
        
        // Detik-Menit-Jam-tanggal-bulan-Tahun (s-i-H-d-m-Y)
        $waktu = now()->format('s-i-H-d-m-Y');
        
        // Range bulan
        $bulans = Pembayaran::whereIn('id', $submittedIds)->orderBy('bulan_ke', 'asc')->pluck('bulan_ke')->toArray();
        if (count($bulans) == 1) {
            $rangeBulan = 'Bulan ' . $bulans[0];
        } else {
            $rangeBulan = 'Bulan ' . min($bulans) . '-' . max($bulans);
        }

        $namaFileCustom = "{$namaKeluargaSafe}_{$waktu}_{$rangeBulan}";
        // ---------------------------------

        // Upload bukti pembayaran (Sementara ini disetting namanya dan masuk ke Cloudinary)
        // Jika Google Drive diaktifkan nanti, nama file ini sangat siap digunakan
        $uploadResult = $cloudinaryService->upload($request->file('bukti_pembayaran'), 'kas-villa/bukti-transfer', 'auto', $namaFileCustom);
        
        if (!$uploadResult['success']) {
            return back()->with('error', 'Gagal mengupload bukti pembayaran: ' . $uploadResult['message']);
        }

        $buktiUrl = $uploadResult['url'];

        $statusBaru = 'proses';
        $pesan = 'Sip! ' . count($submittedIds) . ' bulan tagihan beserta bukti pembayaran berhasil dikirim. Menunggu konfirmasi Admin. ⏳';

        try {
            // Update ke database
            Pembayaran::whereIn('id', $submittedIds)->update([
                'status' => $statusBaru,
                'bukti_pembayaran' => $buktiUrl,
                'updated_at' => now()
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat proses: ' . $e->getMessage());
        }

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
