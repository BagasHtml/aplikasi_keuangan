<?php

use App\Http\Controllers\TransactionsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TransactionsController::class, 'index']);
Route::post('/store', [TransactionsController::class, 'store']);
Route::delete('/delete/{id}', [TransactionsController::class, 'destroy']);
Route::get('/export', [TransactionsController::class, 'export']);
Route::put('/update/{id}', [TransactionsController::class, 'update']);