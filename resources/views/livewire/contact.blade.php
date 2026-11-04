<div>
@php
    // ---------- Helpers ----------
    $to   = fn (string $name) => Route::has($name) ? route($name) : '#';
    $user = auth()->user();

    $contact = [
        'email'    => 'contact@irakavoafidy.mg',
        'phone'    => '+261 34 00 000 00',
        'whatsapp' => '261340000000',
        'address'  => 'Antananarivo, Madagascar',
        'lat'      => -18.8792,
        'lng'      => 47.5079,
        'hours'    => [
            ['Lundi – Vendredi', '08:00 – 17:00'],
            ['Samedi',           '09:00 – 12:00'],
            ['Dimanche',         'Sur rendez-vous'],
        ],
    ];

    $subjects = [
        'Question générale',
        'Rejoindre une chorégraphie',
        'Un événement',
        'Intention de prière',
        'Proposer une chorégraphie',
        'Partenariat',
        'Autre',
    ];

    $serverMode = Route::has('contact.store');

    $faqs = [
        ['Combien de temps pour recevoir une réponse ?', 'Nous répondons dès que possible. Pour une demande urgente, privilégiez WhatsApp ou le téléphone.'],
        ['Puis-je venir sans prévenir ?', 'Pour une première visite, écrivez-nous avant : nous vous indiquerons le lieu et l’horaire de la prochaine rencontre.'],
        ['Comment proposer une idée de chorégraphie ?', 'Choisissez le sujet « Proposer une chorégraphie » et décrivez votre idée en quelques lignes.'],
    ];

    $initial = [
        'name'    => old('name', $user->name ?? ''),
        'email'   => old('email', $user->email ?? ''),
        'phone'   => old('phone', ''),
        'subject' => old('subject', in_array(request('sujet'), $subjects, true) ? request('sujet') : $subjects[0]),
        'message' => old('message', ''),
    ];

    $field = 'w-full rounded-2xl border bg-night-900 px-4 py-3 text-stone-100 placeholder:text-stone-500 transition focus:outline-none focus:ring-2';
    $ok    = 'border-gold-500/30 focus:border-gold-500 focus:ring-gold-500/30';
    $bad   = 'border-rose-400/70 focus:border-rose-400 focus:ring-rose-400/30';
@endphp

{{-- EN-TÊTE --}}
<section class="relative isolate overflow-hidden px-4 pb-10 pt-12 text-center sm:px-6 lg:px-8" aria-labelledby="contact-titre">
    <div class="pointer-events-none absolute left-1/2 top-0 -z-10 size-[36rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gold-500/15 blur-3xl"></div>
    <p class="animate-rise text-sm text-gold-300/80">Nous sommes à l’écoute</p>
    <h1 id="contact-titre" class="mt-3 animate-rise font-display text-5xl font-semibold text-gold-500 sm:text-7xl" style="animation-delay:.15s">Contact</h1>
    <p class="mx-auto mt-5 max-w-2xl animate-rise text-stone-300" style="animation-delay:.3s">
        Une question, une idée, une intention de prière ? Écrivez-nous, nous vous répondrons avec plaisir.
    </p>
</section>

