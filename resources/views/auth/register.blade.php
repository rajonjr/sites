{{-- <x-layouts::guest>
    <div class="w-full flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
            <h2 class="text-2xl font-bold text-white text-center">Créer un compte</h2>

            <form method="post" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <x-field name="name" label="Nom" required autofocus autocomplete="name" />
                <x-field name="email" label="Email" type="email" required autocomplete="username" />
                <x-field name="password" label="Mot de passe" type="password" required autocomplete="new-password" />
                <x-field name="password_confirmation" label="Confirmer le mot de passe" type="password" required
                    autocomplete="new-password" />
                <x-btn>S'inscrire</x-btn>
            </form>

            <!-- Lien vers la connexion -->
            <p class="text-center text-xs text-slate-400 mt-6">
                Déjà un compte ?
                <a href="{{ route('login') }}" class="text-brand-400 font-semibold hover:underline ml-1">Se
                    connecter</a>
            </p>
        </div>
    </div>
</x-layouts::guest> --}}

<x-layouts::guest>
    @php
    $to = fn (string $name) => Route::has($name) ? route($name) : '#';

    $action = Route::has('register.store') ? route('register.store') : route('register');

    $field = 'w-full rounded-2xl border bg-night-900 px-4 py-3.5 text-stone-100 placeholder:text-stone-500 transition focus:outline-none focus:ring-2';
    $ok    = 'border-gold-500/30 focus:border-gold-500 focus:ring-gold-500/30';
    $bad   = 'border-rose-400/70 focus:border-rose-400 focus:ring-rose-400/30';

    $eye    = 'M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z';
    $eyeOff = 'M3 3l18 18M10.6 6.1A10 10 0 0112 6c6.4 0 10 6 10 6a17 17 0 01-3.2 3.9M6.6 6.7A17 17 0 002 12s3.6 7 10 7a10 10 0 004.2-.9M9.9 9.9a3 3 0 004.2 4.2';
@endphp

