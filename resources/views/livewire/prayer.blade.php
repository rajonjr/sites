<div>
@php
    // ---------- Helpers ----------
    $to      = fn (string $name) => Route::has($name) ? route($name) : '#';
    $user    = auth()->user();
    $isAdmin = $user && $user->role === 'admin';

    // Horaires de prière 
    $schedule = [
        ['time' => '06:00', 'label' => 'Prière du matin', 'text' => 'Confier la journée qui commence.'],
        ['time' => '12:00', 'label' => 'Prière de midi',  'text' => 'Une pause courte pour se recentrer.'],
        ['time' => '18:30', 'label' => 'Prière du soir',  'text' => 'Remercier et déposer la journée.'],
    ];

    // Verset du jour
    $verses = [
        ['ref' => 'Philippiens 4, 6-7',  'text' => 'Ne vous inquiétez de rien ; mais en toute chose faites connaître vos besoins à Dieu par des prières et des supplications, avec des actions de grâces. Et la paix de Dieu, qui surpasse toute intelligence, gardera vos cœurs et vos pensées en Jésus-Christ.'],
        ['ref' => 'Psaume 121, 1-2',     'text' => 'Je lève mes yeux vers les montagnes... D’où viendra mon secours ? Mon secours vient de l’Éternel, qui a fait les cieux et la terre.'],
        ['ref' => 'Matthieu 11, 28',     'text' => 'Venez à moi, vous tous qui êtes fatigués et chargés, et je vous donnerai du repos.'],
        ['ref' => 'Josué 1, 9',          'text' => 'Ne t’ai-je pas donné cet ordre : Fortifie-toi et prends courage ? Ne t’effraie point et ne t’épouvante point, car l’Éternel, ton Dieu, est avec toi dans tout ce que tu entreprendras.'],
        ['ref' => 'Jean 15, 16',         'text' => 'Ce n’est pas vous qui m’avez choisi, mais c’est moi qui vous ai choisis.'],
    ];
    $verse = $verses[now()->dayOfYear % count($verses)];

    // Bibliothèque de prières
    $prayers = [
        ['id' => 1, 'title' => 'Au lever du jour', 'category' => 'Matin', 'source' => 'Texte original',
         'text' => "Seigneur, tu m’as réveillé pour ce jour nouveau. Je te le confie : mes pas, mes paroles, mes rencontres.\n\nDonne-moi un cœur attentif, des mains disponibles et une joie qui ne dépende pas des circonstances.\n\nQue tout ce que je ferai aujourd’hui te rende gloire. Amen."],
        ['id' => 2, 'title' => 'Une pause pour te retrouver', 'category' => 'Midi', 'source' => 'Texte original',
         'text' => "Seigneur, au milieu de la journée, je m’arrête. Merci pour tout ce que j’ai reçu depuis ce matin.\n\nPardonne ce qui a manqué d’amour. Ranime mes forces et ma confiance pour la suite du jour.\n\nGarde-moi dans ta paix. Amen."],
        ['id' => 3, 'title' => 'Déposer la journée', 'category' => 'Soir', 'source' => 'Texte original',
         'text' => "Seigneur, voici la journée qui s’achève, avec ses réussites et ses échecs. Je te les remets.\n\nMerci pour chaque grâce, visible ou cachée. Pardonne mes manques, guéris ce qui est blessé.\n\nDonne-moi un sommeil paisible et veille sur ceux que j’aime. Amen."],
        ['id' => 4, 'title' => 'Louange pour la danse', 'category' => 'Louange', 'source' => 'Texte original',
         'text' => "Dieu de toute beauté, tu as créé le souffle, le rythme et le mouvement. Que mon corps te loue comme ma voix.\n\nQue chaque pas soit une offrande, chaque geste une prière.\n\nGloire à toi, maintenant et toujours. Amen."],
        ['id' => 5, 'title' => 'Avant de danser', 'category' => 'Danse', 'source' => 'Texte original',
         'text' => "Seigneur, avant de commencer, rassemble-nous. Unis nos cœurs comme tu unis nos gestes.\n\nQue personne ne danse pour être vu, mais que tous dansent pour te louer. Garde-nous humbles et attentifs les uns aux autres.\n\nQue ta joie conduise notre danse. Amen."],
        ['id' => 6, 'title' => 'Merci', 'category' => 'Gratitude', 'source' => 'Texte original',
         'text' => "Merci, Seigneur, pour la vie, pour le pain de chaque jour, pour les visages qui m’entourent.\n\nMerci pour ce que je vois et pour ce que je ne vois pas encore.\n\nApprends-moi à reconnaître tes dons avant de te demander davantage. Amen."],
        ['id' => 7, 'title' => 'Pour les autres', 'category' => 'Intercession', 'source' => 'Texte original',
         'text' => "Seigneur, je te présente ceux qui souffrent : les malades, les isolés, ceux qui ont perdu un proche.\n\nSois leur consolation et leur force. Donne-moi de les rejoindre par un mot, une visite, un geste concret.\n\nQue ton règne de paix vienne. Amen."],
        ['id' => 8, 'title' => 'Dans l’inquiétude', 'category' => 'Paix', 'source' => 'Texte original',
         'text' => "Seigneur, mon cœur est agité et mes pensées s’éparpillent. Je dépose devant toi ce qui me pèse.\n\nJe ne peux pas tout porter ; toi, tu le peux. Apaise-moi, redonne-moi confiance.\n\nQue ta paix garde mon cœur. Amen."],
        ['id' => 9, 'title' => 'Le Notre Père', 'category' => 'Écriture', 'source' => 'Matthieu 6, 9-13 · Louis Segond 1910',
         'text' => "Notre Père qui es aux cieux ! Que ton nom soit sanctifié ; que ton règne vienne ; que ta volonté soit faite sur la terre comme au ciel.\n\nDonne-nous aujourd’hui notre pain quotidien ; pardonne-nous nos offenses, comme nous aussi nous pardonnons à ceux qui nous ont offensés ; ne nous induis pas en tentation, mais délivre-nous du malin.\n\nCar à toi appartiennent le règne, la puissance et la gloire, aux siècles des siècles ! Amen !"],
        ['id' => 10, 'title' => 'Psaume 23', 'category' => 'Écriture', 'source' => 'Psaume 23 · Louis Segond 1910',
         'text' => "L’Éternel est mon berger : je ne manquerai de rien.\nIl me fait reposer dans de verts pâturages, il me dirige près des eaux paisibles.\nIl restaure mon âme, il me conduit dans les sentiers de la justice, à cause de son nom.\n\nQuand je marche dans la vallée de l’ombre de la mort, je ne crains aucun mal, car tu es avec moi : ta houlette et ton bâton me rassurent.\n\nTu dresses devant moi une table, en face de mes adversaires ; tu oins d’huile ma tête, et ma coupe déborde.\n\nOui, le bonheur et la grâce m’accompagneront tous les jours de ma vie, et j’habiterai dans la maison de l’Éternel jusqu’à la fin de mes jours."],
    ];

    // Intentions de prière publiées 
    $wall = collect();
    try {
        if (class_exists(\App\Models\PrayerRequest::class)) {
            $wall = \App\Models\PrayerRequest::where('is_approved', true)->latest()->take(6)->get()
                ->map(fn ($r) => [
                    'who'  => ($r->is_anonymous ?? false) || ! $r->name ? 'Anonyme' : $r->name,
                    'text' => $r->content,
                    'when' => optional($r->created_at)->locale('fr')->diffForHumans(),
                ]);
        }
    } catch (\Throwable $e) {
        //
    }

    $canRequest = Route::has('prayer.request.store');