<section class="mx-auto max-w-6xl px-4 pb-20 sm:px-6 lg:px-8">
    <div class="grid gap-10 lg:grid-cols-5">

        {{-- FORMULAIRE --}}
        <div class="lg:col-span-3">
            @if (session('contact_sent'))
                <div class="rounded-3xl border border-gold-500/40 bg-gold-500/5 p-10 text-center" role="status">
                    <svg class="mx-auto size-16 text-gold-500" viewBox="0 0 52 52" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                        <circle cx="26" cy="26" r="23" class="opacity-30"/>
                        <path d="M15 27l8 8 14-16" stroke-linecap="round" stroke-linejoin="round" style="stroke-dasharray:40;stroke-dashoffset:40;animation:draw .8s .2s ease-out forwards"/>
                    </svg>
                    <h2 class="mt-5 font-display text-4xl text-gold-500">Message envoyé</h2>
                    <p class="mx-auto mt-3 max-w-md text-stone-300">Merci de nous avoir écrit. Nous reviendrons vers vous dès que possible.</p>
                    <a href="{{ $to('contact') }}" wire:navigate class="mt-6 inline-block rounded-full border border-gold-500 px-6 py-2.5 text-sm text-gold-500 transition hover:bg-gold-500/10">Envoyer un autre message</a>
                </div>
                <style>@keyframes draw { to { stroke-dashoffset: 0; } }</style>
            @else
                <form method="POST" action="{{ $serverMode ? route('contact.store') : '#' }}" novalidate
                      class="space-y-6 rounded-3xl border border-gold-500/20 bg-night-900/60 p-6 sm:p-10"
                      x-data="{
                          sending: false, max: 2000,
                          f: @js($initial),
                          serverMode: @js($serverMode),
                          mailto: @js($contact['email']),
                          get n() { return this.f.message.length; },
                          submit(e) {
                              if (!this.f.name.trim() || !this.f.email.trim() || !this.f.message.trim()) { e.preventDefault(); this.$refs.msg.textContent = 'Merci de remplir le nom, l’email et le message.'; return; }
                              if (!this.serverMode) {
                                  e.preventDefault();
                                  const body = this.f.message + '\n\n— ' + this.f.name + (this.f.phone ? ' (' + this.f.phone + ')' : '') + '\n' + this.f.email;
                                  window.location.href = 'mailto:' + this.mailto + '?subject=' + encodeURIComponent(this.f.subject) + '&body=' + encodeURIComponent(body);
                                  return;
                              }
                              this.sending = true;
                          }
                      }"
                      x-on:submit="submit($event)">
                    @csrf

                    {{-- Champ piège anti-spam : invisible pour les humains --}}
                    <div class="absolute -left-[9999px]" aria-hidden="true">
                        <label>Ne pas remplir<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <p x-ref="msg" class="text-sm text-rose-300" role="alert" aria-live="assertive"></p>

                    @if ($errors->any())
                        <div class="rounded-2xl border border-rose-400/50 bg-rose-500/10 p-4 text-sm text-rose-200" role="alert">
                            Le message n’a pas pu être envoyé. Vérifiez les champs signalés ci-dessous.
                        </div>
                    @endif

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="c-name" class="mb-1.5 block text-sm text-stone-300">Nom <span class="text-gold-500" aria-hidden="true">*</span></label>
                            <input id="c-name" name="name" type="text" required maxlength="100" autocomplete="name" x-model="f.name"
                                   @error('name') aria-invalid="true" aria-describedby="e-name" @enderror
                                   class="{{ $field }} {{ $errors->has('name') ? $bad : $ok }}">
                            @error('name') <p id="e-name" class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="c-email" class="mb-1.5 block text-sm text-stone-300">Email <span class="text-gold-500" aria-hidden="true">*</span></label>
                            <input id="c-email" name="email" type="email" required maxlength="150" autocomplete="email" x-model="f.email"
                                   @error('email') aria-invalid="true" aria-describedby="e-email" @enderror
                                   class="{{ $field }} {{ $errors->has('email') ? $bad : $ok }}">
                            @error('email') <p id="e-email" class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="c-phone" class="mb-1.5 block text-sm text-stone-300">Téléphone <span class="text-stone-500">(facultatif)</span></label>
                            <input id="c-phone" name="phone" type="tel" maxlength="30" autocomplete="tel" x-model="f.phone"
                                   class="{{ $field }} {{ $errors->has('phone') ? $bad : $ok }}">
                            @error('phone') <p class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="c-subject" class="mb-1.5 block text-sm text-stone-300">Sujet</label>
                            <select id="c-subject" name="subject" x-model="f.subject" class="{{ $field }} {{ $errors->has('subject') ? $bad : $ok }}">
                                @foreach ($subjects as $s)
                                    <option value="{{ $s }}">{{ $s }}</option>
                                @endforeach
                            </select>
                            @error('subject') <p class="mt-1 text-sm text-rose-300">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="c-message" class="mb-1.5 block text-sm text-stone-300">Message <span class="text-gold-500" aria-hidden="true">*</span></label>
                        <textarea id="c-message" name="message" rows="6" required maxlength="2000" x-model="f.message"
                                  @error('message') aria-invalid="true" aria-describedby="e-message" @enderror
                                  class="{{ $field }} {{ $errors->has('message') ? $bad : $ok }}"></textarea>
                        <div class="mt-1 flex justify-between text-xs">
                            @error('message') <p id="e-message" class="text-rose-300">{{ $message }}</p> @else <span></span> @enderror
                            <span :class="n > 1800 ? 'text-gold-300' : 'text-stone-500'"><span x-text="n"></span>/2000</span>
                        </div>
                    </div>

                    <label class="flex items-start gap-3 text-sm text-stone-300">
                        <input type="checkbox" name="consent" value="1" required @checked(old('consent')) class="mt-1 size-4 rounded border-gold-500/50 bg-night-900 accent-gold-500">
                        <span>J’accepte que mes informations soient utilisées uniquement pour répondre à ma demande.</span>
                    </label>
                    @error('consent') <p class="-mt-3 text-sm text-rose-300">{{ $message }}</p> @enderror

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <button type="submit" x-bind:disabled="sending"
                                class="relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-night-950 transition duration-300
                                       hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-lg hover:shadow-gold-500/30 disabled:cursor-wait disabled:opacity-70">
                            <svg x-show="sending" x-cloak class="size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                            <span x-text="sending ? 'Envoi en cours…' : 'Envoyer le message'"></span>
                        </button>
                        @unless ($serverMode)
                            <p class="text-xs text-stone-500">Votre application mail s’ouvrira avec le message prêt à envoyer.</p>
                        @endunless
                    </div>
                </form>
            @endif
        </div>

        {{-- COORDONNÉES --}}
        <aside class="space-y-4 lg:col-span-2" aria-label="Coordonnées">
            @php
                $cards = [
                    ['Email',     $contact['email'],   'mailto:'.$contact['email'],                 'M3 7l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z'],
                    ['Téléphone', $contact['phone'],   'tel:'.preg_replace('/\s+/', '', $contact['phone']), 'M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z'],
                    ['WhatsApp',  'Discuter maintenant', 'https://wa.me/'.$contact['whatsapp'],       'M21 12a9 9 0 01-13.4 7.8L3 21l1.3-4.4A9 9 0 1121 12z'],
                    ['Adresse',   $contact['address'], 'https://www.openstreetmap.org/?mlat='.$contact['lat'].'&mlon='.$contact['lng'].'#map=15/'.$contact['lat'].'/'.$contact['lng'], 'M12 21s7-6.1 7-11a7 7 0 10-14 0c0 4.9 7 11 7 11zM12 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z'],
                ];
            @endphp

            @foreach ($cards as $i => [$label, $value, $href, $icon])
                <a href="{{ $href }}" @if (str_starts_with($href, 'http')) target="_blank" rel="noopener noreferrer" @endif
                   class="group flex animate-rise items-center gap-4 rounded-2xl border border-gold-500/20 bg-night-900/60 p-5 transition duration-500
                          hover:-translate-y-1 hover:border-gold-500/70 hover:bg-gold-500/5"
                   style="animation-delay: {{ 200 + $i * 100 }}ms">
                    <span class="grid size-12 shrink-0 place-items-center rounded-full border border-gold-500/40 text-gold-500 transition duration-500 group-hover:bg-gold-500 group-hover:text-night-950">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}"/></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-xs uppercase tracking-wider text-stone-500">{{ $label }}</span>
                        <span class="block truncate text-stone-100 transition group-hover:text-gold-500">{{ $value }}</span>
                    </span>
                </a>
            @endforeach

            {{-- Horaires --}}
            <div class="rounded-2xl border border-gold-500/20 bg-night-900/60 p-5">
                <h2 class="font-display text-2xl text-gold-500">Horaires d’accueil</h2>
                <dl class="mt-3 divide-y divide-gold-500/15 text-sm">
                    @foreach ($contact['hours'] as [$day, $hours])
                        <div class="flex justify-between py-2"><dt class="text-stone-400">{{ $day }}</dt><dd class="text-stone-200">{{ $hours }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </aside>
    </div>
</section>

{{-- CARTE --}}
<section class="mx-auto max-w-6xl px-4 pb-20 sm:px-6 lg:px-8" aria-labelledby="map-titre" x-data="{ map: false }">
    <h2 id="map-titre" class="font-display text-4xl font-semibold text-gold-500">Nous trouver</h2>

    <div class="relative mt-6 aspect-[16/10] overflow-hidden rounded-3xl border border-gold-500/30 bg-night-900 sm:aspect-[16/7]">
        <template x-if="map">
            <iframe title="Carte : {{ $contact['address'] }}" class="absolute inset-0 size-full" loading="lazy" referrerpolicy="no-referrer"
                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $contact['lng'] - 0.02 }}%2C{{ $contact['lat'] - 0.012 }}%2C{{ $contact['lng'] + 0.02 }}%2C{{ $contact['lat'] + 0.012 }}&layer=mapnik&marker={{ $contact['lat'] }}%2C{{ $contact['lng'] }}"></iframe>
        </template>

        <div x-show="!map" class="absolute inset-0 grid place-items-center bg-[radial-gradient(circle_at_center,rgba(212,160,23,.12),transparent_60%)] p-6 text-center">
            <div>
                <p class="font-display text-3xl text-stone-100">{{ $contact['address'] }}</p>
                <p class="mx-auto mt-2 max-w-sm text-sm text-stone-400">La carte est chargée uniquement à votre demande, pour respecter votre vie privée.</p>
                <button type="button" x-on:click="map = true"
                        class="mt-5 rounded-full bg-gold-500 px-6 py-3 text-sm font-semibold text-night-950 transition hover:-translate-y-0.5 hover:bg-gold-400">
                    Afficher la carte
                </button>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="border-t border-gold-500/20 bg-night-900/40 py-20" x-data="{ open: null }" aria-labelledby="faq-titre">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 id="faq-titre" class="font-display text-4xl font-semibold text-gold-500 sm:text-5xl">Avant de nous écrire</h2>

        <div class="mt-8 divide-y divide-gold-500/20 border-y border-gold-500/20">
            @foreach ($faqs as $k => [$q, $a])
                <div>
                    <button type="button" x-on:click="open = open === {{ $k }} ? null : {{ $k }}" x-bind:aria-expanded="open === {{ $k }}"
                            class="flex w-full items-center justify-between gap-4 py-5 text-left text-lg text-stone-100 transition hover:text-gold-500">
                        <span>{{ $q }}</span>
                        <svg class="size-5 shrink-0 text-gold-500 transition-transform duration-300" x-bind:class="open === {{ $k }} && 'rotate-45'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/></svg>
                    </button>
                    <div x-show="open === {{ $k }}" x-collapse x-cloak>
                        <p class="pb-5 leading-relaxed text-stone-400">{{ $a }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
</div>