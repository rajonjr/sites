{{-- <x-layouts::guest>
    <div class="w-full flex items-center justify-center px-4 py-12">
        <div
            class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6 text-center">
            <div
                class="w-16 h-16 mx-auto rounded-2xl bg-brand-950 text-brand-400 flex items-center justify-center text-2xl border border-brand-800/50">
                <i class="fa-solid fa-envelope-circle-check"></i>
            </div>

            <h2 class="text-2xl font-bold text-white">Vérifiez votre e-mail</h2>

            <p class="text-xs text-slate-300 leading-relaxed">
                Merci de vous être inscrit ! Avant de commencer, pourriez-vous vérifier votre adresse e-mail en cliquant
                sur
                le lien que nous venons de vous envoyer ? Si vous n'avez pas reçu l'e-mail, nous vous en enverrons
                volontiers un autre.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-xs">
                    Un nouveau lien de vérification a été envoyé à l'adresse e-mail que vous avez indiquée dans votre
                    profil.
                </div>
            @elseif (session()->has('status'))
                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-xs">
                    {{ session('status') }}
                </div>
            @endif

            <div class="space-y-4 pt-2">
                <form action="{{ route('verification.send') }}" method="post">
                    @csrf
                    <button type="submit"
                        class="w-full py-3.5 bg-linear-to-r from-brand-600 to-brand-500 text-white font-semibold rounded-xl shadow-lg shadow-brand-600/30 transition text-sm">
                        Renvoyer l'e-mail de vérification
                    </button>
                </form>

                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit"
                        class="w-full py-3 bg-slate-950 border border-slate-800 text-slate-400 hover:text-white font-medium rounded-xl transition text-xs">
                        Se déconnecter
                    </button>
                </form>

            </div>
        </div>
    </div>
</x-layouts::guest> --}}

@extends('layouts.guest')

@section('title', 'Vérifiez votre email')

@section('content')
@php
    $to   = fn (string $name) => Route::has($name) ? route($name) : '#';
    $user = auth()->user();

    // Fortify : POST /email/verification-notification (route « verification.send »)
    $action = Route::has('verification.send') ? route('verification.send') : url('/email/verification-notification');

    // Durée de validité du lien (config/auth.php, en minutes)
    $expire = (int) config('auth.verification.expire', 60);

    // Fortify place « verification-link-sent » dans la session après un renvoi
    $resent = session('status') === 'verification-link-sent';

    // Raccourci vers la messagerie, selon le domaine de l'adresse
    $domain = strtolower(substr(strrchr($user->email, '@'), 1));
    $mailbox = match (true) {
        in_array($domain, ['gmail.com', 'googlemail.com'], true)                              => ['Gmail', 'https://mail.google.com'],
        in_array($domain, ['outlook.com', 'hotmail.com', 'live.com', 'outlook.fr', 'hotmail.fr'], true) => ['Outlook', 'https://outlook.live.com/mail'],
        str_starts_with($domain, 'yahoo.')                                                      => ['Yahoo Mail', 'https://mail.yahoo.com'],
        in_array($domain, ['icloud.com', 'me.com', 'mac.com'], true)                           => ['iCloud Mail', 'https://www.icloud.com/mail'],
        default                                                                                 => null,
    };

    // Adresse masquée pour l'affichage : j•••@gmail.com
    $masked = mb_substr($user->email, 0, 1).'•••@'.$domain;
@endphp

