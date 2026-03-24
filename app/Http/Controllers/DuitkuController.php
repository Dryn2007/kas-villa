<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Services\DuitkuService;
use Illuminate\Support\Facades\Log;

class DuitkuController extends Controller
{
    /**
     * Callback dari Duitku setelah pembayaran berhasil atau gagal
     */
    public function callback(Request $request, DuitkuService $duitku)
    {
        $merchantCode = $request->input('merchantCode');
        $amount = $request->input('amount');
        $merchantOrderId = $request->input('merchantOrderId');
        $signature = $request->input('signature');
        $resultCode = $request->input('resultCode');

        if (!empty($merchantCode) && !empty($amount) && !empty($merchantOrderId) && !empty($signature)) {
            // Validasi signature
            if ($duitku->validateSignature($merchantOrderId, $amount, $signature)) {
                // Callback tervalidasi
                $pembayarans = Pembayaran::where('order_id', $merchantOrderId)->get();

                if ($pembayarans->isEmpty()) {
                    Log::warning('Duitku Callback - Order ID tidak ditemukan: ' . $merchantOrderId);
                    return response()->json(['error' => 'Order ID not found'], 404);
                }

                if ($resultCode == '00') {
                    // Success
                    foreach ($pembayarans as $pembayaran) {
                        if ($pembayaran->status !== 'lunas') {
                            $pembayaran->update([
                                'status' => 'lunas',
                                'updated_at' => now(),
                            ]);
                        }
                    }
                    Log::info('Duitku Callback - Payment Success: ' . $merchantOrderId);
                } else {
                    // Failed
                    foreach ($pembayarans as $pembayaran) {
                        if ($pembayaran->status !== 'lunas') {
                            $pembayaran->update(['status' => 'belum']);
                        }
                    }
                    Log::info('Duitku Callback - Payment Failed: ' . $merchantOrderId . ' dengan kode ' . $resultCode);
                }

                return response('00', 200); // 200 OK untuk menghentikan callback retry dari service
            } else {
                Log::error('Duitku Callback - Bad Signature: ' . $merchantOrderId);
                return response()->json(['error' => 'Bad Signature'], 400);
            }
        } else {
            Log::error('Duitku Callback - Bad Parameter', $request->all());
            return response()->json(['error' => 'Bad Parameter'], 400);
        }
    }

    /**
     * Redirect dari Duitku setelah user selesai proses pembayaran
     */
    public function return(Request $request)
    {
        $resultCode = $request->input('resultCode');

        if ($resultCode == '00') {
            return redirect()->route('dashboard')->with('success', 'Pembayaran berhasil dikonfirmasi!');
        } elseif ($resultCode == '01') {
            return redirect()->route('dashboard')->with('warning', 'Pembayaran Anda sedang kami proses...');
        } else {
            return redirect()->route('dashboard')->with('error', 'Pembayaran Anda dibatalkan atau gagal.');
        }
    }
}
