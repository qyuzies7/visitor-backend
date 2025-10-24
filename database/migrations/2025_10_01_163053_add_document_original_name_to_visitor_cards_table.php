<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_cards', function (Blueprint $table) {
            $table->string('document_original_name')->nullable()->after('document_path');
        });
    }

    public function down(): void
    {
        Schema::table('visitor_cards', function (Blueprint $table) {
            $table->dropColumn('document_original_name');
        });
    }
};
