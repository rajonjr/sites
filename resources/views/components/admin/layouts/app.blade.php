{{--
    Layout du panneau d'administration — utilisation :

    <x-admin.layouts.app title="Événements" :breadcrumbs="[['Événements']]">
        <x-slot:actions> ...boutons d'en-tête... </x-slot:actions>
        ...contenu...
    </x-admin.layouts.app>

    Pour un composant Livewire pleine page : #[Layout('components.admin.layouts.app')]
--}}
@props([
    'title' => null,
    'breadcrumbs' => [],
    'actions' => null,
])

@php
    $has = fn (string $name) => Route::has($name);
    $to  = fn (string $name) => Route::has($name) ? route($name) : null;

    $user = auth()->user();

    //Compteurs et notifications
    $counts = \Illuminate\Support\Facades\Cache::remember('admin.layout.counts', 60, function () {
        $out = ['pending' => 0, 'latest' => []];

        try {
            if (class_exists(\App\Models\PrayerRequest::class)) {
                $out['pending'] = \App\Models\PrayerRequest::where('is_approved', false)->count();
            }
            $out['latest'] = \App\Models\User::latest()->take(3)->get()->map(fn ($u) => [
                'name' => $u->name,
                'when' => optional($u->created_at)->locale('fr')->diffForHumans(),
            ])->all();
        } catch (\Throwable $e) {
            // Tables absentes : on affiche simplement des compteurs à zéro
        }

        return $out;
    });

    // Navigation
    $groups = [
        'Général' => [
            ['Tableau de bord', 'admin.dashboard', 'admin.dashboard', 'M4 13h6V4H4v9zm0 7h6v-3H4v3zm10 0h6v-9h-6v9zm0-16v3h6V4h-6z', null],
        ],
        'Contenu' => [
            ['Événements',           'admin.events',         'admin.events*',         'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z', null],
            ['Chorégraphies',        'admin.choreographies', 'admin.choreographies*', 'M9 18V5l12-2v13M9 18a3 3 0 11-6 0 3 3 0 016 0zm12-2a3 3 0 11-6 0 3 3 0 016 0z', null],
            ['Intentions de prière', 'admin.prayers',        'admin.prayers*',        'M12 21s-7-4.4-7-10a4 4 0 017-2.6A4 4 0 0119 11c0 5.6-7 10-7 10z', $counts['pending']],
        ],
        'Communauté' => [
            ['Membres',  'admin.users',    'admin.users*',    'M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8zM21 20v-2a4 4 0 00-3-3.9M16 2.1a4 4 0 010 7.8', null],
            ['Messages', 'admin.messages', 'admin.messages*', 'M3 7l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z', null],
        ],
        'Système' => [
            ['Réglages', 'admin.settings', 'admin.settings*', 'M4 6h10M18 6h2M4 12h2M10 12h10M4 18h12M20 18h0M14 4v4M8 10v4M16 16v4', null],
        ],
    ];

    // Liste à plat pour la palette de commandes (Ctrl + K)
    $palette = [];
    foreach ($groups as $group => $items) {
        foreach ($items as [$label, $route, $pattern, $icon, $badge]) {
            if ($has($route)) {
                $palette[] = ['label' => $label, 'group' => $group, 'url' => route($route), 'icon' => $icon];
            }
        }
    }
    foreach ([['Voir le site', 'home', 'Raccourcis'], ['Mon espace', 'dashboard', 'Raccourcis']] as [$label, $route, $group]) {
        if ($has($route)) {
            $palette[] = ['label' => $label, 'group' => $group, 'url' => route($route), 'icon' => 'M5 12h14M13 6l6 6-6 6'];
        }
    }

    // Fil d'Ariane : chaque élément est [libellé] ou [libellé, url]
    $crumbs = array_merge(
        [['Administration', $to('admin.dashboard')]],
        array_map(fn ($c) => [$c[0], $c[1] ?? null], $breadcrumbs)
    );

    // Le slot « actions »
    $hasActions = $actions && (! $actions instanceof \Illuminate\View\ComponentSlot || $actions->isNotEmpty());

    $notifCount = $counts['pending'] + count($counts['latest']);

    // Messages flash de session → notifications
    $flashes = [];
    foreach (['success' => 'success', 'error' => 'error', 'status' => 'success'] as $key => $type) {
        $msg = session($key);
        if (is_string($msg) && $msg !== '' && ($key !== 'status' || str_contains($msg, ' '))) {
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

    <title>{{ $title ? $title.' · ' : '' }}Administration · Iraka Voafidy</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|inter:400,500,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body
    class="min-h-screen bg-night-950 font-sans text-stone-200 antialiased selection:bg-gold-500 selection:text-night-950"
    x-data="{
        sidebar: $persist(true).as('iv-admin-sidebar'),
        drawer: false,
        scrolled: false,
        navigating: false,

        /* Palette de commandes */
        paletteOpen: false,
        q: '',
        idx: 0,
        items: @js($palette),
        get results() {
            const t = this.norm(this.q);
            return this.items.filter(i => !t || this.norm(i.label + ' ' + i.group).includes(t));
        },
        norm(s) { return (s || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); },
        openPalette() { this.paletteOpen = true; this.q = ''; this.idx = 0; this.$nextTick(() => this.$refs.paletteInput && this.$refs.paletteInput.focus()); },
        move(step) {
            const n = this.results.length;
            if (!n) return;
            this.idx = (this.idx + step + n) % n;
            this.$nextTick(() => { const el = this.$refs.paletteList.querySelectorAll('[data-item]')[this.idx]; if (el) el.scrollIntoView({ block: 'nearest' }); });
        },
        go(item) {
            if (!item) return;
            this.paletteOpen = false;
            if (window.Livewire && window.Livewire.navigate) window.Livewire.navigate(item.url); else window.location.href = item.url;
        },

        /* Notifications (toasts) */
        toasts: [],
        toast(d) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message: d.message, type: d.type || 'success' });
            setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 4500);
        },
        dismiss(id) { this.toasts = this.toasts.filter(t => t.id !== id); }
    }"
    x-init="
        const onScroll = () => scrolled = window.scrollY > 8;
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        @foreach ($flashes as $f)
            $nextTick(() => toast({ message: @js($f['message']), type: '{{ $f['type'] }}' }));
        @endforeach
    "
    x-on:keydown.window="
        if (($event.ctrlKey || $event.metaKey) && $event.key.toLowerCase() === 'k') { $event.preventDefault(); openPalette(); }
        else if (($event.ctrlKey || $event.metaKey) && $event.key.toLowerCase() === 'b') { $event.preventDefault(); sidebar = !sidebar; }
        else if ($event.key === 'Escape') { paletteOpen = false; drawer = false; }
    "
    x-on:toast.window="toast($event.detail)"
    x-on:livewire:navigate.window="navigating = true"
    x-on:livewire:navigated.window="navigating = false; drawer = false"
    x-bind:class="(drawer || paletteOpen) && 'overflow-hidden'">

    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-md focus:bg-gold-500 focus:px-4 focus:py-2 focus:text-night-950">Aller au contenu</a>

    {{-- Barre de chargement lors des navigations --}}
    <div x-show="navigating" x-cloak x-transition.opacity class="fixed inset-x-0 top-0 z-[100] h-0.5 overflow-hidden bg-gold-950">
        <div class="h-full w-1/3 animate-loader bg-gold-500"></div>
    </div>

    {{-- Voile derrière le menu mobile --}}
    <div x-show="drawer" x-cloak x-transition.opacity x-on:click="drawer = false" class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

    {{-- SIDEBAR --}}
    <aside id="admin-sidebar" aria-label="Menu d’administration"
           class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-gold-500/20 bg-night-900/95 backdrop-blur-xl transition-[width,translate] duration-300 ease-out"
           x-bind:class="[sidebar ? 'lg:w-64' : 'lg:w-20', drawer ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']">

        {{-- Marque --}}
        <a href="{{ $to('admin.dashboard') ?? '#' }}" wire:navigate class="group flex h-16 shrink-0 items-center gap-3 border-b border-gold-500/15 px-5">
            <span class="relative grid size-9 shrink-0 place-items-center rounded-full border border-gold-500/60 transition duration-500 group-hover:rotate-180">
                <svg class="size-4 text-gold-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 3v18M5 8l7-5 7 5M5 16l7 5 7-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span class="min-w-0 overflow-hidden whitespace-nowrap transition-all duration-300" x-bind:class="sidebar ? 'opacity-100' : 'lg:w-0 lg:opacity-0'">
                <span class="block font-display text-xl leading-none text-gold-500">Iraka Voafidy</span>
                <span class="block text-[11px] uppercase tracking-widest text-stone-500">Administration</span>
            </span>
        </a>

        {{-- Liens --}}
        <nav class="flex-1 space-y-6 overflow-y-auto overflow-x-hidden px-3 py-5" aria-label="Navigation principale">
            @foreach ($groups as $group => $items)
                <div>
                    <p class="mb-2 truncate px-3 text-[11px] font-medium uppercase tracking-widest text-stone-600 transition-all duration-300"
                       x-bind:class="sidebar ? 'opacity-100' : 'lg:h-px lg:overflow-hidden lg:bg-gold-500/15 lg:opacity-100 lg:text-transparent'">{{ $group }}</p>

                    <ul class="space-y-1">
                        @foreach ($items as $i => [$label, $route, $pattern, $icon, $badge])
                            @php
                                $enabled = $has($route);
                                $active  = $enabled && request()->routeIs($pattern);
                            @endphp
                            <li class="animate-slide-in" style="animation-delay: {{ ($loop->parent->index * 3 + $i) * 45 }}ms">
                                <a href="{{ $enabled ? route($route) : '#' }}"
                                   @if ($enabled) wire:navigate @else aria-disabled="true" tabindex="-1" @endif
                                   @if ($active) aria-current="page" @endif
                                   x-bind:title="sidebar ? '' : '{{ $label }}'"
                                   class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-300
                                          {{ $active ? 'bg-gold-500/10 text-gold-500' : ($enabled ? 'text-stone-400 hover:translate-x-0.5 hover:bg-gold-500/5 hover:text-stone-100' : 'cursor-not-allowed text-stone-700') }}">

                                    {{-- Barre d'état à gauche --}}
                                    <span class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r bg-gold-500 transition-transform duration-300
                                                 {{ $active ? 'scale-y-100' : 'scale-y-0 '.($enabled ? 'group-hover:scale-y-50' : '') }}"></span>

                                    <svg class="size-5 shrink-0 {{ $enabled ? 'group-hover:animate-wiggle' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}"/></svg>

                                    <span class="min-w-0 flex-1 truncate transition-all duration-300" x-bind:class="sidebar ? '' : 'lg:w-0 lg:flex-none lg:opacity-0'">{{ $label }}@unless ($enabled) <span class="ml-1 text-[10px] uppercase text-stone-700">bientôt</span>@endunless</span>

                                    @if ($badge)
                                        <span class="relative grid min-w-5 place-items-center rounded-full bg-gold-500 px-1.5 text-[11px] font-semibold leading-5 text-night-950 transition-all duration-300"
                                              x-bind:class="sidebar ? '' : 'lg:absolute lg:right-1 lg:top-0.5 lg:min-w-4 lg:px-1 lg:leading-4'">
                                            <span class="absolute inset-0 animate-ping rounded-full bg-gold-500/50"></span>
                                            <span class="relative">{{ $badge > 99 ? '99+' : $badge }}</span>
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        {{-- Pied de sidebar --}}
        <div class="hidden shrink-0 border-t border-gold-500/15 p-3 lg:block">
            <button type="button" x-on:click="sidebar = !sidebar" x-bind:aria-expanded="sidebar" aria-controls="admin-sidebar"
                    class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-stone-500 transition hover:bg-gold-500/5 hover:text-gold-500">
                <svg class="size-5 shrink-0 transition-transform duration-500" x-bind:class="sidebar ? '' : 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"/></svg>
                <span class="flex-1 truncate text-left transition-all duration-300" x-bind:class="sidebar ? '' : 'lg:w-0 lg:flex-none lg:opacity-0'">Réduire le menu</span>
                <kbd class="rounded border border-gold-500/20 px-1.5 text-[10px] text-stone-600" x-show="sidebar">Ctrl B</kbd>
            </button>
        </div>
    </aside>

    {{-- COLONNE PRINCIPALE --}}
    <div class="transition-[padding] duration-300 ease-out" x-bind:class="sidebar ? 'lg:pl-64' : 'lg:pl-20'">

        {{-- Barre supérieure --}}
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b px-4 transition-all duration-300 sm:px-6 lg:px-8"
                x-bind:class="scrolled ? 'border-gold-500/25 bg-night-950/85 shadow-lg shadow-black/30 backdrop-blur-xl' : 'border-transparent bg-night-950/40 backdrop-blur-md'">

            {{-- Burger (mobile) --}}
            <button type="button" x-on:click="drawer = true" aria-label="Ouvrir le menu" aria-controls="admin-sidebar"
                    class="grid size-10 place-items-center rounded-full border border-gold-500/30 text-gold-500 transition hover:bg-gold-500/10 active:scale-90 lg:hidden">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
            </button>

            {{-- Fil d'Ariane --}}
            <nav aria-label="Fil d’Ariane" class="hidden min-w-0 flex-1 sm:block">
                <ol class="flex items-center gap-2 text-sm">
                    @foreach ($crumbs as $k => [$label, $url])
                        @if (! $loop->first)<li aria-hidden="true" class="text-stone-700">/</li>@endif
                        <li class="min-w-0 truncate" @if ($loop->last) aria-current="page" @endif>
                            @if ($url && ! $loop->last)
                                <a href="{{ $url }}" wire:navigate class="text-stone-500 transition hover:text-gold-500">{{ $label }}</a>
                            @else
                                <span class="{{ $loop->last ? 'text-stone-200' : 'text-stone-500' }}">{{ $label }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
            <span class="flex-1 sm:hidden"></span>

            {{-- Recherche / palette de commandes --}}
            <button type="button" x-on:click="openPalette()" aria-label="Rechercher (Ctrl K)"
                    class="group flex items-center gap-2 rounded-full border border-gold-500/25 bg-night-900/70 px-3 py-2 text-sm text-stone-500 transition-all duration-300 hover:border-gold-500/60 hover:text-gold-500 active:scale-95 sm:w-56 sm:hover:w-64">
                <svg class="size-4 shrink-0 transition group-hover:scale-110" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.45 4.39l3.58 3.58a.75.75 0 11-1.06 1.06l-3.58-3.58A7 7 0 012 9z" clip-rule="evenodd"/></svg>
                <span class="hidden flex-1 truncate text-left sm:block">Rechercher…</span>
                <kbd class="hidden rounded border border-gold-500/20 px-1.5 text-[10px] sm:block">Ctrl K</kbd>
            </button>

            {{-- Notifications --}}
            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false">
                <button type="button" x-on:click="open = !open" x-bind:aria-expanded="open" aria-label="Notifications"
                        class="group relative grid size-10 place-items-center rounded-full border border-gold-500/30 text-gold-500 transition hover:bg-gold-500/10 active:scale-90">
                    <svg class="size-5 origin-top group-hover:animate-wiggle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 01-3.4 0"/></svg>
                    @if ($counts['pending'] > 0)
                        <span class="absolute right-1.5 top-1.5 flex size-2.5"><span class="absolute inline-flex size-full animate-ping rounded-full bg-gold-500/70"></span><span class="relative inline-flex size-2.5 rounded-full bg-gold-500"></span></span>
                    @endif
                </button>

                <div x-show="open" x-cloak
                     x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-3 w-80 max-w-[calc(100vw-2rem)] origin-top-right overflow-hidden rounded-2xl border border-gold-500/30 bg-night-900/95 shadow-2xl shadow-black/60 backdrop-blur-xl">
                    <p class="border-b border-gold-500/15 px-4 py-3 text-sm font-medium text-stone-100">Notifications</p>

                    <ul class="max-h-80 divide-y divide-gold-500/10 overflow-y-auto">
                        @if ($counts['pending'] > 0)
                            <li>
                                <a href="{{ $to('admin.prayers') ?? '#' }}" wire:navigate class="flex items-start gap-3 px-4 py-3 transition hover:bg-gold-500/5">
                                    <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-full bg-gold-500/15 text-gold-500">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-4.4-7-10a4 4 0 017-2.6A4 4 0 0119 11c0 5.6-7 10-7 10z"/></svg>
                                    </span>
                                    <span class="text-sm text-stone-200">{{ $counts['pending'] }} intention{{ $counts['pending'] > 1 ? 's' : '' }} de prière à modérer</span>
                                </a>
                            </li>
                        @endif
                        @foreach ($counts['latest'] as $n)
                            <li class="flex items-start gap-3 px-4 py-3">
                                <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-full bg-gold-500/15 font-display text-gold-500">{{ mb_strtoupper(mb_substr($n['name'], 0, 1)) }}</span>
                                <span class="min-w-0 text-sm text-stone-300"><span class="block truncate text-stone-100">{{ $n['name'] }}</span><span class="text-xs text-stone-500">a rejoint la communauté · {{ $n['when'] }}</span></span>
                            </li>
                        @endforeach
                        @if ($notifCount === 0)
                            <li class="px-4 py-8 text-center text-sm text-stone-500">Rien de nouveau pour l’instant.</li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Menu utilisateur --}}
            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false">
                <button type="button" x-on:click="open = !open" x-bind:aria-expanded="open"
                        class="group flex items-center gap-2 rounded-full border border-gold-500/30 py-1 pl-1 pr-3 transition hover:border-gold-500/70 hover:bg-gold-500/5 active:scale-95">
                    <span class="relative grid size-8 place-items-center rounded-full bg-gold-500 text-sm font-semibold text-night-950">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                        <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-night-950 bg-emerald-400"></span>
                    </span>
                    <span class="hidden max-w-28 truncate text-sm text-stone-200 md:block">{{ Str::before($user->name, ' ') }}</span>
                    <svg class="size-4 text-gold-500 transition-transform duration-300" x-bind:class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.2 7.7a.75.75 0 011.06 0L10 11.44l3.74-3.74a.75.75 0 111.06 1.06l-4.27 4.27a.75.75 0 01-1.06 0L5.2 8.76a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
                </button>

                <div x-show="open" x-cloak
                     x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-3 w-60 origin-top-right overflow-hidden rounded-2xl border border-gold-500/30 bg-night-900/95 shadow-2xl shadow-black/60 backdrop-blur-xl">
                    <div class="border-b border-gold-500/15 px-4 py-3">
                        <p class="truncate text-sm font-medium text-stone-100">{{ $user->name }}</p>
                        <p class="truncate text-xs text-stone-500">{{ $user->email }}</p>
                        <span class="mt-2 inline-block rounded-full border border-gold-500/50 px-2 py-0.5 text-[11px] text-gold-500">Administrateur</span>
                    </div>
                    @if ($has('dashboard'))<a href="{{ route('dashboard') }}" wire:navigate class="block px-4 py-2.5 text-sm text-stone-300 transition hover:bg-gold-500/10 hover:pl-5 hover:text-gold-500">Mon espace</a>@endif
                    @if ($has('home'))<a href="{{ route('home') }}" wire:navigate class="block px-4 py-2.5 text-sm text-stone-300 transition-all hover:bg-gold-500/10 hover:pl-5 hover:text-gold-500">Voir le site</a>@endif
                    <form method="POST" action="{{ $to('logout') ?? '#' }}" class="border-t border-gold-500/15">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2.5 text-left text-sm text-gold-500 transition hover:bg-gold-500 hover:text-night-950">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        {{-- CONTENU --}}
        <main id="contenu" class="px-4 py-8 sm:px-6 lg:px-8">
            @if ($title || $hasActions)
                <div class="mb-8 flex animate-rise flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    @if ($title)
                        <h1 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">{{ $title }}</h1>
                    @endif
                    @if ($hasActions)
                        <div class="flex flex-wrap items-center gap-3">{{ $actions }}</div>
                    @endif
                </div>
            @endif

            <div class="animate-rise" style="animation-delay:.08s">
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-gold-500/15 px-4 py-6 text-xs text-stone-600 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-2 sm:flex-row">
                <p>&copy; {{ date('Y') }} Iraka Voafidy · Administration</p>
                <p>Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }}</p>
            </div>
        </footer>
    </div>

    {{-- PALETTE DE COMMANDES --}}
    <div x-show="paletteOpen" x-cloak class="fixed inset-0 z-[90] flex items-start justify-center px-4 pt-[12vh]" role="dialog" aria-modal="true" aria-label="Recherche rapide">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" x-on:click="paletteOpen = false"
             x-show="paletteOpen" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:leave="transition duration-150" x-transition:leave-end="opacity-0"></div>

        <div x-show="paletteOpen" x-trap.noscroll="paletteOpen"
             x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0 -translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-xl overflow-hidden rounded-3xl border border-gold-500/40 bg-night-900 shadow-2xl shadow-black/70">

            <div class="flex items-center gap-3 border-b border-gold-500/20 px-5">
                <svg class="size-5 shrink-0 text-gold-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.45 4.39l3.58 3.58a.75.75 0 11-1.06 1.06l-3.58-3.58A7 7 0 012 9z" clip-rule="evenodd"/></svg>
                <input x-ref="paletteInput" x-model="q" x-on:input="idx = 0" type="text" placeholder="Aller à une page…" autocomplete="off" aria-label="Rechercher une page"
                       x-on:keydown.arrow-down.prevent="move(1)" x-on:keydown.arrow-up.prevent="move(-1)" x-on:keydown.enter.prevent="go(results[idx])"
                       class="h-14 w-full bg-transparent text-stone-100 placeholder:text-stone-500 focus:outline-none">
                <kbd class="rounded border border-gold-500/20 px-1.5 py-0.5 text-[10px] text-stone-500">Échap</kbd>
            </div>

            <ul x-ref="paletteList" class="max-h-80 overflow-y-auto p-2" role="listbox">
                <template x-for="(r, i) in results" :key="r.url + r.label">
                    <li data-item role="option" :aria-selected="i === idx">
                        <button type="button" x-on:click="go(r)" x-on:mouseenter="idx = i"
                                class="flex w-full animate-slide-in items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition-colors duration-150"
                                :style="`animation-delay:${Math.min(i, 8) * 25}ms`"
                                :class="i === idx ? 'bg-gold-500/15 text-gold-500' : 'text-stone-300'">
                            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="r.icon"/></svg>
                            <span class="flex-1 truncate" x-text="r.label"></span>
                            <span class="text-xs text-stone-600" x-text="r.group"></span>
                            <svg x-show="i === idx" class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.69L10.47 5.28a.75.75 0 011.06-1.06l5.25 5.25a.75.75 0 010 1.06l-5.25 5.25a.75.75 0 11-1.06-1.06l3.97-3.97H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
                        </button>
                    </li>
                </template>
            </ul>

            <p x-show="!results.length" x-cloak class="px-5 py-10 text-center text-sm text-stone-500">Aucun résultat pour « <span x-text="q"></span> ».</p>

            <div class="flex items-center gap-4 border-t border-gold-500/15 px-5 py-2.5 text-[11px] text-stone-600">
                <span><kbd class="rounded border border-gold-500/20 px-1">↑</kbd> <kbd class="rounded border border-gold-500/20 px-1">↓</kbd> naviguer</span>
                <span><kbd class="rounded border border-gold-500/20 px-1">↵</kbd> ouvrir</span>
            </div>
        </div>
    </div>

    {{-- NOTIFICATIONS (toasts) --}}
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
                {{-- Barre de temps restant --}}
                <div class="h-0.5 animate-shrink bg-current opacity-40"></div>
            </div>
        </template>
    </div>

    @livewireScripts
    {{ $scripts ?? '' }}
</body>
</html>