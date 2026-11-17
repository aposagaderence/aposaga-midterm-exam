<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('baggage_items', function (Blueprint $table) {
            $table->dropColumn('heavy_cargo');
        });
    }

    public function down(): void
    {
        Schema::table('baggage_items', function (Blueprint $table) {
            $table->boolean('heavy_cargo')->default(false);
        });

        DB::table('baggage_items')
            ->where('bag_weight_kg', '>', 32)
            ->update(['heavy_cargo' => true]);
    }
};
