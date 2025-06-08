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
    Schema::table('cryptocurrencies', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->decimal('current_price', 20, 10)->change();
        $table->decimal('market_cap', 20, 2)->change();
        $table->decimal('volume_24h', 20, 2)->change();
        $table->decimal('price_change_24h', 12, 4)->change();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
