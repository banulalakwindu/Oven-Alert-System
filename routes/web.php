<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TemperatureController;
use App\Http\Controllers\LogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LogController::class, 'welcome'])->name('welcome');
Route::get('/log-view', [LogController::class, 'viewLog'])->name('view.log');

Route::get('/temperature', [TemperatureController::class, 'getTemp'])->name('get.temp');
Route::get('/cutoff', [TemperatureController::class, 'cutoff'])->name('cutoff');
Route::get('/poweron', [TemperatureController::class, 'poweron'])->name('power.on');
//Route::get('/log', [LogController::class, 'store'])->name('log');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
