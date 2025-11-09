<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::get('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::get('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

Route::group(['prefix' => 'app', 'middleware' => 'check.auth'], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('app.home');
    Route::get('/todos/{todo_id}', [HomeController::class, 'todoDetail'])->name('app.todos.detail');
});

Route::get('/', function () {
    return redirect()->route('app.home');
});