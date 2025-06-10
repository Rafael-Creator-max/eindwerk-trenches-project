<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            });


        DB::table('asset_types')->insert([
            [
            'name' => 'Cryptocurrency',
            'description' => 'Major digital currencies like Bitcoin and Ethereum',
            ],
            [
            'name' => 'Altcoin',
            'description' => 'Alternative coins to Bitcoin, often with unique use cases',
            ],
            [
            'name' => 'Memecoin',
            'description' => 'Joke or community-driven coins with viral popularity',
            ],
       ]);
    }

    public function down()
    {
        Schema::dropIfExists('asset_types');
    }
};
