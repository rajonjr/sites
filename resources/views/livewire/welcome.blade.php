<div>
@php
    // Helpers
    $to      = fn (string $name) => Route::has($name) ? route($name) : '#';
    $user    = auth()->user();
    $isAdmin = $user && $user->role === 'admin';
    $isUser  = $user && $user->role === 'user';

    $events = collect();
    $choreographies = collect();
    $memberCount = 0;
    $choreoTotal = 0;
    $eventTotal  = 0;

    try {
        if (class_exists(\App\Models\Event::class)) {
            $events = \App\Models\Event::where('date', '>=', now())->orderBy('date')->take(3)->get()
                ->map(fn ($e) => [
                    'date'  => \Illuminate\Support\Carbon::parse($e->date),
                    'title' => $e->title,
                    'place' => $e->location ?? $e->place ?? '',
                ]);
        }
        if (class_exists(\App\Models\Choreography::class)) {
            $choreographies = \App\Models\Choreography::latest()->take(3)->get()
                ->map(fn ($c) => [
                    'title' => $c->title,
                    'tag'   => $c->category ?? 'Chorégraphie',
                    'text'  => \Illuminate\Support\Str::limit($c->description ?? '', 110),
                ]);
        }
        if (class_exists(\App\Models\Choreography::class)) {
            $choreoTotal = \App\Models\Choreography::count();
        }
        if (class_exists(\App\Models\Event::class)) {
            $eventTotal = \App\Models\Event::where('date', '>=', now())->count();
        }
        if (class_exists(\App\Models\User::class)) {
            $memberCount = \App\Models\User::count();
        }
    } catch (\Throwable $e) {
        //
    }

    if ($events->isEmpty()) {
        $events = collect([
            ['date' => now()->addDays(6),  'title' => 'Soirée de louange et de danse',  'place' => 'Antananarivo'],
            ['date' => now()->addDays(20), 'title' => 'Atelier de chorégraphie ouvert', 'place' => 'Salle paroissiale'],
            ['date' => now()->addDays(41), 'title' => 'Grande célébration annuelle',    'place' => 'Antananarivo'],
        ]);
    }

    if ($choreographies->isEmpty()) {
        $choreographies = collect([
            ['title' => 'Louange en mouvement', 'tag' => 'Adoration',   'text' => 'Une danse lente et ample qui accompagne les chants de louange.'],
            ['title' => 'Joie des nations',     'tag' => 'Célébration', 'text' => 'Des rythmes vifs inspirés des danses de plusieurs régions de Madagascar.'],
            ['title' => 'Le chemin choisi',     'tag' => 'Méditation',  'text' => 'Une chorégraphie sobre pour accompagner les temps de silence.'],
        ]);
    }

    $stats = [
        ['value' => $memberCount, 'label' => 'Membres inscrits'],
        ['value' => $choreoTotal, 'label' => 'Chorégraphies'],
        ['value' => $eventTotal,  'label' => 'Événements à venir'],
        ['value' => 3,            'label' => 'Temps de prière par jour'],
    ];

    $words = ['Danse', 'Prière', 'Louange', 'Communauté', 'Partage', 'Joie', 'Foi', 'Mission'];

    $steps = [
        ['Créer votre compte', 'Inscrivez-vous en une minute avec votre email.'],
        ['Choisir une chorégraphie', 'Parcourez les chorégraphies et repérez celle qui vous parle.'],
        ['Venir à un événement', 'Rejoignez-nous à la prochaine rencontre et dansez avec nous.'],
    ];

    $prayers = [
        ['time' => '06:00', 'label' => 'Prière du matin',  'text' => 'Confier la journée qui commence.'],
        ['time' => '12:00', 'label' => 'Prière de midi',   'text' => 'Une pause courte pour se recentrer.'],
        ['time' => '18:30', 'label' => 'Prière du soir',   'text' => 'Remercier et déposer la journée.'],
    ];

    // Témoignages
    $testimonials = [
        ['Une communauté où l’on se sent attendu dès le premier jour. La danse est devenue ma façon de prier.', 'Membre depuis deux ans'],
        ['Les ateliers m’ont appris à danser sans complexe. Tout le monde avance à son rythme.', 'Participante aux ateliers'],
        ['Les temps de prière du soir structurent mes semaines. Je ne voudrais plus m’en passer.', 'Membre du groupe de prière'],
    ];

    $faqs = [
        ['Faut-il savoir danser pour participer ?', 'Non. Les chorégraphies sont expliquées pas à pas et ouvertes à tous les niveaux. L’important est de venir.'],
        ['Comment s’inscrire à un événement ?', 'Créez un compte, ouvrez la page Événements et choisissez celui qui vous intéresse. Les détails pratiques y sont indiqués.'],
        ['Les rencontres sont-elles gratuites ?', 'La participation aux rencontres est ouverte à tous. Les éventuels frais (salle, matériel) sont précisés sur la page de chaque événement.'],
        ['Comment vous contacter ?', 'Utilisez la page Contact ou écrivez-nous directement par email ou WhatsApp, les liens se trouvent en bas de page.'],
        ['Puis-je proposer une chorégraphie ?', 'Oui. Écrivez-nous via la page Contact en décrivant votre idée, nous reviendrons vers vous.'],
    ];

    $arrow = 'M3 10a.75.75 0 01.75-.75h10.69L10.47 5.28a.75.75 0 011.06-1.06l5.25 5.25a.75.75 0 010 1.06l-5.25 5.25a.75.75 0 11-1.06-1.06l3.97-3.97H3.75A.75.75 0 013 10z';
