<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CardTransaction extends Model {

    // Laporan harian kartu masuk/keluar
    public static function getDailyFlow($date)
    {
        return self::selectRaw('transaction_type, count(*) as total')
            ->whereDate('processed_at', $date)
            ->groupBy('transaction_type')
            ->get();
    }

    // Laporan mingguan kartu masuk/keluar
    public static function getWeeklyFlow($week, $year)
    {
        return self::selectRaw('transaction_type, WEEK(processed_at, 1) as week, count(*) as total')
            ->whereYear('processed_at', $year)
            ->whereRaw('WEEK(processed_at, 1) = ?', [$week])
            ->groupBy('transaction_type', 'week')
            ->get();
    }

    // Laporan bulanan kartu masuk/keluar
    public static function getMonthlyFlow($month, $year)
    {
        return self::selectRaw('transaction_type, MONTH(processed_at) as month, count(*) as total')
            ->whereYear('processed_at', $year)
            ->whereMonth('processed_at', $month)
            ->groupBy('transaction_type', 'month')
            ->get();
    }

    // Laporan tahunan kartu masuk/keluar
    public static function getYearlyFlow($year)
    {
        return self::selectRaw('transaction_type, YEAR(processed_at) as year, count(*) as total')
            ->whereYear('processed_at', $year)
            ->groupBy('transaction_type', 'year')
            ->get();
    }

    // Laporan kondisi kartu
    public static function getCardConditionReport()
    {
        return self::selectRaw('card_condition, count(*) as total')
            ->groupBy('card_condition')
            ->get();
    }

    // Scope: transaksi kartu yang sudah dikembalikan
    public function scopeReturned($query)
    {
        return $query->where('transaction_type', 'returned');
    }

    // Edit kondisi kartu (update card_condition, condition_notes, handling_notes)
    public function updateCondition($condition, $notes = null, $handling = null)
    {
        $this->card_condition = $condition;
        $this->condition_notes = $notes;
        $this->handling_notes = $handling;
        $this->save();
        return $this;
    }
    use HasFactory;

    protected $fillable = [
        'visitor_card_id',
        'transaction_type',
        'card_condition',
        'condition_notes',
        'handling_notes',
        'performed_by_user_id',
        'performed_by_name_cached',
        'processed_at',
    ];

    public function visitorCard(){
        return $this->belongsTo(VisitorCard::class);
    }

    public function performedBy(){
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}
