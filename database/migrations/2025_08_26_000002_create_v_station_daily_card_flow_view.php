<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS public.v_station_daily_card_flow');
        DB::statement("CREATE VIEW v_station_daily_card_flow AS
            SELECT
                s.id AS station_id,
                s.station_name,
                DATE(ct.processed_at) AS date,
                SUM(CASE WHEN ct.transaction_type = 'issued' THEN 1 ELSE 0 END) AS cards_issued,
                SUM(CASE WHEN ct.transaction_type = 'returned' THEN 1 ELSE 0 END) AS cards_returned
            FROM stations s
            LEFT JOIN visitor_cards vc ON vc.station_id = s.id
            LEFT JOIN card_transactions ct ON ct.visitor_card_id = vc.id
            GROUP BY s.id, s.station_name, DATE(ct.processed_at)
        ");
    }
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_station_daily_card_flow');
    }
};