<section class="relative isolate mx-auto grid min-h-[calc(100vh-6rem)] max-w-6xl items-center gap-12 px-4 pb-16 pt-6 sm:px-6 lg:grid-cols-2 lg:px-8" aria-labelledby="register-titre">

    <div class="pointer-events-none absolute left-1/2 top-1/2 -z-10 size-[44rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gold-500/10 blur-3xl"></div>

    {{-- PANNEAU DÉCORATIF --}}
    <aside class="relative hidden overflow-hidden rounded-3xl border border-gold-500/20 bg-linear-to-br from-gold-900/40 via-night-900 to-night-950 p-12 lg:block">
        <div class="pointer-events-none absolute left-1/2 top-1/2 size-[60rem] -translate-x-1/2 -translate-y-1/2 animate-spin-slow
                    bg-[conic-gradient(from_0deg,transparent_0deg,rgba(212,160,23,.16)_14deg,transparent_28deg,transparent_90deg,rgba(212,160,23,.10)_104deg,transparent_118deg,transparent_180deg,rgba(212,160,23,.16)_194deg,transparent_208deg,transparent_270deg,rgba(212,160,23,.10)_284deg,transparent_298deg)]
                    [mask-image:radial-gradient(circle,black_5%,transparent_55%)]"></div>

        <div class="relative">
            <p class="font-display text-5xl text-gold-500">Iraka Voafidy</p>
            <p class="mt-6 max-w-sm leading-relaxed text-stone-300">
                Rejoignez une communauté qui danse, prie et se rassemble. Votre compte est gratuit et se crée en une minute.
            </p>

            <ol class="mt-10 space-y-6">
                @foreach ([
                    ['Créez votre compte', 'Votre nom, votre email et un mot de passe.'],
                    ['Explorez', 'Chorégraphies, prières et événements de la communauté.'],
                    ['Participez', 'Gardez vos favoris et rejoignez les prochaines rencontres.'],
                ] as $i => [$t, $d])
                    <li class="flex gap-4">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full border border-gold-500 font-display text-lg text-gold-500">{{ $i + 1 }}</span>
                        <div>
                            <p class="font-medium text-stone-100">{{ $t }}</p>
                            <p class="text-sm text-stone-400">{{ $d }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            <blockquote class="mt-12 border-l-2 border-gold-500 pl-5 font-display text-xl italic text-stone-200">
                Ce n’est pas vous qui m’avez choisi ; mais moi, je vous ai choisis.
                <footer class="mt-2 font-sans text-sm not-italic text-stone-500">Jean 15, 16 · Louis Segond 1910</footer>
            </blockquote>
        </div>
    </aside>

    {{-- FORMULAIRE --}}
    <div class="mx-auto w-full max-w-md animate-rise">
        <div class="rounded-3xl border border-gold-500/25 bg-night-900/70 p-7 shadow-2xl shadow-black/40 backdrop-blur-xl sm:p-10">
            <h1 id="register-titre" class="font-display text-4xl font-semibold text-gold-500">Créer un compte</h1>
            <p class="mt-2 text-sm text-stone-400">Bienvenue parmi nous.</p>

            @if ($errors->any())
                <div class="mt-5 animate-rise rounded-2xl border border-rose-400/50 bg-rose-500/10 px-4 py-3 text-sm text-rose-200" role="alert">
                    {{ $errors->has('website') ? 'Votre inscription n’a pas pu être validée.' : 'Certains champs demandent votre attention.' }}
                </div>
            @endif

            <form method="POST" action="{{ $action }}" novalidate class="mt-7 space-y-5"
                  x-data="{
                      name: @js(old('name', '')),
                      email: @js(old('email', '')),
                      pw: '', pw2: '',
                      show: false, show2: false, terms: @js((bool) old('terms')),
                      sending: false, err: '',

                      get hasLen() { return this.pw.length >= 8; },
                      get hasCase() { return /[a-z]/.test(this.pw) && /[A-Z]/.test(this.pw); },
                      get hasNum() { return /[0-9]/.test(this.pw); },
                      get hasSym() { return /[^A-Za-z0-9]/.test(this.pw); },
                      get valid() { return this.hasLen && this.hasCase && this.hasNum; },
                      get score() {
                          if (!this.pw) return 0;
                          let s = [this.hasLen, this.hasCase, this.hasNum, this.hasSym].filter(Boolean).length;
                          if (s === 4 && this.pw.length >= 12) s = 5;
                          return s;
                      },
                      get strengthLabel() { return ['', 'Très faible', 'Faible', 'Moyen', 'Bon', 'Excellent'][this.score]; },
                      get strengthColor() { return ['', 'bg-rose-500', 'bg-orange-400', 'bg-yellow-400', 'bg-lime-400', 'bg-emerald-400'][this.score]; },
                      get match() { return this.pw2.length > 0 && this.pw === this.pw2; },

                      check(e) {
                          this.err = '';
                          if (!this.name.trim() || !this.email.trim()) this.err = 'Indiquez votre nom et votre email.';
                          else if (!this.valid) this.err = 'Votre mot de passe ne respecte pas encore les règles.';
                          else if (this.pw !== this.pw2) this.err = 'Les deux mots de passe ne sont pas identiques.';
                          else if (!this.terms) this.err = 'Veuillez accepter les conditions pour continuer.';
                          if (this.err) { e.preventDefault(); return; }
                          this.sending = true;
                      }
                  }"
                  x-on:submit="check($event)">
                @csrf

                <div class="absolute -left-[9999px]" aria-hidden="true">
                    <label>Ne pas remplir<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <p x-show="err" x-cloak x-text="err" class="text-sm text-rose-300" role="alert" aria-live="assertive"></p>

                {{-- Nom --}}
                <div>
                    <label for="reg-name" class="mb-1.5 block text-sm text-stone-300">Nom complet</label>
                    <input id="reg-name" name="name" type="text" required maxlength="255" autocomplete="name" autofocus x-model="name"
                           @error('name') aria-invalid="true" aria-describedby="e-name" @enderror
                           class="{{ $field }} {{ $errors->has('name') ? $bad : $ok }}">
                    @error('name') <p id="e-name" class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="reg-email" class="mb-1.5 block text-sm text-stone-300">Adresse email</label>
                    <input id="reg-email" name="email" type="email" required maxlength="255" autocomplete="email" inputmode="email" x-model="email"
                           placeholder="vous@exemple.com"
                           @error('email') aria-invalid="true" aria-describedby="e-email" @enderror
                           class="{{ $field }} {{ $errors->has('email') ? $bad : $ok }}">
                    @error('email') <p id="e-email" class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>

                {{-- Mot de passe --}}
                <div>
                    <label for="reg-password" class="mb-1.5 block text-sm text-stone-300">Mot de passe</label>
                    <div class="relative">
                        <input id="reg-password" name="password" required autocomplete="new-password" x-model="pw" x-bind:type="show ? 'text' : 'password'"
                               aria-describedby="pw-rules"
                               @error('password') aria-invalid="true" @enderror
                               class="{{ $field }} pr-14 {{ $errors->has('password') ? $bad : $ok }}">
                        <button type="button" x-on:click="show = !show" x-bind:aria-pressed="show"
                                x-bind:aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                                class="absolute right-2 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full text-gold-500 transition hover:bg-gold-500/10">
                            <svg x-show="!show" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $eye }}"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="show" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $eyeOff }}"/></svg>
                        </button>
                    </div>
                    @error('password') <p class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror

                    {{-- Force + règles --}}
                    <div x-show="pw.length" x-cloak x-transition class="mt-3">
                        <div class="flex gap-1.5" aria-hidden="true">
                            <template x-for="n in 5" :key="n">
                                <span class="h-1.5 flex-1 rounded-full bg-gold-500/15 transition-colors duration-500" :class="n <= score && strengthColor"></span>
                            </template>
                        </div>
                        <p class="mt-1.5 text-xs text-stone-400" aria-live="polite">Force : <span class="text-stone-200" x-text="strengthLabel"></span></p>
                    </div>

                    <ul id="pw-rules" class="mt-3 space-y-1.5 text-xs">
                        <template x-for="[ok, label] in [[hasLen, '8 caractères minimum'], [hasCase, 'Une majuscule et une minuscule'], [hasNum, 'Un chiffre']]" :key="label">
                            <li class="flex items-center gap-2 transition-colors duration-300" :class="ok ? 'text-emerald-300' : 'text-stone-500'">
                                <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path x-show="ok" fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/>
                                    <circle x-show="!ok" cx="10" cy="10" r="3"/>
                                </svg>
                                <span x-text="label"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- Confirmation --}}
                <div>
                    <label for="reg-password2" class="mb-1.5 block text-sm text-stone-300">Confirmer le mot de passe</label>
                    <div class="relative">
                        <input id="reg-password2" name="password_confirmation" required autocomplete="new-password" x-model="pw2" x-bind:type="show2 ? 'text' : 'password'"
                               class="{{ $field }} pr-14 {{ $ok }}"
                               :class="pw2.length && (match ? 'border-emerald-400/70!' : 'border-rose-400/70!')">
                        <button type="button" x-on:click="show2 = !show2" x-bind:aria-pressed="show2"
                                x-bind:aria-label="show2 ? 'Masquer la confirmation' : 'Afficher la confirmation'"
                                class="absolute right-2 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full text-gold-500 transition hover:bg-gold-500/10">
                            <svg x-show="!show2" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $eye }}"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="show2" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $eyeOff }}"/></svg>
                        </button>
                    </div>
                    <p x-show="pw2.length" x-cloak class="mt-1.5 text-xs" :class="match ? 'text-emerald-300' : 'text-rose-300'" x-text="match ? 'Les mots de passe correspondent.' : 'Les mots de passe ne correspondent pas.'" aria-live="polite"></p>
                </div>

                {{-- Conditions --}}
                <div>
                    <label class="flex cursor-pointer items-start gap-3 text-sm text-stone-300">
                        <input type="checkbox" name="terms" value="1" x-model="terms" class="mt-1 size-4 rounded border-gold-500/50 bg-night-900 accent-gold-500">
                        <span>
                            J’accepte les
                            @if (Route::has('terms'))
                                <a href="{{ route('terms') }}" target="_blank" class="text-gold-500 underline hover:text-gold-300">conditions d’utilisation</a>
                            @else
                                conditions d’utilisation
                            @endif
                            @if (Route::has('privacy'))
                                et la <a href="{{ route('privacy') }}" target="_blank" class="text-gold-500 underline hover:text-gold-300">politique de confidentialité</a>
                            @endif.
                        </span>
                    </label>
                    @error('terms') <p class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>

                {{-- Bouton --}}
                <button type="submit" x-bind:disabled="sending"
                        class="relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition duration-300
                               hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30 disabled:cursor-wait disabled:opacity-70
                               before:absolute before:inset-0 before:-translate-x-full before:skew-x-12 before:bg-white/30 before:transition-transform before:duration-700 hover:before:translate-x-full">
                    <svg x-show="sending" x-cloak class="relative size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    <span class="relative" x-text="sending ? 'Création du compte…' : 'Créer mon compte'"></span>
                </button>
            </form>

            @if (Route::has('login'))
                <p class="mt-8 text-center text-sm text-stone-400">
                    Vous avez déjà un compte ?
                    <a href="{{ route('login') }}" wire:navigate class="font-medium text-gold-500 transition hover:text-gold-300 hover:underline">Login</a>
                </p>
            @endif
        </div>
    </div>
</section>
</x-layouts::guest>