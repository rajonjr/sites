{{-- <x-layouts::guest>
    <div class="w-full flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">
            <h2 class="text-2xl font-bold text-white mb-6 text-center">Connexion</h2>

            <form method="post" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <x-field name="email" label="Email" type="email" required autofocus autocomplete="username" />
                <x-field name="password" label="Mot de passe" type="password" required
                    autocomplete="current-password" />

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-slate-400">
                        <input type="checkbox" wire:model="remember"
                            class="rounded bg-slate-950 border-slate-800 text-brand-600 mr-2 focus:ring-0"> Se souvenir
                        de
                        moi
                    </label>
                    <a href="{{ route('password.request')  }}" class="text-gold-400 hover:underline">Mot de passe oublié
                        ?</a>
                </div>

                <x-btn>Se connecter</x-btn>
            </form>

            <!-- Lien vers l'inscription -->
            <p class="text-center text-xs text-slate-400 mt-6">
                Pas encore de compte ?
                <a href="{{ route('register') }}" class="text-brand-400 font-semibold hover:underline ml-1">Créer un
                    compte</a>
            </p>
        </div>
    </div>
</x-layouts::guest> --}}

<x-layouts::guest>
    @php
    $to = fn (string $name) => Route::has($name) ? route($name) : '#';

    $username = config('fortify.username', 'email');

    $action = Route::has('login.store') ? route('login.store') : route('login');

    $field = 'w-full rounded-2xl border bg-night-900 px-4 py-3.5 text-stone-100 placeholder:text-stone-500 transition focus:outline-none focus:ring-2';
    $ok    = 'border-gold-500/30 focus:border-gold-500 focus:ring-gold-500/30';
    $bad   = 'border-rose-400/70 focus:border-rose-400 focus:ring-rose-400/30';

    $hasError = $errors->has($username) || $errors->has('password');
@endphp

