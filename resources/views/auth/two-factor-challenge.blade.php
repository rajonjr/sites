{{-- <x-layouts::guest>
    <div class="w-full flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
            <div
                class="w-16 h-16 mx-auto rounded-2xl bg-brand-950 text-brand-400 flex items-center justify-center text-2xl border border-brand-800/50">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <h2 class="text-2xl font-bold text-white text-center">Authentification 2FA</h2>

            <p class="text-xs text-slate-400 text-center leading-relaxed">
                <template x-if="!$wire.recovery">
                    Veuillez confirmer l'accès à votre compte en entrant le code d'authentification fourni par votre
                    application d'authentification.
                </template>
                <template x-if="$wire.recovery">
                    Veuillez confirmer l'accès à votre compte en entrant l'un de vos codes de secours d'urgence.
                </template>
            </p>

            <form action="{{ route('two-factor.login') }}" method="post" class="space-y-4">
                @csrf
                @if (!$recovery)
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Code d'authentification</label>
                        <input type="text" name="code" autofocus autocomplete="one-time-code"
                            class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm tracking-widest text-center focus:outline-none focus:border-brand-500">
                        @error('code') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                @else
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Code de secours</label>
                        <input type="text" name="recovery_code" autocomplete="one-time-code"
                            class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                        @error('recovery_code') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                <div class="flex items-center justify-between text-xs">
                    <button type="button" wire:click="toggleRecovery" class="text-gold-400 hover:underline">
                        @if (!$recovery)
                            Utiliser un code de secours
                        @else
                            Utiliser un code d'application
                        @endif
                    </button>
                </div>

                <button type="submit"
                    class="w-full py-3.5 bg-linear-to-r from-brand-600 to-brand-500 text-white font-semibold rounded-xl shadow-lg shadow-brand-600/30 transition">
                    Se connecter
                </button>
            </form>
        </div>
    </div>
</x-layouts::guest> --}}

@extends('layouts.guest')

@section('title', 'Vérification en deux étapes')

@section('content')
@php
    $to = fn (string $name) => Route::has($name) ? route($name) : '#';

    // Fortify : POST /two-factor-challenge (route « two-factor.login »)
    $action = Route::has('two-factor.login') ? route('two-factor.login') : url('/two-factor-challenge');

    // Si le dernier essai était un code de récupération, on rouvre ce mode
    $startRecovery = $errors->has('recovery_code') || old('recovery_code') !== null;
    $hasError = $errors->has('code') || $errors->has('recovery_code');
    $error = $errors->first('code') ?: $errors->first('recovery_code');
@endphp

