<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('option_lists', function (Blueprint $table) {
            $table->id();
            $table->string('group_key');   
            $table->string('value');       
            $table->string('label');       
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['group_key','sort_order']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('option_lists');
    }
};
