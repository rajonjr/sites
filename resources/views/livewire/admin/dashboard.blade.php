<div>
@php
    $has = fn (string $name) => Route::has($name);
    $to  = fn (string $name) => Route::has($name) ? route($name) : '#';

    $kpis    = $this->kpis;
    $series  = $this->series;
    $members = $this->members;
    $me      = auth()->user();

    // ---------- Navigation d'administration ----------
    $nav = [
        ['Tableau de bord',      'admin.dashboard',      true],
        ['Événements',           'admin.events',         false],
        ['Chorégraphies',        'admin.choreographies', false],
        ['Intentions de prière', 'admin.prayers',        false],
    ];

    $quick = [
        ['Nouvel événement',      'admin.events.create',         'M12 5v14M5 12h14'],
        ['Nouvelle chorégraphie', 'admin.choreographies.create', 'M12 5v14M5 12h14'],
        ['Voir le site',          'home',                        'M5 12h14M13 6l6 6-6 6'],
    ];

    // ---------- Graphique ----------
    $W = 600; $H = 200; $padTop = 20; $padBottom = 24;
    $counts = $series->pluck('count')->all();
    $max    = max(max($counts), 1);
    $n      = count($counts);
    $stepX  = $W / max($n - 1, 1);
    $plotH  = $H - $padTop - $padBottom;

    $points = [];
    foreach ($counts as $i => $c) {
        $points[] = [round($i * $stepX, 1), round($padTop + $plotH - ($c / $max) * $plotH, 1)];
    }
    $line = 'M'.implode(' L', array_map(fn ($p) => $p[0].' '.$p[1], $points));
    $area = $line.' L'.$W.' '.($H - $padBottom).' L0 '.($H - $padBottom).' Z';
    $totalPeriod = array_sum($counts);

    $activityIcon = [
        'user'   => 'M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8z',
        'event'  => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z',
        'choreo' => 'M9 18V5l12-2v13',
        'prayer' => 'M12 21s-7-4.4-7-10a4 4 0 017-2.6A4 4 0 0119 11c0 5.6-7 10-7 10z',
    ];

    $field = 'rounded-full border border-gold-500/30 bg-night-900 px-4 py-2.5 text-sm text-stone-100 placeholder:text-stone-500 focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30';
@endphp

<style>
    @keyframes dash-draw { to { stroke-dashoffset: 0; } }
    @keyframes area-in   { from { opacity: 0; } to { opacity: 1; } }
</style>

{{-- ============================ NOTIFICATIONS ============================ --}}
<div class="pointer-events-none fixed bottom-6 right-6 z-[90] flex w-80 max-w-[calc(100vw-3rem)] flex-col gap-3"
     x-data="{
         toasts: [],
         add(d) {
             const id = Date.now() + Math.random();
             this.toasts.push({ id, message: d.message, type: d.type || 'success' });
             setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 4500);
         }
     }"
     x-on:toast.window="add($event.detail)" aria-live="polite">
    <template x-for="t in toasts" :key="t.id">
        <div x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0 translate-x-8"
             class="pointer-events-auto rounded-2xl border px-4 py-3 text-sm shadow-xl shadow-black/40 backdrop-blur"
             :class="t.type === 'error' ? 'border-rose-400/50 bg-rose-950/90 text-rose-100' : 'border-gold-500/50 bg-night-900/95 text-gold-300'"
             role="status" x-text="t.message"></div>
    </template>
</div>

