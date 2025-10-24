<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_cards', function (Blueprint $table) {
            // PIC
            if (!Schema::hasColumn('visitor_cards', 'pic_name')) {
                $table->string('pic_name')->nullable();
            }
            if (!Schema::hasColumn('visitor_cards', 'pic_position')) {
                $table->string('pic_position')->nullable();
            }
            if (!Schema::hasColumn('visitor_cards', 'assistance_service')) {
                $table->string('assistance_service')->nullable();
            }

            // Akses pintu
            if (!Schema::hasColumn('visitor_cards', 'access_door')) {
                $table->string('access_door')->nullable();
            }
            if (!Schema::hasColumn('visitor_cards', 'access_purpose')) {
                $table->string('access_purpose')->nullable();
            }
            if (!Schema::hasColumn('visitor_cards', 'vehicle_type')) {
                $table->string('vehicle_type')->nullable();
            }
            if (!Schema::hasColumn('visitor_cards', 'vehicle_plate')) {
                $table->string('vehicle_plate')->nullable();
            }

            // Protokoler
            if (!Schema::hasColumn('visitor_cards', 'protokoler_count')) {
                $table->unsignedInteger('protokoler_count')->nullable();
            }
            if (!Schema::hasColumn('visitor_cards', 'need_protokoler_escort')) {
                $table->boolean('need_protokoler_escort')->nullable();
            }
        });

        $columnInfo = DB::selectOne("
            SELECT data_type
            FROM information_schema.columns
            WHERE table_name = 'visitor_cards'
              AND column_name = 'access_time'
            LIMIT 1
        ");

        if (!$columnInfo) {
            Schema::table('visitor_cards', function (Blueprint $table) {
                $table->time('access_time')->nullable();
            });
        } else {
            $type = strtolower($columnInfo->data_type ?? '');
            if (in_array($type, ['character varying', 'text'])) {
                DB::statement("ALTER TABLE visitor_cards ADD COLUMN access_time_tmp time NULL");

                DB::statement(<<<'SQL'
                    UPDATE visitor_cards
                    SET access_time_tmp =
                        CASE
                            WHEN access_time ~ '^\d{1,2}:\d{2}$'    THEN access_time::time
                            WHEN access_time ~ '^\d{1,2}:\d{2}:\d{2}$' THEN access_time::time
                            ELSE NULL
                        END
                SQL);

                Schema::table('visitor_cards', function (Blueprint $table) {
                    $table->dropColumn('access_time');
                });

                Schema::table('visitor_cards', function (Blueprint $table) {
                    $table->renameColumn('access_time_tmp', 'access_time');
                });
            }
        }

        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint
                    WHERE conname = 'access_time_no_seconds'
                ) THEN
                    ALTER TABLE visitor_cards
                    ADD CONSTRAINT access_time_no_seconds
                    CHECK (date_part('second', access_time) = 0);
                END IF;
            END$$;
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE visitor_cards
            DROP CONSTRAINT IF EXISTS access_time_no_seconds
        ");

        Schema::table('visitor_cards', function (Blueprint $table) {
            $table->string('access_time_tmp')->nullable();
        });

        DB::statement(<<<'SQL'
            UPDATE visitor_cards
            SET access_time_tmp =
                CASE
                    WHEN pg_typeof(access_time) = 'time without time zone'::regtype
                        THEN to_char(access_time::time, 'HH24:MI')

                    WHEN pg_typeof(access_time) = 'character varying'::regtype
                         AND access_time ~ '^\d{1,2}:\d{2}$'
                        THEN access_time

                    WHEN pg_typeof(access_time) = 'character varying'::regtype
                         AND access_time ~ '^\d{1,2}:\d{2}:\d{2}$'
                        THEN to_char(access_time::time, 'HH24:MI')

                    ELSE NULL
                END
        SQL);

        Schema::table('visitor_cards', function (Blueprint $table) {
            $table->dropColumn('access_time');
        });

        Schema::table('visitor_cards', function (Blueprint $table) {
            $table->renameColumn('access_time_tmp', 'access_time');
        });

        Schema::table('visitor_cards', function (Blueprint $table) {
            $drops = [
                'pic_name',
                'pic_position',
                'assistance_service',
                'access_door',
                'access_purpose',
                'vehicle_type',
                'vehicle_plate',
                'protokoler_count',
                'need_protokoler_escort',
            ];

            foreach ($drops as $col) {
                if (Schema::hasColumn('visitor_cards', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
