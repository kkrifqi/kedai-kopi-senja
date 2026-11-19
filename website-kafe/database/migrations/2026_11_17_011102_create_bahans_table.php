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
        Schema::create('bahans', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama')->unique();

            $table->unsignedBigInteger('stok_sekarang')->default(0);
            $table->enum('satuan_dasar', ['g', 'ml', 'pcs']);
            $table->unsignedBigInteger('batas_minimum')->default(0);

            $table->decimal('harga_sekarang', 12, 2)->default(0);
            $table->date('tanggal_update_harga')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahans');
    }
};
