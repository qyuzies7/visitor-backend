<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {

        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW v_station_daily_card_flow AS
            SELECT
                vc.station_id,
                (ct.processed_at)::date AS flow_date,
                COUNT(*) FILTER (WHERE ct.transaction_type = 'diserahkan')   AS issued_count,
                COUNT(*) FILTER (WHERE ct.transaction_type = 'dikembalikan') AS returned_count,
                COUNT(*) FILTER (WHERE ct.transaction_type = 'rusak')  AS damaged_count,
                COUNT(*) FILTER (WHERE ct.transaction_type = 'hilang')     AS lost_count
            FROM card_transactions ct
            JOIN visitor_cards vc ON vc.id = ct.visitor_card_id
            GROUP BY vc.station_id, (ct.processed_at)::date
        SQL);


        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW v_station_status_summary AS
            SELECT
                station_id,
                COUNT(*) FILTER (WHERE status = 'sedang diproses') AS processing_count,
                COUNT(*) FILTER (WHERE status = 'disetujui')   AS approved_count,
                COUNT(*) FILTER (WHERE status = 'ditolak')   AS rejected_count,
                COUNT(*) FILTER (WHERE status = 'dibatalkan')  AS cancelled_count
            FROM visitor_cards
            GROUP BY station_id
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_station_status_summary');
        DB::statement('DROP VIEW IF EXISTS v_station_daily_card_flow');
    }
};