@endphp

{{-- HERO --}}
<section class="relative isolate flex min-h-[90vh] items-center overflow-hidden px-4 sm:px-6 lg:px-8" aria-labelledby="hero-titre">
    {{-- Rayons dorés qui tournent lentement--}}
    <div class="pointer-events-none absolute left-1/2 top-1/2 -z-10 size-[140vmax] -translate-x-1/2 -translate-y-1/2 animate-spin-slow
                bg-[conic-gradient(from_0deg,transparent_0deg,rgba(212,160,23,.14)_14deg,transparent_28deg,transparent_60deg,rgba(212,160,23,.10)_74deg,transparent_88deg,transparent_120deg,rgba(212,160,23,.14)_134deg,transparent_148deg,transparent_180deg,rgba(212,160,23,.10)_194deg,transparent_208deg,transparent_240deg,rgba(212,160,23,.14)_254deg,transparent_268deg,transparent_300deg,rgba(212,160,23,.10)_314deg,transparent_328deg)]
                [mask-image:radial-gradient(circle,black_8%,transparent_55%)]"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_center,transparent_30%,#0c0a06_80%)]"></div>

    <div class="mx-auto w-full max-w-5xl text-center">
        <p class="animate-rise text-sm text-gold-300/80" style="animation-delay:.1s">
            Danse, prière et communauté à Madagascar
        </p>

        <h1 id="hero-titre" class="mt-6 font-display text-6xl font-semibold leading-[0.95] sm:text-8xl lg:text-9xl">
            <span class="block animate-rise text-stone-100" style="animation-delay:.25s">Iraka</span>
            <span class="block animate-rise bg-linear-to-r from-gold-600 via-gold-300 to-gold-500 bg-clip-text text-transparent" style="animation-delay:.45s">Voafidy</span>
        </h1>

        <p class="mx-auto mt-8 max-w-xl animate-rise text-lg leading-relaxed text-stone-300" style="animation-delay:.7s">
            Choisis pour être envoyés : nous dansons pour louer, nous prions pour porter,
            et nous nous rassemblons pour partager.
        </p>

        <div class="mt-10 flex animate-rise flex-col items-center justify-center gap-4 sm:flex-row" style="animation-delay:.9s">
            <a href="{{ $to('choreographies') }}" wire:navigate
               class="w-full rounded-full bg-gold-500 px-8 py-3.5 text-base font-semibold text-night-950 transition duration-300
                      hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-xl hover:shadow-gold-500/30 sm:w-auto">
                Voir les chorégraphies
            </a>
            <a href="{{ $to('events') }}" wire:navigate
               class="w-full rounded-full border border-gold-500 px-8 py-3.5 text-base font-medium text-gold-500 transition duration-300 hover:bg-gold-500/10 sm:w-auto">
                Prochains événements
            </a>
        </div>
    </div>

    <a href="#mots" aria-label="Descendre"
       class="absolute bottom-6 left-1/2 hidden -translate-x-1/2 animate-float text-gold-500 sm:block">
        <svg class="size-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.2 7.7a.75.75 0 011.06 0L10 11.44l3.74-3.74a.75.75 0 111.06 1.06l-4.27 4.27a.75.75 0 01-1.06 0L5.2 8.76a.75.75 0 010-1.06z" clip-rule="evenodd"/>
        </svg>
    </a>
