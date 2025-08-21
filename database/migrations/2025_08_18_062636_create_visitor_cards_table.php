<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visitor_cards', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 32)->unique();
            $table->string('full_name', 255);
            $table->string('institution', 255);
            $table->string('identity_number', 32);
            $table->string('email', 255);
            $table->string('phone_number', 32);
            $table->foreignId('visit_type_id')->constrained('visit_types')
                    ->nullOnDelete()->cascadeOnUpdate();
            $table->date('visit_start_date');
            $table->date('visit_end_date');
            $table->foreignId('station_id')->constrained('stations')->nullOnDelete();
            $table->text('visit_purpose');
            $table->enum('status', [
                'processing',
                'approved',
                'rejected',
                'cancelled',
            ])->default('processing');
            $table->text('rejection_reason');
            $table->text('approval_notes');
            $table->foreignId('last_updated_by_user_id')->nullable()
                    ->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('last_updated_by_name_cached', 255)->nullable();
            $table->timestamp('last_updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();

            // Index
            $table->index('status', 'idx_vc_status');
            $table->index(['visit_start_date', 'visit_end_date'], 'idx_vc_dates');
            $table->index('station_id', 'idx_vc_station');
        });

        // Validasi tanggal
        DB::statement("
            ALTER TABLE visitor_cards
            ADD CONSTRAINT chk_visit_dates 
            CHECK (visit_end_date >= visit_start_date)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_cards');
    }
};