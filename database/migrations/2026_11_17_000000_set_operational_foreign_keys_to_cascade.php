<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('baggage_items', function (Blueprint $table) {
            $table->dropForeign(['passenger_id']);
            $table->dropForeign(['flight_id']);
            $table->foreign('passenger_id')->references('id')->on('passengers')->cascadeOnDelete();
            $table->foreign('flight_id')->references('id')->on('flights')->cascadeOnDelete();
        });

        Schema::table('screening_logs', function (Blueprint $table) {
            $table->dropForeign(['baggage_item_id']);
            $table->dropForeign(['screening_gate_id']);
            $table->foreign('baggage_item_id')->references('id')->on('baggage_items')->cascadeOnDelete();
            $table->foreign('screening_gate_id')->references('id')->on('screening_gates')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('screening_logs', function (Blueprint $table) {
            $table->dropForeign(['baggage_item_id']);
            $table->dropForeign(['screening_gate_id']);
            $table->foreign('baggage_item_id')->references('id')->on('baggage_items')->restrictOnDelete();
            $table->foreign('screening_gate_id')->references('id')->on('screening_gates')->restrictOnDelete();
        });

        Schema::table('baggage_items', function (Blueprint $table) {
            $table->dropForeign(['passenger_id']);
            $table->dropForeign(['flight_id']);
            $table->foreign('passenger_id')->references('id')->on('passengers')->restrictOnDelete();
            $table->foreign('flight_id')->references('id')->on('flights')->restrictOnDelete();
        });
    }
};
