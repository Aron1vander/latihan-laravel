<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\AuthLoginLivewire;
use App\Livewire\AuthRegisterLivewire;
use App\Http\Controllers\AuthController;
use App\Livewire\FinanceIndexLivewire; // Import komponen baru

// --- Rute Autentikasi ---
Route::middleware(['guest'])->group(function () {
    Route::get('/login', AuthLoginLivewire::class)->name('auth.login');
    Route::get('/register', AuthRegisterLivewire::class)->name('auth.register');
});

// --- Rute Aplikasi (Membutuhkan Login) ---
Route::middleware(['auth'])->group(function () {
    // Ubah rute utama dari HomeLivewire ke FinanceIndexLivewire
    Route::get('/', FinanceIndexLivewire::class)->name('finance.index');
    
    // Hapus rute Todo yang lama
    // Route::get('/todo/{todo}', TodoDetailLivewire::class)->name('todo.detail');

    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

// Redirect default ke halaman login
Route::redirect('/auth/login', '/login');