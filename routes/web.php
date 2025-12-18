<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;


// Trang chủ
Route::get('/', [ProductController::class,'products'])->name('home');
//admin
Route::get('/trang-chu/login',[AdminController::class, 'login'])->name('admin.login');
Route::post('/trang-chu/login',[AdminController::class, 'handleLogin'])->name('admin.handleLogin');

Route::middleware(['admin.check'])->group(function () {
    Route::prefix('trang-chu')->group(function () {
        Route::get('/',[AdminController::class,'index'])->name('admin.home');
    });
});