@endphp

{{--  EN-TÊTE : VERSET + PROCHAINE PRIÈRE --}}
<section class="relative isolate overflow-hidden px-4 pb-14 pt-12 sm:px-6 lg:px-8" aria-labelledby="prayer-titre"
    x-data="{
        times: @js(array_column($schedule, 'time')),
        labels: @js(array_column($schedule, 'label')),
        now: Date.now(),
        timer: null,
        init() { this.timer = setInterval(() => this.now = Date.now(), 1000); },
        at(t, plusDay = 0) { const [h, m] = t.split(':').map(Number); const d = new Date(this.now); d.setHours(h, m, 0, 0); d.setDate(d.getDate() + plusDay); return d.getTime(); },
        get slots() { return this.times.flatMap((t, i) => [0, 1].map(d => ({ i, ts: this.at(t, d) }))).sort((a, b) => a.ts - b.ts); },
        get current() { return this.slots.find(s => this.now >= s.ts && this.now < s.ts + 30 * 60000) || null; },
        get next() { return this.slots.find(s => s.ts > this.now); },
        get cd() { const s = Math.max(0, Math.floor((this.next.ts - this.now) / 1000)); return { h: Math.floor(s / 3600), m: Math.floor(s % 3600 / 60), s: s % 60 }; },
        pad(n) { return String(n).padStart(2, '0'); }
    }"
    x-on:livewire:navigating.window="clearInterval(timer)">

    <div class="pointer-events-none absolute left-1/2 top-0 -z-10 size-[36rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gold-500/15 blur-3xl"></div>

    <div class="mx-auto max-w-4xl text-center">
        <p class="animate-rise text-sm text-gold-300/80">Prier ensemble, chaque jour</p>
        <h1 id="prayer-titre" class="mt-3 animate-rise font-display text-5xl font-semibold text-gold-500 sm:text-7xl" style="animation-delay:.15s">Prière</h1>

        <figure class="mx-auto mt-10 max-w-2xl animate-rise" style="animation-delay:.3s">
            <p class="text-xs uppercase tracking-widest text-gold-300/70">Verset du jour</p>
            <blockquote class="mt-4 font-display text-2xl italic leading-snug text-stone-100 sm:text-3xl">{{ $verse['text'] }}</blockquote>
            <figcaption class="mt-3 text-sm text-gold-500">{{ $verse['ref'] }} <span class="text-stone-500">· Louis Segond 1910</span></figcaption>
        </figure>

        <div class="mx-auto mt-12 max-w-xl animate-rise rounded-3xl border border-gold-500/30 bg-night-900/70 p-6 backdrop-blur" style="animation-delay:.5s">
            <template x-if="current">
                <div>
                    <p class="text-xs uppercase tracking-widest text-gold-300/80">En ce moment</p>
                    <p class="mt-2 font-display text-3xl text-gold-500"><span x-text="labels[current.i]"></span></p>
                    <p class="mt-1 text-sm text-stone-400">Le temps de prière a commencé : rejoignez-nous.</p>
                </div>
            </template>
            <template x-if="!current && next">
                <div>
                    <p class="text-xs uppercase tracking-widest text-gold-300/80">Prochaine prière</p>
                    <p class="mt-2 font-display text-2xl text-stone-100 sm:text-3xl" x-text="labels[next.i]"></p>
                    <div class="mt-4 flex justify-center gap-3 font-display text-4xl text-gold-500 tabular-nums" role="timer" aria-live="off">
                        <span x-text="pad(cd.h)"></span><span class="text-gold-500/50">:</span><span x-text="pad(cd.m)"></span><span class="text-gold-500/50">:</span><span x-text="pad(cd.s)"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>