<div class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8">

    {{-- EN-TÊTE --}}
    <header class="flex flex-col gap-6 pt-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="animate-rise">
            <p class="text-sm capitalize text-gold-300/80">{{ $today }}</p>
            <h1 class="mt-2 font-display text-4xl font-semibold text-gold-500 sm:text-6xl">{{ $greeting }}, {{ Str::before($me->name, ' ') }}</h1>
            <p class="mt-2 flex items-center gap-2 text-sm text-stone-400">
                <span class="relative flex size-2.5"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400/60"></span><span class="relative inline-flex size-2.5 rounded-full bg-emerald-400"></span></span>
                Données en direct · mises à jour à {{ now()->format('H:i:s') }}
            </p>
        </div>

        <div class="flex animate-rise flex-wrap gap-3" style="animation-delay:.15s">
            <button type="button" wire:click="$refresh" wire:loading.attr="disabled" wire:target="$refresh"
                    class="inline-flex items-center gap-2 rounded-full border border-gold-500/40 px-5 py-2.5 text-sm text-gold-500 transition hover:bg-gold-500/10 disabled:opacity-60">
                <svg class="size-4" wire:loading.class="animate-spin" wire:target="$refresh" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 11-3-6.7M21 4v5h-5"/></svg>
                Actualiser
            </button>

            @foreach ($quick as [$label, $route, $icon])
                <a href="{{ $to($route) }}" @if ($has($route)) wire:navigate @else aria-disabled="true" @endif
                   class="inline-flex items-center gap-2 rounded-full border px-5 py-2.5 text-sm font-medium transition duration-300
                          {{ $has($route) ? 'border-gold-500 text-gold-500 hover:-translate-y-0.5 hover:bg-gold-500 hover:text-night-950' : 'pointer-events-none border-gold-500/20 text-stone-600' }}">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}"/></svg>
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </header>

    @if ($this->debugWarning)
        <div class="mt-6 rounded-2xl border border-rose-400/50 bg-rose-500/10 px-5 py-4 text-sm text-rose-200" role="alert">
            <strong class="font-semibold">Attention :</strong> <code>APP_DEBUG</code> est activé en production. Désactivez-le dans le fichier <code>.env</code>.
        </div>
    @endif

    {{-- Navigation d'administration --}}
    <nav class="-mx-4 mt-8 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Administration">
        <ul class="flex w-max gap-2 border-b border-gold-500/20 pb-3 sm:w-auto">
            @foreach ($nav as [$label, $route, $current])
                <li>
                    <a href="{{ $to($route) }}" @if ($has($route)) wire:navigate @else aria-disabled="true" @endif @if ($current) aria-current="page" @endif
                       class="block whitespace-nowrap rounded-full px-4 py-2 text-sm transition duration-300
                              {{ $current ? 'bg-gold-500 font-medium text-night-950' : ($has($route) ? 'text-stone-300 hover:bg-gold-500/10 hover:text-gold-500' : 'pointer-events-none text-stone-600') }}">{{ $label }}</a>
                </li>
            @endforeach
        </ul>
    </nav>

    {{-- Zone actualisée automatiquement toutes les 30 secondes (uniquement si l'onglet est visible) --}}
    <div wire:poll.30s.visible>

        {{-- ============================ INDICATEURS ============================ --}}
        <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5" aria-label="Indicateurs clés">
            @foreach ($kpis as $i => $k)
                <div wire:key="kpi-{{ $k['label'] }}-{{ $k['value'] }}-{{ $period }}"
                     class="group relative animate-rise overflow-hidden rounded-3xl border bg-night-900/70 p-6 transition duration-500 hover:-translate-y-1.5 hover:shadow-xl hover:shadow-gold-500/10
                            {{ (($k['alert'] ?? false) && $k['value'] > 0) ? 'border-gold-500/70' : 'border-gold-500/20 hover:border-gold-500/60' }}"
                     style="animation-delay: {{ $i * 80 }}ms"
                     x-data="{ n: 0 }"
                     x-init="
                        const start = performance.now();
                        const tick = (t) => {
                            const p = Math.min((t - start) / 1200, 1);
                            n = Math.round({{ (int) $k['value'] }} * (1 - Math.pow(1 - p, 3)));
                            if (p < 1) requestAnimationFrame(tick);
                        };
                        requestAnimationFrame(tick);
                     ">
                    <div class="absolute -right-8 -top-8 size-28 rounded-full bg-gold-500/10 transition duration-700 group-hover:scale-[2.2]"></div>

                    <div class="relative flex items-start justify-between">
                        <span class="grid size-11 place-items-center rounded-full border border-gold-500/40 text-gold-500">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $k['icon'] }}"/></svg>
                        </span>

                        @if ($k['delta'] !== null)
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $k['delta'] >= 0 ? 'bg-emerald-500/15 text-emerald-300' : 'bg-rose-500/15 text-rose-300' }}" title="Par rapport aux {{ $period }} jours précédents">
                                {{ $k['delta'] >= 0 ? '↑' : '↓' }} {{ abs($k['delta']) }} %
                            </span>
                        @elseif (($k['alert'] ?? false) && $k['value'] > 0)
                            <span class="relative flex size-3"><span class="absolute inline-flex size-full animate-ping rounded-full bg-gold-500/60"></span><span class="relative inline-flex size-3 rounded-full bg-gold-500"></span></span>
                        @endif
                    </div>

                    <p class="relative mt-5 font-display text-5xl text-stone-100 tabular-nums" x-text="n">0</p>
                    <p class="relative mt-1 text-sm text-gold-500">{{ $k['label'] }}</p>
                    <p class="relative text-xs text-stone-500">{{ $k['hint'] }}</p>
                </div>
            @endforeach
        </section>

        {{-- ============================ GRAPHIQUE + MODÉRATION ============================ --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-3">

            {{-- Graphique --}}
            <section class="rounded-3xl border border-gold-500/20 bg-night-900/70 p-6 sm:p-8 lg:col-span-2" aria-labelledby="chart-titre">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 id="chart-titre" class="font-display text-3xl text-gold-500">Inscriptions</h2>
                        <p class="text-sm text-stone-400">{{ $totalPeriod }} nouveau{{ $totalPeriod > 1 ? 'x' : '' }} membre{{ $totalPeriod > 1 ? 's' : '' }} sur {{ $period }} jours</p>
                    </div>

                    {{-- Sélecteur de période --}}
                    <div class="flex rounded-full border border-gold-500/30 p-1" role="group" aria-label="Période">
                        @foreach ([7, 30, 90] as $p)
                            <button type="button" wire:click="$set('period', {{ $p }})" aria-pressed="{{ $period === $p ? 'true' : 'false' }}"
                                    class="rounded-full px-4 py-1.5 text-sm transition duration-300 {{ $period === $p ? 'bg-gold-500 font-medium text-night-950' : 'text-stone-300 hover:text-gold-500' }}">{{ $p }} j</button>
                        @endforeach
                    </div>
                </div>

                <div class="relative mt-6 transition-opacity" wire:loading.class="opacity-40" wire:target="period"
                     wire:key="chart-{{ $period }}"
                     x-data="{ i: null, labels: @js($series->pluck('label')), vals: @js($counts) }">
                    <div x-show="i !== null" x-cloak x-transition.opacity
                         class="pointer-events-none absolute top-0 z-10 -translate-x-1/2 whitespace-nowrap rounded-xl border border-gold-500/40 bg-night-950 px-3 py-1.5 text-xs shadow-lg"
                         :style="`left:${(i * {{ $stepX }}) / {{ $W }} * 100}%`">
                        <span class="text-stone-400" x-text="labels[i]"></span> ·
                        <span class="font-medium text-gold-500" x-text="vals[i] + (vals[i] > 1 ? ' inscriptions' : ' inscription')"></span>
                    </div>

                    <svg viewBox="0 0 {{ $W }} {{ $H }}" class="mt-6 block h-auto w-full" role="img"
                         aria-label="Courbe des inscriptions sur {{ $period }} jours : {{ $totalPeriod }} au total">
                        <defs>
                            <linearGradient id="areaGold" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#d4a017" stop-opacity=".35"/>
                                <stop offset="100%" stop-color="#d4a017" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        @foreach ([0, .5, 1] as $g)
                            <line x1="0" x2="{{ $W }}" y1="{{ $padTop + $plotH * $g }}" y2="{{ $padTop + $plotH * $g }}" stroke="#d4a017" stroke-opacity=".12" stroke-dasharray="4 6"/>
                        @endforeach
                        <text x="0" y="{{ $padTop - 6 }}" fill="#78716c" font-size="11">{{ $max }}</text>

                        <path d="{{ $area }}" fill="url(#areaGold)" style="animation: area-in 1.2s .6s ease-out both"/>
                        <path d="{{ $line }}" fill="none" stroke="#d4a017" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                              pathLength="1" style="stroke-dasharray:1; stroke-dashoffset:1; animation: dash-draw 1.8s ease-out forwards"/>

                        @foreach ($points as $idx => [$px, $py])
                            <circle x-show="i === {{ $idx }}" cx="{{ $px }}" cy="{{ $py }}" r="5" fill="#d4a017" stroke="#0c0a06" stroke-width="2"/>
                        @endforeach
                        @foreach ($points as $idx => [$px, $py])
                            <rect x="{{ max($px - $stepX / 2, 0) }}" y="0" width="{{ $stepX }}" height="{{ $H }}" fill="transparent"
                                  x-on:mouseenter="i = {{ $idx }}" x-on:mouseleave="i = null"/>
                        @endforeach

                        <text x="0" y="{{ $H - 4 }}" fill="#78716c" font-size="11">{{ $series->first()['label'] }}</text>
                        <text x="{{ $W / 2 }}" y="{{ $H - 4 }}" fill="#78716c" font-size="11" text-anchor="middle">{{ $series[(int) ($n / 2)]['label'] }}</text>
                        <text x="{{ $W }}" y="{{ $H - 4 }}" fill="#78716c" font-size="11" text-anchor="end">{{ $series->last()['label'] }}</text>
                    </svg>
                </div>
            </section>

            {{-- Modération --}}
            <section class="rounded-3xl border border-gold-500/20 bg-night-900/70 p-6 sm:p-8" aria-labelledby="pending-titre">
                <div class="flex items-center justify-between">
                    <h2 id="pending-titre" class="font-display text-3xl text-gold-500">À modérer</h2>
                    @if ($has('admin.prayers'))<a href="{{ route('admin.prayers') }}" wire:navigate class="text-sm text-gold-500 hover:underline">Tout voir</a>@endif
                </div>

                @forelse ($this->pending as $p)
                    <article wire:key="pending-{{ $p['id'] }}" class="mt-5 animate-rise rounded-2xl border border-gold-500/15 bg-night-950/50 p-4">
                        <p class="text-sm leading-relaxed text-stone-200">{{ $p['text'] }}</p>
                        <p class="mt-2 text-xs text-stone-500">{{ $p['who'] }} · {{ $p['when'] }}</p>

                        <div class="mt-3 flex gap-2">
                            <button type="button" wire:click="approve({{ $p['id'] }})" wire:loading.attr="disabled" wire:target="approve({{ $p['id'] }}),reject({{ $p['id'] }})"
                                    class="rounded-full bg-gold-500 px-4 py-1.5 text-xs font-semibold text-night-950 transition hover:bg-gold-400 disabled:opacity-50">Publier</button>
                            <button type="button" wire:click="reject({{ $p['id'] }})" wire:confirm="Refuser et supprimer cette intention ?" wire:loading.attr="disabled" wire:target="approve({{ $p['id'] }}),reject({{ $p['id'] }})"
                                    class="rounded-full border border-rose-400/50 px-4 py-1.5 text-xs text-rose-300 transition hover:bg-rose-500/10 disabled:opacity-50">Refuser</button>
                        </div>
                    </article>
                @empty
                    <div class="mt-6 rounded-2xl border border-dashed border-gold-500/25 p-8 text-center">
                        <p class="font-display text-2xl text-stone-300">Rien en attente</p>
                        <p class="mt-1 text-sm text-stone-500">Toutes les intentions ont été traitées.</p>
                    </div>
                @endforelse
            </section>
        </div>

        {{-- ============================ ÉVÉNEMENTS + ACTIVITÉ ============================ --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="rounded-3xl border border-gold-500/20 bg-night-900/70 p-6 sm:p-8" aria-labelledby="events-titre">
                <div class="flex items-center justify-between">
                    <h2 id="events-titre" class="font-display text-3xl text-gold-500">À venir</h2>
                    @if ($has('admin.events'))<a href="{{ route('admin.events') }}" wire:navigate class="text-sm text-gold-500 hover:underline">Gérer</a>@endif
                </div>
                <ul class="mt-5 space-y-3">
                    @forelse ($this->upcomingEvents as $e)
                        <li class="flex items-center gap-4 rounded-2xl border border-gold-500/10 p-3 transition hover:border-gold-500/40 hover:bg-gold-500/5">
                            <div class="w-12 shrink-0 text-center">
                                <span class="block font-display text-2xl leading-none text-gold-500">{{ $e['day'] }}</span>
                                <span class="block text-[11px] uppercase text-stone-500">{{ $e['month'] }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm text-stone-100">{{ $e['title'] }}</p>
                                <p class="truncate text-xs text-stone-500">{{ $e['time'] }}@if ($e['place']) · {{ $e['place'] }}@endif</p>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-2xl border border-dashed border-gold-500/25 p-6 text-center text-sm text-stone-500">Aucun événement programmé.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-3xl border border-gold-500/20 bg-night-900/70 p-6 sm:p-8" aria-labelledby="activity-titre">
                <h2 id="activity-titre" class="font-display text-3xl text-gold-500">Activité récente</h2>
                <ol class="relative mt-5 space-y-5 border-l border-gold-500/25 pl-6">
                    @forelse ($this->activity as $a)
                        <li class="relative">
                            <span class="absolute -left-[2.15rem] grid size-7 place-items-center rounded-full border border-gold-500/50 bg-night-950 text-gold-500">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $activityIcon[$a['type']] ?? $activityIcon['user'] }}"/></svg>
                            </span>
                            <p class="text-sm text-stone-200">{{ $a['text'] }}</p>
                            <p class="text-xs text-stone-500">{{ $a['when'] }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-stone-500">Aucune activité pour l’instant.</li>
                    @endforelse
                </ol>
            </section>
        </div>
    </div>

    {{-- ============================ MEMBRES (hors actualisation automatique) ============================ --}}
    <section class="mt-6 rounded-3xl border border-gold-500/20 bg-night-900/70 p-6 sm:p-8" aria-labelledby="members-titre">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 id="members-titre" class="font-display text-3xl text-gold-500">Membres</h2>
                <p class="text-sm text-stone-400">{{ $members->total() }} résultat{{ $members->total() > 1 ? 's' : '' }}</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <label class="relative block">
                    <span class="sr-only">Rechercher un membre</span>
                    <svg class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-gold-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.45 4.39l3.58 3.58a.75.75 0 11-1.06 1.06l-3.58-3.58A7 7 0 012 9z" clip-rule="evenodd"/></svg>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Nom ou email" class="{{ $field }} w-full pl-10 sm:w-64">
                </label>
                <label class="block">
                    <span class="sr-only">Filtrer par rôle</span>
                    <select wire:model.live="roleFilter" class="{{ $field }} w-full">
                        <option value="all">Tous les rôles</option>
                        <option value="admin">Administrateurs</option>
                        <option value="user">Membres</option>
                    </select>
                </label>
                @if ($search !== '' || $roleFilter !== 'all')
                    <button type="button" wire:click="resetFilters" class="rounded-full px-4 py-2.5 text-sm text-gold-500 hover:underline">Réinitialiser</button>
                @endif
            </div>
        </div>

        <div class="mt-6 overflow-x-auto transition-opacity" wire:loading.class="opacity-50" wire:target="search,roleFilter,sortBy,previousPage,nextPage,setRole">
            <table class="w-full min-w-[34rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-gold-500/20 text-xs uppercase tracking-wider text-stone-500">
                        @foreach (['name' => 'Nom', 'email' => 'Email', 'role' => 'Rôle', 'created_at' => 'Inscrit'] as $col => $label)
                            <th scope="col" class="py-3 pr-4 font-medium" aria-sort="{{ $sortField === $col ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                <button type="button" wire:click="sortBy('{{ $col }}')" class="inline-flex items-center gap-1 uppercase tracking-wider transition hover:text-gold-500 {{ $sortField === $col ? 'text-gold-500' : '' }}">
                                    {{ $label }}
                                    @if ($sortField === $col)<span aria-hidden="true">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif
                                </button>
                            </th>
                        @endforeach
                        <th scope="col" class="py-3 text-right font-medium"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gold-500/10">
                    @forelse ($members as $m)
                        <tr wire:key="member-{{ $m->id }}" class="transition hover:bg-gold-500/5">
                            <td class="py-3 pr-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-gold-500/15 font-display text-base text-gold-500">{{ mb_strtoupper(mb_substr($m->name, 0, 1)) }}</span>
                                    <span class="truncate text-stone-100">{{ $m->name }}@if ($m->id === $me->id) <span class="text-xs text-stone-500">(vous)</span>@endif</span>
                                </div>
                            </td>
                            <td class="max-w-56 truncate py-3 pr-4 text-stone-400">{{ $m->email }}</td>
                            <td class="py-3 pr-4">
                                <span class="rounded-full border px-3 py-0.5 text-xs {{ $m->role === 'admin' ? 'border-gold-500/60 text-gold-300' : 'border-stone-600 text-stone-400' }}">{{ $m->role === 'admin' ? 'Admin' : 'Membre' }}</span>
                            </td>
                            <td class="whitespace-nowrap py-3 pr-4 text-stone-500">{{ $m->created_at?->locale('fr')->translatedFormat('j M Y') }}</td>
                            <td class="py-3 text-right">
                                @if ($m->id !== $me->id)
                                    @if ($m->role === 'admin')
                                        <button type="button" wire:click="setRole({{ $m->id }}, 'user')" wire:confirm="Retirer les droits d’administrateur à {{ $m->name }} ?"
                                                class="rounded-full border border-gold-500/30 px-3 py-1 text-xs text-stone-300 transition hover:border-rose-400/60 hover:text-rose-300">Rétrograder</button>
                                    @else
                                        <button type="button" wire:click="setRole({{ $m->id }}, 'admin')" wire:confirm="Donner les droits d’administrateur à {{ $m->name }} ?"
                                                class="rounded-full border border-gold-500/40 px-3 py-1 text-xs text-gold-500 transition hover:bg-gold-500 hover:text-night-950">Promouvoir</button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-stone-500">Aucun membre ne correspond à votre recherche.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <div class="mt-5 flex items-center justify-between text-sm">
                <button type="button" wire:click="previousPage" @disabled($members->onFirstPage())
                        class="rounded-full border border-gold-500/30 px-4 py-2 text-gold-500 transition hover:bg-gold-500/10 disabled:cursor-not-allowed disabled:opacity-30">← Précédent</button>
                <span class="text-stone-400">Page {{ $members->currentPage() }} sur {{ $members->lastPage() }}</span>
                <button type="button" wire:click="nextPage" @disabled(! $members->hasMorePages())
                        class="rounded-full border border-gold-500/30 px-4 py-2 text-gold-500 transition hover:bg-gold-500/10 disabled:cursor-not-allowed disabled:opacity-30">Suivant →</button>
            </div>
        @endif
    </section>

    {{-- ============================ SYSTÈME ============================ --}}
    <section class="mt-6 rounded-3xl border border-gold-500/20 bg-night-900/50 p-6 sm:p-8" aria-labelledby="system-titre">
        <h2 id="system-titre" class="font-display text-2xl text-gold-500">Système</h2>
        <dl class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
            @foreach ($this->system as [$label, $value])
                <div>
                    <dt class="text-xs uppercase tracking-wider text-stone-500">{{ $label }}</dt>
                    <dd class="mt-1 text-stone-200">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
</div>
</div>