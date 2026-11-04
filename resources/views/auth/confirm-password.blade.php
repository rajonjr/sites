{{-- <x-layouts::guest>
    <div class="w-full flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
            <div
                class="w-16 h-16 mx-auto rounded-2xl bg-brand-950 text-brand-400 flex items-center justify-center text-2xl border border-brand-800/50">
                <i class="fa-solid fa-log"></i>
            </div>

            <h2 class="text-2xl font-bold text-white text-center">Zone sécurisée</h2>

            <p class="text-xs text-slate-400 text-center leading-relaxed">
                Il s'agit d'une zone sécurisée de l'application. Veuillez confirmer votre mot de passe avant de
                continuer.
            </p>

            <form action="{{ route('password.confirm') }}" method="post" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Mot de passe</label>
                    <input type="password" name="password" required autofocus
                        class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                    @error('password') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <button type="submit"
                    class="w-full py-3.5 bg-linear-to-r from-brand-600 to-brand-500 text-white font-semibold rounded-xl shadow-lg shadow-brand-600/30 transition">
                    Confirmer
                </button>
            </form>
        </div>
    </div>
</x-layouts::guest> --}}

@extends('layouts.guest')

@section('title', 'Confirmer le mot de passe')

@section('content')
@php
    $to   = fn (string $name) => Route::has($name) ? route($name) : '#';
    $user = auth()->user();

    // Fortify : POST /user/confirm-password (la route GET porte le nom « password.confirm »)
    $action = Route::has('password.confirm.store') ? route('password.confirm.store') : (Route::has('password.confirm') ? route('password.confirm') : url('/user/confirm-password'));

    // Durée pendant laquelle la confirmation reste valable (config/auth.php, en secondes)
    $timeout = (int) config('auth.password_timeout', 10800);
    $validity = $timeout >= 3600
        ? intdiv($timeout, 3600).' heure'.(intdiv($timeout, 3600) > 1 ? 's' : '')
        : max(1, intdiv($timeout, 60)).' minute'.(intdiv($timeout, 60) > 1 ? 's' : '');

    // Page que l'utilisateur essayait d'ouvrir (mémorisée par Laravel avant la redirection)
    $intended = session('url.intended');
    $intendedPath = is_string($intended) ? parse_url($intended, PHP_URL_PATH) : null;

    $field = 'w-full rounded-2xl border bg-night-900 px-4 py-3.5 pr-14 text-stone-100 placeholder:text-stone-500 transition focus:outline-none focus:ring-2';
    $ok    = 'border-gold-500/30 focus:border-gold-500 focus:ring-gold-500/30';
    $bad   = 'border-rose-400/70 focus:border-rose-400 focus:ring-rose-400/30';

    $eye    = 'M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z';
    $eyeOff = 'M3 3l18 18M10.6 6.1A10 10 0 0112 6c6.4 0 10 6 10 6a17 17 0 01-3.2 3.9M6.6 6.7A17 17 0 002 12s3.6 7 10 7a10 10 0 004.2-.9M9.9 9.9a3 3 0 004.2 4.2';
@endphp