{{-- HORAIRES --}}
<section class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8" aria-labelledby="horaires-titre"
         x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <h2 id="horaires-titre" class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Nos temps de prière</h2>
    <p class="mt-3 max-w-xl text-stone-400">Chaque jour, trois rendez-vous pour s’arrêter, ensemble ou chacun de son côté.</p>

    <ul class="mt-10 grid gap-5 md:grid-cols-3">
        @foreach ($schedule as $i => $s)
            <li class="group relative overflow-hidden rounded-3xl border border-gold-500/20 bg-linear-to-br from-gold-900/30 via-night-900 to-night-950 p-7 transition duration-700 ease-out
                       hover:-translate-y-2 hover:border-gold-500/70 hover:shadow-2xl hover:shadow-gold-500/10"
                x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'" style="transition-delay: {{ $i * 150 }}ms">
                <div class="absolute -right-8 -top-8 size-32 rounded-full bg-gold-500/15 transition duration-700 group-hover:scale-[2.2]"></div>
                <p class="relative font-display text-5xl text-gold-500">{{ $s['time'] }}</p>
                <h3 class="relative mt-3 text-lg font-medium text-stone-100">{{ $s['label'] }}</h3>
                <p class="relative mt-1 text-sm text-stone-400">{{ $s['text'] }}</p>
            </li>
        @endforeach
    </ul>
</section>

