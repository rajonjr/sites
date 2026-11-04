<?php

use App\Livewire\User\Dashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        /*Route::get('/profile', UserProfile::class)->name('profile');*/
    });