<section class="mx-auto flex min-h-[calc(100vh-14rem)] max-w-md items-center px-4 pb-16 pt-6 sm:px-6" aria-labelledby="verify-titre">
    <div class="w-full animate-rise">
        <div class="rounded-3xl border border-gold-500/25 bg-night-900/70 p-7 text-center shadow-2xl shadow-black/40 backdrop-blur-xl sm:p-10"
             x-data="{
                 key: 'iv-verify-sent',
                 resent: @js($resent),
                 sentAt: 0,
                 now: Date.now(),
                 sending: false,
                 timer: null,
                 poller: null,
                 detected: false,
                 noticeUrl: @js($to('verification.notice')),
                 home: @js($to('dashboard') !== '#' ? $to('dashboard') : url('/')),

                 init() {
                     // Un renvoi vient d'avoir lieu : on mémorise l'heure pour garder le délai après un rechargement
                     try {
                         if (this.resent) sessionStorage.setItem(this.key, String(Date.now()));
                         this.sentAt = parseInt(sessionStorage.getItem(this.key) || '0', 10) || 0;
                     } catch (e) {}

                     this.timer = setInterval(() => this.now = Date.now(), 1000);
                     // Vérifie toutes les 6 secondes si le lien a été cliqué (autre onglet, téléphone…)
                     this.poller = setInterval(() => this.poll(), 6000);
                 },
                 get left() { return this.sentAt ? Math.max(0, 60 - Math.floor((this.now - this.sentAt) / 1000)) : 0; },

                 /* Quand l'email est vérifié, Fortify redirige cette page : on le détecte sans lire la réponse */
                 async poll() {
                     if (document.hidden || this.detected) return;
                     try {
                         const r = await fetch(this.noticeUrl, { redirect: 'manual', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                         if (r.type === 'opaqueredirect') {
                             this.detected = true;
                             clearInterval(this.poller);
                             setTimeout(() => window.location.href = this.home, 1400);
                         }
                     } catch (e) {}
                 },
                 stop() { clearInterval(this.timer); clearInterval(this.poller); }
             }"
             x-on:livewire:navigating.window="stop()">

            {{-- Enveloppe qui flotte ; une coche apparaît quand la vérification est détectée --}}
            <span class="relative mx-auto grid size-24 place-items-center rounded-full border border-gold-500/50 bg-gold-500/10 text-gold-500">
                <span class="absolute inset-0 animate-ping-slow rounded-full border border-gold-500/40"></span>
                <svg class="size-11 animate-bounce [animation-duration:2.6s]" x-show="!detected" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="M3 7l9 6.5L21 7"/>
                </svg>
                <svg x-show="detected" x-cloak class="size-11 animate-pop text-emerald-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            </span>

            <h1 id="verify-titre" class="mt-6 font-display text-4xl font-semibold text-gold-500" x-text="detected ? 'Email vérifié !' : 'Vérifiez votre email'">Vérifiez votre email</h1>

            <div x-show="!detected">
                <p class="mt-3 text-sm leading-relaxed text-stone-400">
                    Merci pour votre inscription. Cliquez sur le lien que nous venons d’envoyer à
                    <strong class="font-medium text-stone-100">{{ $masked }}</strong> pour activer votre compte.
                </p>

                @if ($resent)
                    <p class="mt-5 animate-rise rounded-2xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200" role="status">
                        Un nouveau lien vient d’être envoyé à votre adresse.
                    </p>
                @endif

                @if ($mailbox)
                    <a href="{{ $mailbox[1] }}" target="_blank" rel="noopener noreferrer"
                       class="group mt-6 inline-flex items-center gap-2 rounded-full border border-gold-500 px-6 py-3 text-sm font-medium text-gold-500 transition duration-300 hover:bg-gold-500 hover:text-night-950 active:scale-95">
                        Ouvrir {{ $mailbox[0] }}
                        <svg class="size-4 transition-transform duration-300 group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.69L10.47 5.28a.75.75 0 011.06-1.06l5.25 5.25a.75.75 0 010 1.06l-5.25 5.25a.75.75 0 11-1.06-1.06l3.97-3.97H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
                    </a>
                @endif

                <ul class="mx-auto mt-6 max-w-xs space-y-2 text-left text-xs text-stone-500">
                    <li class="flex gap-2"><span class="text-gold-500" aria-hidden="true">•</span> Pensez à regarder dans vos courriers indésirables.</li>
                    <li class="flex gap-2"><span class="text-gold-500" aria-hidden="true">•</span> Le lien est valable {{ $expire }} minute{{ $expire > 1 ? 's' : '' }}.</li>
                </ul>

                {{-- Renvoi du lien, avec délai de 60 secondes --}}
                <form method="POST" action="{{ $action }}" class="mt-8" x-on:submit="sending = true">
                    @csrf
                    <button type="submit" x-bind:disabled="left > 0 || sending"
                            class="w-full rounded-full bg-gold-500 px-6 py-3 font-semibold text-night-950 transition duration-300 hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:translate-y-0 disabled:hover:shadow-none">
                        <span x-show="left > 0" x-cloak>Renvoyer le lien dans <span class="tabular-nums" x-text="left"></span> s</span>
                        <span x-show="left <= 0 && !sending">Renvoyer l’email de vérification</span>
                        <span x-show="sending" x-cloak>Envoi…</span>
                    </button>
                </form>

                {{-- Indicateur de vérification automatique --}}
                <p class="mt-4 flex items-center justify-center gap-2 text-xs text-stone-600">
                    <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-gold-500/60"></span><span class="relative inline-flex size-2 rounded-full bg-gold-500"></span></span>
                    Cette page se met à jour dès que votre email est vérifié.
                </p>
            </div>

            <p x-show="detected" x-cloak class="mt-3 animate-rise text-sm text-emerald-300" role="status">Votre adresse est confirmée. Redirection en cours…</p>

            {{-- Mauvaise adresse ? --}}
            <div class="mt-8 flex flex-col items-center justify-center gap-3 border-t border-gold-500/15 pt-6 text-sm sm:flex-row sm:gap-6" x-show="!detected">
                @if (Route::has('dashboard'))
                    <a href="{{ route('dashboard') }}#parametres" wire:navigate class="text-stone-400 transition hover:text-gold-500">Modifier mon adresse</a>
                @endif
                <form method="POST" action="{{ $to('logout') }}">
                    @csrf
                    <button type="submit" class="text-stone-400 transition hover:text-gold-500">Se déconnecter</button>
                </form>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-stone-500">
            Pas de nouvelles ? <a href="{{ $to('contact') }}" wire:navigate class="text-gold-500/80 hover:text-gold-500 hover:underline">Contactez-nous</a>
        </p>
    </div>
</section>
@endsection