<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Pembayaran;
use App\Models\User;
use Carbon\Carbon; // <-- Tambahan untuk manipulasi waktu

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Data global untuk Progress Bar di Header
        $totalTerkumpul = Pembayaran::where('status', 'lunas')->sum('nominal');
        $userCount = User::count();
        $targetDana = $userCount * 14 * 65000;
        $persentase = $targetDana > 0 ? ($totalTerkumpul / $targetDana) * 100 : 0;

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

        // JIKA TUNAI: Tandai sebagai menunggu konfirmasi admin
        if ($metode === 'tunai') {
            // Update ke database
            Pembayaran::whereIn('id', $submittedIds)->update([
                'status' => 'proses',
                'updated_at' => now()
            ]);

            $pesan = 'Sip! ' . count($submittedIds) . ' bulan tagihan sedang menunggu konfirmasi Admin. ⏳';
            return back()->with('success', $pesan);
        }

        // JIKA ONLINE: Buat Invoice Duitku
        if ($metode === 'online') {
            $merchantCode = env('DUITKU_MERCHANT_CODE');
            $merchantKey = env('DUITKU_MERCHANT_KEY');

            // Bikin Order ID unik (Misal: KAS-17091234-User1)
            $orderId = 'KAS-' . time() . '-' . Auth::id();

            // Hitung total nominal langsung dari Database
            $amount = Pembayaran::whereIn('id', $submittedIds)->sum('nominal');

            // Rumus rahasia Duitku (MD5)
            $signature = md5($merchantCode . $orderId . $amount . $merchantKey);

            // Hitung total nominal dari tagihan yang dipilih
            if (!isset($totalNominal)) {
                $totalNominal = Pembayaran::whereIn('id', $submittedIds)->sum('nominal');
                $amount = $totalNominal;
            }

            // Rumus rahasia Duitku (MD5)
            $signature = md5($merchantCode . $orderId . $amount . $merchantKey);

            // Tentukan bulan teks untuk Invoice Duitku
            if (count($submittedIds) === 1) {
                $tagihan = Pembayaran::find($submittedIds[0]);
                $bulanTeks = $this->getBulanTeks($tagihan->bulan_ke);
            } else {
                // Trik Keren: Menampilkan "April 2026 s/d Juni 2026"
                $tagihanAwal = Pembayaran::find($submittedIds[0]);
                $tagihanAkhir = Pembayaran::find(end($submittedIds));
                $bulanTeks = $this->getBulanTeks($tagihanAwal->bulan_ke) . ' s/d ' . $this->getBulanTeks($tagihanAkhir->bulan_ke);
            }

            $params = [
                'merchantCode' => $merchantCode,
                'paymentAmount' => $amount,
                'merchantOrderId' => $orderId,
                'productDetails' => 'Pembayaran Kas ' . $bulanTeks,
                'email' => Auth::user()->email,
                'customerVaName' => Auth::user()->name,
                'phoneNumber' => Auth::user()->no_wa ?? '081234567890',
                'returnUrl' => route('dashboard'), // Balik ke sini setelah bayar
                'callbackUrl' => url('/api/duitku/callback'), // URL untuk robot Duitku lapor
                'signature' => $signature,
                'expiryPeriod' => 60 // Expired dalam 60 menit
            ];

            // Tembak API Duitku
            $response = Http::post('https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry', $params);
            $result = $response->json();

            if (isset($result['paymentUrl'])) {
                // Simpan Order ID ke tagihan yang dipilih, ubah status jadi 'proses_online'
                Pembayaran::whereIn('id', $submittedIds)->update([
                    'status' => 'proses_online',
                    'order_id' => $orderId,
                    'updated_at' => now()
                ]);

                // Lempar warga ke halaman kasir Duitku!
                return redirect($result['paymentUrl']);
            } else {
                return back()->with('error', 'Gagal memproses Duitku: ' . ($result['statusMessage'] ?? 'Unknown Error'));
            }
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

    // Helper function untuk konversi bulan_ke ke nama bulan & tahun asli
    private function getBulanTeks($bulanKe)
    {
        // Start date: 1 Maret 2026. 
        // Jika $bulanKe = 1, maka ditambah 1 bulan -> 1 April 2026
        // Jika $bulanKe = 14, maka ditambah 14 bulan -> 1 Mei 2027
        return Carbon::create(2026, 3, 1)->addMonths($bulanKe)->translatedFormat('F Y');
    }

    // Callback dari Duitku untuk notifikasi pembayaran
    public function duitkuCallback(Request $request)
    {
        $merchantCode = env('DUITKU_MERCHANT_CODE');
        $merchantKey = env('DUITKU_MERCHANT_KEY');

        // Terima data dari Duitku
        $orderId = $request->input('merchantOrderId');
        $statusCode = $request->input('statusCode');
        $resultCode = $request->input('resultCode');
        $signature = $request->input('signature');
        $amount = $request->input('amount');

        // Validasi signature untuk memastikan request dari Duitku yang asli
        $expectedSignature = md5($merchantCode . $orderId . $amount . $merchantKey);

        if ($signature !== $expectedSignature) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        // Jika pembayaran berhasil (resultCode == '00')
        if ($resultCode === '00') {
            // Cari tagihan dengan order_id ini
            // Gunakan where() dan update() massal karena 1 order_id bisa untuk beberapa bulan
            $pembayaranTerkait = Pembayaran::where('order_id', $orderId)->get();

            if ($pembayaranTerkait->count() > 0) {
                // Update semua yang terhubung dengan order_id ini menjadi lunas
                Pembayaran::where('order_id', $orderId)->update([
                    'status' => 'lunas',
                    'updated_at' => now()
                ]);

                // Log transaksi sukses
                Log::info('Duitku Payment Success', [
                    'order_id' => $orderId,
                    'amount' => $amount,
                    'timestamp' => now()
                ]);

                return response()->json(['message' => 'Payment processed successfully'], 200);
            }
        } else {
            // Jika pembayaran gagal/expired, kembalikan status ke 'belum'
            Pembayaran::where('order_id', $orderId)->update([
                'status' => 'belum',
                'order_id' => null, // Reset order_id agar bersih
                'updated_at' => now()
            ]);

            Log::warning('Duitku Payment Failed', [
                'order_id' => $orderId,
                'result_code' => $resultCode,
                'timestamp' => now()
            ]);

            return response()->json(['message' => 'Payment failed'], 400);
        }
    }

    
}
