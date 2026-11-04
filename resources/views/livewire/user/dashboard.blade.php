<div>
@php
    $to = fn (string $name) => Route::has($name) ? route($name) : '#';

    $me        = auth()->user();
    $events    = $this->events;
    $myEvents  = $this->myEvents;
    $requests  = $this->myRequests;
    $checklist = $this->checklist;

    $doneCount  = collect($checklist)->where('done', true)->count();
    $totalCount = count($checklist);

    // Horaires de prière (les mêmes que sur la page Prière)
    $schedule = [['06:00', 'Prière du matin'], ['12:00', 'Prière de midi'], ['18:30', 'Prière du soir']];

    $tabs = [
        'apercu'     => 'Aperçu',
        'evenements' => 'Événements',
        'favoris'    => 'Favoris',
        'intentions' => 'Intentions',
        'parametres' => 'Paramètres',
    ];

    $field = 'w-full rounded-2xl border bg-night-900 px-4 py-3 text-stone-100 transition focus:outline-none focus:ring-2';
    $ok    = 'border-gold-500/30 focus:border-gold-500 focus:ring-gold-500/30';
    $bad   = 'border-rose-400/70 focus:border-rose-400 focus:ring-rose-400/30';
    $arrow = 'M3 10a.75.75 0 01.75-.75h10.69L10.47 5.28a.75.75 0 011.06-1.06l5.25 5.25a.75.75 0 010 1.06l-5.25 5.25a.75.75 0 11-1.06-1.06l3.97-3.97H3.75A.75.75 0 013 10z';
    $spinner = 'M21 12a9 9 0 00-9-9';
@endphp

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

