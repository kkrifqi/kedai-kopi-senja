<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan #{{ $pesanan->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-100 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-6">
        @if (session('sukses'))
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800">
                {{ session('sukses') }}
            </div>
        @endif

        <div class="text-center border-b border-dashed border-stone-300 pb-4 mb-4">
            <h1 class="text-xl font-bold text-stone-800">Kedai Kopi Senja</h1>
            <p class="text-xs text-stone-500 mt-1">
                Pesanan #{{ $pesanan->id }} • {{ $pesanan->tanggal_pesanan->format('d/m/Y H:i') }}
            </p>
            <p class="text-xs text-stone-600 mt-1">
                Meja: <strong>{{ $pesanan->no_meja }}</strong> |
                Bayar: <strong>{{ $pesanan->metode_bayar }}</strong> |
                Status: <strong>{{ $pesanan->status }}</strong>
            </p>
        </div>

        <div class="space-y-2 text-sm border-b border-dashed border-stone-300 pb-4 mb-4">
            @foreach ($pesanan->detailPesanans as $detail)
                <div class="flex justify-between">
                    <div>
                        <div class="font-medium text-stone-800">{{ $detail->menu->nama }}</div>
                        <div class="text-xs text-stone-500">
                            {{ $detail->jumlah_porsi }} x Rp {{ number_format($detail->harga_satuan_terekam, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="font-medium text-stone-800">
                        Rp {{ number_format($detail->jumlah_porsi * $detail->harga_satuan_terekam, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="space-y-1.5 text-sm">
            <div class="flex justify-between">
                <span class="text-stone-600">Subtotal</span>
                <span>Rp {{ number_format($pesanan->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-stone-600">Service Charge (5%)</span>
                <span>Rp {{ number_format($pesanan->service, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-stone-600">Pajak (10%)</span>
                <span>Rp {{ number_format($pesanan->pajak, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-base font-bold text-stone-900 border-t border-stone-200 pt-2">
                <span>Total</span>
                <span>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <a href="{{ route('kasir.dashboard') }}" class="flex-1 text-center py-2 rounded-lg bg-amber-700 hover:bg-amber-800 text-white text-sm font-semibold">
                Kembali ke POS
            </a>
            <button onclick="window.print()" class="px-4 py-2 rounded-lg border border-stone-300 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                Cetak
            </button>
        </div>
    </div>
</body>
</html>