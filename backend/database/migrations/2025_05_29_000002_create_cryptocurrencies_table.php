<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cryptocurrencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_type_id')->constrained()->onDelete('cascade');
            $table->string('symbol');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('external_id');
            $table->decimal('current_price', 24, 8)->nullable();
            $table->decimal('market_cap', 24, 2)->nullable();
            $table->decimal('volume_24h', 24, 2)->nullable();
            $table->decimal('price_change_24h', 10, 8)->nullable();
            $table->timestamps();

            // Add index for frequently queried columns
            $table->index('symbol');
            $table->index('name');
            $table->index('slug');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cryptocurrencies');
    }
};
