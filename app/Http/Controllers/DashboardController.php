<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pembayaran;
use App\Models\User;
use App\Services\DuitkuService;

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
    public function dummyPayBulk(Request $request, DuitkuService $duitku)
    {
        $ids = $request->input('tagihan_ids', []);
        $metode = $request->input('metode', 'online'); // Menangkap metode dari tombol

        if (empty($ids)) {
            return back()->with('error', 'Pilih minimal satu bulan untuk dibayar.');
        }

        try {
            $firstTagihan = Pembayaran::findOrFail($ids[0]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Data tagihan tidak ditemukan.');
        }

        $userId = $firstTagihan->user_id;

        // Ambil SEMUA tagihan yang murni belum dibayar (abaikan yang lunas & yang sedang proses)
        $allUnpaid = Pembayaran::where('user_id', $userId)
            ->whereNotIn('status', ['lunas', 'proses'])
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

        if ($metode === 'online') {
            $totalAmount = Pembayaran::whereIn('id', $submittedIds)->sum('nominal');
            $orderId = DuitkuService::generateOrderId($userId);

            $items = Pembayaran::whereIn('id', $submittedIds)->get()->map(function ($p) {
                return [
                    'name' => 'Iuran Bulan ke-' . $p->bulan_ke,
                    'price' => $p->nominal,
                    'quantity' => 1
                ];
            })->toArray();

            try {
                $user = User::findOrFail($userId);
            } catch (\Throwable $e) {
                return back()->with('error', 'Data User tidak ditemukan.');
            }

            $payload = [
                'paymentAmount' => (int) $totalAmount,
                'merchantOrderId' => $orderId,
                'productDetails' => 'Pembayaran Iuran Kas',
                'email' => $user->email,
                'phoneNumber' => '081234567890', // Bisa disesuaikan dengan no hp user jika ada 
                'customerVaName' => function_exists('mb_substr') ? mb_substr($user->name, 0, 20) : substr($user->name, 0, 20),
                'itemDetails' => $items,
                'customerDetail' => [
                    'firstName' => $user->name,
                    'lastName' => '',
                    'email' => $user->email,
                    'phoneNumber' => '081234567890',
                ],
                'callbackUrl' => url('/api/duitku/callback'),
                'returnUrl' => url('/duitku/return'),
                'expiryPeriod' => 60 // 1 jam
            ];

            // Panggil API Duitku (Pop API lebih gampang untuk generic checkout)
            $duitkuService = new DuitkuService();
            $response = $duitkuService->createInvoicePop($payload);

            if (isset($response['statusCode']) && $response['statusCode'] === '00' && isset($response['paymentUrl'])) {
                try {
                    // Update ke database
                    Pembayaran::whereIn('id', $submittedIds)->update([
                        'status' => 'proses_online',
                        'order_id' => $orderId,
                        'payment_url' => $response['paymentUrl'],
                        'updated_at' => now()
                    ]);

                    return redirect()->away($response['paymentUrl']);
                } catch (\Throwable $e) {
                    try {
                        \Illuminate\Support\Facades\Log::error('DB Update Error: ' . $e->getMessage());
                    } catch (\Throwable $logErr) {
                    }
                    return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan data transaksi. Silahkan hubungi admin. Error: ' . $e->getMessage());
                }
            } else {
                return back()->with('error', 'Gagal membuat invoice pembayaran: ' . ($response['statusMessage'] ?? 'Unknown Error'));
            }
        } else {
            // Tunai
            $statusBaru = 'proses';
            $pesan = 'Sip! ' . count($submittedIds) . ' bulan tagihan sedang menunggu konfirmasi Admin. ⏳';

            try {
                // Update ke database
                Pembayaran::whereIn('id', $submittedIds)->update([
                    'status' => $statusBaru,
                    'updated_at' => now()
                ]);
            } catch (\Throwable $e) {
                return back()->with('error', 'Terjadi kesalahan sistem saat proses Tunai: ' . $e->getMessage());
            }

            return back()->with('success', $pesan);
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
