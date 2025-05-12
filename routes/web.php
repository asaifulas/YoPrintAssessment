<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UploadController::class, 'index']);
Route::post('/', [UploadController::class, 'store'])->name('upload');
