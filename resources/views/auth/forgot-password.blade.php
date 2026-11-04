{{-- <x-layouts::guest>
    <div class="w-full flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
            <h2 class="text-2xl font-bold text-white text-center">Mot de passe oublié</h2>

            <p class="text-xs text-slate-400 text-center">
                Indiquez votre adresse e-mail ci-dessous et nous vous enverrons un lien de réinitialisation pour choisir
                un
                nouveau mot de passe.
            </p>

            <!-- Message de succès -->
            @if (session()->has('status'))
                <div
                    class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-xs text-center">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="post" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Adresse e-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-sm text-slate-100 focus:outline-none focus:border-brand-500">
                    @error('email') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <button type="submit"
                    class="w-full py-3.5 bg-linear-to-r from-brand-600 to-brand-500 text-white font-semibold rounded-xl shadow-lg shadow-brand-600/30 transition">
                    Envoyer le lien de réinitialisation
                </button>
            </form>

            <!-- Lien de retour vers la connexion -->
            <p class="text-center text-xs text-slate-400 mt-6">
                <a href="{{ route('login') }}"
                    class="text-brand-400 font-semibold hover:underline flex items-center justify-center space-x-1">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Retour à la connexion
                </a>
            </p>
        </div>
    </div>
</x-layouts::guest> --}}


@extends('layouts.guest')

@section('title', 'Mot de passe oublié')

@section('content')
@php
    $to = fn (string $name) => Route::has($name) ? route($name) : '#';

    // Fortify : POST /forgot-password (route « password.email »)
    $action = Route::has('password.email') ? route('password.email') : url('/forgot-password');

    // Durée de validité du lien, en minutes (config/auth.php)
    $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords', 'users').'.expire', 60);

    $field = 'w-full rounded-2xl border bg-night-900 px-4 py-3.5 text-stone-100 placeholder:text-stone-500 transition focus:outline-none focus:ring-2';
    $ok    = 'border-gold-500/30 focus:border-gold-500 focus:ring-gold-500/30';
    $bad   = 'border-rose-400/70 focus:border-rose-400 focus:ring-rose-400/30';

    // Fortify place le message de confirmation dans « status » ; on ignore les codes techniques (sans espace)
    $sent = is_string(session('status')) && str_contains(session('status'), ' ');
@endphp

