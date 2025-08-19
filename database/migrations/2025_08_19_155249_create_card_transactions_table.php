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
        Schema::create('card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_card_id')->constrained('visitor_cards')->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('transaction_type', ['diserahkan', 'dikembalikan', 'rusak', 'hilang']);
            $table->enum('card_condition', ['baik', 'rusak', 'hilang'])->default('baik');
            $table->text('condition_notes')->nullable();
            $table->text('handling_notes')->nullable();

            $table->foreignId('performed_by_user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('performed_by_name_cached', 255);
            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();

            //index
            $table->index('visitor_card_id', 'idx_ct_vc');
            $table->index(['transaction_type', 'processed_at'], 'idx_ct_type_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_transactions');
    }
};
