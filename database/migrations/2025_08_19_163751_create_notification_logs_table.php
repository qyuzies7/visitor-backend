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
        Schema::create('notification_logs_', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_card_id')->constrained('visitor_cards')->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('notification_type', ['email', 'whatsapp']);
            $table->string('recipient', 255);
            $table->text('message');
            $table->enum('send_status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            //index
            $table->index('visitor_card_id', 'idx_notif_vc');
            $table->index(['notification_type','send_status'], 'idx_notif_type_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs_');
    }
};
