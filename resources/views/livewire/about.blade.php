<div>
@php
    // Helpers
    $to      = fn (string $name) => Route::has($name) ? route($name) : '#';
    $user    = auth()->user();
    $isAdmin = $user && $user->role === 'admin';
    $counts = ['members' => 0, 'choreos' => 0, 'events' => 0];
    $team = collect();

    try {
        if (class_exists(\App\Models\User::class))         $counts['members'] = \App\Models\User::count();
        if (class_exists(\App\Models\Choreography::class)) $counts['choreos'] = \App\Models\Choreography::count();
        if (class_exists(\App\Models\Event::class))        $counts['events']  = \App\Models\Event::count();

        if (class_exists(\App\Models\TeamMember::class)) {
            $team = \App\Models\TeamMember::orderBy('id')->get()->map(fn ($m) => [
                'name'  => $m->name,
                'role'  => $m->role ?? '',
                'bio'   => $m->bio ?? '',
                'photo' => ! empty($m->photo) ? asset('storage/'.$m->photo) : null,
            ]);
        }
    } catch (\Throwable $e) {
        //
    }

    $stats = array_values(array_filter([
        ['value' => $counts['members'], 'label' => 'Membres inscrits'],
        ['value' => $counts['choreos'], 'label' => 'Chorégraphies'],
        ['value' => $counts['events'],  'label' => 'Événements organisés'],
    ], fn ($s) => $s['value'] > 0));

    // Contenu
    $pillars = [
        ['Danser', 'Des chorégraphies pensées comme des prières, ouvertes à tous les niveaux, apprises ensemble.', 'choreographies', 'Voir les chorégraphies'],
        ['Prier',  'Trois temps de prière chaque jour, des prières à garder et un espace pour confier ses intentions.', 'prayer', 'Découvrir la prière'],
        ['Se rassembler', 'Des ateliers, des veillées et des célébrations pour se retrouver et s’encourager.', 'events', 'Voir les événements'],
    ];

    $values = [
        ['Foi',      'Tout part d’une relation vivante avec Dieu, que la danse et la prière expriment.'],
        ['Joie',     'Nous dansons avec simplicité, sans chercher la performance.'],
        ['Humilité', 'Personne ne danse pour être vu : tout est offert.'],
        ['Unité',    'Chacun avance à son rythme, mais nous avançons ensemble.'],
        ['Service',  'Nous apprenons en donnant : du temps, des talents, de l’attention.'],
        ['Accueil',  'Il y a une place pour chacun, quel que soit son parcours.'],
    ];

    $steps = [
        ['Entendre l’appel', 'Tout commence par un temps de prière : on écoute avant de créer.'],
        ['Créer ensemble',   'Les chorégraphies naissent en groupe, à partir de ce que chacun apporte.'],
        ['Partager',         'Elles sont enseignées lors d’ateliers ouverts, pas à pas.'],
        ['Être envoyés',     'Ce que nous recevons, nous le portons ensuite autour de nous.'],
    ];

    // Pôles de la communauté
    $poles = [
        ['Danseurs',      'Ils créent et enseignent les chorégraphies.'],
        ['Musiciens',     'Ils accompagnent les temps de louange.'],
        ['Intercesseurs', 'Ils portent les intentions de prière.'],
        ['Accueil',       'Ils reçoivent les nouveaux et organisent les rencontres.'],
    ];

    $arrow = 'M3 10a.75.75 0 01.75-.75h10.69L10.47 5.28a.75.75 0 011.06-1.06l5.25 5.25a.75.75 0 010 1.06l-5.25 5.25a.75.75 0 11-1.06-1.06l3.97-3.97H3.75A.75.75 0 013 10z';
@endphp