<div class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8"
    x-data="{
        tab: 'apercu',
        tabs: @js(array_keys($tabs)),

        choreos: @js($this->choreographies),
        favC: $persist([]).as('iv-choreo-favs'),
        favP: $persist([]).as('iv-prayer-favs'),

        times: @js(array_column($schedule, 0)),
        labels: @js(array_column($schedule, 1)),
        now: Date.now(),
        timer: null,

        init() {
            const h = location.hash.replace('#', '');
            if (this.tabs.includes(h)) this.tab = h;
            this.timer = setInterval(() => this.now = Date.now(), 1000);
        },
        setTab(t) { this.tab = t; history.replaceState(null, '', '#' + t); },

        get favChoreos() { return this.choreos.filter(c => this.favC.includes(c.id)); },
        removeFav(id) { this.favC = this.favC.filter(f => f !== id); },

        /* Part de profil complétée : étapes serveur + « avoir gardé une chorégraphie ou une prière » */
        ratio(done, total) { return (done + (this.favC.length + this.favP.length > 0 ? 1 : 0)) / (total + 1); },

        at(t, plus = 0) { const [h, m] = t.split(':').map(Number); const d = new Date(this.now); d.setHours(h, m, 0, 0); d.setDate(d.getDate() + plus); return d.getTime(); },
        get slots() { return this.times.flatMap((t, i) => [0, 1].map(d => ({ i, ts: this.at(t, d) }))).sort((a, b) => a.ts - b.ts); },
        get current() { return this.slots.find(s => this.now >= s.ts && this.now < s.ts + 30 * 60000) || null; },
        get next() { return this.slots.find(s => s.ts > this.now); },
        get cd() { const s = Math.max(0, Math.floor((this.next.ts - this.now) / 1000)); return { h: Math.floor(s / 3600), m: Math.floor(s % 3600 / 60), s: s % 60 }; },
        pad(n) { return String(n).padStart(2, '0'); }
    }"
    x-on:livewire:navigating.window="clearInterval(timer)">

    {{-- ============================ EN-TÊTE ============================ --}}
    <header class="flex flex-col gap-6 pt-6 sm:flex-row sm:items-center">
        <span class="grid size-20 shrink-0 animate-rise place-items-center rounded-full border-2 border-gold-500/60 bg-gold-500/10 font-display text-4xl text-gold-500">
            {{ mb_strtoupper(mb_substr($me->name, 0, 1)) }}
        </span>

        <div class="animate-rise" style="animation-delay:.1s">
            <p class="text-sm capitalize text-gold-300/80">{{ $today }}</p>
            <h1 class="mt-1 font-display text-4xl font-semibold text-gold-500 sm:text-5xl">{{ $greeting }}, {{ Str::before($me->name, ' ') }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-stone-400">
                @if ($since) <span>Membre depuis {{ $since }}</span> @endif
                @if ($verified === true)
                    <span class="inline-flex items-center gap-1 text-emerald-300">
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        Email vérifié
                    </span>
                @elseif ($verified === false)
                    <a href="{{ $to('verification.notice') }}" class="text-gold-300 underline hover:text-gold-500">Email non vérifié</a>
                @endif
            </div>
        </div>
    </header>

    {{-- ============================ CHIFFRES ============================ --}}
    <section class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4" aria-label="Mon activité">
        @foreach ([
            ['Mes inscriptions', $myEvents->count(), 'evenements', null],
            ['Chorégraphies gardées', null, 'favoris', 'favChoreos.length'],
            ['Prières gardées', null, 'favoris', 'favP.length'],
            ['Mes intentions', $requests->count(), 'intentions', null],
        ] as $i => [$label, $value, $target, $expr])
            <button type="button" x-on:click="setTab('{{ $target }}')"
                    class="group animate-rise rounded-3xl border border-gold-500/20 bg-night-900/70 p-5 text-left transition duration-500 hover:-translate-y-1.5 hover:border-gold-500/60 hover:shadow-xl hover:shadow-gold-500/10"
                    style="animation-delay: {{ $i * 80 }}ms">
                <span class="block font-display text-5xl text-stone-100 tabular-nums" @if ($expr) x-text="{{ $expr }}" @endif>{{ $value ?? 0 }}</span>
                <span class="mt-1 block text-sm text-gold-500">{{ $label }}</span>
            </button>
        @endforeach
    </section>

    {{-- ============================ ONGLETS ============================ --}}
    <nav class="-mx-4 mt-10 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Sections de mon espace">
        <ul class="flex w-max gap-2 border-b border-gold-500/20 pb-3 sm:w-auto" role="tablist">
            @foreach ($tabs as $key => $label)
                <li>
                    <button type="button" role="tab" x-on:click="setTab('{{ $key }}')" :aria-selected="tab === '{{ $key }}'"
                            class="whitespace-nowrap rounded-full px-4 py-2 text-sm transition duration-300"
                            :class="tab === '{{ $key }}' ? 'bg-gold-500 font-medium text-night-950' : 'text-stone-300 hover:bg-gold-500/10 hover:text-gold-500'">{{ $label }}</button>
                </li>
            @endforeach
        </ul>
    </nav>

    {{-- ============================ APERÇU ============================ --}}
    <div x-show="tab === 'apercu'" x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-y-4" class="mt-8 grid gap-6 lg:grid-cols-3" role="tabpanel">

        <div class="space-y-6 lg:col-span-2">
            @php $nextEvent = $myEvents->first() ?? $events->first(); @endphp
            <section class="relative overflow-hidden rounded-3xl border border-gold-500/25 bg-linear-to-br from-gold-900/30 via-night-900 to-night-950 p-7 sm:p-9" aria-labelledby="next-event-titre">
                <div class="pointer-events-none absolute -right-12 -top-12 size-48 rounded-full bg-gold-500/15 blur-2xl"></div>
                <p class="relative text-xs uppercase tracking-widest text-gold-300/80">{{ $myEvents->isNotEmpty() ? 'Votre prochain événement' : 'Prochain événement' }}</p>

                @if ($nextEvent)
                    <div class="relative mt-4 flex items-center gap-6" wire:key="next-{{ $nextEvent['id'] }}-{{ (int) $nextEvent['registered'] }}">
                        <div class="w-20 shrink-0 rounded-2xl border border-gold-500/40 py-3 text-center">
                            <span class="block font-display text-5xl leading-none text-gold-500">{{ $nextEvent['day'] }}</span>
                            <span class="block text-xs uppercase tracking-wider text-stone-400">{{ $nextEvent['month'] }}</span>
                        </div>
                        <div class="min-w-0">
                            <h2 id="next-event-titre" class="font-display text-3xl text-stone-100 sm:text-4xl">{{ $nextEvent['title'] }}</h2>
                            <p class="mt-1 text-sm capitalize text-stone-400">{{ $nextEvent['long'] }} · {{ $nextEvent['time'] }}@if ($nextEvent['place']) · {{ $nextEvent['place'] }}@endif</p>
                        </div>
                    </div>

                    <div class="relative mt-6 flex flex-wrap gap-3">
                        @if ($nextEvent['registered'])
                            <span class="rounded-full bg-emerald-500/15 px-5 py-2.5 text-sm font-medium text-emerald-300">Vous êtes inscrit(e)</span>
                        @elseif ($this->registrationsEnabled && ! $nextEvent['full'])
                            <button type="button" wire:click="joinEvent({{ $nextEvent['id'] }})" wire:loading.attr="disabled" wire:target="joinEvent({{ $nextEvent['id'] }})"
                                    class="rounded-full bg-gold-500 px-6 py-2.5 text-sm font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400 disabled:opacity-60">Participer</button>
                        @elseif ($nextEvent['full'])
                            <span class="rounded-full border border-rose-400/40 px-5 py-2.5 text-sm text-rose-300">Complet</span>
                        @endif
                        <a href="{{ $to('events') }}" wire:navigate class="rounded-full border border-gold-500 px-6 py-2.5 text-sm text-gold-500 transition hover:bg-gold-500/10">Tous les événements</a>
                    </div>
                @else
                    <p class="relative mt-4 font-display text-3xl text-stone-300">Aucun événement programmé</p>
                    <p class="relative mt-1 text-sm text-stone-500">Revenez bientôt : de nouvelles dates seront publiées.</p>
                @endif
            </section>

            <section class="rounded-3xl border border-gold-500/20 bg-night-900/70 p-7 sm:p-9" aria-labelledby="fav-apercu-titre">
                <div class="flex items-center justify-between">
                    <h2 id="fav-apercu-titre" class="font-display text-3xl text-gold-500">Mes chorégraphies</h2>
                    <button type="button" x-on:click="setTab('favoris')" class="text-sm text-gold-500 hover:underline">Tout voir</button>
                </div>

                <ul class="mt-5 space-y-3" x-show="favChoreos.length" x-cloak>
                    <template x-for="c in favChoreos.slice(0, 3)" :key="c.id">
                        <li class="flex items-center justify-between gap-3 rounded-2xl border border-gold-500/10 px-4 py-3">
                            <span class="truncate text-stone-100" x-text="c.title"></span>
                            <span class="shrink-0 rounded-full border border-gold-500/40 px-3 py-0.5 text-xs text-gold-300" x-text="c.category"></span>
                        </li>
                    </template>
                </ul>

                <div x-show="!favChoreos.length" class="mt-5 rounded-2xl border border-dashed border-gold-500/25 p-6 text-center">
                    <p class="text-sm text-stone-400">Vous n’avez encore gardé aucune chorégraphie.</p>
                    <a href="{{ $to('choreographies') }}" wire:navigate class="mt-3 inline-block text-sm font-medium text-gold-500 hover:underline">Découvrir les chorégraphies</a>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            {{-- Prochaine prière --}}
            <section class="rounded-3xl border border-gold-500/20 bg-night-900/70 p-7 text-center" aria-labelledby="prayer-card-titre">
                <h2 id="prayer-card-titre" class="text-xs uppercase tracking-widest text-gold-300/80" x-text="current ? 'En ce moment' : 'Prochaine prière'"></h2>
                <p class="mt-3 font-display text-3xl text-stone-100" x-text="labels[current ? current.i : next.i]"></p>
                <template x-if="!current">
                    <p class="mt-3 font-display text-4xl text-gold-500 tabular-nums" role="timer">
                        <span x-text="pad(cd.h)"></span><span class="text-gold-500/50">:</span><span x-text="pad(cd.m)"></span><span class="text-gold-500/50">:</span><span x-text="pad(cd.s)"></span>
                    </p>
                </template>
                <a href="{{ $to('prayer') }}" wire:navigate class="mt-4 inline-block text-sm font-medium text-gold-500 hover:underline">Aller à la prière</a>
            </section>

            {{-- Progression du profil : recréée quand une étape serveur change --}}
            <section class="rounded-3xl border border-gold-500/20 bg-night-900/70 p-7" aria-labelledby="progress-titre"
                     wire:key="progress-{{ $doneCount }}-{{ $totalCount }}"
                     x-data="{ done: {{ $doneCount }}, total: {{ $totalCount }} }">
                <h2 id="progress-titre" class="font-display text-2xl text-gold-500">Mon profil</h2>

                <div class="mt-4 flex items-center gap-5">
                    <div class="relative size-24 shrink-0">
                        <svg class="size-full -rotate-90" viewBox="0 0 100 100" aria-hidden="true">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="currentColor" stroke-width="6" class="text-gold-500/15"/>
                            <circle cx="50" cy="50" r="40" fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round" class="text-gold-500 transition-[stroke-dashoffset] duration-1000 ease-out"
                                    stroke-dasharray="251.33" :stroke-dashoffset="251.33 * (1 - ratio(done, total))"/>
                        </svg>
                        <span class="absolute inset-0 grid place-items-center font-display text-2xl text-stone-100" x-text="Math.round(ratio(done, total) * 100) + '%'"></span>
                    </div>

                    <ul class="min-w-0 flex-1 space-y-2 text-sm">
                        @foreach ($checklist as $c)
                            <li class="flex items-start gap-2 {{ $c['done'] ? 'text-stone-300' : 'text-stone-500' }}">
                                <svg class="mt-0.5 size-4 shrink-0 {{ $c['done'] ? 'text-emerald-300' : 'text-stone-600' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    @if ($c['done'])<path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/>@else<circle cx="10" cy="10" r="3"/>@endif
                                </svg>
                                @if (! $c['done'] && $c['route'] && Route::has($c['route']))
                                    <a href="{{ route($c['route']) }}" class="text-gold-300 underline hover:text-gold-500">{{ $c['label'] }}</a>
                                @else
                                    <span>{{ $c['label'] }}</span>
                                @endif
                            </li>
                        @endforeach
                        <li class="flex items-start gap-2" :class="favC.length + favP.length > 0 ? 'text-stone-300' : 'text-stone-500'">
                            <svg class="mt-0.5 size-4 shrink-0" :class="favC.length + favP.length > 0 ? 'text-emerald-300' : 'text-stone-600'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path x-show="favC.length + favP.length > 0" fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/>
                                <circle x-show="!(favC.length + favP.length > 0)" cx="10" cy="10" r="3"/>
                            </svg>
                            <span>Garder une chorégraphie ou une prière</span>
                        </li>
                    </ul>
                </div>
            </section>
        </div>
    </div>

    {{-- ============================ ÉVÉNEMENTS ============================ --}}
    <div x-show="tab === 'evenements'" x-cloak x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-y-4" class="mt-8" role="tabpanel">
        <h2 class="font-display text-3xl text-gold-500">Événements à venir</h2>

        @unless ($this->registrationsEnabled)
            <p class="mt-4 rounded-2xl border border-dashed border-gold-500/30 p-4 text-sm text-stone-400">Les inscriptions en ligne ne sont pas encore activées : consultez la page Événements pour participer.</p>
        @endunless

        <ol class="mt-6 space-y-3">
            @forelse ($events as $e)
                <li wire:key="event-{{ $e['id'] }}" class="flex animate-rise flex-wrap items-center gap-4 rounded-3xl border bg-night-900/70 p-5 transition duration-500 hover:-translate-y-1 hover:border-gold-500/60
                           {{ $e['registered'] ? 'border-emerald-400/40' : 'border-gold-500/20' }}" style="animation-delay: {{ min($loop->index, 8) * 60 }}ms">
                    <div class="w-16 shrink-0 text-center">
                        <span class="block font-display text-4xl leading-none text-gold-500">{{ $e['day'] }}</span>
                        <span class="block text-xs uppercase text-stone-500">{{ $e['month'] }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-lg text-stone-100">{{ $e['title'] }}</h3>
                        <p class="truncate text-sm capitalize text-stone-500">{{ $e['long'] }} · {{ $e['time'] }}@if ($e['place']) · {{ $e['place'] }}@endif</p>
                        @if ($e['spots'] !== null)
                            <p class="mt-0.5 text-xs {{ $e['spots'] > 0 ? 'text-gold-300' : 'text-rose-300' }}">{{ $e['spots'] > 0 ? $e['spots'].' place'.($e['spots'] > 1 ? 's' : '').' restante'.($e['spots'] > 1 ? 's' : '') : 'Complet' }}</p>
                        @endif
                    </div>

                    @if ($this->registrationsEnabled)
                        @if ($e['registered'])
                            <span class="rounded-full bg-emerald-500/15 px-4 py-1.5 text-xs font-medium text-emerald-300">Inscrit(e)</span>
                            <button type="button" wire:click="leaveEvent({{ $e['id'] }})" wire:confirm="Annuler votre inscription à « {{ $e['title'] }} » ?" wire:loading.attr="disabled" wire:target="leaveEvent({{ $e['id'] }})"
                                    class="rounded-full border border-rose-400/40 px-4 py-1.5 text-xs text-rose-300 transition hover:bg-rose-500/10 disabled:opacity-50">Annuler</button>
                        @elseif ($e['full'])
                            <span class="rounded-full border border-rose-400/40 px-4 py-1.5 text-xs text-rose-300">Complet</span>
                        @else
                            <button type="button" wire:click="joinEvent({{ $e['id'] }})" wire:loading.attr="disabled" wire:target="joinEvent({{ $e['id'] }})"
                                    class="inline-flex items-center gap-2 rounded-full border border-gold-500 px-5 py-2 text-sm text-gold-500 transition hover:bg-gold-500 hover:text-night-950 disabled:opacity-60">
                                <svg class="hidden size-4 animate-spin" wire:loading.class.remove="hidden" wire:target="joinEvent({{ $e['id'] }})" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="{{ $spinner }}" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                                Participer
                            </button>
                        @endif
                    @else
                        <a href="{{ $to('events') }}" wire:navigate class="rounded-full border border-gold-500 px-5 py-2 text-sm text-gold-500 transition hover:bg-gold-500 hover:text-night-950">Voir</a>
                    @endif
                </li>
            @empty
                <li class="rounded-3xl border border-dashed border-gold-500/25 p-10 text-center text-stone-400">Aucun événement programmé pour l’instant.</li>
            @endforelse
        </ol>
    </div>

    {{-- ============================ FAVORIS ============================ --}}
    <div x-show="tab === 'favoris'" x-cloak x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-y-4" class="mt-8 space-y-10" role="tabpanel">
        <section>
            <h2 class="font-display text-3xl text-gold-500">Chorégraphies gardées</h2>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" x-show="favChoreos.length">
                <template x-for="c in favChoreos" :key="c.id">
                    <article class="relative animate-rise rounded-3xl border border-gold-500/20 bg-night-900/70 p-6 transition duration-500 hover:-translate-y-1 hover:border-gold-500/60">
                        <span class="rounded-full border border-gold-500/50 px-3 py-0.5 text-xs text-gold-300" x-text="c.category"></span>
                        <h3 class="mt-4 font-display text-2xl text-stone-100" x-text="c.title"></h3>
                        <p class="mt-1 text-xs text-stone-500"><span x-text="c.level"></span><span x-show="c.duration > 0"> · <span x-text="c.duration + ' min'"></span></span></p>
                        <button type="button" x-on:click="removeFav(c.id)" class="mt-4 text-sm text-rose-300 hover:underline">Retirer</button>
                    </article>
                </template>
            </div>

            <div x-show="!favChoreos.length" class="mt-6 rounded-3xl border border-dashed border-gold-500/25 p-10 text-center">
                <p class="text-stone-400">Touchez le cœur d’une chorégraphie pour la retrouver ici.</p>
                <a href="{{ $to('choreographies') }}" wire:navigate class="mt-4 inline-block rounded-full bg-gold-500 px-6 py-2.5 text-sm font-semibold text-night-950 transition hover:bg-gold-400">Voir les chorégraphies</a>
            </div>
        </section>

        <section>
            <h2 class="font-display text-3xl text-gold-500">Prières gardées</h2>
            <div class="mt-6 rounded-3xl border border-gold-500/20 bg-night-900/70 p-7">
                <p class="text-stone-300">
                    <span class="font-display text-4xl text-gold-500" x-text="favP.length"></span>
                    <span x-text="favP.length > 1 ? 'prières gardées' : 'prière gardée'" class="ml-2"></span>
                </p>
                <a href="{{ $to('prayer') }}" wire:navigate class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-gold-500 hover:underline">
                    Ouvrir la bibliothèque de prières
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrow }}" clip-rule="evenodd"/></svg>
                </a>
            </div>
        </section>

        <p class="text-xs text-stone-500">Vos favoris sont enregistrés sur cet appareil.</p>
    </div>

    {{-- ============================ INTENTIONS ============================ --}}
    <div x-show="tab === 'intentions'" x-cloak x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-y-4" class="mt-8 grid gap-6 lg:grid-cols-5" role="tabpanel">

        {{-- Nouvelle intention --}}
        <section class="rounded-3xl border border-gold-500/20 bg-night-900/70 p-7 sm:p-9 lg:col-span-2" aria-labelledby="new-request-titre">
            <h2 id="new-request-titre" class="font-display text-3xl text-gold-500">Confier une intention</h2>
            <p class="mt-2 text-sm text-stone-400">Elle sera relue par l’équipe avant d’être partagée.</p>

            @if ($this->prayerEnabled)
                <form wire:submit="sendRequest" class="mt-6 space-y-4">
                    <div>
                        <label for="req-content" class="sr-only">Votre intention</label>
                        <textarea id="req-content" wire:model="content" rows="5" maxlength="500" placeholder="Pour qui ou pour quoi souhaitez-vous que nous priions ?"
                                  class="{{ $field }} placeholder:text-stone-500 {{ $errors->has('content') ? $bad : $ok }}"></textarea>
                        <div class="mt-1 flex justify-between text-xs">
                            @error('content') <p class="text-rose-300">{{ $message }}</p> @else <span></span> @enderror
                            <span class="text-stone-500"><span x-text="$wire.content.length">0</span>/500</span>
                        </div>
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 text-sm text-stone-300">
                        <input type="checkbox" wire:model="anonymous" class="mt-1 size-4 rounded border-gold-500/50 bg-night-900 accent-gold-500">
                        <span>Rester anonyme si mon intention est partagée</span>
                    </label>

                    <button type="submit" wire:loading.attr="disabled" wire:target="sendRequest"
                            class="inline-flex items-center gap-2 rounded-full bg-gold-500 px-7 py-3 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400 disabled:cursor-wait disabled:opacity-70">
                        <svg class="hidden size-4 animate-spin" wire:loading.class.remove="hidden" wire:target="sendRequest" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="{{ $spinner }}" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                        <span wire:loading.remove wire:target="sendRequest">Envoyer mon intention</span>
                        <span wire:loading wire:target="sendRequest">Envoi…</span>
                    </button>
                </form>
            @else
                <a href="{{ $to('prayer') }}" wire:navigate class="mt-6 inline-block rounded-full bg-gold-500 px-6 py-3 font-semibold text-night-950 transition hover:bg-gold-400">Aller à la page Prière</a>
            @endif
        </section>

        {{-- Mes intentions --}}
        <section class="lg:col-span-3" aria-labelledby="my-requests-titre">
            <h2 id="my-requests-titre" class="font-display text-3xl text-gold-500">Mes intentions</h2>

            <ul class="mt-6 space-y-3">
                @forelse ($requests as $r)
                    <li wire:key="request-{{ $r['id'] }}" class="animate-rise rounded-3xl border border-gold-500/20 bg-night-900/70 p-6">
                        <p class="leading-relaxed text-stone-200">{{ $r['text'] }}</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                            <span class="rounded-full px-3 py-1 {{ $r['approved'] ? 'bg-emerald-500/15 text-emerald-300' : 'bg-gold-500/10 text-gold-300' }}">{{ $r['approved'] ? 'Publiée' : 'En attente de relecture' }}</span>
                            <span class="text-stone-500">{{ $r['when'] }}</span>
                            @unless ($r['approved'])
                                <button type="button" wire:click="deleteRequest({{ $r['id'] }})" wire:confirm="Retirer cette intention ?" class="ml-auto text-rose-300 hover:underline">Retirer</button>
                            @endunless
                        </div>
                    </li>
                @empty
                    <li class="rounded-3xl border border-dashed border-gold-500/25 p-10 text-center text-stone-400">Vous n’avez pas encore confié d’intention.</li>
                @endforelse
            </ul>
        </section>
    </div>

    {{-- ============================ PARAMÈTRES ============================ --}}
    <div x-show="tab === 'parametres'" x-cloak x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-y-4" class="mt-8 grid gap-6 lg:grid-cols-2" role="tabpanel">

        {{-- Profil --}}
        <section class="rounded-3xl border border-gold-500/20 bg-night-900/70 p-7 sm:p-9" aria-labelledby="profil-titre">
            <h2 id="profil-titre" class="font-display text-3xl text-gold-500">Informations personnelles</h2>

            <form wire:submit="saveProfile" class="mt-6 space-y-5" novalidate>
                <div>
                    <label for="p-name" class="mb-1.5 block text-sm text-stone-300">Nom complet</label>
                    <input id="p-name" type="text" wire:model="name" required maxlength="255" autocomplete="name" class="{{ $field }} {{ $errors->has('name') ? $bad : $ok }}">
                    @error('name') <p class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="p-email" class="mb-1.5 block text-sm text-stone-300">Adresse email</label>
                    <input id="p-email" type="email" wire:model="email" required maxlength="255" autocomplete="email" class="{{ $field }} {{ $errors->has('email') ? $bad : $ok }}">
                    @error('email') <p class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <button type="submit" wire:loading.attr="disabled" wire:target="saveProfile"
                        class="rounded-full bg-gold-500 px-7 py-3 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400 disabled:cursor-wait disabled:opacity-70">
                    <span wire:loading.remove wire:target="saveProfile">Enregistrer</span>
                    <span wire:loading wire:target="saveProfile">Enregistrement…</span>
                </button>
            </form>
        </section>

        {{-- Mot de passe --}}
        <section class="rounded-3xl border border-gold-500/20 bg-night-900/70 p-7 sm:p-9" aria-labelledby="mdp-titre">
            <h2 id="mdp-titre" class="font-display text-3xl text-gold-500">Mot de passe</h2>

            <form wire:submit="updatePassword" class="mt-6 space-y-5" novalidate x-data="{ show: false }">
                <div>
                    <label for="pw-current" class="mb-1.5 block text-sm text-stone-300">Mot de passe actuel</label>
                    <input id="pw-current" wire:model="current_password" required autocomplete="current-password" x-bind:type="show ? 'text' : 'password'"
                           class="{{ $field }} {{ $errors->has('current_password') ? $bad : $ok }}">
                    @error('current_password') <p class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="pw-new" class="mb-1.5 block text-sm text-stone-300">Nouveau mot de passe</label>
                    <input id="pw-new" wire:model="password" required autocomplete="new-password" x-bind:type="show ? 'text' : 'password'"
                           class="{{ $field }} {{ $errors->has('password') ? $bad : $ok }}">
                    @error('password') <p class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror
                    <p class="mt-1.5 text-xs text-stone-500">8 caractères minimum, avec une majuscule, une minuscule et un chiffre.</p>
                </div>
                <div>
                    <label for="pw-confirm" class="mb-1.5 block text-sm text-stone-300">Confirmer le nouveau mot de passe</label>
                    <input id="pw-confirm" wire:model="password_confirmation" required autocomplete="new-password" x-bind:type="show ? 'text' : 'password'" class="{{ $field }} {{ $ok }}">
                </div>

                <label class="flex cursor-pointer items-center gap-3 text-sm text-stone-300">
                    <input type="checkbox" x-model="show" class="size-4 rounded border-gold-500/50 bg-night-900 accent-gold-500"> Afficher les mots de passe
                </label>

                <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword"
                        class="rounded-full bg-gold-500 px-7 py-3 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400 disabled:cursor-wait disabled:opacity-70">
                    <span wire:loading.remove wire:target="updatePassword">Modifier le mot de passe</span>
                    <span wire:loading wire:target="updatePassword">Modification…</span>
                </button>
            </form>
        </section>

        {{-- Session --}}
        <section class="rounded-3xl border border-gold-500/20 bg-night-900/50 p-7 lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-2xl text-gold-500">Session</h2>
                    <p class="text-sm text-stone-400">Connecté en tant que {{ $me->email }}</p>
                </div>
                <form method="POST" action="{{ $to('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-gold-500 px-6 py-2.5 text-sm font-medium text-gold-500 transition hover:bg-gold-500 hover:text-night-950">Logout</button>
                </form>
            </div>
        </section>
    </div>
</div>
</div>