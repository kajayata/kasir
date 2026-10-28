<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [TransaksiController::class, 'index'])->name('home');

    Route::get('/kasir', [KasirController::class, 'index'])->name('kasir');
    Route::get('/kasir/api/products', [KasirController::class, 'search'])->name('kasir.search');

    Route::resource('kasir/produk', ProdukController::class)
        ->except(['show'])
        ->names('kasir.produk');

    Route::post('/kasir/transaksi', [TransaksiController::class, 'store'])->name('kasir.store');
    Route::post('/kasir/transaksi/hold', [TransaksiController::class, 'hold'])->name('kasir.hold');
    Route::post('/kasir/transaksi/{transaksi}/resume', [TransaksiController::class, 'resume'])->name('kasir.resume');
    Route::post('/kasir/transaksi/{transaksi}/cancel', [TransaksiController::class, 'cancel'])->name('kasir.cancel');
    Route::get('/kasir/transaksi/{transaksi}', [TransaksiController::class, 'detail'])->name('kasir.detail');
    Route::get('/kasir/transaksi/{transaksi}/struk', [TransaksiController::class, 'struk'])->name('kasir.struk');
    Route::get('/kasir/transaksi/{transaksi}/invoice', [TransaksiController::class, 'invoice'])->name('kasir.invoice');

    Route::get('/kasir/ditahan', [TransaksiController::class, 'ditahan'])->name('kasir.ditahan');
    Route::get('/kasir/riwayat', [TransaksiController::class, 'riwayat'])->name('kasir.riwayat');
});
