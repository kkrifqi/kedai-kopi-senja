<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Kasir\PesananController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'tampilLogin'])->name('login');
Route::post('/login', [AuthController::class, 'prosesLogin'])->name('login.proses');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/ganti-password', [AuthController::class, 'tampilGantiPassword'])->name('password.ganti');
Route::post('/ganti-password', [AuthController::class, 'prosesGantiPassword'])->name('password.ganti.proses');

// Grup halaman khusus Kasir
Route::middleware(['auth', 'peran:kasir'])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {
        Route::get('/', [PesananController::class, 'halamanPos'])->name('dashboard');
        Route::post('/pesanan', [PesananController::class, 'simpanPesanan'])->name('pesanan.simpan');
        Route::get('/pesanan', [PesananController::class, 'daftarPesanan'])->name('pesanan.index');
        Route::get('/pesanan/{pesanan}/struk', [PesananController::class, 'tampilStruk'])->name('pesanan.struk');
        Route::patch('/pesanan/{pesanan}/status', [PesananController::class, 'ubahStatus'])->name('pesanan.status');
        Route::patch('/pesanan/{pesanan}/batal', [PesananController::class, 'batalkan'])->name('pesanan.batal');
    });