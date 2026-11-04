<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0c0a06">

    <title>{{ isset($title) ? $title . ' | ' : '' }}Iraka Voafidy</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|inter:400,500,600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {{ $head ?? '' }}
</head>

@php
    // Helper
    $to = fn(string $name) => Route::has($name) ? route($name) : '#';

    $menu = [
        ['label' => 'Accueil', 'route' => 'home', 'match' => 'home'],
        ['label' => 'Chorégraphies', 'route' => 'choreographies', 'match' => 'choreographies*'],
        ['label' => 'Événements', 'route' => 'events', 'match' => 'events*'],
        ['label' => 'Prière', 'route' => 'prayer', 'match' => 'prayer*'],
        ['label' => 'À propos', 'route' => 'about', 'match' => 'about'],
        ['label' => 'Contact', 'route' => 'contact', 'match' => 'contact'],
    ];

    $user = auth()->user();
    $isAdmin = $user && $user->role === 'admin';
    $isUser = $user && $user->role === 'user';
@endphp

<body x-data="{
        open: false,
        scrolled: false,
        progress: 0,
        navigating: false,
    }" x-init="
        const onScroll = () => {
            scrolled = window.scrollY > 24;
            const h = document.documentElement;
            progress = h.scrollHeight - h.clientHeight > 0
                ? (window.scrollY / (h.scrollHeight - h.clientHeight)) * 100 : 0;
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    " x-on:livewire:navigate.window="navigating = true; open = false"
    x-on:livewire:navigated.window="navigating = false; open = false" x-on:keydown.escape.window="open = false"
    x-bind:class="{ 'overflow-hidden lg:overflow-auto': open }"
    class="min-h-screen bg-night-950 font-sans text-stone-200 antialiased selection:bg-gold-500 selection:text-night-950">
    {{-- Lien d'évitement --}}
    <a href="#contenu"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-md focus:bg-gold-500 focus:px-4 focus:py-2 focus:text-night-950">
        Aller au contenu
    </a>

    {{-- Barre de progression de lecture --}}
    <div class="fixed inset-x-0 top-0 z-[60] h-0.5 bg-transparent">
        <div class="h-full bg-linear-to-r from-gold-700 via-gold-400 to-gold-500 transition-[width] duration-150"
            x-bind:style="`width:${progress}%`"></div>
    </div>

    {{-- Barre de chargement --}}
    <div x-show="navigating" x-cloak x-transition.opacity
        class="fixed inset-x-0 top-0 z-[70] h-1 overflow-hidden bg-gold-950">
        <div class="h-full w-1/3 animate-loader bg-gold-500"></div>
    </div>

    {{-- HEADER --}}
    <header class="fixed inset-x-0 top-0 z-50 animate-drop-in border-b transition-all duration-500" x-bind:class="scrolled
            ? 'border-gold-500/30 bg-night-950/85 py-2 shadow-lg shadow-black/40 backdrop-blur-xl'
            : 'border-transparent bg-transparent py-4'">

        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            aria-label="Navigation principale">

            {{-- Logo / Nom --}}
            <a href="{{ $to('home') }}" wire:navigate class="group flex items-center gap-3">
                <span class="relative grid size-10 place-items-center rounded-full border border-gold-500/60
                             transition duration-500 group-hover:rotate-180 group-hover:border-gold-400">
                    <span class="absolute inset-0 animate-ping-slow rounded-full border border-gold-500/40"></span>
                    <svg class="size-5 text-gold-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.6" aria-hidden="true">
                        <path d="M12 3v18M5 8l7-5 7 5M5 16l7 5 7-5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <span class="font-display text-2xl font-semibold tracking-wide text-gold-500 sm:text-3xl">
                    <span
                        class="animate-shimmer bg-linear-to-r from-gold-600 via-gold-300 to-gold-600 bg-[length:200%_auto] bg-clip-text text-transparent">
                        Iraka Voafidy
                    </span>
                </span>
            </a>

            {{-- Menu desktop --}}
            <ul class="hidden items-center gap-1 lg:flex">
                @foreach ($menu as $i => $item)
                    @php $active = request()->routeIs($item['match']); @endphp
                    <li class="animate-fade-down" style="animation-delay: {{ 150 + $i * 70 }}ms">
                        <a href="{{ $to($item['route']) }}" wire:navigate @if($active) aria-current="page" @endif class="group relative block px-3 py-2 text-sm font-medium transition-colors duration-300
                                      {{ $active ? 'text-gold-500' : 'text-stone-300 hover:text-gold-500' }}">
                            {{ $item['label'] }}
                            <span class="absolute inset-x-3 -bottom-0.5 h-px origin-left bg-gold-500 transition-transform duration-500
                                             {{ $active ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- Actions desktop --}}
            <div class="hidden items-center gap-3 lg:flex">
                @guest
                    <a href="{{ $to('login') }}" wire:navigate
                        class="rounded-full px-4 py-2 text-sm font-medium text-gold-500 transition hover:bg-gold-500/10">
                        Login
                    </a>
                    <a href="{{ $to('register') }}" wire:navigate class="relative overflow-hidden rounded-full bg-gold-500 px-5 py-2 text-sm font-semibold text-night-950
                                  transition duration-300 hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30
                                  before:absolute before:inset-0 before:-translate-x-full before:bg-white/30 before:skew-x-12
                                  before:transition-transform before:duration-700 hover:before:translate-x-full">
                        <span class="relative">Register</span>
                    </a>
                @endguest

                @auth
                    <div class="relative" x-data="{ menu: false }" x-on:click.outside="menu = false"
                        x-on:keydown.escape="menu = false">
                        <button type="button" x-on:click="menu = !menu" x-bind:aria-expanded="menu" class="flex items-center gap-2 rounded-full border border-gold-500/40 py-1 pl-1 pr-3 text-sm text-gold-500
                                           transition hover:border-gold-400 hover:bg-gold-500/10">
                            <span
                                class="grid size-8 place-items-center rounded-full bg-gold-500 font-semibold text-night-950">
                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                            </span>
                            <span class="max-w-28 truncate">{{ $user->name }}</span>
                            <svg class="size-4 transition-transform duration-300" x-bind:class="menu && 'rotate-180'"
                                viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M5.2 7.7a.75.75 0 011.06 0L10 11.44l3.74-3.74a.75.75 0 111.06 1.06l-4.27 4.27a.75.75 0 01-1.06 0L5.2 8.76a.75.75 0 010-1.06z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="menu" x-cloak x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-2 scale-95" class="absolute right-0 mt-3 w-60 origin-top-right overflow-hidden rounded-2xl border border-gold-500/30
                                        bg-night-900/95 shadow-2xl shadow-black/60 backdrop-blur-xl">
                            <div class="border-b border-gold-500/20 px-4 py-3">
                                <p class="truncate text-sm font-medium text-stone-100">{{ $user->name }}</p>
                                <p class="truncate text-xs text-stone-400">{{ $user->email }}</p>
                                <span
                                    class="mt-2 inline-block rounded-full border border-gold-500/50 px-2 py-0.5 text-xs text-gold-500">
                                    {{ $isAdmin ? 'Administrateur' : 'Membre' }}
                                </span>
                            </div>

                            {{-- role === admin --}}
                            @if ($isAdmin)
                                <a href="{{ $to('admin.dashboard') }}" wire:navigate
                                    class="block px-4 py-2.5 text-sm text-gold-500 transition hover:bg-gold-500/10">
                                    Tableau de bord admin
                                </a>
                                <a href="{{ $to('admin.events') }}" wire:navigate
                                    class="block px-4 py-2.5 text-sm text-gold-500 transition hover:bg-gold-500/10">
                                    Gérer les événements
                                </a>
                                <a href="{{ $to('admin.choreographies') }}" wire:navigate
                                    class="block px-4 py-2.5 text-sm text-gold-500 transition hover:bg-gold-500/10">
                                    Gérer les chorégraphies
                                </a>
                            @endif

                            {{-- role === user --}}
                            @if ($isUser)
                                <a href="{{ $to('dashboard') }}" wire:navigate
                                    class="block px-4 py-2.5 text-sm text-gold-500 transition hover:bg-gold-500/10">
                                    Mon espace
                                </a>
                                <a href="{{ $to('profile') }}" wire:navigate
                                    class="block px-4 py-2.5 text-sm text-gold-500 transition hover:bg-gold-500/10">
                                    Mon profil
                                </a>
                            @endif

                            <form method="POST" action="{{ $to('logout') }}" class="border-t border-gold-500/20">
                                @csrf
                                <button type="submit"
                                    class="w-full px-4 py-2.5 text-left text-sm text-gold-500 transition hover:bg-gold-500 hover:text-night-950">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>

            {{-- Bouton burger --}}
            <button type="button" x-on:click="open = !open" x-bind:aria-expanded="open" aria-controls="menu-mobile"
                aria-label="Ouvrir le menu"
                class="relative z-50 grid size-11 place-items-center rounded-full border border-gold-500/40 text-gold-500 transition hover:bg-gold-500/10 lg:hidden">
                <span class="sr-only">Menu</span>
                <span class="relative block h-4 w-5">
                    <span class="absolute left-0 top-0 h-0.5 w-5 rounded bg-current transition duration-300"
                        x-bind:class="open && 'translate-y-[7px] rotate-45'"></span>
                    <span class="absolute left-0 top-[7px] h-0.5 w-5 rounded bg-current transition duration-300"
                        x-bind:class="open && 'scale-x-0 opacity-0'"></span>
                    <span class="absolute left-0 top-[14px] h-0.5 w-5 rounded bg-current transition duration-300"
                        x-bind:class="open && '-translate-y-[7px] -rotate-45'"></span>
                </span>
            </button>
        </nav>
    </header>

    {{-- MENU MOBILE --}}
    <div id="menu-mobile" x-show="open" x-cloak x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 -translate-y-6" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-6"
        class="fixed inset-0 z-40 overflow-y-auto bg-night-950/95 px-6 pb-10 pt-28 backdrop-blur-2xl lg:hidden">

        <ul class="space-y-1">
            @foreach ($menu as $i => $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <li x-show="open" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
                    style="transition-delay: {{ 80 + $i * 60 }}ms">
                    <a href="{{ $to($item['route']) }}" wire:navigate class="block border-b border-gold-500/15 py-4 font-display text-3xl transition
                                  {{ $active ? 'pl-3 text-gold-500' : 'text-stone-200 hover:pl-3 hover:text-gold-500' }}">
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-8 space-y-3">
            @guest
                <a href="{{ $to('login') }}" wire:navigate
                    class="block rounded-full border border-gold-500 py-3 text-center font-medium text-gold-500 transition hover:bg-gold-500/10">
                    Login
                </a>
                <a href="{{ $to('register') }}" wire:navigate
                    class="block rounded-full bg-gold-500 py-3 text-center font-semibold text-night-950 transition hover:bg-gold-400">
                    Register
                </a>
            @endguest

            @auth
                <div class="rounded-2xl border border-gold-500/30 p-4">
                    <p class="font-medium text-stone-100">{{ $user->name }}</p>
                    <p class="text-sm text-stone-400">{{ $user->email }}</p>
                </div>

                @if ($isAdmin)
                    <a href="{{ $to('admin.dashboard') }}" wire:navigate
                        class="block rounded-full border border-gold-500 py-3 text-center font-medium text-gold-500">
                        Tableau de bord admin
                    </a>
                @endif

                @if ($isUser)
                    <a href="{{ $to('dashboard') }}" wire:navigate
                        class="block rounded-full border border-gold-500 py-3 text-center font-medium text-gold-500">
                        Mon espace
                    </a>
                @endif

                <form method="POST" action="{{ $to('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full rounded-full bg-gold-500 py-3 font-semibold text-night-950 transition hover:bg-gold-400">
                        Logout
                    </button>
                </form>
            @endauth
        </div>
    </div>

    {{-- CONTENU --}}
    <main id="contenu" class="pt-24">
        {{-- Messages flash --}}
        @if (session('status') || session('success') || session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
                x-transition:enter="transition duration-500 ease-out" x-transition:enter-start="opacity-0 -translate-y-4"
                x-transition:leave="transition duration-500 ease-in" x-transition:leave-end="opacity-0 -translate-y-4"
                role="status"
                class="mx-auto mb-4 max-w-3xl rounded-xl border px-4 py-3 text-sm
                            {{ session('error') ? 'border-red-400/50 bg-red-500/10 text-red-200' : 'border-gold-500/50 bg-gold-500/10 text-gold-300' }}">
                {{ session('status') ?? session('success') ?? session('error') }}
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{--  FOOTER --}}
    <footer x-data="{ shown: false }" x-intersect.once.margin.-80px="shown = true"
        class="relative mt-24 overflow-hidden border-t border-gold-500/30 bg-night-900/70"
        aria-labelledby="footer-titre">

        {{-- Liseré doré animé --}}
        <div class="absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-gold-500 to-transparent"></div>
        <div
            class="pointer-events-none absolute -bottom-32 left-1/2 size-96 -translate-x-1/2 rounded-full bg-gold-500/10 blur-3xl">
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-12">

                {{-- Marque --}}
                <div class="transition duration-700 ease-out lg:col-span-4"
                    x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'">
                    <a href="{{ $to('home') }}" wire:navigate class="inline-block">
                        <h2 id="footer-titre" class="font-display text-3xl font-semibold text-gold-500">Iraka Voafidy
                        </h2>
                    </a>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-stone-400">
                        Une communauté qui danse, prie et se rassemble. Retrouvez nos chorégraphies,
                        nos prochains événements et nos temps de prière.
                    </p>

                    {{-- Réseaux sociaux --}}
                    <ul class="mt-6 flex items-center gap-3">
                        <li>
                            <a href="#" aria-label="Facebook" rel="noopener"
                                class="grid size-10 place-items-center rounded-full border border-gold-500/40 text-gold-500 transition duration-300 hover:-translate-y-1 hover:bg-gold-500 hover:text-night-950">
                                <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M13.5 21v-8h2.7l.4-3.2h-3.1V7.8c0-.9.3-1.5 1.6-1.5h1.7V3.4c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.5-4 4.1v2.4H7.7V13h2.7v8h3.1z" />
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="#" aria-label="Instagram" rel="noopener"
                                class="grid size-10 place-items-center rounded-full border border-gold-500/40 text-gold-500 transition duration-300 hover:-translate-y-1 hover:bg-gold-500 hover:text-night-950">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" aria-hidden="true">
                                    <rect x="3" y="3" width="18" height="18" rx="5" />
                                    <circle cx="12" cy="12" r="4" />
                                    <circle cx="17.5" cy="6.5" r="1" fill="currentColor" />
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="#" aria-label="YouTube" rel="noopener"
                                class="grid size-10 place-items-center rounded-full border border-gold-500/40 text-gold-500 transition duration-300 hover:-translate-y-1 hover:bg-gold-500 hover:text-night-950">
                                <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M21.6 7.2a2.5 2.5 0 00-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 002.4 7.2C2 8.8 2 12 2 12s0 3.2.4 4.8a2.5 2.5 0 001.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 001.8-1.8c.4-1.6.4-4.8.4-4.8s0-3.2-.4-4.8zM10 15V9l5.2 3L10 15z" />
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="#" aria-label="WhatsApp" rel="noopener"
                                class="grid size-10 place-items-center rounded-full border border-gold-500/40 text-gold-500 transition duration-300 hover:-translate-y-1 hover:bg-gold-500 hover:text-night-950">
                                <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm5.2 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7a11.700 11.700 0 01-4.800-4.200c-.4-.5-1.200-1.600-1.200-3s.7-2.100 1-2.400c.2-.3.500-.3.700-.3h.500c.2 0 .4 0 .6.500l.8 2c.1.200.1.400 0 .5l-.4.600-.3.400c-.1.200-.3.300-.1.600.2.300.8 1.300 1.700 2.100 1.100 1 2.100 1.300 2.400 1.500.3.100.4.100.6-.1l.8-1c.2-.3.400-.2.700-.1l1.900.9c.3.100.5.200.5.400.1.100.1.700-.1 1.300z" />
                                </svg>
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Navigation --}}
                <nav class="transition delay-100 duration-700 ease-out lg:col-span-2"
                    x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'"
                    aria-label="Navigation du pied de page">
                    <h3 class="font-display text-xl text-gold-500">Découvrir</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach ($menu as $item)
                            <li>
                                <a href="{{ $to($item['route']) }}" wire:navigate
                                    class="group inline-flex items-center gap-2 text-stone-400 transition duration-300 hover:translate-x-1 hover:text-gold-500">
                                    <span class="h-px w-0 bg-gold-500 transition-all duration-300 group-hover:w-3"></span>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                {{-- Compte (selon le rôle) --}}
                <div class="transition delay-200 duration-700 ease-out lg:col-span-2"
                    x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'">
                    <h3 class="font-display text-xl text-gold-500">Mon compte</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @guest
                            <li><a href="{{ $to('login') }}" wire:navigate
                                    class="text-stone-400 transition hover:text-gold-500">Login</a></li>
                            <li><a href="{{ $to('register') }}" wire:navigate
                                    class="text-stone-400 transition hover:text-gold-500">Register</a></li>
                        @endguest

                        @auth
                            @if ($isAdmin)
                                <li><a href="{{ $to('admin.dashboard') }}" wire:navigate
                                        class="text-stone-400 transition hover:text-gold-500">Tableau de bord admin</a></li>
                                <li><a href="{{ $to('admin.events') }}" wire:navigate
                                        class="text-stone-400 transition hover:text-gold-500">Gérer les événements</a></li>
                                <li><a href="{{ $to('admin.choreographies') }}" wire:navigate
                                        class="text-stone-400 transition hover:text-gold-500">Gérer les chorégraphies</a></li>
                            @endif
                            @if ($isUser)
                                <li><a href="{{ $to('dashboard') }}" wire:navigate
                                        class="text-stone-400 transition hover:text-gold-500">Mon espace</a></li>
                                <li><a href="{{ $to('profile') }}" wire:navigate
                                        class="text-stone-400 transition hover:text-gold-500">Mon profil</a></li>
                            @endif
                            <li>
                                <form method="POST" action="{{ $to('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="text-stone-400 transition hover:text-gold-500">Logout</button>
                                </form>
                            </li>
                        @endauth
                    </ul>
                </div>

                {{-- Contact + horaires --}}
                <div class="transition delay-300 duration-700 ease-out sm:col-span-2 lg:col-span-4"
                    x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'">
                    <h3 class="font-display text-xl text-gold-500">Nous retrouver</h3>
                    <ul class="mt-4 space-y-3 text-sm text-stone-400">
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 size-5 shrink-0 text-gold-500" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path d="M12 21s7-6.100 7-11a7 7 0 10-14 0c0 4.900 7 11 7 11z" />
                                <circle cx="12" cy="10" r="2.500" />
                            </svg>
                            <span>Antananarivo, Madagascar</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 size-5 shrink-0 text-gold-500" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path d="M3 7l9 6 9-6" />
                            </svg>
                            <a href="mailto:contact@irakavoafidy.mg"
                                class="transition hover:text-gold-500">contact@irakavoafidy.mg</a>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 size-5 shrink-0 text-gold-500" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path
                                    d="M5 4h4l2 5-2.500 1.500a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z" />
                            </svg>
                            <a href="tel:+261340000000" class="transition hover:text-gold-500">+261 34 00 000 00</a>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 size-5 shrink-0 text-gold-500" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 7v5l3 2" />
                            </svg>
                            <span>Répétitions et prière : consultez la page Prière</span>
                        </li>
                    </ul>

                    <a href="{{ $to('contact') }}" wire:navigate class="mt-6 inline-flex items-center gap-2 rounded-full border border-gold-500 px-5 py-2 text-sm font-medium text-gold-500
                              transition duration-300 hover:bg-gold-500 hover:text-night-950">
                        Nous écrire
                    </a>
                </div>
            </div>

            {{-- Barre du bas --}}
            <div
                class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-gold-500/20 pt-6 text-xs text-stone-500 sm:flex-row">
                <p>&copy; {{ date('Y') }} Iraka Voafidy. Tous droits réservés.</p>

                <ul class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
                    <li><a href="{{ $to('privacy') }}" wire:navigate
                            class="transition hover:text-gold-500">Confidentialité</a></li>
                    <li><a href="{{ $to('terms') }}" wire:navigate class="transition hover:text-gold-500">Conditions
                            d’utilisation</a></li>
                    <li>
                        <button type="button" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                            class="transition hover:text-gold-500">
                            Haut de page
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </footer>

    {{-- Retour en haut --}}
    <button type="button" x-show="scrolled" x-cloak x-transition:enter="transition duration-300"
        x-transition:enter-start="opacity-0 translate-y-4" x-transition:leave="transition duration-200"
        x-transition:leave-end="opacity-0 translate-y-4" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        aria-label="Retour en haut" class="fixed bottom-6 right-6 z-40 grid size-12 place-items-center rounded-full bg-gold-500 text-night-950
                   shadow-lg shadow-gold-500/30 transition hover:-translate-y-1 hover:bg-gold-400">
        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M10 17a.75.75 0 01-.75-.75V5.56L5.28 9.53a.75.75 0 01-1.06-1.06l5.25-5.25a.75.75 0 011.06 0l5.25 5.25a.75.75 0 11-1.06 1.06l-3.97-3.97v10.69A.75.75 0 0110 17z"
                clip-rule="evenodd" />
        </svg>
    </button>

    @livewireScripts
    {{ $scripts ?? '' }}
    @stack('scripts')
</body>

</html>