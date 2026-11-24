<x-filament-panels::page>
    <x-filament::section>
        {{-- Filter Tanggal --}}
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

        {{-- Tabel Penjualan per Menu --}}
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
    </x-filament::section>
</x-filament-panels::page>