{{--
    Layout « invité » : connexion, inscription, mot de passe oublié, vérification d'email…

    Utilisation (vue Blade classique) :
        @extends('layouts.guest')
        @section('title', 'Connexion')
        @section('content') ... @endsection

    Utilisation (composant Livewire pleine page) :
        #[Layout('layouts.guest')] #[Title('Connexion')]
--}}
@php
    $has = fn (string $name) => Route::has($name);

    $pageTitle = $title ?? View::yieldContent('title');

    // Lien contextuel en haut à droite : bascule entre connexion et inscription
    $context = match (true) {
        request()->routeIs('login') && $has('register')    => ['Pas encore de compte ?', 'Créer un compte', route('register')],
        request()->routeIs('register') && $has('login')    => ['Déjà membre ?', 'Se connecter', route('login')],
        $has('login') && ! request()->routeIs('login')     => ['Vous avez un compte ?', 'Se connecter', route('login')],
        default                                            => null,
    };

    // Particules dorées : positions fixes (graine constante) pour un rendu identique à chaque page
    mt_srand(42);
    $dust = [];
    for ($i = 0; $i < 18; $i++) {
        $dust[] = [
            'left'  => mt_rand(0, 100),
            'size'  => mt_rand(2, 5),
            'dur'   => mt_rand(12, 26),
            'delay' => -mt_rand(0, 24),
            'dx'    => mt_rand(-40, 40),
            'op'    => mt_rand(25, 70) / 100,
        ];
    }

    // Messages flash : succès / erreur (le « status » de Fortify est affiché par les vues elles-mêmes)
    $flashes = [];
    foreach (['success' => 'success', 'error' => 'error'] as $key => $type) {
        $msg = session($key);
        if (is_string($msg) && $msg !== '') {
            $flashes[] = ['message' => $msg, 'type' => $type];
        }
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0c0a06">

    <title>{{ $pageTitle ? $pageTitle.' | ' : '' }}Iraka Voafidy</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|inter:400,500,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* Particules dorées qui montent doucement */
        @keyframes dust {
            0%   { transform: translate3d(0, 0, 0); opacity: 0; }
            12%  { opacity: var(--op, .5); }
            85%  { opacity: var(--op, .5); }
            100% { transform: translate3d(var(--dx, 0px), -105vh, 0); opacity: 0; }
        }
        .dust { animation: dust var(--dur, 18s) linear var(--delay, 0s) infinite; }

        /* Halo qui suit le curseur (desktop uniquement) */
        .cursor-glow {
            background: radial-gradient(420px circle at var(--mx, 50%) var(--my, 40%), rgba(212,160,23,.13), transparent 60%);
            transition: background-position .2s;
        }
    </style>
    {{ $head ?? '' }}
</head>

<body
    class="relative min-h-screen overflow-x-hidden bg-night-950 font-sans text-stone-200 antialiased selection:bg-gold-500 selection:text-night-950"
    x-data="{
        navigating: false,
        toasts: [],
        raf: null,

        /* Le halo suit la souris, limité à une mise à jour par image */
        move(e) {
            if (this.raf || !window.matchMedia('(pointer: fine)').matches) return;
            this.raf = requestAnimationFrame(() => {
                this.$refs.glow.style.setProperty('--mx', e.clientX + 'px');
                this.$refs.glow.style.setProperty('--my', e.clientY + 'px');
                this.raf = null;
            });
        },

        toast(d) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message: d.message, type: d.type || 'success' });
            setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 4500);
        },
        dismiss(id) { this.toasts = this.toasts.filter(t => t.id !== id); }
    }"
    x-init="
        @foreach ($flashes as $f)
            $nextTick(() => toast({ message: @js($f['message']), type: '{{ $f['type'] }}' }));
        @endforeach
    "
    x-on:pointermove.window.passive="move($event)"
    x-on:toast.window="toast($event.detail)"
    x-on:livewire:navigate.window="navigating = true"
    x-on:livewire:navigated.window="navigating = false"
    x-on:livewire:navigating.window="cancelAnimationFrame(raf)">

    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-md focus:bg-gold-500 focus:px-4 focus:py-2 focus:text-night-950">Aller au contenu</a>

    {{-- Barre de chargement lors des navigations --}}
    <div x-show="navigating" x-cloak x-transition.opacity class="fixed inset-x-0 top-0 z-[100] h-0.5 overflow-hidden bg-gold-950">
        <div class="h-full w-1/3 animate-loader bg-gold-500"></div>
    </div>

    {{-- ============================ FOND ANIMÉ ============================ --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        {{-- Rayons dorés qui tournent très lentement --}}
        <div class="absolute left-1/2 top-1/2 size-[150vmax] -translate-x-1/2 -translate-y-1/2 animate-spin-slow opacity-80
                    bg-[conic-gradient(from_0deg,transparent_0deg,rgba(212,160,23,.12)_14deg,transparent_28deg,transparent_72deg,rgba(212,160,23,.08)_86deg,transparent_100deg,transparent_150deg,rgba(212,160,23,.12)_164deg,transparent_178deg,transparent_220deg,rgba(212,160,23,.08)_234deg,transparent_248deg,transparent_290deg,rgba(212,160,23,.12)_304deg,transparent_318deg)]
                    [mask-image:radial-gradient(circle,black_6%,transparent_52%)]"></div>

        {{-- Halos qui dérivent --}}
        <div class="absolute -left-24 top-[12%] size-[26rem] animate-drift rounded-full bg-gold-500/10 blur-3xl"></div>
        <div class="absolute -right-20 bottom-[8%] size-[30rem] animate-drift rounded-full bg-gold-600/10 blur-3xl" style="animation-delay:-9s; animation-direction:alternate-reverse"></div>
        <div class="absolute left-1/3 top-1/2 size-72 animate-drift rounded-full bg-gold-300/5 blur-3xl" style="animation-delay:-15s"></div>

        {{-- Halo qui suit le curseur --}}
        <div x-ref="glow" class="cursor-glow absolute inset-0"></div>

        {{-- Particules --}}
        @foreach ($dust as $d)
            <span class="dust absolute -bottom-4 rounded-full bg-gold-400"
                  style="left: {{ $d['left'] }}%; width: {{ $d['size'] }}px; height: {{ $d['size'] }}px; --dur: {{ $d['dur'] }}s; --delay: {{ $d['delay'] }}s; --dx: {{ $d['dx'] }}px; --op: {{ $d['op'] }}"></span>
        @endforeach

        {{-- Vignette --}}
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_35%,#0c0a06_92%)]"></div>
    </div>

    <div class="relative z-10 flex min-h-screen flex-col">

        {{-- ============================ EN-TÊTE ============================ --}}
        <header class="animate-fade-down px-4 sm:px-6 lg:px-8">
            <div class="mx-auto flex h-20 max-w-6xl items-center justify-between">
                <a href="{{ $has('home') ? route('home') : url('/') }}" wire:navigate class="group flex items-center gap-3" aria-label="Iraka Voafidy, retour à l’accueil">
                    <span class="relative grid size-10 place-items-center rounded-full border border-gold-500/60 transition duration-700 group-hover:rotate-180 group-hover:border-gold-400">
                        <span class="absolute inset-0 animate-ping-slow rounded-full border border-gold-500/40"></span>
                        <svg class="size-5 text-gold-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 3v18M5 8l7-5 7 5M5 16l7 5 7-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="animate-shimmer bg-linear-to-r from-gold-600 via-gold-300 to-gold-600 bg-[length:200%_auto] bg-clip-text font-display text-2xl font-semibold text-transparent sm:text-3xl">Iraka Voafidy</span>
                </a>

                <div class="flex items-center gap-3 sm:gap-5">
                    @if ($context)
                        <p class="hidden text-sm text-stone-500 md:block">{{ $context[0] }}</p>
                        <a href="{{ $context[2] }}" wire:navigate
                           class="group relative overflow-hidden rounded-full border border-gold-500/50 px-4 py-2 text-sm font-medium text-gold-500 transition duration-300 hover:border-gold-500 hover:bg-gold-500 hover:text-night-950 active:scale-95">
                            {{ $context[1] }}
                        </a>
                    @endif
                </div>
            </div>
        </header>

        {{-- ============================ CONTENU ============================ --}}
        <main id="contenu" class="flex-1 animate-rise" style="animation-delay:.1s">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- ============================ PIED DE PAGE ============================ --}}
        <footer class="animate-rise px-4 pb-8 pt-4 sm:px-6 lg:px-8" style="animation-delay:.35s">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 border-t border-gold-500/15 pt-6 text-xs text-stone-500 sm:flex-row">
                <a href="{{ $has('home') ? route('home') : url('/') }}" wire:navigate class="group inline-flex items-center gap-2 transition hover:text-gold-500">
                    <svg class="size-4 transition-transform duration-300 group-hover:-translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.56l3.97 3.97a.75.75 0 11-1.06 1.06l-5.25-5.25a.75.75 0 010-1.06l5.25-5.25a.75.75 0 111.06 1.06L5.56 9.25h10.69A.75.75 0 0117 10z" clip-rule="evenodd"/></svg>
                    Retour au site
                </a>

                <ul class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
                    @foreach ([['À propos', 'about'], ['Contact', 'contact'], ['Confidentialité', 'privacy'], ['Conditions', 'terms']] as [$label, $route])
                        @if ($has($route))
                            <li><a href="{{ route($route) }}" wire:navigate class="relative transition hover:text-gold-500 after:absolute after:inset-x-0 after:-bottom-0.5 after:h-px after:origin-left after:scale-x-0 after:bg-gold-500 after:transition-transform after:duration-300 hover:after:scale-x-100">{{ $label }}</a></li>
                        @endif
                    @endforeach
                </ul>

                <p>&copy; {{ date('Y') }} Iraka Voafidy</p>
            </div>
        </footer>
    </div>

    {{-- ============================ NOTIFICATIONS ============================ --}}
    <div class="pointer-events-none fixed bottom-6 right-6 z-[95] flex w-80 max-w-[calc(100vw-3rem)] flex-col gap-3" aria-live="polite">
        <template x-for="t in toasts" :key="t.id">
            <div x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0 translate-x-10" x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="opacity-0 translate-x-10"
                 class="pointer-events-auto relative overflow-hidden rounded-2xl border shadow-xl shadow-black/40 backdrop-blur"
                 :class="t.type === 'error' ? 'border-rose-400/50 bg-rose-950/90 text-rose-100' : 'border-gold-500/50 bg-night-900/95 text-gold-300'" role="status">
                <div class="flex items-start gap-3 px-4 py-3 text-sm">
                    <svg class="mt-0.5 size-5 shrink-0 animate-pop" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path x-show="t.type !== 'error'" d="M20 6L9 17l-5-5"/>
                        <path x-show="t.type === 'error'" d="M12 8v5M12 17h.01M10.3 3.9L2.4 18a2 2 0 001.7 3h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
                    </svg>
                    <p class="flex-1" x-text="t.message"></p>
                    <button type="button" x-on:click="dismiss(t.id)" aria-label="Fermer" class="text-current opacity-60 transition hover:opacity-100">
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                    </button>
                </div>
                <div class="h-0.5 animate-shrink bg-current opacity-40"></div>
            </div>
        </template>
    </div>

    @livewireScripts
    {{ $scripts ?? '' }}
    @stack('scripts')
</body>
</html>