<section class="relative isolate mx-auto grid min-h-[calc(100vh-6rem)] max-w-6xl items-center gap-12 px-4 pb-16 pt-6 sm:px-6 lg:grid-cols-2 lg:px-8" aria-labelledby="login-titre">

    {{-- Halo d'arrière-plan --}}
    <div class="pointer-events-none absolute left-1/2 top-1/2 -z-10 size-[44rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gold-500/10 blur-3xl"></div>

    {{-- PANNEAU DÉCORATIF --}}
    <aside class="relative hidden overflow-hidden rounded-3xl border border-gold-500/20 bg-linear-to-br from-gold-900/40 via-night-900 to-night-950 p-12 lg:block">
        <div class="pointer-events-none absolute left-1/2 top-1/2 size-[60rem] -translate-x-1/2 -translate-y-1/2 animate-spin-slow
                    bg-[conic-gradient(from_0deg,transparent_0deg,rgba(212,160,23,.16)_14deg,transparent_28deg,transparent_90deg,rgba(212,160,23,.10)_104deg,transparent_118deg,transparent_180deg,rgba(212,160,23,.16)_194deg,transparent_208deg,transparent_270deg,rgba(212,160,23,.10)_284deg,transparent_298deg)]
                    [mask-image:radial-gradient(circle,black_5%,transparent_55%)]"></div>

        <div class="relative">
            <p class="font-display text-5xl text-gold-500">Iraka Voafidy</p>
            <p class="mt-6 max-w-sm leading-relaxed text-stone-300">
                Retrouvez vos chorégraphies favorites, vos prières gardées et les prochains événements de la communauté.
            </p>

            <ul class="mt-10 space-y-4 text-sm text-stone-300">
                @foreach (['Garder vos chorégraphies et prières favorites', 'Suivre les événements et vous y inscrire', 'Confier vos intentions de prière'] as $point)
                    <li class="flex items-start gap-3">
                        <svg class="mt-0.5 size-5 shrink-0 text-gold-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        {{ $point }}
                    </li>
                @endforeach
            </ul>

            <blockquote class="mt-14 border-l-2 border-gold-500 pl-5 font-display text-xl italic text-stone-200">
                Venez à moi, vous tous qui êtes fatigués et chargés, et je vous donnerai du repos.
                <footer class="mt-2 font-sans text-sm not-italic text-stone-500">Matthieu 11, 28 · Louis Segond 1910</footer>
            </blockquote>
        </div>
    </aside>

    {{-- FORMULAIRE --}}
    <div class="mx-auto w-full max-w-md animate-rise">
        <div class="rounded-3xl border border-gold-500/25 bg-night-900/70 p-7 shadow-2xl shadow-black/40 backdrop-blur-xl sm:p-10">
            <h1 id="login-titre" class="font-display text-4xl font-semibold text-gold-500">Connexion</h1>
            <p class="mt-2 text-sm text-stone-400">Heureux de vous revoir.</p>

            {{-- Message de statut --}}
            @if (session('status'))
                <p class="mt-5 rounded-2xl border border-gold-500/40 bg-gold-500/10 px-4 py-3 text-sm text-gold-300" role="status">{{ session('status') }}</p>
            @endif

            {{-- Erreur globale --}}
            @if ($hasError)
                <div class="mt-5 animate-rise rounded-2xl border border-rose-400/50 bg-rose-500/10 px-4 py-3 text-sm text-rose-200" role="alert">
                    {{ $errors->first($username) ?: $errors->first('password') }}
                </div>
            @endif

            <form method="POST" action="{{ $action }}" novalidate class="mt-7 space-y-5"
                  x-data="{ show: false, caps: false, sending: false, email: @js(old($username, '')), password: '', err: '' }"
                  x-on:submit="
                      err = '';
                      if (!email.trim() || !password) { $event.preventDefault(); err = 'Saisissez votre identifiant et votre mot de passe.'; return; }
                      sending = true;
                  ">
                @csrf

                <p x-show="err" x-cloak x-text="err" class="text-sm text-rose-300" role="alert" aria-live="assertive"></p>

                {{-- Email --}}
                <div>
                    <label for="login-email" class="mb-1.5 block text-sm text-stone-300">{{ $username === 'email' ? 'Adresse email' : 'Identifiant' }}</label>
                    <input id="login-email" name="{{ $username }}" type="{{ $username === 'email' ? 'email' : 'text' }}" required autocomplete="username" @if ($username === 'email') inputmode="email" @endif x-model="email"
                           @if (! $hasError) autofocus @endif
                           @error($username) aria-invalid="true" @enderror
                           class="{{ $field }} {{ $errors->has($username) ? $bad : $ok }}" placeholder="{{ $username === 'email' ? 'vous@exemple.com' : '' }}">
                </div>

                {{-- Mot de passe --}}
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label for="login-password" class="text-sm text-stone-300">Mot de passe</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-gold-500 transition hover:text-gold-300 hover:underline">Mot de passe oublié ?</a>
                        @endif
                    </div>

                    <div class="relative">
                        <input id="login-password" name="password" required autocomplete="current-password" x-model="password"
                               x-bind:type="show ? 'text' : 'password'"
                               x-on:keyup="caps = $event.getModifierState && $event.getModifierState('CapsLock')"
                               x-on:keydown="caps = $event.getModifierState && $event.getModifierState('CapsLock')"
                               x-on:blur="caps = false"
                               @if ($hasError) autofocus @endif
                               @error('password') aria-invalid="true" @enderror
                               class="{{ $field }} pr-14 {{ $errors->has('password') ? $bad : $ok }}" placeholder="Votre mot de passe">

                        <button type="button" x-on:click="show = !show" x-bind:aria-pressed="show"
                                x-bind:aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                                class="absolute right-2 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full text-gold-500 transition hover:bg-gold-500/10">
                            <svg x-show="!show" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="show" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18M10.6 6.1A10 10 0 0112 6c6.4 0 10 6 10 6a17 17 0 01-3.2 3.9M6.6 6.7A17 17 0 002 12s3.6 7 10 7a10 10 0 004.2-.9M9.9 9.9a3 3 0 004.2 4.2"/></svg>
                        </button>
                    </div>

                    <p x-show="caps" x-cloak x-transition class="mt-2 text-xs text-gold-300" role="status">Les majuscules sont activées.</p>
                </div>

                {{-- Se souvenir de moi --}}
                <label class="flex cursor-pointer items-center gap-3 text-sm text-stone-300">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-4 rounded border-gold-500/50 bg-night-900 accent-gold-500">
                    Rester connecté sur cet appareil
                </label>

                {{-- Bouton --}}
                <button type="submit" x-bind:disabled="sending"
                        class="relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition duration-300
                               hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30 disabled:cursor-wait disabled:opacity-70
                               before:absolute before:inset-0 before:-translate-x-full before:skew-x-12 before:bg-white/30 before:transition-transform before:duration-700 hover:before:translate-x-full">
                    <svg x-show="sending" x-cloak class="relative size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    <span class="relative" x-text="sending ? 'Connexion…' : 'Se connecter'"></span>
                </button>
            </form>

            {{-- Inscription --}}
            @if (Route::has('register'))
                <p class="mt-8 text-center text-sm text-stone-400">
                    Pas encore de compte ?
                    <a href="{{ route('register') }}" wire:navigate class="font-medium text-gold-500 transition hover:text-gold-300 hover:underline">Register</a>
                </p>
            @endif
        </div>

        <p class="mt-6 text-center text-xs text-stone-500">
            Besoin d’aide ? <a href="{{ $to('contact') }}" wire:navigate class="text-gold-500/80 hover:text-gold-500 hover:underline">Contactez-nous</a>
        </p>
    </div>
</section>
</x-layouts::guest>

