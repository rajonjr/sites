<div>
@php
    // ---------- Helpers ----------
    $to      = fn (string $name) => Route::has($name) ? route($name) : '#';
    $user    = auth()->user();
    $isAdmin = $user && $user->role === 'admin';

    $items = collect();

    try {
        if (class_exists(\App\Models\Choreography::class)) {
            $items = \App\Models\Choreography::latest()->get()->map(fn ($c) => [
                'id'          => $c->id,
                'title'       => $c->title,
                'category'    => $c->category ?: 'Autre',
                'level'       => $c->level ?: 'Tous niveaux',
                'duration'    => (int) ($c->duration ?? 0),
                'description' => $c->description ?: 'Aucune description pour le moment.',
                'music'       => $c->music ?? null,
                'video'       => $c->video_url ?? null,
                'ts'          => optional($c->created_at)->timestamp ?? 0,
            ]);
        }
    } catch (\Throwable $e) {
        //
    }

    $isSample = $items->isEmpty();

    if ($isSample) {
        $items = collect([
            ['id' => 1, 'title' => 'Louange en mouvement', 'category' => 'Adoration',   'level' => 'Débutant',      'duration' => 6,  'description' => 'Une danse lente et ample qui accompagne les chants de louange. Idéale pour découvrir la danse comme prière.', 'music' => null, 'video' => null, 'ts' => 6],
            ['id' => 2, 'title' => 'Joie des nations',     'category' => 'Célébration', 'level' => 'Intermédiaire', 'duration' => 8,  'description' => 'Des rythmes vifs inspirés des danses de plusieurs régions de Madagascar, pour les grandes célébrations.', 'music' => null, 'video' => null, 'ts' => 5],
            ['id' => 3, 'title' => 'Le chemin choisi',     'category' => 'Méditation',  'level' => 'Débutant',      'duration' => 5,  'description' => 'Une chorégraphie sobre pour accompagner les temps de silence et de recueillement.', 'music' => null, 'video' => null, 'ts' => 4],
            ['id' => 4, 'title' => 'Envoyés',              'category' => 'Célébration', 'level' => 'Avancé',        'duration' => 10, 'description' => 'Une pièce d’ensemble exigeante, avec des changements de formation et des portés de groupe.', 'music' => null, 'video' => null, 'ts' => 3],
            ['id' => 5, 'title' => 'Souffle du matin',     'category' => 'Adoration',   'level' => 'Intermédiaire', 'duration' => 7,  'description' => 'Une danse fluide, inspirée du lever du jour, qui travaille la respiration et les bras.', 'music' => null, 'video' => null, 'ts' => 2],
            ['id' => 6, 'title' => 'Cercle de paix',       'category' => 'Méditation',  'level' => 'Tous niveaux',  'duration' => 4,  'description' => 'Une ronde simple, faite pour être dansée à plusieurs, sans aucune expérience préalable.', 'music' => null, 'video' => null, 'ts' => 1],
        ]);
    }

    $arrow = 'M3 10a.75.75 0 01.75-.75h10.69L10.47 5.28a.75.75 0 011.06-1.06l5.25 5.25a.75.75 0 010 1.06l-5.25 5.25a.75.75 0 11-1.06-1.06l3.97-3.97H3.75A.75.75 0 013 10z';
@endphp

{{-- EN-TÊTE --}}
<section class="relative isolate overflow-hidden px-4 pb-10 pt-12 text-center sm:px-6 lg:px-8" aria-labelledby="choreo-titre">
    <div class="pointer-events-none absolute left-1/2 top-0 -z-10 size-[36rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gold-500/15 blur-3xl"></div>

    <p class="animate-rise text-sm text-gold-300/80">Danser pour louer</p>
    <h1 id="choreo-titre" class="mt-3 animate-rise font-display text-5xl font-semibold text-gold-500 sm:text-7xl" style="animation-delay:.15s">
        Nos chorégraphies
    </h1>
    <p class="mx-auto mt-5 max-w-2xl animate-rise text-stone-300" style="animation-delay:.3s">
        Parcourez, filtrez et enregistrez vos chorégraphies préférées. Chacune est pensée pour être partagée en communauté.
    </p>

    @if ($isAdmin)
        <a href="{{ $to('admin.choreographies') }}" wire:navigate
           class="mt-6 inline-flex animate-rise items-center gap-2 rounded-full bg-gold-500 px-6 py-2.5 text-sm font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400"
           style="animation-delay:.45s">
            Gérer les chorégraphies
        </a>
    @endif
