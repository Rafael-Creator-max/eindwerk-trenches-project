<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_crypto_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('cryptocurrency_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure a user can only follow a cryptocurrency once
            $table->unique(['user_id', 'cryptocurrency_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_crypto_follows');
    }
};
