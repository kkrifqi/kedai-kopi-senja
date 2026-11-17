
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pesanans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pesanan_id')->constrained('pesanans')->restrictOnDelete();

            $table->foreignId('menu_id')->constrained('menus')->restrictOnDelete();

            $table->unsignedInteger('jumlah_porsi');

            $table->decimal('harga_satuan_terekam', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanans');
    }
};