{{-- EN-TÊTE --}}
<section class="relative isolate overflow-hidden px-4 pb-20 pt-12 text-center sm:px-6 lg:px-8" aria-labelledby="about-titre">
    <div class="pointer-events-none absolute left-1/2 top-0 -z-10 size-[40rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gold-500/15 blur-3xl"></div>

    <p class="animate-rise text-sm text-gold-300/80">À propos</p>
    <h1 id="about-titre" class="mt-3 animate-rise font-display text-5xl font-semibold text-gold-500 sm:text-7xl" style="animation-delay:.15s">
        Une mission, une famille
    </h1>
    <p class="mx-auto mt-6 max-w-2xl animate-rise text-lg leading-relaxed text-stone-300" style="animation-delay:.3s">
        Iraka Voafidy est une communauté malgache qui danse, prie et se rassemble. Nous croyons que le corps
        et la voix peuvent porter une prière, et que chacun est appelé à la partager.
    </p>

    {{-- Le nom, mot par mot --}}
    <div class="mx-auto mt-14 grid max-w-3xl gap-4 sm:grid-cols-2">
        <div class="animate-rise rounded-3xl border border-gold-500/30 bg-night-900/70 p-7 text-left transition duration-500 hover:-translate-y-1 hover:border-gold-500/70" style="animation-delay:.5s">
            <p class="font-display text-5xl text-gold-500">Iraka</p>
            <p class="mt-3 text-sm leading-relaxed text-stone-400">La mission, le message que l’on porte. Celui qui reçoit quelque chose pour le transmettre.</p>
        </div>
        <div class="animate-rise rounded-3xl border border-gold-500/30 bg-night-900/70 p-7 text-left transition duration-500 hover:-translate-y-1 hover:border-gold-500/70" style="animation-delay:.7s">
            <p class="font-display text-5xl text-gold-500">Voafidy</p>
            <p class="mt-3 text-sm leading-relaxed text-stone-400">Choisi. Non pour sa valeur, mais pour être envoyé, appelé par son nom.</p>
        </div>
    </div>
</section>

{{-- VERSET FONDATEUR --}}
<section class="border-y border-gold-500/20 bg-night-900/60 py-16" x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <figure class="mx-auto max-w-3xl px-4 text-center transition duration-1000 ease-out sm:px-6"
            x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'">
        <blockquote class="font-display text-2xl italic leading-snug text-stone-100 sm:text-3xl">
            Ce n’est pas vous qui m’avez choisi ; mais moi, je vous ai choisis, et je vous ai établis, afin que vous alliez, et que vous portiez du fruit, et que votre fruit demeure.
        </blockquote>
        <figcaption class="mt-4 text-sm text-gold-500">Jean 15, 16 <span class="text-stone-500">· Louis Segond 1910</span></figcaption>
    </figure>
</section>

{{-- MISSION & VISION --}}
<section class="mx-auto max-w-6xl px-4 py-24 sm:px-6 lg:px-8" x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <div class="grid gap-6 md:grid-cols-2">
        @foreach ([
            ['Notre mission', 'Offrir à chacun un chemin simple pour louer Dieu par la danse et la prière, et former une communauté où l’on grandit ensemble.'],
            ['Notre vision',  'Une communauté accueillante, ancrée dans la culture malgache, où la louange devient un service rendu aux autres.'],
        ] as $i => [$t, $d])
            <article class="group relative overflow-hidden rounded-3xl border border-gold-500/20 bg-linear-to-br from-gold-900/30 via-night-900 to-night-950 p-8 transition duration-700 ease-out
                            hover:-translate-y-2 hover:border-gold-500/70 hover:shadow-2xl hover:shadow-gold-500/10 sm:p-10"
                     x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'" style="transition-delay: {{ $i * 200 }}ms">
                <div class="absolute -right-10 -top-10 size-40 rounded-full bg-gold-500/15 transition duration-700 group-hover:scale-[2.2]"></div>
                <h2 class="relative font-display text-3xl text-gold-500 sm:text-4xl">{{ $t }}</h2>
                <p class="relative mt-4 leading-relaxed text-stone-300">{{ $d }}</p>
            </article>
        @endforeach
    </div>
</section>

