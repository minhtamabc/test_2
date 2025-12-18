<?php

use Illuminate\Support\Facades\Route;


// Trang chủ
Route::get('/', [ProductController::class,'products'])->name('home');
