<?php

use App\Livewire\About;
use App\Livewire\Choreography;
use App\Livewire\Contact;
use App\Livewire\Event;
use App\Livewire\Prayer;
use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class)->name('home');
Route::get('/choreographies', Choreography::class)->name('choreographies');
Route::get('/evenements', Event::class)->name('events');
Route::get('/prieres', Prayer::class)->name('prayer');
Route::get('/apropos', About::class)->name('about');
Route::get('/contact', Contact::class)->name('contact');