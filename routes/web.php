<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BkashController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/create', function () {
    return view('create');
})->name('bkash.create');


Route::post('/bkash/checkout', [BkashController::class, 'checkout'])->name('bkash.checkout');
Route::get('/bkash/callback', [BkashController::class, 'callback'])->name('bkash.callback');
Route::get('/bkash/query/{paymentId}', [BkashController::class, 'query'])->name('bkash.query');
Route::post('/bkash/refund', [BkashController::class, 'refund'])->name('bkash.refund');
Route::get('/bkash/success', [BkashController::class, 'success'])->name('bkash.success');
Route::get('/bkash/fail', [BkashController::class, 'failure'])->name('bkash.fail');
Route::get('/bkash/cancel', [BkashController::class, 'cancel'])->name('bkash.cancel');
