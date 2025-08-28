<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS public.v_station_status_summary');
        DB::statement("CREATE VIEW v_station_status_summary AS
            SELECT
                s.id AS station_id,
                s.station_name,
                COUNT(DISTINCT vc.id) AS active_visitors,
                SUM(CASE WHEN vc.status = 'processing' THEN 1 ELSE 0 END) AS pending_verification
            FROM stations s
            LEFT JOIN visitor_cards vc ON vc.station_id = s.id AND vc.status IN ('processing','approved')
            GROUP BY s.id, s.station_name
        ");
    }
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_station_status_summary');
    }
};
