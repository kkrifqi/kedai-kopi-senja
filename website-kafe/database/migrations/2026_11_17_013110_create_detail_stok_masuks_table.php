
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_stok_masuks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stok_masuk_id')->constrained('stok_masuks')->restrictOnDelete();

            $table->foreignId('bahan_id')->constrained('bahans')->restrictOnDelete();

            $table->decimal('jumlah', 12, 3);

            $table->timestamps();

            $table->unique(['stok_masuk_id', 'bahan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_stok_masuks');
    }
};
