<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
        // 🚨 TANGKAP SEMUA ERROR FATAL BIAR GAK JADI LAYAR HITAM 500
        try {
            $ids = $request->input('tagihan_ids', []);

            if (empty($ids)) {
                return back()->with('error', 'Pilih minimal satu bulan untuk dibayar.');
            }

            $request->validate([
                'bukti_pembayaran' => 'required|image|max:5120'
            ]);

            $firstTagihan = Pembayaran::findOrFail($ids[0]);
            $userId = $firstTagihan->user_id;

            // Ambil SEMUA tagihan yang murni belum dibayar
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

            // --- Custom Filename Logic ---
            $user = Auth::user();

            // Format Nama Keluarga (Sanitasi karakter aneh jadi underscore)
            $namaKeluargaSafe = preg_replace('/[^A-Za-z0-9_-]/', '_', $user->name);
            $waktu = now()->format('s-i-H-d-m-Y');

            $paidPayments = Pembayaran::whereIn('id', $submittedIds)->orderBy('bulan_ke')->get();

            if ($paidPayments->isEmpty()) {
                $rangeBulan = 'Unknown';
            } else {
                $firstMonth = $paidPayments->first();
                $lastMonth = $paidPayments->last();

                $startMonthName = \Carbon\Carbon::create(2026, 3)->addMonths($firstMonth->bulan_ke)->translatedFormat('F');
                $endMonthName = \Carbon\Carbon::create(2026, 3)->addMonths($lastMonth->bulan_ke)->translatedFormat('F');

                if ($paidPayments->count() == 1) {
                    $rangeBulan = $startMonthName;
                } else {
                    $rangeBulan = $startMonthName . '-' . $endMonthName;
                }
            }

            $finalFilename = "{$namaKeluargaSafe}_{$waktu}_{$rangeBulan}";

            // Upload bukti pembayaran ke Cloudinary
            $uploadResult = $cloudinaryService->upload(
                $request->file('bukti_pembayaran'),
                'kas-villa/bukti-transfer',
                'auto',
                $finalFilename
            );

            if (!$uploadResult['success']) {
                return back()->with('error', 'Gagal mengupload bukti ke Cloudinary: ' . $uploadResult['message']);
            }

            $buktiUrl = $uploadResult['url'];

            // --- Backup ke Google Drive (VERSI DIET 0 MB - BEBAS VERCEL 250MB LIMIT) ---
            try {
                $file = $request->file('bukti_pembayaran');
                $extension = $file->getClientOriginalExtension();
                $gdFilename = $finalFilename . '.' . $extension;
                $mimeType = $file->getMimeType();
                $fileContent = file_get_contents($file->getRealPath());

                // 1. Minta "Surat Izin Masuk" (Access Token) ke Google
                $tokenResponse = \Illuminate\Support\Facades\Http::post('https://oauth2.googleapis.com/token', [
                    'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
                    'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
                    'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
                    'grant_type' => 'refresh_token',
                ]);

                if ($tokenResponse->successful()) {
                    $accessToken = $tokenResponse->json('access_token');
                    $folderId = env('GOOGLE_DRIVE_FOLDER_ID');

                    // 2. Bikin "Cangkang/Wadah" file kosong di Folder Drive Kas Villa
                    $metaResponse = \Illuminate\Support\Facades\Http::withToken($accessToken)
                        ->post('https://www.googleapis.com/drive/v3/files', [
                            'name' => $gdFilename,
                            'parents' => [$folderId]
                        ]);

                    if ($metaResponse->successful()) {
                        $fileId = $metaResponse->json('id');

                        // 3. Suntikkan foto aslinya ke dalam "Wadah" tadi
                        \Illuminate\Support\Facades\Http::withToken($accessToken)
                            ->withBody($fileContent, $mimeType)
                            ->patch('https://www.googleapis.com/upload/drive/v3/files/' . $fileId . '?uploadType=media');
                    }
                }
            } catch (\Throwable $e) {
                // Kalau internet Google lagi gangguan, catat diam-diam aja biar warga nggak kena layar error
                Log::error('Gagal backup HTTP Google Drive: ' . $e->getMessage());
            }

            $statusBaru = 'proses';
            $pesan = 'Sip! ' . count($submittedIds) . ' bulan tagihan beserta bukti pembayaran berhasil dikirim. Menunggu konfirmasi Admin. ⏳';

            // Update ke database
            Pembayaran::whereIn('id', $submittedIds)->update([
                'status' => $statusBaru,
                'bukti_pembayaran' => $buktiUrl,
                'updated_at' => now()
            ]);

            return back()->with('success', $pesan);
        } catch (\Throwable $e) {
            // KALAU ADA YANG MELEDAK, TAMPILKAN DI KOTAK MERAH!
            return back()->with('error', 'Sistem Gagal (Error 500): ' . $e->getMessage() . ' di baris ' . $e->getLine());
        }
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
