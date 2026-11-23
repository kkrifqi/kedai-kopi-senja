<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Kasir - Kedai Kopi Senja</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-100 min-h-screen">
    <nav class="bg-white border-b border-stone-200 px-6 py-4 flex items-center justify-between">
        <div class="font-bold text-lg text-stone-800">Kopi Senja</div>
        <a href="{{ route('kasir.pesanan.index') }}">Daftar Pesanan</a>
        <div class="flex items-center gap-4">
            <span class="text-sm text-stone-600">{{ auth()->user()->name }} (Kasir)</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:underline">Logout</button>
            </form>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6">
        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-300 p-4 text-sm text-red-800">
                <p class="font-semibold mb-1">Pesanan ditolak:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $galat)
                        <li>{{ $galat }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Kolom Kiri: Daftar Menu --}}
            <div class="lg:col-span-2">
                <h2 class="text-lg font-bold text-stone-800 mb-3">Daftar Menu</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach ($daftarMenu as $menu)
                        <div class="bg-white rounded-xl border border-stone-200 p-4 flex flex-col justify-between {{ ! $menu['tersedia'] ? 'opacity-60' : '' }}">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-semibold text-stone-800">{{ $menu['nama'] }}</h3>
                                    @if (! $menu['punya_resep'])
                                        <span class="text-xs px-2 py-0.5 rounded bg-stone-200 text-stone-700">Belum Ada Resep</span>
                                    @elseif (! $menu['tersedia'])
                                        <span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-700 font-medium">Habis</span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">Tersedia</span>
                                    @endif
                                </div>
                                <p class="text-amber-700 font-bold mt-2">
                                    Rp {{ number_format($menu['harga_jual'], 0, ',', '.') }}
                                </p>
                            </div>

                            <button type="button"
                                onclick="tambahKeKeranjang({{ $menu['id'] }}, @js($menu['nama']), {{ $menu['harga_jual'] }})"
                                {{ ! $menu['tersedia'] ? 'disabled' : '' }}
                                class="mt-4 w-full py-2 rounded-lg text-sm font-semibold transition
                                    {{ $menu['tersedia'] ? 'bg-amber-700 hover:bg-amber-800 text-white' : 'bg-stone-200 text-stone-400 cursor-not-allowed' }}">
                                {{ $menu['tersedia'] ? '+ Tambah' : 'Tidak Tersedia' }}
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Kolom Kanan: Keranjang & Checkout --}}
            <div class="bg-white rounded-xl border border-stone-200 p-5 h-fit sticky top-6">
                <h2 class="text-lg font-bold text-stone-800 mb-4">Pesanan Baru</h2>

                <form method="POST" action="{{ route('kasir.pesanan.simpan') }}" id="formPesanan">
                    @csrf

                    <div id="daftarKeranjang" class="space-y-3 mb-4 min-h-[80px] text-sm text-stone-500">
                        Belum ada menu yang dipilih.
                    </div>

                    <div id="inputTersembunyi"></div>

                    <div class="border-t border-stone-200 pt-3 space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-stone-600">Subtotal</span>
                            <span id="teksSubtotal" class="font-medium">Rp 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-stone-600">Service Charge (5%)</span>
                            <span id="teksService" class="font-medium">Rp 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-stone-600">Pajak (10%)</span>
                            <span id="teksPajak" class="font-medium">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-stone-900 border-t border-stone-200 pt-2">
                            <span>Total</span>
                            <span id="teksTotal">Rp 0</span>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Nomor Meja (1–10)</label>
                            <input type="number" name="no_meja" min="1" max="10" value="{{ old('no_meja', 1) }}" required
                                class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Metode Bayar</label>
                            <select name="metode_bayar" required class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
                                <option value="Tunai">Tunai</option>
                                <option value="QRIS">QRIS</option>
                            </select>
                        </div>

                        <button type="submit" id="tombolSimpan" disabled
                            class="w-full py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:bg-stone-300 text-white font-semibold transition">
                            Buat Pesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        const keranjang = {};

        function formatRupiah(angka) {
            return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
        }

        function tambahKeKeranjang(id, nama, harga) {
            if (keranjang[id]) {
                keranjang[id].jumlah += 1;
            } else {
                keranjang[id] = { id, nama, harga, jumlah: 1 };
            }
            renderKeranjang();
        }

        function ubahJumlah(id, selisih) {
            if (!keranjang[id]) return;
            keranjang[id].jumlah += selisih;
            if (keranjang[id].jumlah <= 0) {
                delete keranjang[id];
            }
            renderKeranjang();
        }

        function renderKeranjang() {
            const wadah = document.getElementById('daftarKeranjang');
            const wadahInput = document.getElementById('inputTersembunyi');
            const tombolSimpan = document.getElementById('tombolSimpan');

            const daftar = Object.values(keranjang);
            wadahInput.innerHTML = '';

            if (daftar.length === 0) {
                wadah.innerHTML = 'Belum ada menu yang dipilih.';
                tombolSimpan.disabled = true;
                perbaruiRingkasan(0);
                return;
            }

            let html = '';
            let subtotal = 0;

            daftar.forEach((item, indeks) => {
                subtotal += item.harga * item.jumlah;
                html += `
                    <div class="flex items-center justify-between gap-2 bg-stone-50 p-2.5 rounded-lg">
                        <div>
                            <div class="font-medium text-stone-800">${item.nama}</div>
                            <div class="text-xs text-stone-500">${formatRupiah(item.harga)}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="ubahJumlah(${item.id}, -1)" class="w-7 h-7 rounded bg-stone-200 hover:bg-stone-300 font-bold">-</button>
                            <span class="w-6 text-center font-semibold text-stone-800">${item.jumlah}</span>
                            <button type="button" onclick="ubahJumlah(${item.id}, 1)" class="w-7 h-7 rounded bg-stone-200 hover:bg-stone-300 font-bold">+</button>
                        </div>
                    </div>
                `;

                wadahInput.innerHTML += `
                    <input type="hidden" name="items[${indeks}][menu_id]" value="${item.id}">
                    <input type="hidden" name="items[${indeks}][jumlah_porsi]" value="${item.jumlah}">
                `;
            });

            wadah.innerHTML = html;
            tombolSimpan.disabled = false;
            perbaruiRingkasan(subtotal);
        }

        function perbaruiRingkasan(subtotal) {
            const service = subtotal * 0.05;
            const pajak = (subtotal + service) * 0.10;
            const total = subtotal + service + pajak;

            document.getElementById('teksSubtotal').textContent = formatRupiah(subtotal);
            document.getElementById('teksService').textContent = formatRupiah(service);
            document.getElementById('teksPajak').textContent = formatRupiah(pajak);
            document.getElementById('teksTotal').textContent = formatRupiah(total);
        }
    </script>
</body>
</html>