<section class="mx-auto flex min-h-[calc(100vh-14rem)] max-w-md items-center px-4 pb-16 pt-6 sm:px-6" aria-labelledby="tf-titre">
    <div class="w-full animate-rise">
        <div class="rounded-3xl border border-gold-500/25 bg-night-900/70 p-7 text-center shadow-2xl shadow-black/40 backdrop-blur-xl sm:p-10 {{ $hasError ? 'animate-shake' : '' }}"
             x-data="{
                 recovery: @js($startRecovery),
                 digits: ['', '', '', '', '', ''],
                 recoveryCode: '',
                 sending: false,
                 err: '',
                 now: Date.now(),
                 timer: null,

                 init() {
                     this.timer = setInterval(() => this.now = Date.now(), 1000);
                     this.$nextTick(() => this.focusFirst());
                 },

                 /* Fenêtre de 30 secondes des codes à usage unique (TOTP) */
                 get remain() { return 30 - (Math.floor(this.now / 1000) % 30); },
                 get C() { return 2 * Math.PI * 18; },

                 get code() { return this.digits.join(''); },
                 get complete() { return this.code.length === 6; },

                 boxes() { return this.$refs.otp ? this.$refs.otp.querySelectorAll('input') : []; },
                 focusAt(i) { this.$nextTick(() => { const b = this.boxes()[i]; if (b) { b.focus(); b.select(); } }); },
                 focusFirst() { if (this.recovery) { this.$refs.rec && this.$refs.rec.focus(); } else { this.focusAt(0); } },

                 /* Répartit une suite de chiffres (saisie, collage ou remplissage automatique) à partir de la case i */
                 fill(i, raw) {
                     const nums = (raw || '').replace(/\D/g, '').slice(0, 6 - i);
                     if (!nums) { this.digits[i] = ''; return; }
                     [...nums].forEach((n, k) => this.digits[i + k] = n);
                     this.focusAt(Math.min(i + nums.length, 5));
                     this.submitIfComplete();
                 },
                 onInput(i, e) { this.fill(i, e.target.value); e.target.value = this.digits[i]; },
                 onPaste(i, e) { e.preventDefault(); this.fill(i, (e.clipboardData || window.clipboardData).getData('text')); },
                 onKey(i, e) {
                     if (e.key === 'Backspace' && !this.digits[i] && i > 0) { this.digits[i - 1] = ''; this.focusAt(i - 1); e.preventDefault(); }
                     else if (e.key === 'ArrowLeft' && i > 0) { this.focusAt(i - 1); e.preventDefault(); }
                     else if (e.key === 'ArrowRight' && i < 5) { this.focusAt(i + 1); e.preventDefault(); }
                 },
                 submitIfComplete() {
                     if (this.complete && !this.sending) { this.sending = true; this.$nextTick(() => this.$refs.form.requestSubmit()); }
                 },

                 switchMode() {
                     this.recovery = !this.recovery;
                     this.err = '';
                     this.digits = ['', '', '', '', '', ''];
                     this.recoveryCode = '';
                     this.$nextTick(() => this.focusFirst());
                 },
                 check(e) {
                     this.err = '';
                     if (this.recovery && !this.recoveryCode.trim()) this.err = 'Saisissez un code de récupération.';
                     else if (!this.recovery && !this.complete) this.err = 'Saisissez les 6 chiffres du code.';
                     if (this.err) { e.preventDefault(); this.sending = false; return; }
                     this.sending = true;
                 }
             }"
             x-on:livewire:navigating.window="clearInterval(timer)">

            {{-- Icône : bouclier --}}
            <span class="relative mx-auto grid size-20 place-items-center rounded-full border border-gold-500/50 bg-gold-500/10 text-gold-500">
                <span class="absolute inset-0 animate-ping-slow rounded-full border border-gold-500/40"></span>
                <svg class="size-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z"/>
                    <path x-show="!recovery" d="M9.5 12.5l1.8 1.8 3.4-3.6"/>
                    <path x-show="recovery" x-cloak d="M10 9.5a2 2 0 112.8 1.8c-.5.3-.8.7-.8 1.2M12 15h.01"/>
                </svg>
            </span>

            <h1 id="tf-titre" class="mt-6 font-display text-4xl font-semibold text-gold-500">Vérification en deux étapes</h1>
            <p class="mt-3 text-sm leading-relaxed text-stone-400" x-show="!recovery">
                Ouvrez votre application d’authentification et saisissez le code à 6 chiffres affiché pour <span class="text-stone-200">Iraka Voafidy</span>.
            </p>
            <p class="mt-3 text-sm leading-relaxed text-stone-400" x-show="recovery" x-cloak>
                Saisissez l’un des codes de récupération enregistrés lors de l’activation. Chaque code ne sert qu’une seule fois.
            </p>

            <form method="POST" action="{{ $action }}" novalidate x-ref="form" class="mt-8" x-on:submit="check($event)">
                @csrf

                {{-- ===== Mode : code de l'application ===== --}}
                <div x-show="!recovery">
                    <input type="hidden" name="code" :value="code" :disabled="recovery">

                    <div x-ref="otp" class="flex justify-center gap-2 sm:gap-3" role="group" aria-label="Code à 6 chiffres">
                        <template x-for="(d, i) in digits" :key="i">
                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code"
                                   :aria-label="'Chiffre ' + (i + 1)"
                                   :value="digits[i]"
                                   x-on:input="onInput(i, $event)" x-on:keydown="onKey(i, $event)" x-on:paste="onPaste(i, $event)" x-on:focus="$event.target.select()"
                                   class="size-12 rounded-xl border bg-night-900 text-center font-display text-3xl text-gold-500 transition duration-200 focus:-translate-y-0.5 focus:scale-105 focus:outline-none focus:ring-2 sm:size-14"
                                   :class="[digits[i] ? 'border-gold-500 bg-gold-500/10' : '{{ $hasError ? 'border-rose-400/70' : 'border-gold-500/30' }}', 'focus:border-gold-500 focus:ring-gold-500/30']">
                        </template>
                    </div>

                    {{-- Compte à rebours de la fenêtre de 30 secondes --}}
                    <div class="mt-5 flex items-center justify-center gap-2 text-xs text-stone-500" aria-live="off">
                        <svg class="size-5 -rotate-90" viewBox="0 0 40 40" aria-hidden="true">
                            <circle cx="20" cy="20" r="18" fill="none" stroke="currentColor" stroke-width="3" class="text-gold-500/15"/>
                            <circle cx="20" cy="20" r="18" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    class="text-gold-500 transition-[stroke-dashoffset] duration-1000 ease-linear"
                                    :stroke-dasharray="C" :stroke-dashoffset="C * (1 - remain / 30)"/>
                        </svg>
                        <span>Le code change dans <span class="tabular-nums text-stone-300" x-text="remain"></span> s</span>
                    </div>
                </div>

                {{-- ===== Mode : code de récupération ===== --}}
                <div x-show="recovery" x-cloak class="text-left">
                    <label for="tf-recovery" class="mb-1.5 block text-sm text-stone-300">Code de récupération</label>
                    <input id="tf-recovery" x-ref="rec" name="recovery_code" type="text" autocomplete="off" autocapitalize="none" spellcheck="false"
                           x-model="recoveryCode" :disabled="!recovery" placeholder="xxxxxxxxxx-xxxxxxxxxx"
                           class="w-full rounded-2xl border bg-night-900 px-4 py-3.5 font-mono text-stone-100 placeholder:text-stone-600 transition focus:outline-none focus:ring-2
                                  {{ $errors->has('recovery_code') ? 'border-rose-400/70 focus:border-rose-400 focus:ring-rose-400/30' : 'border-gold-500/30 focus:border-gold-500 focus:ring-gold-500/30' }}">
                </div>

                {{-- Erreurs --}}
                <p x-show="err" x-cloak x-text="err" class="mt-4 text-sm text-rose-300" role="alert" aria-live="assertive"></p>
                @if ($error)
                    <p class="mt-4 animate-rise text-sm text-rose-300" role="alert">{{ $error }}</p>
                @endif

                {{-- Bouton (le code à 6 chiffres est envoyé automatiquement dès qu'il est complet) --}}
                <button type="submit" x-bind:disabled="sending"
                        class="relative mt-6 flex w-full items-center justify-center gap-2 overflow-hidden rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition duration-300
                               hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30 active:scale-95 disabled:cursor-wait disabled:opacity-70
                               before:absolute before:inset-0 before:-translate-x-full before:skew-x-12 before:bg-white/30 before:transition-transform before:duration-700 hover:before:translate-x-full">
                    <svg x-show="sending" x-cloak class="relative size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    <span class="relative" x-text="sending ? 'Vérification…' : 'Valider'"></span>
                </button>
            </form>

            <button type="button" x-on:click="switchMode()"
                    class="mt-6 text-sm text-gold-500 underline-offset-4 transition hover:text-gold-300 hover:underline"
                    x-text="recovery ? 'Utiliser mon application d’authentification' : 'Utiliser un code de récupération'"></button>

            <p class="mt-5">
                <a href="{{ $to('login') }}" wire:navigate class="group inline-flex items-center gap-2 text-sm text-stone-400 transition hover:text-gold-500">
                    <svg class="size-4 transition-transform duration-300 group-hover:-translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.56l3.97 3.97a.75.75 0 11-1.06 1.06l-5.25-5.25a.75.75 0 010-1.06l5.25-5.25a.75.75 0 111.06 1.06L5.56 9.25h10.69A.75.75 0 0117 10z" clip-rule="evenodd"/></svg>
                    Retour à la connexion
                </a>
            </p>
        </div>

        <p class="mt-6 text-center text-xs text-stone-500">
            Plus d’accès à votre application ? <a href="{{ $to('contact') }}" wire:navigate class="text-gold-500/80 hover:text-gold-500 hover:underline">Contactez-nous</a>
        </p>
    </div>
</section>
@endsection