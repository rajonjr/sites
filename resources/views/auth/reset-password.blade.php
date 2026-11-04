{{-- <x-layouts::guest>
    <div class="w-full flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
            <h2 class="text-2xl font-bold text-white text-center">Réinitialiser le mot de passe</h2>

            <p class="text-xs text-slate-400 text-center">
                Veuillez entrer votre nouvelle adresse e-mail et choisir un nouveau mot de passe sécurisé.
            </p>

            <form action="{{ route('password.update') }}" method="post" class="space-y-4">
                @csrf
                <!-- Token caché -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Adresse e-mail</label>
                    <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                        class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                    @error('email') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Nouveau mot de passe</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                    @error('password') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-1">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <button type="submit"
                    class="w-full py-3.5 bg-linear-to-r from-brand-600 to-brand-500 text-white font-semibold rounded-xl shadow-lg shadow-brand-600/30 transition">
                    Réinitialiser le mot de passe
                </button>
            </form>

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

@section('title', 'Nouveau mot de passe')

@section('content')
@php
    $to  = fn (string $name) => Route::has($name) ? route($name) : '#';
    $req = $request ?? request();

    // Fortify : POST /reset-password (route « password.update »)
    $action = Route::has('password.update') ? route('password.update') : url('/reset-password');

    // Le jeton est dans l'URL (/reset-password/{token}) et l'email dans la chaîne de requête (?email=…)
    $token = $req->route('token');
    $email = old('email', $req->query('email', ''));

    $field = 'w-full rounded-2xl border bg-night-900 px-4 py-3.5 pr-14 text-stone-100 placeholder:text-stone-500 transition focus:outline-none focus:ring-2';
    $ok    = 'border-gold-500/30 focus:border-gold-500 focus:ring-gold-500/30';
    $bad   = 'border-rose-400/70 focus:border-rose-400 focus:ring-rose-400/30';

    $eye    = 'M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z';
    $eyeOff = 'M3 3l18 18M10.6 6.1A10 10 0 0112 6c6.4 0 10 6 10 6a17 17 0 01-3.2 3.9M6.6 6.7A17 17 0 002 12s3.6 7 10 7a10 10 0 004.2-.9M9.9 9.9a3 3 0 004.2 4.2';
@endphp

<section class="mx-auto flex min-h-[calc(100vh-14rem)] max-w-md items-center px-4 pb-16 pt-6 sm:px-6" aria-labelledby="reset-titre">
    <div class="w-full animate-rise">
        <div class="rounded-3xl border border-gold-500/25 bg-night-900/70 p-7 shadow-2xl shadow-black/40 backdrop-blur-xl sm:p-10 {{ $errors->has('email') || $errors->has('password') ? 'animate-shake' : '' }}"
             x-data="{
                 pw: '',
                 pw2: '',
                 show: false,
                 sending: false,
                 err: '',
                 generated: false,

                 get hasLen() { return this.pw.length >= 8; },
                 get hasCase() { return /[a-z]/.test(this.pw) && /[A-Z]/.test(this.pw); },
                 get hasNum() { return /[0-9]/.test(this.pw); },
                 get hasSym() { return /[^A-Za-z0-9]/.test(this.pw); },
                 get valid() { return this.hasLen && this.hasCase && this.hasNum; },
                 get score() {
                     if (!this.pw) return 0;
                     let s = [this.hasLen, this.hasCase, this.hasNum, this.hasSym].filter(Boolean).length;
                     if (s === 4 && this.pw.length >= 12) s = 5;
                     return s;
                 },
                 get strengthLabel() { return ['', 'Très faible', 'Faible', 'Moyen', 'Bon', 'Excellent'][this.score]; },
                 get strengthColor() { return ['', 'bg-rose-500', 'bg-orange-400', 'bg-yellow-400', 'bg-lime-400', 'bg-emerald-400'][this.score]; },
                 get match() { return this.pw2.length > 0 && this.pw === this.pw2; },

                 /* Génère un mot de passe fort (16 caractères) avec le générateur aléatoire sécurisé du navigateur */
                 generate() {
                     const sets = ['abcdefghijkmnopqrstuvwxyz', 'ABCDEFGHJKLMNPQRSTUVWXYZ', '23456789', '!@#$%&*?-_'];
                     const all = sets.join('');
                     const rnd = (n) => { const a = new Uint32Array(1); crypto.getRandomValues(a); return a[0] % n; };
                     const chars = sets.map(s => s[rnd(s.length)]);
                     while (chars.length < 16) chars.push(all[rnd(all.length)]);
                     for (let i = chars.length - 1; i > 0; i--) { const j = rnd(i + 1); [chars[i], chars[j]] = [chars[j], chars[i]]; }
                     this.pw = this.pw2 = chars.join('');
                     this.show = true;
                     this.generated = true;
                 },
                 async copy() {
                     try { await navigator.clipboard.writeText(this.pw); this.copied = true; setTimeout(() => this.copied = false, 2000); } catch (e) {}
                 },
                 copied: false,

                 check(e) {
                     this.err = '';
                     if (!this.valid) this.err = 'Votre mot de passe ne respecte pas encore les règles.';
                     else if (this.pw !== this.pw2) this.err = 'Les deux mots de passe ne sont pas identiques.';
                     if (this.err) { e.preventDefault(); return; }
                     this.sending = true;
                 }
             }">

            {{-- Icône --}}
            <span class="relative mx-auto grid size-20 place-items-center rounded-full border border-gold-500/50 bg-gold-500/10 text-gold-500">
                <span class="absolute inset-0 animate-ping-slow rounded-full border border-gold-500/40"></span>
                <svg class="size-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/>
                </svg>
            </span>

            <h1 id="reset-titre" class="mt-6 text-center font-display text-4xl font-semibold text-gold-500">Nouveau mot de passe</h1>
            <p class="mt-3 text-center text-sm leading-relaxed text-stone-400">Choisissez un mot de passe solide que vous n’utilisez nulle part ailleurs.</p>

            {{-- Lien invalide ou expiré (erreur du jeton, rattachée au champ email par Fortify) --}}
            @if ($errors->has('email'))
                <div class="mt-6 animate-rise rounded-2xl border border-rose-400/50 bg-rose-500/10 p-4 text-sm text-rose-200" role="alert">
                    <p>{{ $errors->first('email') }}</p>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" wire:navigate class="mt-2 inline-block font-medium text-gold-300 underline hover:text-gold-500">Demander un nouveau lien</a>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ $action }}" novalidate class="mt-7 space-y-5" x-on:submit="check($event)">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                {{-- Email : fixé par le lien, non modifiable --}}
                <div>
                    <label for="reset-email" class="mb-1.5 block text-sm text-stone-300">Adresse email</label>
                    <div class="relative">
                        <input id="reset-email" name="email" type="email" value="{{ $email }}" readonly required autocomplete="username"
                               class="w-full cursor-not-allowed rounded-2xl border border-gold-500/15 bg-night-950/60 px-4 py-3.5 pr-12 text-stone-400 focus:outline-none">
                        <svg class="pointer-events-none absolute right-4 top-1/2 size-5 -translate-y-1/2 text-stone-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                    </div>
                </div>

                {{-- Nouveau mot de passe --}}
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label for="reset-password" class="text-sm text-stone-300">Nouveau mot de passe</label>
                        <button type="button" x-on:click="generate()" class="group inline-flex items-center gap-1.5 text-xs text-gold-500 transition hover:text-gold-300">
                            <svg class="size-3.5 transition-transform duration-500 group-hover:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 11-3-6.7M21 4v5h-5"/></svg>
                            Générer un mot de passe
                        </button>
                    </div>

                    <div class="relative">
                        <input id="reset-password" name="password" required autofocus autocomplete="new-password" x-model="pw" x-bind:type="show ? 'text' : 'password'"
                               aria-describedby="reset-rules"
                               @error('password') aria-invalid="true" @enderror
                               class="{{ $field }} {{ $errors->has('password') ? $bad : $ok }}">
                        <button type="button" x-on:click="show = !show" x-bind:aria-pressed="show"
                                x-bind:aria-label="show ? 'Masquer les mots de passe' : 'Afficher les mots de passe'"
                                class="absolute right-2 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full text-gold-500 transition hover:bg-gold-500/10 active:scale-90">
                            <svg x-show="!show" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $eye }}"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="show" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $eyeOff }}"/></svg>
                        </button>
                    </div>
                    @error('password') <p class="mt-1.5 animate-rise text-sm text-rose-300" role="alert">{{ $message }}</p> @enderror

                    {{-- Mot de passe généré : à copier avant de valider --}}
                    <div x-show="generated" x-cloak x-transition class="mt-2 flex items-center justify-between gap-3 rounded-xl border border-gold-500/30 bg-gold-500/5 px-3 py-2 text-xs text-stone-300">
                        <span>Notez-le ou copiez-le avant de valider.</span>
                        <button type="button" x-on:click="copy()" class="shrink-0 rounded-full border border-gold-500/50 px-3 py-1 text-gold-500 transition hover:bg-gold-500 hover:text-night-950" x-text="copied ? 'Copié ✓' : 'Copier'"></button>
                    </div>

                    {{-- Force --}}
                    <div x-show="pw.length" x-cloak x-transition class="mt-3">
                        <div class="flex gap-1.5" aria-hidden="true">
                            <template x-for="n in 5" :key="n">
                                <span class="h-1.5 flex-1 rounded-full bg-gold-500/15 transition-colors duration-500" :class="n <= score && strengthColor"></span>
                            </template>
                        </div>
                        <p class="mt-1.5 text-xs text-stone-400" aria-live="polite">Force : <span class="text-stone-200" x-text="strengthLabel"></span></p>
                    </div>

                    {{-- Règles --}}
                    <ul id="reset-rules" class="mt-3 space-y-1.5 text-xs">
                        <template x-for="[ok, label] in [[hasLen, '8 caractères minimum'], [hasCase, 'Une majuscule et une minuscule'], [hasNum, 'Un chiffre']]" :key="label">
                            <li class="flex items-center gap-2 transition-colors duration-300" :class="ok ? 'text-emerald-300' : 'text-stone-500'">
                                <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path x-show="ok" fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/>
                                    <circle x-show="!ok" cx="10" cy="10" r="3"/>
                                </svg>
                                <span x-text="label"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- Confirmation --}}
                <div>
                    <label for="reset-password2" class="mb-1.5 block text-sm text-stone-300">Confirmer le mot de passe</label>
                    <input id="reset-password2" name="password_confirmation" required autocomplete="new-password" x-model="pw2" x-bind:type="show ? 'text' : 'password'"
                           class="{{ $field }} {{ $ok }}"
                           :class="pw2.length && (match ? 'border-emerald-400/70!' : 'border-rose-400/70!')">
                    <p x-show="pw2.length" x-cloak class="mt-1.5 text-xs" :class="match ? 'text-emerald-300' : 'text-rose-300'"
                       x-text="match ? 'Les mots de passe correspondent.' : 'Les mots de passe ne correspondent pas.'" aria-live="polite"></p>
                </div>

                <p x-show="err" x-cloak x-text="err" class="text-sm text-rose-300" role="alert" aria-live="assertive"></p>

                <button type="submit" x-bind:disabled="sending"
                        class="relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition duration-300
                               hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30 active:scale-95 disabled:cursor-wait disabled:opacity-70
                               before:absolute before:inset-0 before:-translate-x-full before:skew-x-12 before:bg-white/30 before:transition-transform before:duration-700 hover:before:translate-x-full">
                    <svg x-show="sending" x-cloak class="relative size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    <span class="relative" x-text="sending ? 'Enregistrement…' : 'Réinitialiser le mot de passe'"></span>
                </button>
            </form>

            <p class="mt-6 text-center">
                <a href="{{ $to('login') }}" wire:navigate class="group inline-flex items-center gap-2 text-sm text-stone-400 transition hover:text-gold-500">
                    <svg class="size-4 transition-transform duration-300 group-hover:-translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.56l3.97 3.97a.75.75 0 11-1.06 1.06l-5.25-5.25a.75.75 0 010-1.06l5.25-5.25a.75.75 0 111.06 1.06L5.56 9.25h10.69A.75.75 0 0117 10z" clip-rule="evenodd"/></svg>
                    Retour à la connexion
                </a>
            </p>
        </div>
    </div>
</section>
@endsection