{{-- BIBLIOTHÈQUE DE PRIÈRES --}}
<section class="border-y border-gold-500/20 bg-night-900/40 py-20" aria-labelledby="biblio-titre">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8"
        x-data="{
            items: @js($prayers),
            q: '', cat: 'Toutes', onlyFavs: false, sel: null, size: 1, copied: false,
            favs: $persist([]).as('iv-prayer-favs'),

            norm(s) { return (s || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); },
            get cats() { return ['Toutes', ...new Set(this.items.map(i => i.category))]; },
            get filtered() {
                const q = this.norm(this.q);
                return this.items.filter(i =>
                    (this.cat === 'Toutes' || i.category === this.cat) &&
                    (!this.onlyFavs || this.favs.includes(i.id)) &&
                    (!q || this.norm(i.title + ' ' + i.text + ' ' + i.category).includes(q)));
            },
            isFav(id) { return this.favs.includes(id); },
            toggleFav(id) { this.favs = this.isFav(id) ? this.favs.filter(f => f !== id) : [...this.favs, id]; },
            sizeClass() { return ['text-lg', 'text-xl', 'text-2xl', 'text-3xl'][this.size]; },
            async copy(p) {
                try { await navigator.clipboard.writeText(p.title + '\n\n' + p.text); this.copied = true; setTimeout(() => this.copied = false, 2000); } catch (e) {}
            }
        }"
        x-on:keydown.escape.window="sel = null"
        x-on:livewire:navigating.window="sel = null">

        <h2 id="biblio-titre" class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Bibliothèque de prières</h2>
        <p class="mt-3 max-w-2xl text-stone-400">Des prières à lire, à copier et à garder. Les textes marqués « Texte original » ont été rédigés pour Iraka Voafidy ; les textes bibliques sont tirés de la Bible Louis Segond (1910).</p>

        {{-- Outils --}}
        <div class="mt-8 flex flex-col gap-4 lg:flex-row lg:items-center">
            <label class="relative block flex-1">
                <span class="sr-only">Rechercher une prière</span>
                <svg class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-gold-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.45 4.39l3.58 3.58a.75.75 0 11-1.06 1.06l-3.58-3.58A7 7 0 012 9z" clip-rule="evenodd"/></svg>
                <input type="search" x-model="q" placeholder="Rechercher un mot ou un thème"
                       class="w-full rounded-full border border-gold-500/30 bg-night-900 py-3 pl-12 pr-4 text-sm text-stone-100 placeholder:text-stone-500 focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30">
            </label>
            <button type="button" x-on:click="onlyFavs = !onlyFavs" :aria-pressed="onlyFavs"
                    class="flex items-center justify-center gap-2 rounded-full border px-5 py-3 text-sm transition duration-300"
                    :class="onlyFavs ? 'border-rose-400 bg-rose-500/15 text-rose-200' : 'border-gold-500/30 text-stone-300 hover:border-gold-500'">
                <svg class="size-4" viewBox="0 0 20 20" :fill="onlyFavs ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M10 17s-6.5-4.2-6.5-9A3.5 3.5 0 0110 6.2 3.5 3.5 0 0116.5 8c0 4.8-6.5 9-6.5 9z" stroke-linejoin="round"/></svg>
                Mes prières (<span x-text="favs.length"></span>)
            </button>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <template x-for="c in cats" :key="c">
                <button type="button" x-on:click="cat = c"
                        class="rounded-full border px-4 py-1.5 text-sm transition duration-300"
                        :class="cat === c ? 'border-gold-500 bg-gold-500 font-medium text-night-950' : 'border-gold-500/30 text-stone-300 hover:border-gold-500 hover:text-gold-500'"
                        x-text="c"></button>
            </template>
        </div>

        {{-- Grille --}}
        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <template x-for="(p, idx) in filtered" :key="p.id">
                <article class="group relative flex animate-rise flex-col rounded-3xl border border-gold-500/20 bg-night-900/70 p-6 transition duration-500
                                hover:-translate-y-1.5 hover:border-gold-500/70 hover:shadow-xl hover:shadow-gold-500/10"
                         :style="`animation-delay:${(idx % 6) * 70}ms`">
                    <div class="flex items-start justify-between gap-3">
                        <span class="rounded-full border border-gold-500/50 px-3 py-0.5 text-xs text-gold-300" x-text="p.category"></span>
                        <button type="button" x-on:click="toggleFav(p.id)" :aria-pressed="isFav(p.id)"
                                :aria-label="isFav(p.id) ? 'Retirer de mes prières' : 'Garder cette prière'"
                                class="grid size-9 place-items-center rounded-full border border-gold-500/30 transition duration-300 hover:scale-110"
                                :class="isFav(p.id) ? 'border-rose-400 bg-rose-500/15 text-rose-300' : 'text-stone-400 hover:text-rose-300'">
                            <svg class="size-4" viewBox="0 0 20 20" :fill="isFav(p.id) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M10 17s-6.5-4.2-6.5-9A3.5 3.5 0 0110 6.2 3.5 3.5 0 0116.5 8c0 4.8-6.5 9-6.5 9z" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                    <h3 class="mt-6 font-display text-2xl leading-tight text-stone-100" x-text="p.title"></h3>
                    <p class="mt-2 line-clamp-4 whitespace-pre-line text-sm leading-relaxed text-stone-400" x-text="p.text"></p>
                    <button type="button" x-on:click="sel = p"
                            class="mt-5 w-fit border-b border-gold-500/0 font-medium text-gold-500 transition hover:border-gold-500">Lire la prière</button>
                </article>
            </template>
        </div>

        <div x-show="filtered.length === 0" x-cloak class="mt-10 rounded-3xl border border-dashed border-gold-500/30 px-6 py-14 text-center">
            <p class="font-display text-3xl text-gold-500">Aucune prière trouvée</p>
            <button type="button" x-on:click="q = ''; cat = 'Toutes'; onlyFavs = false"
                    class="mt-5 rounded-full border border-gold-500 px-6 py-2.5 text-sm text-gold-500 transition hover:bg-gold-500 hover:text-night-950">Réinitialiser</button>
        </div>

        {{-- Mode lecture --}}
        <div x-show="sel" x-cloak class="fixed inset-0 z-[80] flex items-end justify-center sm:items-center sm:p-6" role="dialog" aria-modal="true" aria-labelledby="prayer-modal-titre">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" x-on:click="sel = null"></div>

            <div x-show="sel" x-trap.noscroll="sel"
                 x-transition:enter="transition duration-500 ease-out" x-transition:enter-start="opacity-0 translate-y-10 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 class="relative max-h-[92vh] w-full overflow-y-auto rounded-t-3xl border border-gold-500/40 bg-night-900 p-7 shadow-2xl shadow-black/60 sm:max-w-2xl sm:rounded-3xl sm:p-12">

                <button type="button" x-on:click="sel = null" aria-label="Fermer"
                        class="absolute right-4 top-4 grid size-10 place-items-center rounded-full border border-gold-500/40 text-gold-500 transition hover:rotate-90 hover:bg-gold-500/10">
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                </button>

                <template x-if="sel">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-gold-300/80" x-text="sel.category"></p>
                        <h2 id="prayer-modal-titre" class="mt-2 font-display text-4xl text-gold-500 sm:text-5xl" x-text="sel.title"></h2>
                        <p class="mt-8 whitespace-pre-line font-display leading-relaxed text-stone-100 transition-all duration-300" :class="sizeClass()" x-text="sel.text"></p>
                        <p class="mt-6 text-xs text-stone-500" x-text="sel.source"></p>

                        <div class="mt-8 flex flex-wrap items-center gap-3">
                            <div class="flex items-center rounded-full border border-gold-500/40" role="group" aria-label="Taille du texte">
                                <button type="button" x-on:click="size = Math.max(0, size - 1)" :disabled="size === 0" aria-label="Réduire le texte" class="px-4 py-2 text-sm text-gold-500 disabled:opacity-30">A−</button>
                                <button type="button" x-on:click="size = Math.min(3, size + 1)" :disabled="size === 3" aria-label="Agrandir le texte" class="border-l border-gold-500/40 px-4 py-2 text-lg text-gold-500 disabled:opacity-30">A+</button>
                            </div>
                            <button type="button" x-on:click="copy(sel)" class="rounded-full border border-gold-500 px-5 py-2.5 text-sm text-gold-500 transition hover:bg-gold-500/10" x-text="copied ? 'Copié ✓' : 'Copier'"></button>
                            <button type="button" x-on:click="toggleFav(sel.id)" class="rounded-full px-5 py-2.5 text-sm font-medium transition"
                                    :class="isFav(sel.id) ? 'bg-rose-500/20 text-rose-200' : 'bg-gold-500 text-night-950 hover:bg-gold-400'"
                                    x-text="isFav(sel.id) ? 'Retirer de mes prières' : 'Garder cette prière'"></button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</section>

