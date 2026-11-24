<x-filament-panels::page>
    {{-- Tombol Tab --}}
    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
        <x-filament::button
            type="button"
            wire:click="gantiTab('penjualan')"
            :color="$tabAktif === 'penjualan' ? 'warning' : 'gray'"
        >
            Penjualan per Menu
        </x-filament::button>

        <x-filament::button
            type="button"
            wire:click="gantiTab('bahan_menipis')"
            :color="$tabAktif === 'bahan_menipis' ? 'warning' : 'gray'"
        >
            Bahan Menipis
        </x-filament::button>

        <x-filament::button
            type="button"
            wire:click="gantiTab('margin')"
            :color="$tabAktif === 'margin' ? 'warning' : 'gray'"
        >
            Margin per Menu
        </x-filament::button>
    </div>

    <x-filament::section>
        {{-- TAB 1: PENJUALAN PER MENU --}}
        @if ($tabAktif === 'penjualan')
            <form wire:submit="tampilkanLaporan" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="display: block; font-size: 12px; margin-bottom: 4px; opacity: 0.75;">Dari</label>
                    <input
                        type="date"
                        wire:model="inputDari"
                        class="fi-input rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm"
                    >
                </div>

                <div>
                    <label style="display: block; font-size: 12px; margin-bottom: 4px; opacity: 0.75;">Sampai</label>
                    <input
                        type="date"
                        wire:model="inputSampai"
                        class="fi-input rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm"
                    >
                </div>

                <x-filament::button type="submit" color="warning">
                    Tampilkan
                </x-filament::button>
            </form>

            @php
                $daftarPenjualan = $this->dataPenjualan;
                $pendapatanTertinggi = (float) ($daftarPenjualan->max('pendapatan') ?? 0);
            @endphp

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(156, 163, 175, 0.3); font-size: 12px; text-transform: uppercase; opacity: 0.75;">
                            <th style="padding: 10px 8px; text-align: left;">Menu</th>
                            <th style="padding: 10px 8px; text-align: right; width: 110px;">Terjual</th>
                            <th style="padding: 10px 8px; text-align: right; width: 160px;">Pendapatan</th>
                            <th style="padding: 10px 8px 10px 24px; text-align: left; width: 280px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftarPenjualan as $baris)
                            @php
                                $persenLebar = $pendapatanTertinggi > 0
                                    ? round(((float) $baris->pendapatan / $pendapatanTertinggi) * 100, 1)
                                    : 0;
                            @endphp
                            <tr style="border-bottom: 1px solid rgba(156, 163, 175, 0.15);">
                                <td style="padding: 14px 8px; font-weight: 500;">
                                    {{ $baris->nama }}
                                </td>
                                <td style="padding: 14px 8px; text-align: right;">
                                    {{ number_format((int) $baris->terjual, 0, ',', '.') }}
                                </td>
                                <td style="padding: 14px 8px; text-align: right; font-weight: 500;">
                                    Rp {{ number_format((float) $baris->pendapatan, 0, ',', '.') }}
                                </td>
                                <td style="padding: 14px 8px 14px 24px;">
                                    <div style="width: 100%; height: 14px; border-radius: 4px; background: rgba(156, 163, 175, 0.2); overflow: hidden;">
                                        <div style="height: 100%; border-radius: 4px; background: #c26d2d; width: {{ $persenLebar }}%;"></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 32px 8px; text-align: center; opacity: 0.65;">
                                    Belum ada data penjualan pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        {{-- TAB 2: BAHAN MENIPIS --}}
        @elseif ($tabAktif === 'bahan_menipis')
            @php $daftarMenipis = $this->dataBahanMenipis; @endphp

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(156, 163, 175, 0.3); font-size: 12px; text-transform: uppercase; opacity: 0.75;">
                            <th style="padding: 10px 8px; text-align: left; width: 120px;">Kode</th>
                            <th style="padding: 10px 8px; text-align: left;">Nama Bahan</th>
                            <th style="padding: 10px 8px; text-align: right; width: 160px;">Stok Sekarang</th>
                            <th style="padding: 10px 8px; text-align: right; width: 160px;">Batas Minimum</th>
                            <th style="padding: 10px 8px; text-align: center; width: 120px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftarMenipis as $bahan)
                            <tr style="border-bottom: 1px solid rgba(156, 163, 175, 0.15);">
                                <td style="padding: 14px 8px; font-family: monospace; opacity: 0.75;">
                                    {{ $bahan->kode }}
                                </td>
                                <td style="padding: 14px 8px; font-weight: 500;">
                                    {{ $bahan->nama }}
                                </td>
                                <td style="padding: 14px 8px; text-align: right; font-weight: 600; color: #ef4444;">
                                    {{ rtrim(rtrim(number_format((float) $bahan->stok_sekarang, 3, ',', '.'), '0'), ',') }}
                                    {{ $bahan->satuan_dasar }}
                                </td>
                                <td style="padding: 14px 8px; text-align: right;">
                                    {{ rtrim(rtrim(number_format((float) $bahan->batas_minimum, 3, ',', '.'), '0'), ',') }}
                                    {{ $bahan->satuan_dasar }}
                                </td>
                                <td style="padding: 14px 8px; text-align: center;">
                                    @if ((float) $bahan->stok_sekarang <= 0)
                                        <x-filament::badge color="danger">Habis</x-filament::badge>
                                    @else
                                        <x-filament::badge color="warning">Menipis</x-filament::badge>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 32px 8px; text-align: center; opacity: 0.65;">
                                    Semua stok bahan masih di atas batas minimum.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        {{-- TAB 3: MARGIN PER MENU --}}
        @elseif ($tabAktif === 'margin')
            @php $daftarMargin = $this->dataMarginMenu; @endphp

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(156, 163, 175, 0.3); font-size: 12px; text-transform: uppercase; opacity: 0.75;">
                            <th style="padding: 10px 8px; text-align: left;">Menu</th>
                            <th style="padding: 10px 8px; text-align: right; width: 160px;">Harga Jual</th>
                            <th style="padding: 10px 8px; text-align: right; width: 160px;">HPP</th>
                            <th style="padding: 10px 8px; text-align: right; width: 160px;">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftarMargin as $item)
                            <tr style="border-bottom: 1px solid rgba(156, 163, 175, 0.15);">
                                <td style="padding: 14px 8px; font-weight: 500;">
                                    <span>{{ $item['nama'] }}</span>
                                    @if (! $item['punya_resep'])
                                        <span style="margin-left: 8px;">
                                            <x-filament::badge color="gray">Belum ada resep</x-filament::badge>
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 14px 8px; text-align: right;">
                                    Rp {{ number_format($item['harga_jual'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 14px 8px; text-align: right; opacity: 0.85;">
                                    Rp {{ number_format($item['hpp'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 14px 8px; text-align: right; font-weight: 600; color: {{ $item['margin'] < 0 ? '#ef4444' : '#22c55e' }};">
                                    Rp {{ number_format($item['margin'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 32px 8px; text-align: center; opacity: 0.65;">
                                    Belum ada data menu.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>