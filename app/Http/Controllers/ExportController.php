<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExportController extends Controller
{
    protected function monthName($bulan_ke)
    {
        Carbon::setLocale('id');
        return Carbon::create(2026, 3)->addMonths((int)$bulan_ke)->translatedFormat('M Y');
    }

    protected function getRanges($numbers)
    {
        if (empty($numbers)) return '-';
        sort($numbers);
        $ranges = [];
        $start = $numbers[0];
        $prev = $numbers[0];

        for ($i = 1; $i < count($numbers); $i++) {
            if ($numbers[$i] == $prev + 1) {
                $prev = $numbers[$i];
            } else {
                $ranges[] = ($start == $prev) ? $this->monthName($start) : $this->monthName($start) . " s/d " . $this->monthName($prev);
                $start = $numbers[$i];
                $prev = $numbers[$i];
            }
        }
        $ranges[] = ($start == $prev) ? $this->monthName($start) : $this->monthName($start) . " s/d " . $this->monthName($prev);
        return implode(', ', $ranges);
    }

    protected function getExportData(Request $request)
    {
        $kk_ids = $request->input('kk_ids', []);
        $start_bulan = $request->input('start_bulan', 1);
        $end_bulan = $request->input('end_bulan', 14);

        $query = User::with(['pembayarans' => function ($q) use ($start_bulan, $end_bulan) {
            $q->whereBetween('bulan_ke', [$start_bulan, $end_bulan])
                ->orderBy('bulan_ke', 'asc');
        }]);

        if (!empty($kk_ids) && !in_array('all', $kk_ids)) {
            $query->whereIn('id', $kk_ids);
        }

        $users = $query->get();

        $data = [];
        $totalDana = 0;

        foreach ($users as $user) {
            $lunas = [];
            $belum = [];
            $nominalLunas = 0;

            foreach ($user->pembayarans as $pembayaran) {
                if ($pembayaran->status === 'lunas') {
                    $lunas[] = $pembayaran->bulan_ke;
                    $nominalLunas += $pembayaran->nominal;
                    $totalDana += $pembayaran->nominal;
                } else {
                    $belum[] = $pembayaran->bulan_ke;
                }
            }

            $data[] = [
                'name' => $user->name,
                'lunas_range' => $this->getRanges($lunas),
                'belum_range' => $this->getRanges($belum),
                'nominal_lunas' => $nominalLunas
            ];
        }

        Carbon::setLocale('id');
        $timeStr = $this->monthName($start_bulan) . " - " . $this->monthName($end_bulan);

        if (empty($kk_ids) || in_array('all', $kk_ids)) {
            $titlePrefix = "Laporan Kas Villa Semua Keluarga";
        } elseif (count($kk_ids) == 1) {
            $titlePrefix = "Laporan Kas Villa KK " . $users->first()->name;
        } else {
            $titlePrefix = "Laporan Kas Villa Beberapa Keluarga";
        }

        $fileNameStr = "{$titlePrefix} ({$timeStr})";

        return compact('data', 'totalDana', 'fileNameStr', 'timeStr');
    }

    public function exportPdf(Request $request)
    {
        extract($this->getExportData($request));

        $pdf = Pdf::loadView('exports.laporan_kas', compact('data', 'totalDana', 'fileNameStr', 'timeStr'));
        return $pdf->download($fileNameStr . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        extract($this->getExportData($request));

        $view = view('exports.laporan_kas', compact('data', 'totalDana', 'fileNameStr', 'timeStr'))->render();
        return response($view)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $fileNameStr . '.xls"');
    }
}
