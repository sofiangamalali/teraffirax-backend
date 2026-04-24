<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedirectController;

Route::get('/{slug}', RedirectController::class)->name('redirect');