{{-- CE QUE NOUS FAISONS --}}
<section class="bg-night-900/40 py-24" x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Ce que nous faisons</h2>

        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach ($pillars as $i => [$t, $d, $route, $cta])
                <a href="{{ $to($route) }}" wire:navigate
                   class="group flex flex-col rounded-3xl border border-gold-500/20 bg-night-900/70 p-8 transition duration-700 ease-out
                          hover:-translate-y-2 hover:border-gold-500/70 hover:bg-gold-500/5 hover:shadow-xl hover:shadow-gold-500/10"
                   x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'" style="transition-delay: {{ $i * 150 }}ms">
                    <span class="font-display text-6xl text-gold-500/30 transition duration-500 group-hover:text-gold-500">{{ $i + 1 }}</span>
                    <h3 class="mt-4 font-display text-3xl text-stone-100">{{ $t }}</h3>
                    <p class="mt-3 flex-1 text-sm leading-relaxed text-stone-400">{{ $d }}</p>
                    <span class="mt-6 inline-flex items-center gap-2 font-medium text-gold-500">
                        {{ $cta }}
                        <svg class="size-4 transition duration-300 group-hover:translate-x-1.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrow }}" clip-rule="evenodd"/></svg>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- VALEURS --}}
<section class="mx-auto max-w-6xl px-4 py-24 sm:px-6 lg:px-8" x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Nos valeurs</h2>

    <dl class="mt-12 grid gap-x-10 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($values as $i => [$t, $d])
            <div class="group border-l border-gold-500/30 pl-6 transition duration-700 ease-out hover:border-gold-500"
                 x-bind:class="shown ? 'translate-x-0 opacity-100' : '-translate-x-6 opacity-0'" style="transition-delay: {{ $i * 100 }}ms">
                <dt class="font-display text-3xl text-gold-500 transition duration-300 group-hover:translate-x-1">{{ $t }}</dt>
                <dd class="mt-2 text-sm leading-relaxed text-stone-400">{{ $d }}</dd>
            </div>
        @endforeach
    </dl>
</section>

{{-- CHIFFRES RÉELS --}}
@if (count($stats))
    <section class="border-y border-gold-500/20 bg-night-900/60" aria-label="La communauté en chiffres">
        <dl class="mx-auto grid max-w-4xl {{ [1 => 'grid-cols-1', 2 => 'grid-cols-2', 3 => 'grid-cols-3'][min(count($stats), 3)] }} gap-6 px-4 py-14 sm:px-6 lg:px-8">
            @foreach ($stats as $stat)
                <div class="text-center"
                     x-data="{ n: 0, target: {{ (int) $stat['value'] }} }"
                     x-intersect.once="
                        const start = performance.now(), dur = 1600;
                        const tick = (t) => {
                            const p = Math.min((t - start) / dur, 1);
                            n = Math.round(target * (1 - Math.pow(1 - p, 3)));
                            if (p < 1) requestAnimationFrame(tick);
                        };
                        requestAnimationFrame(tick);
                     ">
                    <dd class="font-display text-5xl font-semibold text-gold-500 sm:text-6xl"><span x-text="n">0</span></dd>
                    <dt class="mt-2 text-sm text-stone-400">{{ $stat['label'] }}</dt>
                </div>
            @endforeach
        </dl>
    </section>
@endif

{{-- NOTRE MANIÈRE DE FAIRE --}}
<section class="mx-auto max-w-4xl px-4 py-24 sm:px-6 lg:px-8" x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Notre manière de faire</h2>

    <ol class="relative mt-14 space-y-10 border-l border-gold-500/30 pl-8">
        <span class="absolute -left-px top-0 h-full w-px origin-top bg-gold-500 transition-transform duration-[1800ms] ease-out"
              x-bind:class="shown ? 'scale-y-100' : 'scale-y-0'"></span>

        @foreach ($steps as $i => [$t, $d])
            <li class="relative transition duration-700 ease-out" x-bind:class="shown ? 'translate-x-0 opacity-100' : '-translate-x-6 opacity-0'"
                style="transition-delay: {{ 300 + $i * 300 }}ms">
                <span class="absolute -left-[2.9rem] top-0 grid size-8 place-items-center rounded-full border border-gold-500 bg-night-950 font-display text-lg text-gold-500">{{ $i + 1 }}</span>
                <h3 class="font-display text-2xl text-stone-100">{{ $t }}</h3>
                <p class="mt-1 text-stone-400">{{ $d }}</p>
            </li>
        @endforeach
    </ol>
