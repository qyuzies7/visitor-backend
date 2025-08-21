<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visitor_card_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_card_id')->constrained('visitor_cards')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('performed_by_user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('performed_by_name_cached', 255);
            $table->enum('old_status', ['processing', 'approved', 'rejected', 'cancelled'])->default('processing');
            $table->enum('new_status', ['processing', 'approved', 'rejected', 'cancelled']);
            $table->text('notes')->nullable();
            $table->timestamp('changed_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();

            // Index
            $table->index('visitor_card_id', 'idx_vc_logs_vs');
            $table->index('changed_at', 'idx_vc_logs_changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_card_status_logs');
    }
};