{{-- MINUTEUR GUIDÉ --}}
<section class="mx-auto max-w-3xl px-4 py-20 text-center sm:px-6 lg:px-8" aria-labelledby="timer-titre"
    x-data="{
        mins: 10, total: 600, left: 600, running: false, done: false, timer: null,
        phases: ['Se poser et respirer', 'Remercier', 'Présenter ce qui pèse', 'Écouter en silence'],
        get C() { return 2 * Math.PI * 54; },
        get offset() { return this.C * (1 - this.left / this.total); },
        get phase() { return this.phases[Math.min(3, Math.floor((this.total - this.left) / this.total * 4))]; },
        get label() { return String(Math.floor(this.left / 60)).padStart(2, '0') + ':' + String(this.left % 60).padStart(2, '0'); },
        pick(m) { this.stop(); this.mins = m; this.total = m * 60; this.left = this.total; this.done = false; },
        start() {
            if (this.left <= 0) this.pick(this.mins);
            this.done = false; this.running = true;
            this.timer = setInterval(() => {
                this.left--;
                if (this.left <= 0) { this.stop(); this.done = true; if (navigator.vibrate) navigator.vibrate([300, 150, 300]); }
            }, 1000);
        },
        stop() { clearInterval(this.timer); this.running = false; },
        reset() { this.pick(this.mins); }
    }"
    x-on:livewire:navigating.window="stop()">

    <h2 id="timer-titre" class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Un temps de prière guidé</h2>
    <p class="mx-auto mt-3 max-w-xl text-stone-400">Choisissez une durée. Le minuteur vous accompagne en quatre moments, sans musique ni distraction.</p>

    <div class="mt-8 flex justify-center gap-2" role="group" aria-label="Durée">
        @foreach ([5, 10, 15, 20] as $m)
            <button type="button" x-on:click="pick({{ $m }})" :aria-pressed="mins === {{ $m }}"
                    class="rounded-full border px-5 py-2 text-sm transition duration-300"
                    :class="mins === {{ $m }} ? 'border-gold-500 bg-gold-500 font-medium text-night-950' : 'border-gold-500/30 text-stone-300 hover:border-gold-500'">{{ $m }} min</button>
        @endforeach
    </div>

    <div class="relative mx-auto mt-10 grid size-72 place-items-center">
        <svg class="absolute inset-0 -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
            <circle cx="60" cy="60" r="54" fill="none" stroke="currentColor" stroke-width="2" class="text-gold-500/15"/>
            <circle cx="60" cy="60" r="54" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                    class="text-gold-500 transition-[stroke-dashoffset] duration-1000 ease-linear"
                    :stroke-dasharray="C" :stroke-dashoffset="offset"/>
        </svg>
        <div class="relative">
            <p class="font-display text-6xl text-gold-500 tabular-nums" x-text="label" role="timer"></p>
            <p class="mt-2 min-h-6 text-sm text-stone-300" x-text="done ? 'Temps terminé. Amen.' : (running ? phase : 'Prêt à commencer')" aria-live="polite"></p>
        </div>
        <span x-show="running" class="pointer-events-none absolute inset-4 animate-ping-slow rounded-full border border-gold-500/30"></span>
    </div>

    <div class="mt-8 flex justify-center gap-3">
        <button type="button" x-show="!running" x-on:click="start()"
                class="rounded-full bg-gold-500 px-8 py-3 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30"
                x-text="left < total && left > 0 ? 'Reprendre' : 'Commencer'"></button>
        <button type="button" x-show="running" x-on:click="stop()"
                class="rounded-full border border-gold-500 px-8 py-3 font-medium text-gold-500 transition hover:bg-gold-500/10">Pause</button>
        <button type="button" x-on:click="reset()" class="rounded-full border border-gold-500/30 px-6 py-3 text-stone-300 transition hover:border-gold-500">Recommencer</button>
    </div>