</section>

{{-- ÉQUIPE / PÔLES --}}
<section class="border-y border-gold-500/20 bg-night-900/40 py-24" x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">{{ $team->isNotEmpty() ? 'Ceux qui servent' : 'Une communauté, plusieurs services' }}</h2>

        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @if ($team->isNotEmpty())
                @foreach ($team as $i => $m)
                    <article class="group rounded-3xl border border-gold-500/20 bg-night-900/70 p-6 text-center transition duration-700 ease-out hover:-translate-y-2 hover:border-gold-500/70"
                             x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'" style="transition-delay: {{ ($i % 4) * 120 }}ms">
                        @if ($m['photo'])
                            <img src="{{ $m['photo'] }}" alt="{{ $m['name'] }}" loading="lazy" class="mx-auto size-24 rounded-full border border-gold-500/50 object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <span class="mx-auto grid size-24 place-items-center rounded-full border border-gold-500/50 bg-gold-500/10 font-display text-4xl text-gold-500">{{ mb_strtoupper(mb_substr($m['name'], 0, 1)) }}</span>
                        @endif
                        <h3 class="mt-4 font-display text-2xl text-stone-100">{{ $m['name'] }}</h3>
                        @if ($m['role']) <p class="text-sm text-gold-500">{{ $m['role'] }}</p> @endif
                        @if ($m['bio']) <p class="mt-3 text-sm leading-relaxed text-stone-400">{{ $m['bio'] }}</p> @endif
                    </article>
                @endforeach
            @else
                @foreach ($poles as $i => [$t, $d])
                    <article class="group rounded-3xl border border-gold-500/20 bg-night-900/70 p-7 transition duration-700 ease-out hover:-translate-y-2 hover:border-gold-500/70 hover:bg-gold-500/5"
                             x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'" style="transition-delay: {{ $i * 120 }}ms">
                        <span class="block h-px w-8 bg-gold-500 transition-all duration-500 group-hover:w-16"></span>
                        <h3 class="mt-5 font-display text-2xl text-gold-500">{{ $t }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-stone-400">{{ $d }}</p>
                    </article>
                @endforeach
            @endif
        </div>
    </div>
</section>

{{-- REJOINDRE --}}
<section class="relative overflow-hidden py-24" x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <div class="pointer-events-none absolute left-1/2 top-1/2 size-[32rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gold-500/10 blur-3xl"></div>

    <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
        <div class="transition duration-1000 ease-out" x-bind:class="shown ? 'scale-100 opacity-100' : 'scale-95 opacity-0'">
            @guest
                <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Venez comme vous êtes</h2>
                <p class="mx-auto mt-4 max-w-lg text-stone-400">Créez votre compte, découvrez les chorégraphies et rejoignez-nous à la prochaine rencontre.</p>
                <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ $to('register') }}" wire:navigate class="w-full rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400 sm:w-auto">Register</a>
                    <a href="{{ $to('contact') }}" wire:navigate class="w-full rounded-full border border-gold-500 px-8 py-3.5 font-medium text-gold-500 transition hover:bg-gold-500/10 sm:w-auto">Nous contacter</a>
                </div>
            @endguest

            @auth
                <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Merci de faire partie de la famille</h2>
                <p class="mx-auto mt-4 max-w-lg text-stone-400">
                    {{ $isAdmin ? 'Gérez le contenu de la communauté depuis votre tableau de bord.' : 'Une idée, une question ? Notre porte est ouverte.' }}
                </p>
                <a href="{{ $isAdmin ? $to('admin.dashboard') : $to('contact') }}" wire:navigate
                   class="mt-8 inline-block rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400">
                    {{ $isAdmin ? 'Ouvrir le tableau de bord' : 'Nous écrire' }}
                </a>
            @endauth
        </div>
    </div>
</section>
</div>