<section class="mx-auto flex min-h-[calc(100vh-14rem)] max-w-md items-center px-4 pb-16 pt-6 sm:px-6" aria-labelledby="forgot-titre">
    <div class="w-full animate-rise">
        <div class="rounded-3xl border border-gold-500/25 bg-night-900/70 p-7 text-center shadow-2xl shadow-black/40 backdrop-blur-xl sm:p-10">

            @if ($sent)
                {{-- ============================ ÉTAT : LIEN ENVOYÉ ============================ --}}
                <div x-data="{
                        email: '',
                        left: 60,
                        timer: null,
                        sending: false,
                        init() {
                            try { this.email = sessionStorage.getItem('iv-reset-email') || ''; } catch (e) {}
                            this.timer = setInterval(() => { if (this.left > 0) this.left--; }, 1000);
                        },
                        get masked() {
                            const [u, d] = this.email.split('@');
                            return u && d ? u.slice(0, 1) + '•••' + '@' + d : '';
                        }
                     }"
                     x-on:livewire:navigating.window="clearInterval(timer)">

                    <span class="relative mx-auto grid size-20 place-items-center rounded-full border border-gold-500/50 bg-gold-500/10 text-gold-500">
                        <span class="absolute inset-0 animate-ping-slow rounded-full border border-gold-500/40"></span>
                        <svg class="size-9 animate-pop" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 7l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/>
                            <path d="M16 15l1.5 1.5L21 13" stroke="#34d399" style="stroke-dasharray:14;stroke-dashoffset:14;animation:draw-check .6s .4s ease-out forwards"/>
                        </svg>
                    </span>

                    <h1 id="forgot-titre" class="mt-6 font-display text-4xl font-semibold text-gold-500">Vérifiez votre boîte mail</h1>
                    <p class="mt-3 text-sm leading-relaxed text-stone-400" role="status">{{ session('status') }}</p>
                    <p class="mt-1 text-sm text-stone-300" x-show="masked" x-cloak>Envoyé à <strong class="font-medium text-stone-100" x-text="masked"></strong></p>

                    <ul class="mx-auto mt-6 max-w-xs space-y-2 text-left text-xs text-stone-500">
                        <li class="flex gap-2"><span class="text-gold-500" aria-hidden="true">•</span> Pensez à regarder dans vos courriers indésirables.</li>
                        <li class="flex gap-2"><span class="text-gold-500" aria-hidden="true">•</span> Le lien est valable {{ $expire }} minute{{ $expire > 1 ? 's' : '' }}.</li>
                    </ul>

                    {{-- Renvoi du lien, avec compte à rebours --}}
                    <form method="POST" action="{{ $action }}" class="mt-8" x-on:submit="sending = true">
                        @csrf
                        <input type="hidden" name="email" x-bind:value="email">

                        <button type="submit" x-bind:disabled="left > 0 || sending || !email"
                                class="w-full rounded-full border border-gold-500 px-6 py-3 font-medium text-gold-500 transition duration-300 hover:bg-gold-500/10 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40">
                            <span x-show="left > 0" x-cloak>Renvoyer le lien dans <span class="tabular-nums" x-text="left"></span> s</span>
                            <span x-show="left <= 0 && !sending">Renvoyer le lien</span>
                            <span x-show="sending" x-cloak>Envoi…</span>
                        </button>
                    </form>

                    @error('email') <p class="mt-3 text-sm text-rose-300" role="alert">{{ $message }}</p> @enderror

                    <a href="{{ $to('login') }}" wire:navigate class="group mt-6 inline-flex items-center gap-2 text-sm text-stone-400 transition hover:text-gold-500">
                        <svg class="size-4 transition-transform duration-300 group-hover:-translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.56l3.97 3.97a.75.75 0 11-1.06 1.06l-5.25-5.25a.75.75 0 010-1.06l5.25-5.25a.75.75 0 111.06 1.06L5.56 9.25h10.69A.75.75 0 0117 10z" clip-rule="evenodd"/></svg>
                        Retour à la connexion
                    </a>
                </div>
            @else
                {{-- ============================ ÉTAT : FORMULAIRE ============================ --}}
                <span class="relative mx-auto grid size-20 place-items-center rounded-full border border-gold-500/50 bg-gold-500/10 text-gold-500">
                    <span class="absolute inset-0 animate-ping-slow rounded-full border border-gold-500/40"></span>
                    <svg class="size-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="8" cy="15" r="4"/><path d="M10.8 12.2L20 3M16 7l3 3M14 9l2 2"/>
                    </svg>
                </span>

                <h1 id="forgot-titre" class="mt-6 font-display text-4xl font-semibold text-gold-500">Mot de passe oublié ?</h1>
                <p class="mt-3 text-sm leading-relaxed text-stone-400">Indiquez l’adresse email de votre compte : nous vous enverrons un lien pour choisir un nouveau mot de passe.</p>

                <form method="POST" action="{{ $action }}" novalidate class="mt-8 space-y-5 text-left"
                      x-data="{ email: @js(old('email', '')), sending: false, err: '' }"
                      x-on:submit="
                          err = '';
                          if (!/^\S+@\S+\.\S+$/.test(email.trim())) { $event.preventDefault(); err = 'Saisissez une adresse email valide.'; return; }
                          try { sessionStorage.setItem('iv-reset-email', email.trim()); } catch (e) {}
                          sending = true;
                      ">
                    @csrf

                    <div>
                        <label for="forgot-email" class="mb-1.5 block text-sm text-stone-300">Adresse email</label>
                        <input id="forgot-email" name="email" type="email" required autofocus autocomplete="email" inputmode="email" x-model="email"
                               placeholder="vous@exemple.com"
                               @error('email') aria-invalid="true" aria-describedby="forgot-error" @enderror
                               class="{{ $field }} {{ $errors->has('email') ? $bad : $ok }}">

                        <p x-show="err" x-cloak x-text="err" class="mt-1.5 text-sm text-rose-300" role="alert"></p>
                        @error('email') <p id="forgot-error" class="mt-1.5 animate-rise text-sm text-rose-300" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" x-bind:disabled="sending"
                            class="relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition duration-300
                                   hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30 active:scale-95 disabled:cursor-wait disabled:opacity-70
                                   before:absolute before:inset-0 before:-translate-x-full before:skew-x-12 before:bg-white/30 before:transition-transform before:duration-700 hover:before:translate-x-full">
                        <svg x-show="sending" x-cloak class="relative size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                        <span class="relative" x-text="sending ? 'Envoi en cours…' : 'Envoyer le lien'"></span>
                    </button>
                </form>

                <a href="{{ $to('login') }}" wire:navigate class="group mt-6 inline-flex items-center gap-2 text-sm text-stone-400 transition hover:text-gold-500">
                    <svg class="size-4 transition-transform duration-300 group-hover:-translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.56l3.97 3.97a.75.75 0 11-1.06 1.06l-5.25-5.25a.75.75 0 010-1.06l5.25-5.25a.75.75 0 111.06 1.06L5.56 9.25h10.69A.75.75 0 0117 10z" clip-rule="evenodd"/></svg>
                    Retour à la connexion
                </a>
            @endif
        </div>

        <p class="mt-6 text-center text-xs text-stone-500">
            Un problème ? <a href="{{ $to('contact') }}" wire:navigate class="text-gold-500/80 hover:text-gold-500 hover:underline">Contactez-nous</a>
        </p>
    </div>
</section>

<style>@keyframes draw-check { to { stroke-dashoffset: 0; } }</style>
@endsection