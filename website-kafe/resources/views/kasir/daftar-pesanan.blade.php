<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-100 min-h-screen">
    <nav class="bg-white border-b border-stone-200 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-6">
            <span class="font-bold text-lg text-stone-800">POS Kasir — Kedai Kopi Senja</span>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('kasir.dashboard') }}" class="text-stone-600 hover:text-amber-700 font-medium">Buat Pesanan</a>
                <a href="{{ route('kasir.pesanan.index') }}" class="text-amber-700 font-semibold border-b-2 border-amber-700 pb-0.5">Daftar Pesanan</a>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-stone-600">{{ auth()->user()->name }} (Kasir)</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:underline">Logout</button>
            </form>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
            <h1 class="text-xl font-bold text-stone-800">Daftar Pesanan</h1>

            {{-- Filter Status --}}
            <div class="flex flex-wrap gap-2 text-sm">
                @php
                    $opsiStatus = [null => 'Semua', 'Baru' => 'Baru', 'Diproses' => 'Diproses', 'Selesai' => 'Selesai', 'Dibatalkan' => 'Dibatalkan'];
                @endphp
                @foreach ($opsiStatus as $nilai => $label)
                    <a href="{{ route('kasir.pesanan.index', $nilai ? ['status' => $nilai] : []) }}"
                       class="px-3 py-1.5 rounded-lg font-medium border transition
                           {{ $filterStatus === $nilai ? 'bg-amber-700 text-white border-amber-700' : 'bg-white text-stone-700 border-stone-300 hover:bg-stone-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if (session('sukses'))
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-300 p-4 text-sm text-emerald-800">
                {{ session('sukses') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-300 p-4 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-stone-200 overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50 border-b border-stone-200 text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Pesanan</th>
                        <th class="px-4 py-3">Meja / Sumber</th>
                        <th class="px-4 py-3">Item Menu</th>
                        <th class="px-4 py-3">Total & Bayar</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($daftarPesanan as $pesanan)
                        <tr class="hover:bg-stone-50 align-top">
                            <td class="px-4 py-3">
                                <div class="font-bold text-stone-800">#{{ $pesanan->id }}</div>
                                <div class="text-xs text-stone-500">{{ $pesanan->tanggal_pesanan->format('d/m/Y H:i') }}</div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-medium text-stone-800">Meja {{ $pesanan->no_meja }}</div>
                                <div class="text-xs text-stone-500">
                                    {{ $pesanan->pelanggan ? 'APK: ' . $pesanan->pelanggan->name : 'Kasir: ' . ($pesanan->kasir->name ?? '-') }}
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <ul class="space-y-0.5 text-stone-700">
                                    @foreach ($pesanan->detailPesanans as $detail)
                                        <li>{{ $detail->jumlah_porsi }}x {{ $detail->menu->nama ?? 'Menu dihapus' }}</li>
                                    @endforeach
                                </ul>
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-semibold text-stone-800">
                                    Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-stone-500">{{ $pesanan->metode_bayar }}</div>
                            </td>

                            <td class="px-4 py-3">
                                @php
                                    $warnaBadge = match ($pesanan->status) {
                                        'Baru' => 'bg-blue-100 text-blue-800',
                                        'Diproses' => 'bg-amber-100 text-amber-800',
                                        'Selesai' => 'bg-emerald-100 text-emerald-800',
                                        'Dibatalkan' => 'bg-red-100 text-red-800',
                                        default => 'bg-stone-100 text-stone-800',
                                    };
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $warnaBadge }}">
                                    {{ $pesanan->status }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($pesanan->status === 'Baru')
                                        <form method="POST" action="{{ route('kasir.pesanan.status', $pesanan->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="Diproses">
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold">
                                                Proses
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('kasir.pesanan.batal', $pesanan->id) }}"
                                              onsubmit="return confirm('Batalkan pesanan #{{ $pesanan->id }} dan kembalikan stok bahan?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-semibold">
                                                Batalkan
                                            </button>
                                        </form>
                                    @elseif ($pesanan->status === 'Diproses')
                                        <form method="POST" action="{{ route('kasir.pesanan.status', $pesanan->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="Selesai">
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">
                                                Selesai
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('kasir.pesanan.struk', $pesanan->id) }}"
                                       class="px-3 py-1.5 rounded-lg border border-stone-300 text-stone-700 hover:bg-stone-100 text-xs font-semibold">
                                        Struk
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-stone-500">
                                Belum ada pesanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $daftarPesanan->links() }}
        </div>
    </main>
</body>
</html>