<section class="mx-auto flex min-h-[calc(100vh-14rem)] max-w-md items-center px-4 pb-16 pt-6 sm:px-6" aria-labelledby="confirm-titre">
    <div class="w-full animate-rise">

        {{-- La carte « secoue » quand le mot de passe est refusé --}}
        <div class="rounded-3xl border border-gold-500/25 bg-night-900/70 p-7 text-center shadow-2xl shadow-black/40 backdrop-blur-xl sm:p-10 {{ $errors->has('password') ? 'animate-shake' : '' }}"
             x-data="{
                 pw: '',
                 show: false,
                 focused: false,
                 caps: false,
                 sending: false,
                 err: '',
                 get open() { return this.focused || this.pw.length > 0; },
                 check(e) {
                     this.err = '';
                     if (!this.pw) { e.preventDefault(); this.err = 'Saisissez votre mot de passe pour continuer.'; return; }
                     this.sending = true;
                 }
             }">

            {{-- Cadenas : l'anse se soulève pendant la saisie --}}
            <span class="relative mx-auto grid size-20 place-items-center rounded-full border bg-gold-500/10 text-gold-500 transition-colors duration-500"
                  :class="open ? 'border-gold-400' : 'border-gold-500/50'">
                <span class="absolute inset-0 animate-ping-slow rounded-full border border-gold-500/40"></span>
                <svg class="size-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M8 11V7a4 4 0 018 0v4" class="transition-transform duration-500 ease-out" :style="open ? 'transform: translateY(-2.5px)' : ''"/>
                    <rect x="5" y="11" width="14" height="10" rx="2"/>
                    <circle cx="12" cy="16" r="1.2" fill="currentColor" class="transition-opacity duration-300" :class="sending ? 'animate-pulse' : ''"/>
                </svg>
            </span>

            <h1 id="confirm-titre" class="mt-6 font-display text-4xl font-semibold text-gold-500">Zone sécurisée</h1>
            <p class="mt-3 text-sm leading-relaxed text-stone-400">
                Pour continuer, confirmez votre mot de passe. Vous n’aurez pas à le saisir de nouveau pendant {{ $validity }}.
            </p>

            @if ($intendedPath && $intendedPath !== '/')
                <p class="mx-auto mt-4 inline-block max-w-full truncate rounded-full border border-gold-500/20 px-3 py-1 text-xs text-stone-500">
                    Page demandée : <code class="text-gold-300">{{ $intendedPath }}</code>
                </p>
            @endif

            <p class="mt-5 text-xs text-stone-500">Connecté en tant que <span class="text-stone-300">{{ $user->email }}</span></p>

            <form method="POST" action="{{ $action }}" novalidate class="mt-6 space-y-5 text-left" x-on:submit="check($event)">
                @csrf

                <div>
                    <label for="confirm-password" class="mb-1.5 block text-sm text-stone-300">Mot de passe</label>
                    <div class="relative">
                        <input id="confirm-password" name="password" required autofocus autocomplete="current-password" x-model="pw"
                               x-bind:type="show ? 'text' : 'password'"
                               x-on:focus="focused = true" x-on:blur="focused = false; caps = false"
                               x-on:keydown="caps = $event.getModifierState && $event.getModifierState('CapsLock')"
                               x-on:keyup="caps = $event.getModifierState && $event.getModifierState('CapsLock')"
                               @error('password') aria-invalid="true" aria-describedby="confirm-error" @enderror
                               class="{{ $field }} {{ $errors->has('password') ? $bad : $ok }}" placeholder="Votre mot de passe">

                        <button type="button" x-on:click="show = !show" x-bind:aria-pressed="show"
                                x-bind:aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                                class="absolute right-2 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full text-gold-500 transition hover:bg-gold-500/10 active:scale-90">
                            <svg x-show="!show" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $eye }}"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="show" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $eyeOff }}"/></svg>
                        </button>
                    </div>

                    <p x-show="caps" x-cloak x-transition class="mt-2 text-xs text-gold-300" role="status">Les majuscules sont activées.</p>
                    <p x-show="err" x-cloak x-text="err" class="mt-1.5 text-sm text-rose-300" role="alert"></p>
                    @error('password') <p id="confirm-error" class="mt-1.5 animate-rise text-sm text-rose-300" role="alert">{{ $message }}</p> @enderror
                </div>

                <button type="submit" x-bind:disabled="sending"
                        class="relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition duration-300
                               hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30 active:scale-95 disabled:cursor-wait disabled:opacity-70
                               before:absolute before:inset-0 before:-translate-x-full before:skew-x-12 before:bg-white/30 before:transition-transform before:duration-700 hover:before:translate-x-full">
                    <svg x-show="sending" x-cloak class="relative size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    <span class="relative" x-text="sending ? 'Vérification…' : 'Confirmer'"></span>
                </button>
            </form>

            <div class="mt-6 flex flex-col items-center justify-between gap-3 text-sm sm:flex-row">
                <a href="{{ $to('dashboard') !== '#' ? $to('dashboard') : url('/') }}" wire:navigate class="group inline-flex items-center gap-2 text-stone-400 transition hover:text-gold-500">
                    <svg class="size-4 transition-transform duration-300 group-hover:-translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.56l3.97 3.97a.75.75 0 11-1.06 1.06l-5.25-5.25a.75.75 0 010-1.06l5.25-5.25a.75.75 0 111.06 1.06L5.56 9.25h10.69A.75.75 0 0117 10z" clip-rule="evenodd"/></svg>
                    Annuler
                </a>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate class="text-gold-500 transition hover:text-gold-300 hover:underline">Mot de passe oublié ?</a>
                @endif
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-stone-500">
            Cette étape protège votre compte lors d’actions sensibles.
        </p>
    </div>
</section>
@endsection