</section>

{{-- BANDEAU DÉFILANT --}}
<section id="mots" class="overflow-hidden border-y border-gold-500/20 bg-night-900/60 py-5" aria-label="Nos valeurs">
    <ul class="flex w-max animate-marquee gap-12 whitespace-nowrap font-display text-3xl text-gold-500/80 hover:[animation-play-state:paused]">
        @foreach (array_merge($words, $words) as $i => $w)
            <li class="flex items-center gap-12" @if($i >= count($words)) aria-hidden="true" @endif>
                {{ $w }}
                <span class="size-1.5 rotate-45 bg-gold-500"></span>
            </li>
        @endforeach
    </ul>
</section>

{{-- CHIFFRES --}}
<section aria-label="Chiffres clés">
    <dl class="mx-auto grid max-w-7xl grid-cols-2 gap-y-8 px-4 py-14 sm:px-6 lg:grid-cols-4 lg:px-8">
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

{{-- À PROPOS --}}
<section class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8" x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <div class="grid items-center gap-14 lg:grid-cols-2">
        <div class="transition duration-1000 ease-out" x-bind:class="shown ? 'translate-x-0 opacity-100' : '-translate-x-10 opacity-0'">
            <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Une mission, une famille</h2>
            <p class="mt-6 max-w-prose leading-relaxed text-stone-300">
                Iraka Voafidy rassemble des danseurs, des musiciens et des priants autour d'une même
                conviction : le corps et la voix peuvent porter une prière. Chaque chorégraphie est
                pensée avec la communauté et partagée avec elle.
            </p>
            <p class="mt-4 max-w-prose leading-relaxed text-stone-400">
                Que vous veniez pour danser, apprendre ou simplement prier avec nous, il y a une place pour vous.
            </p>
            <a href="{{ $to('about') }}" wire:navigate class="group mt-8 inline-flex items-center gap-2 font-medium text-gold-500">
                <span class="border-b border-gold-500/0 transition group-hover:border-gold-500">Découvrir notre histoire</span>
                <svg class="size-4 transition duration-300 group-hover:translate-x-1.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrow }}" clip-rule="evenodd"/></svg>
            </a>
        </div>

        <ul class="space-y-4 transition delay-200 duration-1000 ease-out" x-bind:class="shown ? 'translate-x-0 opacity-100' : 'translate-x-10 opacity-0'">
            @foreach ([
                ['Danser', 'Des chorégraphies ouvertes à tous les niveaux.'],
                ['Prier', 'Des temps de prière réguliers, en groupe ou à distance.'],
                ['Partager', 'Des événements pour se retrouver et s’encourager.'],
            ] as [$t, $d])
                <li class="group flex gap-5 rounded-2xl border border-gold-500/20 bg-night-900/50 p-6 transition duration-500 hover:-translate-y-1 hover:border-gold-500/60 hover:bg-gold-500/5">
                    <span class="mt-1 block h-10 w-px shrink-0 bg-gold-500 transition-all duration-500 group-hover:h-14"></span>
                    <div>
                        <h3 class="font-display text-2xl text-gold-500">{{ $t }}</h3>
                        <p class="mt-1 text-sm text-stone-400">{{ $d }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- CHORÉGRAPHIES --}}
