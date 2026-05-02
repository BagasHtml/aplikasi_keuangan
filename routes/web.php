<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionsController;

Route::get('/', [TransactionsController::class, 'index']);
Route::post('/store', [TransactionsController::class, 'store']);
Route::put('/update/{id}', [TransactionsController::class, 'update'])->name('transactions.update'); // Tambahan baru
Route::delete('/delete/{id}', [TransactionsController::class, 'destroy']);
Route::get('/export', [TransactionsController::class, 'export'])->name('transactions.export');