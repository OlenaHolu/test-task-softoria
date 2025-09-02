<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SerpController;

Route::get('/', [SerpController::class, 'index'])->name('serp.index');

Route::post('/api/search', [SerpController::class, 'search'])
    ->middleware('throttle:10,1') 
    ->name('serp.search');