</section>

{{-- EXPLORATEUR --}}
<section class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8"
    x-data="{
        items: @js($items->values()),
        q: '',
        cat: 'Toutes',
        level: 'Tous',
        sort: 'recent',
        onlyFavs: false,
        visible: 6,
        sel: null,
        favs: $persist([]).as('iv-choreo-favs'),

        norm(s) { return (s || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); },
        get cats() { return ['Toutes', ...new Set(this.items.map(i => i.category))]; },
        get levels() { return ['Tous', ...new Set(this.items.map(i => i.level))]; },
        get filtered() {
            const q = this.norm(this.q);
            let list = this.items.filter(i =>
                (this.cat === 'Toutes' || i.category === this.cat) &&
                (this.level === 'Tous' || i.level === this.level) &&
                (!this.onlyFavs || this.favs.includes(i.id)) &&
                (!q || this.norm(i.title + ' ' + i.description + ' ' + i.category).includes(q))
            );
            if (this.sort === 'az') list = [...list].sort((a, b) => a.title.localeCompare(b.title, 'fr'));
            else if (this.sort === 'duree') list = [...list].sort((a, b) => a.duration - b.duration);
            else list = [...list].sort((a, b) => b.ts - a.ts);
            return list;
        },
        get shown() { return this.filtered.slice(0, this.visible); },
        isFav(id) { return this.favs.includes(id); },
        toggleFav(id) { this.favs = this.isFav(id) ? this.favs.filter(f => f !== id) : [...this.favs, id]; },
        reset() { this.q = ''; this.cat = 'Toutes'; this.level = 'Tous'; this.onlyFavs = false; this.visible = 6; },
        levelClass(l) {
            return l === 'Débutant' ? 'border-emerald-400/50 text-emerald-300'
                 : l === 'Intermédiaire' ? 'border-gold-500/60 text-gold-300'
                 : l === 'Avancé' ? 'border-rose-400/50 text-rose-300'
                 : 'border-stone-500/50 text-stone-300';
        }
    }"
    x-on:keydown.escape.window="sel = null"
    x-on:livewire:navigating.window="sel = null">

    @if ($isSample)
        <p class="mb-6 rounded-xl border border-gold-500/30 bg-gold-500/5 px-4 py-3 text-center text-sm text-gold-300" role="note">
            Aucune chorégraphie enregistrée pour l’instant : voici des exemples.
        </p>
    @endif

    {{-- Barre d'outils collante --}}
    <div class="sticky top-16 z-30 -mx-4 border-y border-gold-500/20 bg-night-950/85 px-4 py-4 backdrop-blur-xl sm:mx-0 sm:rounded-2xl sm:border">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
            {{-- Recherche --}}
            <label class="relative block flex-1">
                <span class="sr-only">Rechercher une chorégraphie</span>
                <svg class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-gold-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.45 4.39l3.58 3.58a.75.75 0 11-1.06 1.06l-3.58-3.58A7 7 0 012 9z" clip-rule="evenodd"/>
                </svg>
                <input type="search" x-model="q" x-on:input="visible = 6" placeholder="Rechercher par titre ou mot-clé"
                       class="w-full rounded-full border border-gold-500/30 bg-night-900 py-3 pl-12 pr-4 text-sm text-stone-100 placeholder:text-stone-500
                              transition focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30">
            </label>

            {{-- Niveau + tri --}}
            <div class="grid grid-cols-2 gap-3 lg:flex">
                <label class="block">
                    <span class="sr-only">Niveau</span>
                    <select x-model="level" x-on:change="visible = 6"
                            class="w-full rounded-full border border-gold-500/30 bg-night-900 px-4 py-3 text-sm text-stone-200 focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30">
                        <template x-for="l in levels" :key="l"><option :value="l" x-text="l === 'Tous' ? 'Tous les niveaux' : l"></option></template>
                    </select>
                </label>
                <label class="block">
                    <span class="sr-only">Trier par</span>
                    <select x-model="sort"
                            class="w-full rounded-full border border-gold-500/30 bg-night-900 px-4 py-3 text-sm text-stone-200 focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30">
                        <option value="recent">Plus récentes</option>
                        <option value="az">Ordre alphabétique</option>
                        <option value="duree">Durée croissante</option>
                    </select>
                </label>
            </div>
        </div>

        {{-- Catégories + favoris --}}
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <template x-for="c in cats" :key="c">
                <button type="button" x-on:click="cat = c; visible = 6"
                        class="rounded-full border px-4 py-1.5 text-sm transition duration-300"
                        :class="cat === c ? 'border-gold-500 bg-gold-500 font-medium text-night-950' : 'border-gold-500/30 text-stone-300 hover:border-gold-500 hover:text-gold-500'"
                        x-text="c"></button>
            </template>

            <button type="button" x-on:click="onlyFavs = !onlyFavs; visible = 6"
                    class="ml-auto flex items-center gap-2 rounded-full border px-4 py-1.5 text-sm transition duration-300"
                    :class="onlyFavs ? 'border-rose-400 bg-rose-500/15 text-rose-200' : 'border-gold-500/30 text-stone-300 hover:border-gold-500'"
                    :aria-pressed="onlyFavs">
                <svg class="size-4" viewBox="0 0 20 20" :fill="onlyFavs ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <path d="M10 17s-6.5-4.2-6.5-9A3.5 3.5 0 0110 6.2 3.5 3.5 0 0116.500 8c0 4.800-6.500 9-6.500 9z" stroke-linejoin="round"/>
                </svg>
                Favoris (<span x-text="favs.length"></span>)
            </button>
        </div>
    </div>

    {{-- Compteur --}}
    <p class="mt-6 text-sm text-stone-400" aria-live="polite">
        <span x-text="filtered.length"></span>
        <span x-text="filtered.length > 1 ? 'chorégraphies trouvées' : 'chorégraphie trouvée'"></span>
    </p>

    {{-- Grille --}}
    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <template x-for="(c, idx) in shown" :key="c.id">
            <article class="group relative flex animate-rise flex-col overflow-hidden rounded-3xl border border-gold-500/20
                            bg-linear-to-br from-gold-900/30 via-night-900 to-night-950 p-6 transition duration-500
                            hover:-translate-y-2 hover:border-gold-500/70 hover:shadow-2xl hover:shadow-gold-500/10"
                     :style="`animation-delay:${(idx % 6) * 70}ms`">
                <div class="absolute -right-10 -top-10 size-36 rounded-full bg-gold-500/15 transition duration-700 group-hover:scale-[2.2] group-hover:bg-gold-500/10"></div>

                <div class="relative flex items-start justify-between gap-3">
                    <span class="rounded-full border border-gold-500/50 px-3 py-1 text-xs text-gold-300" x-text="c.category"></span>
                    <button type="button" x-on:click="toggleFav(c.id)" :aria-pressed="isFav(c.id)"
                            :aria-label="isFav(c.id) ? 'Retirer des favoris' : 'Ajouter aux favoris'"
                            class="grid size-9 place-items-center rounded-full border border-gold-500/30 transition duration-300 hover:scale-110"
                            :class="isFav(c.id) ? 'border-rose-400 bg-rose-500/15 text-rose-300' : 'text-stone-400 hover:text-rose-300'">
                        <svg class="size-4" viewBox="0 0 20 20" :fill="isFav(c.id) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path d="M10 17s-6.5-4.2-6.5-9A3.5 3.5 0 0110 6.2 3.5 3.5 0 0116.500 8c0 4.800-6.500 9-6.500 9z" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <h2 class="relative mt-8 font-display text-3xl leading-tight text-stone-100" x-text="c.title"></h2>
                <p class="relative mt-2 line-clamp-3 text-sm leading-relaxed text-stone-400" x-text="c.description"></p>

                <div class="relative mt-5 flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full border px-3 py-1" :class="levelClass(c.level)" x-text="c.level"></span>
                    <span class="text-stone-400" x-show="c.duration > 0" x-text="c.duration + ' min'"></span>
                </div>

                <button type="button" x-on:click="sel = c"
                        class="relative mt-6 inline-flex w-fit items-center gap-2 font-medium text-gold-500">
                    <span class="border-b border-gold-500/0 transition group-hover:border-gold-500">Voir le détail</span>
                    <svg class="size-4 transition duration-300 group-hover:translate-x-1.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrow }}" clip-rule="evenodd"/></svg>
                </button>
            </article>
        </template>
    </div>

    {{-- Aucun résultat --}}
    <div x-show="filtered.length === 0" x-cloak
         x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-y-4"
         class="mt-10 rounded-3xl border border-dashed border-gold-500/30 px-6 py-16 text-center">
        <p class="font-display text-3xl text-gold-500">Aucun résultat</p>
        <p class="mt-2 text-stone-400">Essayez un autre mot-clé ou retirez un filtre.</p>
        <button type="button" x-on:click="reset()"
                class="mt-6 rounded-full border border-gold-500 px-6 py-2.5 text-sm font-medium text-gold-500 transition hover:bg-gold-500 hover:text-night-950">
            Réinitialiser les filtres
        </button>
    </div>

    {{-- Charger plus --}}
    <div class="mt-12 text-center" x-show="filtered.length > visible" x-cloak>
        <button type="button" x-on:click="visible += 6"
                class="rounded-full bg-gold-500 px-8 py-3 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30">
            Voir plus
        </button>
    </div>

    {{-- FENÊTRE DE DÉTAIL --}}
    <div x-show="sel" x-cloak class="fixed inset-0 z-[80] flex items-end justify-center p-0 sm:items-center sm:p-6"
         role="dialog" aria-modal="true" aria-labelledby="choreo-modal-titre">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"
             x-show="sel" x-transition:enter="transition duration-300" x-transition:enter-start="opacity-0" x-transition:leave="transition duration-200" x-transition:leave-end="opacity-0"
             x-on:click="sel = null"></div>

        <div x-show="sel" x-trap.noscroll="sel"
             x-transition:enter="transition duration-400 ease-out" x-transition:enter-start="opacity-0 translate-y-10 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="opacity-0 translate-y-10 sm:scale-95"
             class="relative max-h-[90vh] w-full overflow-y-auto rounded-t-3xl border border-gold-500/40 bg-night-900 p-7 shadow-2xl shadow-black/60 sm:max-w-2xl sm:rounded-3xl sm:p-10">

            <button type="button" x-on:click="sel = null" aria-label="Fermer"
                    class="absolute right-4 top-4 grid size-10 place-items-center rounded-full border border-gold-500/40 text-gold-500 transition hover:rotate-90 hover:bg-gold-500/10">
                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
            </button>

            <template x-if="sel">
                <div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full border border-gold-500/50 px-3 py-1 text-gold-300" x-text="sel.category"></span>
                        <span class="rounded-full border px-3 py-1" :class="levelClass(sel.level)" x-text="sel.level"></span>
                        <span class="text-stone-400" x-show="sel.duration > 0" x-text="sel.duration + ' min'"></span>
                    </div>

                    <h2 id="choreo-modal-titre" class="mt-4 font-display text-4xl text-gold-500 sm:text-5xl" x-text="sel.title"></h2>
                    <p class="mt-5 leading-relaxed text-stone-300" x-text="sel.description"></p>

                    <dl class="mt-6 space-y-2 text-sm" x-show="sel.music">
                        <div class="flex gap-2"><dt class="text-stone-500">Musique :</dt><dd class="text-stone-300" x-text="sel.music"></dd></div>
                    </dl>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a x-show="sel.video" :href="sel.video" target="_blank" rel="noopener noreferrer"
                           class="rounded-full bg-gold-500 px-6 py-3 text-center font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400">
                            Voir la vidéo
                        </a>
                        <button type="button" x-on:click="toggleFav(sel.id)"
                                class="rounded-full border px-6 py-3 font-medium transition"
                                :class="isFav(sel.id) ? 'border-rose-400 bg-rose-500/15 text-rose-200' : 'border-gold-500 text-gold-500 hover:bg-gold-500/10'"
                                x-text="isFav(sel.id) ? 'Retirer des favoris' : 'Ajouter aux favoris'"></button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>

{{-- APPEL À L'ACTION --}}
<section class="relative overflow-hidden border-t border-gold-500/20 bg-night-900/60">
    <div class="relative mx-auto max-w-4xl px-4 py-20 text-center sm:px-6 lg:px-8">
        @guest
            <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Envie d’apprendre avec nous ?</h2>
            <p class="mx-auto mt-4 max-w-lg text-stone-400">Créez un compte pour suivre les ateliers et retrouver vos favoris.</p>
            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ $to('register') }}" wire:navigate class="w-full rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400 sm:w-auto">Register</a>
                <a href="{{ $to('events') }}" wire:navigate class="w-full rounded-full border border-gold-500 px-8 py-3.5 font-medium text-gold-500 transition hover:bg-gold-500/10 sm:w-auto">Voir les événements</a>
            </div>
        @endguest

        @auth
            <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Prochain atelier</h2>
            <p class="mx-auto mt-4 max-w-lg text-stone-400">Retrouvez les dates des prochains ateliers dans la page Événements.</p>
            <a href="{{ $to('events') }}" wire:navigate
               class="mt-8 inline-block rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400">
                Voir les événements
            </a>
        @endauth
    </div>
</section>
</div>