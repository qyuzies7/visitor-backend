<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visitor_cards', function (Blueprint $table) {
            $table->dropUnique(['identity_number']);    
        });

        Schema::table('visitor_cards', function (Blueprint $table) {
            if (Schema::hasColumn('visitor_cards', 'identity_number')) {
                $table->dropColumn('identity_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitor_cards', function (Blueprint $table) {
            $table->string('identity_number', 50)->nullable();
            $table->unique('identity_number');
        });
    }
};