</section>

{{-- INTENTIONS DE PRIÈRE --}}
<section class="border-y border-gold-500/20 bg-night-900/60 py-20" aria-labelledby="intentions-titre">
    <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">

        {{-- Formulaire --}}
        <div>
            <h2 id="intentions-titre" class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Une intention de prière ?</h2>
            <p class="mt-3 text-stone-400">Confiez-nous ce que vous portez. Nous prierons avec vous.</p>

            @if ($canRequest)
                <form method="POST" action="{{ route('prayer.request.store') }}" class="mt-8 space-y-5"
                      x-data="{ n: {{ strlen(old('content', '')) }}, max: 500 }">
                    @csrf
                    {{-- Champ piège anti-spam --}}
                    <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

                    <div>
                        <label for="pr-name" class="mb-1.5 block text-sm text-stone-300">Prénom (facultatif)</label>
                        <input id="pr-name" name="name" type="text" maxlength="80" value="{{ old('name', $user->name ?? '') }}"
                               class="w-full rounded-2xl border border-gold-500/30 bg-night-900 px-4 py-3 text-stone-100 focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30">
                        @error('name') <p class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="pr-content" class="mb-1.5 block text-sm text-stone-300">Votre intention</label>
                        <textarea id="pr-content" name="content" rows="5" required maxlength="500" x-on:input="n = $event.target.value.length"
                                  class="w-full rounded-2xl border border-gold-500/30 bg-night-900 px-4 py-3 text-stone-100 focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30">{{ old('content') }}</textarea>
                        <div class="mt-1 flex justify-between text-xs">
                            @error('content') <p class="text-rose-300">{{ $message }}</p> @else <span></span> @enderror
                            <span class="text-stone-500" :class="n > 450 && 'text-gold-300'"><span x-text="n"></span>/500</span>
                        </div>
                    </div>

                    <label class="flex items-start gap-3 text-sm text-stone-300">
                        <input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous')) class="mt-1 size-4 rounded border-gold-500/50 bg-night-900 accent-gold-500">
                        <span>Rester anonyme si mon intention est partagée</span>
                    </label>

                    <button type="submit"
                            class="rounded-full bg-gold-500 px-8 py-3 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30">
                        Envoyer mon intention
                    </button>
                </form>
            @else
                <div class="mt-8 rounded-3xl border border-gold-500/30 p-6">
                    <p class="text-stone-300">Pour nous confier une intention, écrivez-nous directement.</p>
                    <a href="{{ $to('contact') }}" wire:navigate
                       class="mt-5 inline-block rounded-full bg-gold-500 px-6 py-3 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400">
                        Nous écrire
                    </a>
                </div>
            @endif
        </div>

        {{-- Mur d'intentions --}}
        <div>
            <h3 class="font-display text-3xl text-gold-500">Intentions partagées</h3>
            @if ($wall->isNotEmpty())
                <ul class="mt-6 space-y-4">
                    @foreach ($wall as $w)
                        <li class="rounded-2xl border border-gold-500/20 bg-night-900/70 p-5">
                            <p class="leading-relaxed text-stone-200">{{ $w['text'] }}</p>
                            <p class="mt-3 text-xs text-stone-500">{{ $w['who'] }} · {{ $w['when'] }}</p>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-6 rounded-2xl border border-dashed border-gold-500/30 p-6 text-stone-400">
                    Les intentions approuvées par l’équipe apparaîtront ici.
                </p>
            @endif
        </div>
    </div>
</section>

{{-- APPEL À L'ACTION --}}
<section class="mx-auto max-w-4xl px-4 py-20 text-center sm:px-6 lg:px-8">
    @guest
        <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Prier avec la communauté</h2>
        <p class="mx-auto mt-4 max-w-lg text-stone-400">Créez un compte pour recevoir les rappels et retrouver vos prières favorites.</p>
        <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ $to('register') }}" wire:navigate class="w-full rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400 sm:w-auto">Register</a>
            <a href="{{ $to('events') }}" wire:navigate class="w-full rounded-full border border-gold-500 px-8 py-3.5 font-medium text-gold-500 transition hover:bg-gold-500/10 sm:w-auto">Voir les événements</a>
        </div>
    @endguest

    @auth
        <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Vous n’êtes pas seul(e)</h2>
        <p class="mx-auto mt-4 max-w-lg text-stone-400">Retrouvez la communauté lors des prochains événements.</p>
        <a href="{{ $to('events') }}" wire:navigate class="mt-8 inline-block rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400">Voir les événements</a>
    @endauth
</section>
</div>