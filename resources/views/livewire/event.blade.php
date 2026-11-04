<div>
@php
    // ---------- Helpers ----------
    $to      = fn (string $name, $param = null) => Route::has($name) ? route($name, $param) : '#';
    $user    = auth()->user();
    $isAdmin = $user && $user->role === 'admin';

    $regUrl = function ($id) use ($user) {
        if (! $user) return Route::has('login') ? route('login') : '#';
        if (Route::has('events.register')) return route('events.register', $id);
        return Route::has('contact') ? route('contact') : '#';
    };
    $regLabel = ! $user ? 'Se connecter pour participer'
        : (Route::has('events.register') ? 'Participer à cet événement' : 'Nous contacter pour participer');

    $items = collect();

    try {
        if (class_exists(\App\Models\Event::class)) {
            $items = \App\Models\Event::orderBy('date')->get()->map(function ($e) use ($regUrl) {
                $start = \Illuminate\Support\Carbon::parse($e->date);
                $end   = isset($e->end_date) && $e->end_date ? \Illuminate\Support\Carbon::parse($e->end_date) : $start->copy()->addHours(2);
                return [
                    'id'       => $e->id,
                    'title'    => $e->title,
                    'type'     => $e->type ?? $e->category ?? 'Rencontre',
                    'start'    => $start->toIso8601String(),
                    'end'      => $end->toIso8601String(),
                    'place'    => $e->location ?? $e->place ?? 'Lieu à confirmer',
                    'desc'     => $e->description ?: 'Aucune description pour le moment.',
                    'price'    => $e->price ?? null,
                    'capacity' => $e->capacity ?? null,
                    'reg'      => $regUrl($e->id),
                ];
            });
        }
    } catch (\Throwable $e) {
        //
    }

    $isSample = $items->isEmpty();

    if ($isSample) {
        $mk = fn ($id, $title, $type, $days, $hour, $place, $desc, $price = null) => [
            'id' => $id, 'title' => $title, 'type' => $type,
            'start' => now()->addDays($days)->setTime($hour, 0)->toIso8601String(),
            'end'   => now()->addDays($days)->setTime($hour + 2, 0)->toIso8601String(),
            'place' => $place, 'desc' => $desc, 'price' => $price, 'capacity' => null, 'reg' => $regUrl($id),
        ];
        $items = collect([
            $mk(1, 'Grande célébration annuelle',      'Célébration', 41,  15, 'Antananarivo',     'Une journée entière de louange, de danse et de partage avec toute la communauté.'),
            $mk(2, 'Atelier de chorégraphie ouvert',   'Atelier',     20,  14, 'Salle paroissiale', 'Apprenez une nouvelle chorégraphie pas à pas, quel que soit votre niveau.'),
            $mk(3, 'Soirée de louange et de danse',    'Louange',     6,   18, 'Antananarivo',     'Une soirée simple pour louer ensemble, danser et prier.'),
            $mk(4, 'Veillée de prière',                'Prière',      13,  19, 'Chapelle',         'Un temps de prière prolongé, ouvert à tous, dans le calme.'),
            $mk(5, 'Répétition générale',              'Atelier',     -9,  16, 'Salle paroissiale', 'Dernière répétition avant la célébration.'),
            $mk(6, 'Soirée d’ouverture de la saison',  'Célébration', -30, 18, 'Antananarivo',     'Lancement de la saison avec toute l’équipe.'),
        ])->sortBy('start')->values();
    }

    $arrow = 'M3 10a.75.75 0 01.75-.75h10.69L10.47 5.28a.75.75 0 011.06-1.06l5.25 5.25a.75.75 0 010 1.06l-5.25 5.25a.75.75 0 11-1.06-1.06l3.97-3.97H3.75A.75.75 0 013 10z';
@endphp