<section class="bg-night-900/40 py-24" x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Nos chorégraphies</h2>
            <a href="{{ $to('choreographies') }}" wire:navigate class="text-sm font-medium text-gold-500 hover:underline">Tout voir</a>
        </div>

        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach ($choreographies as $i => $c)
                <article class="group relative flex min-h-72 flex-col justify-end overflow-hidden rounded-3xl border border-gold-500/20
                                bg-linear-to-br from-gold-900/40 via-night-900 to-night-950 p-7 transition duration-700 ease-out
                                hover:-translate-y-2 hover:border-gold-500/70 hover:shadow-2xl hover:shadow-gold-500/10"
                         x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'"
                         style="transition-delay: {{ $i * 150 }}ms">
                    <div class="absolute -right-10 -top-10 size-40 rounded-full bg-gold-500/15 transition duration-700 group-hover:scale-[2.2] group-hover:bg-gold-500/10"></div>
                    <span class="relative mb-3 w-fit rounded-full border border-gold-500/50 px-3 py-1 text-xs text-gold-300">{{ $c['tag'] }}</span>
                    <h3 class="relative font-display text-3xl text-stone-100">{{ $c['title'] }}</h3>
                    <p class="relative mt-2 text-sm leading-relaxed text-stone-400">{{ $c['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- COMMENT REJOINDRE --}}
<section class="mx-auto max-w-5xl px-4 py-24 sm:px-6 lg:px-8" x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Nous rejoindre en trois étapes</h2>

    <ol class="relative mt-14 space-y-10 border-l border-gold-500/30 pl-8">
        <span class="absolute -left-px top-0 w-px origin-top bg-gold-500 transition-transform duration-[1800ms] ease-out"
              x-bind:class="shown ? 'scale-y-100' : 'scale-y-0'" style="height:100%"></span>

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

{{-- ÉVÉNEMENTS --}}
<section class="border-y border-gold-500/20 bg-night-900/40 py-24" x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Prochains événements</h2>

        <ol class="mt-12 divide-y divide-gold-500/20 border-y border-gold-500/20">
            @foreach ($events as $i => $e)
                <li class="transition duration-700 ease-out" x-bind:class="shown ? 'translate-x-0 opacity-100' : '-translate-x-8 opacity-0'" style="transition-delay: {{ $i * 150 }}ms">
                    <a href="{{ $to('events') }}" wire:navigate class="group flex items-center gap-5 py-6 transition hover:bg-gold-500/5 sm:gap-8 sm:px-4">
                        <time datetime="{{ $e['date']->toDateString() }}" class="w-16 shrink-0 text-center">
                            <span class="block font-display text-4xl leading-none text-gold-500">{{ $e['date']->format('d') }}</span>
                            <span class="block text-sm text-stone-400">{{ $e['date']->locale('fr')->translatedFormat('M') }}</span>
                        </time>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-lg font-medium text-stone-100 transition group-hover:text-gold-500">{{ $e['title'] }}</h3>
                            <p class="text-sm text-stone-400">{{ $e['place'] }}</p>
                        </div>
                        <svg class="size-5 shrink-0 text-gold-500 transition duration-300 group-hover:translate-x-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrow }}" clip-rule="evenodd"/></svg>
                    </a>
                </li>
            @endforeach
        </ol>

        <div class="mt-8 text-center">
            <a href="{{ $to('events') }}" wire:navigate class="inline-block rounded-full border border-gold-500 px-6 py-3 text-sm font-medium text-gold-500 transition hover:bg-gold-500 hover:text-night-950">
                Tous les événements
            </a>
        </div>
    </div>
</section>

{{-- PRIÈRE --}}
<section class="relative overflow-hidden py-24" x-data="{ shown: false, active: 0, texts: @js(array_column($prayers, 'text')) }" x-intersect.once.margin.-100px="shown = true">
    <div class="pointer-events-none absolute left-1/2 top-0 size-96 -translate-x-1/2 rounded-full bg-gold-500/10 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
        <div class="transition duration-1000 ease-out" x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'">
            <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Prier ensemble</h2>
            <blockquote class="mt-6 border-l-2 border-gold-500 pl-5 font-display text-2xl italic leading-snug text-stone-200">
                Ce n’est pas vous qui m’avez choisi, c’est moi qui vous ai choisis.
                <footer class="mt-2 font-sans text-sm not-italic text-stone-400">Jean 15, 16</footer>
            </blockquote>
            <p class="mt-6 min-h-12 text-stone-300" x-text="texts[active]"></p>
            <a href="{{ $to('prayer') }}" wire:navigate class="mt-6 inline-block rounded-full border border-gold-500 px-6 py-3 text-sm font-medium text-gold-500 transition hover:bg-gold-500 hover:text-night-950">
                Rejoindre la prière
            </a>
        </div>

        <ul class="space-y-3 transition delay-200 duration-1000 ease-out" x-bind:class="shown ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'">
            @foreach ($prayers as $i => $p)
                <li>
                    <button type="button" x-on:click="active = {{ $i }}"
                            class="flex w-full items-center justify-between rounded-2xl border px-6 py-5 text-left transition duration-300"
                            x-bind:class="active === {{ $i }} ? 'border-gold-500 bg-gold-500/10 text-gold-500' : 'border-gold-500/20 text-stone-300 hover:border-gold-500/50'">
                        <span>{{ $p['label'] }}</span>
                        <span class="font-display text-3xl">{{ $p['time'] }}</span>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- TÉMOIGNAGES --}}
<section class="border-y border-gold-500/20 bg-night-900/60 py-24"
         x-data="{ i: 0, count: {{ count($testimonials) }}, paused: false, timer: null }"
         x-init="timer = setInterval(() => { if (!paused) i = (i + 1) % count }, 6000)"
         x-on:livewire:navigating.window="clearInterval(timer)"
         x-on:mouseenter="paused = true" x-on:mouseleave="paused = false">
    <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
        <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Ils en parlent</h2>

        <div class="relative mt-12 min-h-56 sm:min-h-44">
            @foreach ($testimonials as $k => [$quote, $who])
                <figure x-show="i === {{ $k }}" x-cloak
                        x-transition:enter="transition duration-700 ease-out" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition duration-300 ease-in absolute inset-x-0" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="absolute inset-x-0 top-0">
                    <blockquote class="font-display text-2xl leading-snug text-stone-100 sm:text-3xl">{{ $quote }}</blockquote>
                    <figcaption class="mt-4 text-sm text-gold-500">{{ $who }}</figcaption>
                </figure>
            @endforeach
        </div>

        <div class="mt-8 flex justify-center gap-2" role="tablist" aria-label="Choisir un témoignage">
            @foreach ($testimonials as $k => $t)
                <button type="button" role="tab" x-on:click="i = {{ $k }}" aria-label="Témoignage {{ $k + 1 }}"
                        class="h-1.5 rounded-full transition-all duration-500"
                        x-bind:class="i === {{ $k }} ? 'w-8 bg-gold-500' : 'w-3 bg-gold-500/30 hover:bg-gold-500/60'"></button>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="mx-auto max-w-3xl px-4 py-24 sm:px-6 lg:px-8" x-data="{ open: 0 }">
    <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Questions fréquentes</h2>

    <div class="mt-10 divide-y divide-gold-500/20 border-y border-gold-500/20">
        @foreach ($faqs as $k => [$q, $a])
            <div>
                <button type="button" x-on:click="open = open === {{ $k }} ? null : {{ $k }}"
                        x-bind:aria-expanded="open === {{ $k }}"
                        class="flex w-full items-center justify-between gap-4 py-5 text-left text-lg text-stone-100 transition hover:text-gold-500">
                    <span>{{ $q }}</span>
                    <svg class="size-5 shrink-0 text-gold-500 transition-transform duration-300" x-bind:class="open === {{ $k }} && 'rotate-45'"
                         viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/>
                    </svg>
                </button>
                <div x-show="open === {{ $k }}" x-collapse x-cloak>
                    <p class="pb-5 leading-relaxed text-stone-400">{{ $a }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- APPEL À L'ACTION --}}
<section class="relative overflow-hidden border-t border-gold-500/20 bg-night-900/60" x-data="{ shown: false }" x-intersect.once.margin.-100px="shown = true">
    <div class="pointer-events-none absolute left-1/2 top-1/2 size-[32rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gold-500/10 blur-3xl"></div>
    <div class="relative mx-auto max-w-4xl px-4 py-24 text-center sm:px-6 lg:px-8">
        <div class="transition duration-1000 ease-out" x-bind:class="shown ? 'scale-100 opacity-100' : 'scale-95 opacity-0'">
            @guest
                <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Rejoignez Iraka Voafidy</h2>
                <p class="mx-auto mt-4 max-w-lg text-stone-400">Créez votre compte pour suivre les événements et accéder aux chorégraphies.</p>
                <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ $to('register') }}" wire:navigate class="w-full rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400 sm:w-auto">Register</a>
                    <a href="{{ $to('login') }}" wire:navigate class="w-full rounded-full border border-gold-500 px-8 py-3.5 font-medium text-gold-500 transition hover:bg-gold-500/10 sm:w-auto">Login</a>
                </div>
            @endguest

            @auth
                <h2 class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Bon retour, {{ $user->name }}</h2>
                <p class="mx-auto mt-4 max-w-lg text-stone-400">
                    {{ $isAdmin ? 'Gérez les chorégraphies, les événements et les membres.' : 'Retrouvez vos événements et vos chorégraphies.' }}
                </p>
                <a href="{{ $isAdmin ? $to('admin.dashboard') : $to('dashboard') }}" wire:navigate
                   class="mt-8 inline-block rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400">
                    {{ $isAdmin ? 'Ouvrir le tableau de bord admin' : 'Aller à mon espace' }}
                </a>
            @endauth
        </div>
    </div>
</section>
</div>