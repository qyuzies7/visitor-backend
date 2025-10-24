<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\VisitorCard;
use App\Models\CardTransaction;
use App\Models\StationStatusSummary;
use App\Models\StationDailyCardFlow;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getActiveVisitors()
    {
        return response()->json(StationStatusSummary::select('station_id','station_name','active_visitors')->get());
    }
    public function getPendingCount()
    {
        return response()->json(StationStatusSummary::select('station_id','station_name','pending_verification')->get());
    }
    public function getTodayIssued(Request $request)
    {
        $date = $request->date ?? now()->toDateString();
        return response()->json(StationDailyCardFlow::where('date', $date)->select('station_id','station_name','cards_issued')->get());
    }
    public function getTodayReturned(Request $request)
    {
        $date = $request->date ?? now()->toDateString();
        return response()->json(StationDailyCardFlow::where('date', $date)->select('station_id','station_name','cards_returned')->get());
    }
    public function getDamagedCards(Request $request)
    {
        $date = $request->date ?? now()->toDateString();
        // use timezone-aware day range to avoid day-shift issues
        $start = Carbon::parse($date, config('app.timezone'))->startOfDay();
        $end = Carbon::parse($date, config('app.timezone'))->endOfDay();
        $count = CardTransaction::where('transaction_type', 'damaged')
            ->whereBetween('processed_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->count();
        return response()->json(['total' => $count]);
    }

    public function getLostCards(Request $request)
    {
        $date = $request->date ?? now()->toDateString();
        // use timezone-aware day range to avoid day-shift issues
        $start = Carbon::parse($date, config('app.timezone'))->startOfDay();
        $end = Carbon::parse($date, config('app.timezone'))->endOfDay();
        $count = CardTransaction::where('transaction_type', 'lost')
            ->whereBetween('processed_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->count();
        return response()->json(['total' => $count]);
    }
}