<div
    x-data="{
        items: @js($items->values()),
        regLabel: @js($regLabel),
        tab: 'avenir',
        q: '',
        type: 'Tous',
        month: 'Tous',
        view: 'liste',
        day: null,
        sel: null,
        copied: false,
        now: Date.now(),
        timer: null,
        cal: { y: new Date().getFullYear(), m: new Date().getMonth() },

        init() {
            this.timer = setInterval(() => this.now = Date.now(), 1000);
            const id = parseInt(location.hash.replace('#evenement-', ''));
            if (id) this.sel = this.items.find(i => i.id === id) || null;
        },
        norm(s) { return (s || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); },
        startOf(i) { return new Date(i.start); },
        endOf(i) { return new Date(i.end); },
        isPast(i) { return this.endOf(i).getTime() < this.now; },
        key(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); },
        monthKey(i) { return this.key(this.startOf(i)).slice(0, 7); },
        monthName(k) { const [y, m] = k.split('-'); return new Date(y, m - 1, 1).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' }); },

        get types() { return ['Tous', ...new Set(this.items.map(i => i.type))]; },
        get months() { return ['Tous', ...new Set(this.items.map(i => this.monthKey(i)))]; },
        get upcomingList() { return this.items.filter(i => !this.isPast(i)).sort((a, b) => this.startOf(a) - this.startOf(b)); },
        get next() { return this.upcomingList[0] || null; },
        get filtered() {
            const q = this.norm(this.q);
            let list = this.items.filter(i =>
                (this.tab === 'tous' || (this.tab === 'avenir') !== this.isPast(i)) &&
                (this.type === 'Tous' || i.type === this.type) &&
                (this.month === 'Tous' || this.monthKey(i) === this.month) &&
                (!this.day || this.key(this.startOf(i)) === this.day) &&
                (!q || this.norm(i.title + ' ' + i.place + ' ' + i.desc + ' ' + i.type).includes(q))
            );
            list = list.sort((a, b) => this.startOf(a) - this.startOf(b));
            return this.tab === 'passes' ? list.reverse() : list;
        },

        /* Compte à rebours */
        get cd() {
            if (!this.next) return null;
            let s = Math.max(0, Math.floor((this.startOf(this.next) - this.now) / 1000));
            return { d: Math.floor(s / 86400), h: Math.floor(s % 86400 / 3600), m: Math.floor(s % 3600 / 60), s: s % 60, started: this.startOf(this.next) <= this.now };
        },
        pad(n) { return String(n).padStart(2, '0'); },

        /* Formats */
        dDay(i) { return this.startOf(i).getDate(); },
        dMonth(i) { return this.startOf(i).toLocaleDateString('fr-FR', { month: 'short' }).replace('.', ''); },
        dLong(i) { return this.startOf(i).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); },
        dTime(i) { const o = { hour: '2-digit', minute: '2-digit' }; return this.startOf(i).toLocaleTimeString('fr-FR', o) + ' – ' + this.endOf(i).toLocaleTimeString('fr-FR', o); },
        rel(i) {
            if (this.isPast(i)) return 'Terminé';
            const d = Math.ceil((this.startOf(i) - this.now) / 86400000);
            if (this.startOf(i) <= this.now) return 'En cours';
            return d <= 1 ? 'Bientôt' : 'Dans ' + d + ' jours';
        },
        priceLabel(i) { return i.price === null || i.price === undefined || i.price === '' || Number(i.price) === 0 ? 'Gratuit' : Number(i.price).toLocaleString('fr-FR') + ' Ar'; },

        /* Ajouter au calendrier */
        stamp(d) { return d.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, ''); },
        gcal(i) {
            const p = new URLSearchParams({ action: 'TEMPLATE', text: i.title, dates: this.stamp(this.startOf(i)) + '/' + this.stamp(this.endOf(i)), details: i.desc, location: i.place });
            return 'https://calendar.google.com/calendar/render?' + p.toString();
        },
        ics(i) {
            const esc = t => (t || '').replace(/\\/g, '\\\\').replace(/;/g, '\\;').replace(/,/g, '\\,').replace(/\n/g, '\\n');
            const body = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Iraka Voafidy//FR', 'BEGIN:VEVENT',
                'UID:event-' + i.id + '\u0040iraka-voafidy', 'DTSTAMP:' + this.stamp(new Date()),
                'DTSTART:' + this.stamp(this.startOf(i)), 'DTEND:' + this.stamp(this.endOf(i)),
                'SUMMARY:' + esc(i.title), 'DESCRIPTION:' + esc(i.desc), 'LOCATION:' + esc(i.place),
                'END:VEVENT', 'END:VCALENDAR'].join('\r\n');
            const a = document.createElement('a');
            a.href = URL.createObjectURL(new Blob([body], { type: 'text/calendar;charset=utf-8' }));
            a.download = 'evenement-' + i.id + '.ics';
            a.click();
            URL.revokeObjectURL(a.href);
        },
        async share(i) {
            const url = location.origin + location.pathname + '#evenement-' + i.id;
            try { await navigator.clipboard.writeText(url); this.copied = true; setTimeout(() => this.copied = false, 2000); } catch (e) {}
        },

        /* Calendrier mensuel */
        get calLabel() { return new Date(this.cal.y, this.cal.m, 1).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' }); },
        get calDays() {
            const first = new Date(this.cal.y, this.cal.m, 1);
            const offset = (first.getDay() + 6) % 7;
            const count = new Date(this.cal.y, this.cal.m + 1, 0).getDate();
            const days = [];
            for (let n = 0; n < offset; n++) days.push(null);
            for (let n = 1; n <= count; n++) {
                const k = this.cal.y + '-' + String(this.cal.m + 1).padStart(2, '0') + '-' + String(n).padStart(2, '0');
                days.push({ n, k, evs: this.items.filter(i => this.key(this.startOf(i)) === k) });
            }
            return days;
        },
        moveCal(step) { const d = new Date(this.cal.y, this.cal.m + step, 1); this.cal = { y: d.getFullYear(), m: d.getMonth() }; },
        pickDay(k) { this.day = this.day === k ? null : k; this.view = 'liste'; this.tab = 'tous'; },
        reset() { this.q = ''; this.type = 'Tous'; this.month = 'Tous'; this.day = null; },
        open(i) { this.sel = i; history.replaceState(null, '', '#evenement-' + i.id); },
        close() { this.sel = null; history.replaceState(null, '', location.pathname + location.search); }
    }"
    x-on:keydown.escape.window="if (sel) close()"
    x-on:livewire:navigating.window="clearInterval(timer)">

    {{--  EN-TÊTE + COMPTE À REBOURS --}}
    <section class="relative isolate overflow-hidden px-4 pb-14 pt-12 text-center sm:px-6 lg:px-8" aria-labelledby="events-titre">
        <div class="pointer-events-none absolute left-1/2 top-0 -z-10 size-[36rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gold-500/15 blur-3xl"></div>

        <p class="animate-rise text-sm text-gold-300/80">Se retrouver, prier, danser</p>
        <h1 id="events-titre" class="mt-3 animate-rise font-display text-5xl font-semibold text-gold-500 sm:text-7xl" style="animation-delay:.15s">Nos événements</h1>
        <p class="mx-auto mt-5 max-w-2xl animate-rise text-stone-300" style="animation-delay:.3s">
            Retrouvez les prochaines rencontres, ajoutez-les à votre agenda et ne manquez plus aucune célébration.
        </p>

        @if ($isAdmin)
            <a href="{{ $to('admin.events') }}" wire:navigate
               class="mt-6 inline-flex animate-rise items-center gap-2 rounded-full bg-gold-500 px-6 py-2.5 text-sm font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400"
               style="animation-delay:.45s">
                Gérer les événements
            </a>
        @endif

        {{-- Compte à rebours --}}
        <div x-show="next && cd" x-cloak class="mx-auto mt-12 max-w-2xl animate-rise rounded-3xl border border-gold-500/30 bg-night-900/70 p-6 backdrop-blur" style="animation-delay:.6s">
            <p class="text-xs uppercase tracking-widest text-gold-300/80">Prochain événement</p>
            <p class="mt-2 font-display text-2xl text-stone-100 sm:text-3xl" x-text="next ? next.title : ''"></p>

            <template x-if="cd && !cd.started">
                <div class="mt-5 grid grid-cols-4 gap-3" role="timer" aria-live="off">
                    <template x-for="[v, l] in [[cd.d, 'jours'], [cd.h, 'heures'], [cd.m, 'min'], [cd.s, 'sec']]" :key="l">
                        <div class="rounded-2xl border border-gold-500/20 bg-night-950/60 py-3">
                            <div class="font-display text-4xl text-gold-500 tabular-nums sm:text-5xl" x-text="pad(v)"></div>
                            <div class="text-xs text-stone-400" x-text="l"></div>
                        </div>
                    </template>
                </div>
            </template>
            <p x-show="cd && cd.started" class="mt-5 font-display text-3xl text-gold-500">C’est en cours !</p>

            <button type="button" x-on:click="open(next)" class="mt-5 text-sm font-medium text-gold-500 hover:underline">Voir le détail</button>
        </div>
    </section>

    {{-- LISTE / CALENDRIER --}}
    <section class="mx-auto max-w-5xl px-4 pb-24 sm:px-6 lg:px-8">

        @if ($isSample)
            <p class="mb-6 rounded-xl border border-gold-500/30 bg-gold-500/5 px-4 py-3 text-center text-sm text-gold-300" role="note">
                Aucun événement enregistré pour l’instant : voici des exemples.
            </p>
        @endif

        {{-- Outils --}}
        <div class="sticky top-16 z-30 -mx-4 border-y border-gold-500/20 bg-night-950/85 px-4 py-4 backdrop-blur-xl sm:mx-0 sm:rounded-2xl sm:border">
            <div class="flex flex-wrap items-center gap-3">
                {{-- Onglets --}}
                <div class="flex rounded-full border border-gold-500/30 p-1" role="tablist" aria-label="Période">
                    @foreach (['avenir' => 'À venir', 'passes' => 'Passés', 'tous' => 'Tous'] as $k => $l)
                        <button type="button" role="tab" x-on:click="tab = '{{ $k }}'"
                                class="rounded-full px-4 py-1.5 text-sm transition duration-300"
                                :class="tab === '{{ $k }}' ? 'bg-gold-500 font-medium text-night-950' : 'text-stone-300 hover:text-gold-500'">{{ $l }}</button>
                    @endforeach
                </div>

                {{-- Vue --}}
                <div class="ml-auto flex rounded-full border border-gold-500/30 p-1" role="group" aria-label="Affichage">
                    <button type="button" x-on:click="view = 'liste'" :aria-pressed="view === 'liste'"
                            class="rounded-full px-4 py-1.5 text-sm transition" :class="view === 'liste' ? 'bg-gold-500 font-medium text-night-950' : 'text-stone-300 hover:text-gold-500'">Liste</button>
                    <button type="button" x-on:click="view = 'calendrier'" :aria-pressed="view === 'calendrier'"
                            class="rounded-full px-4 py-1.5 text-sm transition" :class="view === 'calendrier' ? 'bg-gold-500 font-medium text-night-950' : 'text-stone-300 hover:text-gold-500'">Calendrier</button>
                </div>
            </div>

            <div class="mt-3 grid gap-3 sm:grid-cols-3" x-show="view === 'liste'">
                <label class="relative block sm:col-span-1">
                    <span class="sr-only">Rechercher un événement</span>
                    <svg class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-gold-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.45 4.39l3.58 3.58a.75.75 0 11-1.06 1.06l-3.58-3.58A7 7 0 012 9z" clip-rule="evenodd"/></svg>
                    <input type="search" x-model="q" placeholder="Rechercher"
                           class="w-full rounded-full border border-gold-500/30 bg-night-900 py-2.5 pl-12 pr-4 text-sm text-stone-100 placeholder:text-stone-500 focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30">
                </label>
                <label class="block">
                    <span class="sr-only">Type</span>
                    <select x-model="type" class="w-full rounded-full border border-gold-500/30 bg-night-900 px-4 py-2.5 text-sm text-stone-200 focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30">
                        <template x-for="t in types" :key="t"><option :value="t" x-text="t === 'Tous' ? 'Tous les types' : t"></option></template>
                    </select>
                </label>
                <label class="block">
                    <span class="sr-only">Mois</span>
                    <select x-model="month" class="w-full rounded-full border border-gold-500/30 bg-night-900 px-4 py-2.5 text-sm text-stone-200 focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30">
                        <template x-for="m in months" :key="m"><option :value="m" x-text="m === 'Tous' ? 'Tous les mois' : monthName(m)"></option></template>
                    </select>
                </label>
            </div>

            <p x-show="day" x-cloak class="mt-3 text-sm text-gold-300">
                Jour sélectionné : <span x-text="day"></span>
                <button type="button" x-on:click="day = null" class="ml-2 underline">Retirer</button>
            </p>
        </div>

        {{-- Vue liste --}}
        <div x-show="view === 'liste'">
            <p class="mt-6 text-sm text-stone-400" aria-live="polite">
                <span x-text="filtered.length"></span> <span x-text="filtered.length > 1 ? 'événements' : 'événement'"></span>
            </p>

            <ol class="mt-4 space-y-4">
                <template x-for="(e, idx) in filtered" :key="e.id">
                    <li class="animate-rise" :style="`animation-delay:${Math.min(idx, 8) * 60}ms`">
                        <button type="button" x-on:click="open(e)"
                                class="group flex w-full items-center gap-5 rounded-3xl border border-gold-500/20 bg-night-900/60 p-5 text-left transition duration-500
                                       hover:-translate-y-1 hover:border-gold-500/70 hover:bg-gold-500/5 hover:shadow-xl hover:shadow-gold-500/10 sm:gap-8 sm:p-6"
                                :class="isPast(e) && 'opacity-60'">
                            <div class="w-16 shrink-0 rounded-2xl border border-gold-500/40 py-2 text-center transition group-hover:bg-gold-500 group-hover:text-night-950">
                                <span class="block font-display text-4xl leading-none text-gold-500 group-hover:text-night-950" x-text="dDay(e)"></span>
                                <span class="block text-xs uppercase tracking-wider text-stone-400 group-hover:text-night-950" x-text="dMonth(e)"></span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="rounded-full border border-gold-500/50 px-3 py-0.5 text-gold-300" x-text="e.type"></span>
                                    <span class="text-stone-400" x-text="rel(e)"></span>
                                </div>
                                <h2 class="mt-2 truncate font-display text-2xl text-stone-100 transition group-hover:text-gold-500 sm:text-3xl" x-text="e.title"></h2>
                                <p class="mt-1 text-sm text-stone-400"><span x-text="dTime(e)"></span> · <span x-text="e.place"></span></p>
                            </div>

                            <div class="hidden shrink-0 text-right sm:block">
                                <p class="text-sm text-gold-500" x-text="priceLabel(e)"></p>
                                <svg class="ml-auto mt-3 size-5 text-gold-500 transition duration-300 group-hover:translate-x-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrow }}" clip-rule="evenodd"/></svg>
                            </div>
                        </button>
                    </li>
                </template>
            </ol>

            <div x-show="filtered.length === 0" x-cloak class="mt-10 rounded-3xl border border-dashed border-gold-500/30 px-6 py-16 text-center">
                <p class="font-display text-3xl text-gold-500">Aucun événement</p>
                <p class="mt-2 text-stone-400">Aucun résultat pour cette sélection.</p>
                <button type="button" x-on:click="reset(); tab = 'tous'"
                        class="mt-6 rounded-full border border-gold-500 px-6 py-2.5 text-sm font-medium text-gold-500 transition hover:bg-gold-500 hover:text-night-950">
                    Tout afficher
                </button>
            </div>
        </div>

        {{-- Vue calendrier --}}
        <div x-show="view === 'calendrier'" x-cloak class="mt-6 rounded-3xl border border-gold-500/20 bg-night-900/60 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <button type="button" x-on:click="moveCal(-1)" aria-label="Mois précédent" class="grid size-10 place-items-center rounded-full border border-gold-500/40 text-gold-500 transition hover:bg-gold-500/10">‹</button>
                <h2 class="font-display text-2xl capitalize text-gold-500 sm:text-3xl" x-text="calLabel"></h2>
                <button type="button" x-on:click="moveCal(1)" aria-label="Mois suivant" class="grid size-10 place-items-center rounded-full border border-gold-500/40 text-gold-500 transition hover:bg-gold-500/10">›</button>
            </div>

            <div class="mt-5 grid grid-cols-7 gap-1 text-center text-xs text-stone-500 sm:gap-2">
                @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $d)<div>{{ $d }}</div>@endforeach
            </div>

            <div class="mt-2 grid grid-cols-7 gap-1 sm:gap-2">
                <template x-for="(d, n) in calDays" :key="n">
                    <div>
                        <template x-if="d">
                            <button type="button" x-on:click="d.evs.length && pickDay(d.k)" :disabled="!d.evs.length"
                                    class="relative grid aspect-square w-full place-items-center rounded-xl border text-sm transition duration-300"
                                    :class="d.evs.length ? 'border-gold-500/50 bg-gold-500/10 text-gold-300 hover:scale-105 hover:bg-gold-500 hover:text-night-950' : 'border-transparent text-stone-500'"
                                    :aria-label="d.evs.length ? d.n + ' : ' + d.evs.length + ' événement(s)' : String(d.n)">
                                <span x-text="d.n"></span>
                                <span x-show="d.evs.length" class="absolute bottom-1.5 size-1.5 rounded-full bg-gold-500"></span>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
            <p class="mt-4 text-center text-xs text-stone-500">Cliquez sur un jour en surbrillance pour voir ses événements.</p>
        </div>
    </section>

    {{-- FENÊTRE DE DÉTAIL --}}
    <div x-show="sel" x-cloak class="fixed inset-0 z-[80] flex items-end justify-center sm:items-center sm:p-6"
         role="dialog" aria-modal="true" aria-labelledby="event-modal-titre">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" x-on:click="close()"></div>

        <div x-show="sel" x-trap.noscroll="sel"
             x-transition:enter="transition duration-400 ease-out" x-transition:enter-start="opacity-0 translate-y-10 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             class="relative max-h-[90vh] w-full overflow-y-auto rounded-t-3xl border border-gold-500/40 bg-night-900 p-7 shadow-2xl shadow-black/60 sm:max-w-2xl sm:rounded-3xl sm:p-10">

            <button type="button" x-on:click="close()" aria-label="Fermer"
                    class="absolute right-4 top-4 grid size-10 place-items-center rounded-full border border-gold-500/40 text-gold-500 transition hover:rotate-90 hover:bg-gold-500/10">
                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
            </button>

            <template x-if="sel">
                <div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full border border-gold-500/50 px-3 py-1 text-gold-300" x-text="sel.type"></span>
                        <span class="text-stone-400" x-text="rel(sel)"></span>
                    </div>

                    <h2 id="event-modal-titre" class="mt-4 font-display text-4xl text-gold-500 sm:text-5xl" x-text="sel.title"></h2>

                    <dl class="mt-6 space-y-3 text-sm">
                        <div class="flex gap-3"><dt class="w-20 shrink-0 text-stone-500">Date</dt><dd class="capitalize text-stone-200" x-text="dLong(sel)"></dd></div>
                        <div class="flex gap-3"><dt class="w-20 shrink-0 text-stone-500">Horaire</dt><dd class="text-stone-200" x-text="dTime(sel)"></dd></div>
                        <div class="flex gap-3"><dt class="w-20 shrink-0 text-stone-500">Lieu</dt><dd class="text-stone-200" x-text="sel.place"></dd></div>
                        <div class="flex gap-3"><dt class="w-20 shrink-0 text-stone-500">Tarif</dt><dd class="text-stone-200" x-text="priceLabel(sel)"></dd></div>
                        <div class="flex gap-3" x-show="sel.capacity"><dt class="w-20 shrink-0 text-stone-500">Places</dt><dd class="text-stone-200" x-text="sel.capacity"></dd></div>
                    </dl>

                    <p class="mt-6 leading-relaxed text-stone-300" x-text="sel.desc"></p>

                    {{-- Participation --}}
                    <a x-show="!isPast(sel)" :href="sel.reg" wire:navigate
                       class="mt-8 block rounded-full bg-gold-500 px-6 py-3.5 text-center font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30"
                       x-text="regLabel"></a>

                    {{-- Agenda + partage --}}
                    <div class="mt-4 grid gap-3 sm:grid-cols-3" x-show="!isPast(sel)">
                        <a :href="gcal(sel)" target="_blank" rel="noopener noreferrer"
                           class="rounded-full border border-gold-500 px-4 py-2.5 text-center text-sm text-gold-500 transition hover:bg-gold-500/10">Google Agenda</a>
                        <button type="button" x-on:click="ics(sel)"
                                class="rounded-full border border-gold-500 px-4 py-2.5 text-sm text-gold-500 transition hover:bg-gold-500/10">Fichier .ics</button>
                        <button type="button" x-on:click="share(sel)"
                                class="rounded-full border border-gold-500 px-4 py-2.5 text-sm text-gold-500 transition hover:bg-gold-500/10"
                                x-text="copied ? 'Lien copié ✓' : 'Copier le lien'"></button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
</div>