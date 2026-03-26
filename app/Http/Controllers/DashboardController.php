<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
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
            $uploadMethod = $request->input('upload_method', 'satu');

            if (empty($ids)) {
                return back()->with('error', 'Pilih minimal satu bulan untuk dibayar.');
            }

            if ($uploadMethod === 'satu') {
                $request->validate([
                    'bukti_pembayaran' => 'required|array',
                    'bukti_pembayaran.*' => 'image|max:5120'
                ]);
            } else {
                $request->validate([
                    'bukti_pembayaran_pisah' => 'required|array',
                    'bukti_pembayaran_pisah.*' => 'image|max:5120',
                    'bukti_pembayaran_months' => 'required|array'
                ]);
            }

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
            $namaKeluargaSafe = preg_replace('/[^A-Za-z0-9_-]/', '_', $user->name);

            // 🚨 PERBAIKAN WAKTU: Jam-Menit-Detik_Tanggal-Bulan-Tahun (Waktu Indonesia WIB)
            $waktu = now()->timezone('Asia/Jakarta')->format('H-i-s_d-m-Y');

            $paidPayments = Pembayaran::whereIn('id', $submittedIds)->orderBy('bulan_ke')->get();

            if ($paidPayments->isEmpty()) {
                $rangeBulan = 'Unknown';
            } else {
                $firstMonth = $paidPayments->first();
                $lastMonth = $paidPayments->last();
                $startMonthName = \Carbon\Carbon::create(2026, 3)->addMonths($firstMonth->bulan_ke)->translatedFormat('F');
                $endMonthName = \Carbon\Carbon::create(2026, 3)->addMonths($lastMonth->bulan_ke)->translatedFormat('F');

                $rangeBulan = ($paidPayments->count() == 1) ? $startMonthName : $startMonthName . '-' . $endMonthName;
            }

            // HASIL NAMA FILE: Keluarga_Udin_15-30-05_24-03-2026_Maret-Mei
            $finalFilename = "{$namaKeluargaSafe}_{$waktu}_{$rangeBulan}";

            $statusBaru = 'proses';
            $pesan = 'Sip! ' . count($submittedIds) . ' bulan tagihan beserta bukti pembayaran berhasil dikirim. Menunggu konfirmasi Admin. ⏳';
            $waktuUpdate = now();

            if ($uploadMethod === 'satu') {
                $files = Arr::wrap($request->file('bukti_pembayaran'));

                if (empty($files)) {
                    return back()->with('error', 'Tidak ada file bukti yang dikirim.');
                }

                $uploadedUrls = [];

                foreach ($files as $index => $file) {
                    $fileSuffix = count($files) > 1 ? "_" . ($index + 1) : "";
                    $currentFilename = $finalFilename . $fileSuffix;

                    $uploadResult = $cloudinaryService->upload(
                        $file,
                        'kas-villa/bukti-transfer/' . $namaKeluargaSafe,
                        'auto',
                        $currentFilename
                    );

                    if (!$uploadResult['success']) {
                        return back()->with('error', 'Gagal mengupload bukti ke Cloudinary: ' . $uploadResult['message']);
                    }
                    $uploadedUrls[] = $uploadResult['url'];

                    // GDrive backup
                    $this->uploadToGDrive($file, $currentFilename, $namaKeluargaSafe);
                }

                $buktiUrlStr = implode(',', $uploadedUrls);

                Pembayaran::whereIn('id', $submittedIds)->update([
                    'status' => $statusBaru,
                    'bukti_pembayaran' => $buktiUrlStr,
                    'updated_at' => $waktuUpdate
                ]);
            } else {
                // Metode upload pisah (Tandai bukti pembayaran perfoto)
                $filesPisah = $request->file('bukti_pembayaran_pisah');
                $monthsPisah = $request->input('bukti_pembayaran_months');

                if (empty($filesPisah) || empty($monthsPisah)) {
                    return back()->with('error', 'Silakan upload bukti dan pilih bulannya terlebih dahulu.');
                }

                $tagihanUpdates = [];

                foreach ($filesPisah as $idx => $file) {
                    if (!isset($monthsPisah[$idx])) continue;

                    $fileSuffix = count($filesPisah) > 1 ? "_part" . ($idx + 1) : "";
                    $currentFilename = $finalFilename . $fileSuffix;

                    $uploadResult = $cloudinaryService->upload(
                        $file,
                        'kas-villa/bukti-transfer/' . $namaKeluargaSafe,
                        'auto',
                        $currentFilename
                    );

                    if (!$uploadResult['success']) {
                        return back()->with('error', 'Gagal mengupload bukti ke Cloudinary: ' . $uploadResult['message']);
                    }
                    $uploadedUrl = $uploadResult['url'];

                    // GDrive backup
                    $this->uploadToGDrive($file, $currentFilename, $namaKeluargaSafe);

                    foreach ($monthsPisah[$idx] as $tid) {
                        if (!isset($tagihanUpdates[$tid])) {
                            $tagihanUpdates[$tid] = [];
                        }
                        $tagihanUpdates[$tid][] = $uploadedUrl;
                    }
                }

                // Update masing-masing record tagihan dengan foto spesifiknya
                foreach ($tagihanUpdates as $tid => $urls) {
                    if (in_array($tid, $submittedIds)) {
                        Pembayaran::where('id', $tid)->update([
                            'status' => $statusBaru,
                            'bukti_pembayaran' => implode(',', $urls),
                            'updated_at' => $waktuUpdate
                        ]);
                    }
                }

                // Jika masih ada sisa ID tagihan yg ikut dikirim tp ga ditandai foto, anggap proses saja (bisa aja admin cek manual)
                $unupdatedIds = array_diff($submittedIds, array_keys($tagihanUpdates));
                if (!empty($unupdatedIds)) {
                    Pembayaran::whereIn('id', $unupdatedIds)->update([
                        'status' => $statusBaru,
                        'updated_at' => $waktuUpdate
                    ]);
                }
            }

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

    private function uploadToGDrive($file, $currentFilename, $namaKeluargaSafe)
    {
        try {
            $extension = $file->getClientOriginalExtension();
            $gdFilename = $currentFilename . '.' . $extension;
            $mimeType = $file->getMimeType();
            $fileContent = file_get_contents($file->getRealPath());

            // Minta Token (Max 2 Detik)
            $tokenResponse = \Illuminate\Support\Facades\Http::timeout(2)->post('https://oauth2.googleapis.com/token', [
                'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
                'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
                'grant_type' => 'refresh_token',
            ]);

            if ($tokenResponse->successful()) {
                $accessToken = $tokenResponse->json('access_token');
                $parentFolderId = env('GOOGLE_DRIVE_FOLDER_ID');

                // Cari apakah folder user sudah ada
                $searchResponse = \Illuminate\Support\Facades\Http::withToken($accessToken)->timeout(2)
                    ->get('https://www.googleapis.com/drive/v3/files', [
                        'q' => "mimeType='application/vnd.google-apps.folder' and name='" . $namaKeluargaSafe . "' and '" . $parentFolderId . "' in parents and trashed=false",
                        'fields' => 'files(id, name)'
                    ]);

                $folderId = null;
                if ($searchResponse->successful() && count($searchResponse->json('files')) > 0) {
                    $folderId = $searchResponse->json('files')[0]['id'];
                } else {
                    // Buat folder baru di dalam folder utama
                    $createFolderResponse = \Illuminate\Support\Facades\Http::withToken($accessToken)->timeout(2)
                        ->post('https://www.googleapis.com/drive/v3/files', [
                            'name' => $namaKeluargaSafe,
                            'mimeType' => 'application/vnd.google-apps.folder',
                            'parents' => [$parentFolderId]
                        ]);
                    if ($createFolderResponse->successful()) {
                        $folderId = $createFolderResponse->json('id');
                    } else {
                        $folderId = $parentFolderId; // fallback ke root jika gagal buat
                    }
                }

                // Bikin Wadah (Max 2 Detik)
                $metaResponse = \Illuminate\Support\Facades\Http::withToken($accessToken)->timeout(2)
                    ->post('https://www.googleapis.com/drive/v3/files', [
                        'name' => $gdFilename,
                        'parents' => [$folderId]
                    ]);

                if ($metaResponse->successful()) {
                    $fileId = $metaResponse->json('id');
                    // Suntik Foto (Max 3 Detik)
                    \Illuminate\Support\Facades\Http::withToken($accessToken)->timeout(3)
                        ->withBody($fileContent, $mimeType)
                        ->patch('https://www.googleapis.com/upload/drive/v3/files/' . $fileId . '?uploadType=media');
                }
            }
        } catch (\Throwable $e) {
            // Diabaikan aja kalau G-Drive lemot
        }
    }
}
