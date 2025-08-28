<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StationDailyCardFlow extends Model {

    public static function getDaily($date = null)
    {
        $date = $date ?: now()->toDateString();
        return self::where('date', $date)->get();
    }
    protected $table = 'v_station_daily_card_flow';
    public $timestamps = false;
}
