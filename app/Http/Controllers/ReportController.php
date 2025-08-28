<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CardTransaction;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller {

    // Export laporan harian kartu masuk/keluar per station (dari view)
    public function exportStationDailyFlow(Request $request)
    {
    $date = $request->input('date', now()->toDateString());
    $data = \App\Models\StationDailyCardFlow::getDaily($date)->toArray();
    $headings = ['Station ID', 'Station Name', 'Date', 'Cards Issued', 'Cards Returned'];
    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericArrayExport($data, $headings), 'station_daily_card_flow.xlsx');
    }
    // Export semua transaksi kartu ke Excel
    public function exportAll(Request $request)
    {
        $data = CardTransaction::with('visitorCard')->get();
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\CardTransactionExport($data), 'card_transactions.xlsx');
    }

    // Export laporan harian kartu masuk/keluar
    public function exportDailyFlow(Request $request)
    {
    $date = $request->input('date', now()->toDateString());
    $data = CardTransaction::getDailyFlow($date)->toArray();
    $headings = ['Transaction Type', 'Total'];
    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericArrayExport($data, $headings), 'daily_card_flow.xlsx');
    }

    // Export laporan mingguan
    public function exportWeeklyFlow(Request $request)
    {
    $week = $request->input('week', now()->weekOfYear);
    $year = $request->input('year', now()->year);
    $data = CardTransaction::getWeeklyFlow($week, $year)->toArray();
    $headings = ['Transaction Type', 'Week', 'Total'];
    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericArrayExport($data, $headings), 'weekly_card_flow.xlsx');
    }

    // Export laporan bulanan
    public function exportMonthlyFlow(Request $request)
    {
    $month = $request->input('month', now()->month);
    $year = $request->input('year', now()->year);
    $data = CardTransaction::getMonthlyFlow($month, $year)->toArray();
    $headings = ['Transaction Type', 'Month', 'Total'];
    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericArrayExport($data, $headings), 'monthly_card_flow.xlsx');
    }

    // Export laporan tahunan
    public function exportYearlyFlow(Request $request)
    {
    $year = $request->input('year', now()->year);
    $data = CardTransaction::getYearlyFlow($year)->toArray();
    $headings = ['Transaction Type', 'Year', 'Total'];
    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericArrayExport($data, $headings), 'yearly_card_flow.xlsx');
    }

    // Export kondisi kartu
    public function exportCardCondition(Request $request)
    {
    $data = CardTransaction::getCardConditionReport()->toArray();
    $headings = ['Card Condition', 'Total'];
    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericArrayExport($data, $headings), 'card_condition_report.xlsx');
    }
}
