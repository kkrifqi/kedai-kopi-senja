
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pelanggan_id')->nullable()->constrained('users')->restrictOnDelete();

            $table->foreignId('kasir_id')->nullable()->constrained('users')->restrictOnDelete();

            $table->unsignedTinyInteger('no_meja');

            $table->enum('metode_bayar', ['Tunai', 'QRIS']);

            $table->enum('status', [
                'Baru',
                'Diproses',
                'Selesai',
                'Dibatalkan',
            ])->default('Baru');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('service', 12, 2)->default(0);
            $table->decimal('pajak', 12, 2)->default(0);
            $table->decimal('total_harga', 12, 2)->default(0);

            $table->dateTime('tanggal_pesanan');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanans');
    }
};
