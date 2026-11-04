<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Mon espace')]
class Dashboard extends Component
{
    // ---- Formulaire : profil
    public string $name = '';
    public string $email = '';

    // ---- Formulaire : mot de passe
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // ---- Formulaire : intention de prière
    public string $content = '';
    public bool $anonymous = false;

    // ------------------------------------------------------------------ cycle de vie

    public function mount(): void
    {
        $this->guard();

        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    // ------------------------------------------------------------------ actions : profil

    public function saveProfile(): void
    {
        $this->guard();
        $this->resetErrorBag();

        $user = auth()->user();

        $this->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'name.required'  => 'Veuillez indiquer votre nom.',
            'email.required' => 'Veuillez indiquer votre adresse email.',
            'email.email'    => 'Cette adresse email n’est pas valide.',
            'email.unique'   => 'Un compte existe déjà avec cette adresse email.',
        ]);

        $emailChanged = $this->email !== $user->email;

        $user->forceFill(['name' => $this->name, 'email' => $this->email]);

        if ($emailChanged && $user instanceof MustVerifyEmail) {
            // Nouvelle adresse : elle doit être vérifiée à nouveau
            $user->forceFill(['email_verified_at' => null]);
        }

        $user->save();

        if ($emailChanged && $user instanceof MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
            $this->notify('Profil mis à jour. Vérifiez votre nouvelle adresse email.');

            return;
        }

        $this->notify('Vos informations ont été mises à jour.');
    }

    public function updatePassword(): void
    {
        $this->guard();
        $this->resetErrorBag();

        $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password'         => ['required', 'string', Password::min(8)->letters()->mixedCase()->numbers(), 'confirmed'],
        ], [
            'current_password.required'         => 'Veuillez saisir votre mot de passe actuel.',
            'current_password.current_password' => 'Votre mot de passe actuel est incorrect.',
            'password.required'                 => 'Veuillez choisir un nouveau mot de passe.',
            'password.confirmed'                => 'Les deux mots de passe ne sont pas identiques.',
            'password.min'                      => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.mixed'                    => 'Le mot de passe doit contenir une majuscule et une minuscule.',
            'password.numbers'                  => 'Le mot de passe doit contenir au moins un chiffre.',
        ]);

        auth()->user()->forceFill(['password' => Hash::make($this->password)])->save();

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->notify('Votre mot de passe a été modifié.');
    }

    // ------------------------------------------------------------------ actions : événements

    public function joinEvent(int $id): void
    {
        $this->guard();

        $registration = $this->model('EventRegistration');
        $eventModel = $this->model('Event');

        if (! $registration || ! $eventModel) {
            $this->notify('Les inscriptions ne sont pas encore activées.', 'error');

            return;
        }

        $event = $this->safe(fn () => $eventModel::where('date', '>=', now())->find($id), null);

        if (! $event) {
            $this->notify('Cet événement n’est plus disponible.', 'error');

            return;
        }

        $already = $registration::where('user_id', auth()->id())->where('event_id', $id)->exists();

        if ($already) {
            $this->notify('Vous êtes déjà inscrit(e) à cet événement.');

            return;
        }

        $capacity = (int) ($event->capacity ?? 0);

        if ($capacity > 0 && $registration::where('event_id', $id)->count() >= $capacity) {
            $this->notify('Cet événement est complet.', 'error');

            return;
        }

        $registration::firstOrCreate(['user_id' => auth()->id(), 'event_id' => $id]);
        $this->notify("Inscription confirmée : {$event->title}.");
    }

    public function leaveEvent(int $id): void
    {
        $this->guard();

        $registration = $this->model('EventRegistration');

        if (! $registration) {
            return;
        }

        $deleted = $registration::where('user_id', auth()->id())->where('event_id', $id)->delete();

        $this->notify($deleted ? 'Votre inscription a été annulée.' : 'Aucune inscription à annuler.', $deleted ? 'success' : 'error');
    }

    // ------------------------------------------------------------------ actions : intentions de prière

    public function sendRequest(): void
    {
        $this->guard();
        $this->resetErrorBag('content');

        $model = $this->model('PrayerRequest');

        if (! $model) {
            $this->notify('L’envoi d’intentions n’est pas encore activé.', 'error');

            return;
        }

        $this->validate([
            'content' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'content.required' => 'Écrivez votre intention avant de l’envoyer.',
            'content.min'      => 'Votre intention est un peu courte (5 caractères minimum).',
            'content.max'      => 'Votre intention est trop longue (500 caractères maximum).',
        ]);

        // Anti-abus : 5 intentions par heure et par membre
        $sent = RateLimiter::attempt('prayer-request:'.auth()->id(), 5, function () use ($model) {
            $model::forceCreate([
                'user_id'      => auth()->id(),
                'name'         => auth()->user()->name,
                'content'      => $this->content,
                'is_anonymous' => $this->anonymous,
                'is_approved'  => false,
            ]);
        }, 3600);

        if (! $sent) {
            $this->notify('Vous avez envoyé beaucoup d’intentions : réessayez dans un moment.', 'error');

            return;
        }

        $this->reset('content', 'anonymous');
        $this->notify('Intention envoyée. Elle sera relue avant d’être partagée.');
    }

    public function deleteRequest(int $id): void
    {
        $this->guard();

        $model = $this->model('PrayerRequest');

        if (! $model) {
            return;
        }

        // Un membre ne peut retirer que ses propres intentions non encore publiées
        $deleted = $model::where('id', $id)->where('user_id', auth()->id())->where('is_approved', false)->delete();

        $this->notify($deleted ? 'Intention retirée.' : 'Cette intention ne peut pas être retirée.', $deleted ? 'success' : 'error');
    }

    // ------------------------------------------------------------------ données

    #[Computed]
    public function registrationsEnabled(): bool
    {
        return (bool) $this->model('EventRegistration');
    }

    #[Computed]
    public function prayerEnabled(): bool
    {
        return (bool) $this->model('PrayerRequest');
    }

    /** Événements à venir, avec l'état d'inscription de l'utilisateur */
    #[Computed]
    public function events(): Collection
    {
        $eventModel = $this->model('Event');

        if (! $eventModel) {
            return collect();
        }

        return $this->safe(function () use ($eventModel) {
            $events = $eventModel::where('date', '>=', now())->orderBy('date')->take(12)->get();
            $ids = $events->pluck('id')->all();

            $registration = $this->model('EventRegistration');
            $mine = $registration ? $registration::where('user_id', auth()->id())->whereIn('event_id', $ids)->pluck('event_id')->all() : [];
            $taken = $registration
                ? $registration::whereIn('event_id', $ids)->selectRaw('event_id, COUNT(*) as c')->groupBy('event_id')->pluck('c', 'event_id')
                : collect();

            return $events->map(function ($e) use ($mine, $taken) {
                $d = Carbon::parse($e->date)->locale('fr');
                $capacity = (int) ($e->capacity ?? 0);
                $count = (int) ($taken[$e->id] ?? 0);
                $registered = in_array($e->id, $mine);

                return [
                    'id'         => $e->id,
                    'title'      => $e->title,
                    'place'      => $e->location ?? $e->place ?? '',
                    'day'        => $d->format('d'),
                    'month'      => $d->translatedFormat('M'),
                    'long'       => $d->translatedFormat('l j F'),
                    'time'       => $d->format('H:i'),
                    'registered' => $registered,
                    'spots'      => $capacity > 0 ? max($capacity - $count, 0) : null,
                    'full'       => $capacity > 0 && $count >= $capacity && ! $registered,
                ];
            })->values();
        }, collect());
    }

    #[Computed]
    public function myEvents(): Collection
    {
        return $this->events->where('registered', true)->values();
    }

    /** Chorégraphies : les favoris sont lus côté navigateur (clé « iv-choreo-favs ») */
    #[Computed]
    public function choreographies(): Collection
    {
        $model = $this->model('Choreography');

        if (! $model) {
            return collect();
        }

        return $this->safe(fn () => $model::latest()->get()->map(fn ($c) => [
            'id'       => $c->id,
            'title'    => $c->title,
            'category' => $c->category ?: 'Chorégraphie',
            'level'    => $c->level ?: 'Tous niveaux',
            'duration' => (int) ($c->duration ?? 0),
        ])->values(), collect());
    }

    #[Computed]
    public function myRequests(): Collection
    {
        $model = $this->model('PrayerRequest');

        if (! $model) {
            return collect();
        }

        return $this->safe(fn () => $model::where('user_id', auth()->id())->latest()->take(10)->get()->map(fn ($r) => [
            'id'       => $r->id,
            'text'     => Str::limit($r->content, 200),
            'approved' => (bool) ($r->is_approved ?? false),
            'when'     => optional($r->created_at)->locale('fr')->diffForHumans(),
        ])->values(), collect());
    }

    /** Étapes de complétion du profil : uniquement ce qui existe dans votre application */
    #[Computed]
    public function checklist(): array
    {
        $user = auth()->user();
        $items = [['label' => 'Compte créé', 'done' => true, 'route' => null]];

        if ($user instanceof MustVerifyEmail) {
            $items[] = ['label' => 'Adresse email vérifiée', 'done' => $user->hasVerifiedEmail(), 'route' => 'verification.notice'];
        }

        if (array_key_exists('two_factor_secret', $user->getAttributes())) {
            $items[] = ['label' => 'Double authentification activée', 'done' => ! empty($user->two_factor_confirmed_at), 'route' => null];
        }

        if ($this->registrationsEnabled) {
            $items[] = ['label' => 'Inscrit à un événement', 'done' => $this->myEvents->isNotEmpty(), 'route' => 'events'];
        }

        return $items;
    }

    // ------------------------------------------------------------------ rendu

    public function render()
    {
        $user = auth()->user();
        $hour = (int) now()->format('G');

        return view('livewire.user.dashboard', [
            'greeting' => $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir'),
            'today'    => now()->locale('fr')->translatedFormat('l j F Y'),
            'since'    => optional($user->created_at)->locale('fr')->translatedFormat('F Y'),
            'verified' => $user instanceof MustVerifyEmail ? $user->hasVerifiedEmail() : null,
        ]);
    }

    // ------------------------------------------------------------------ outils

    /** Les actions Livewire sont appelables directement : chacune vérifie la connexion. */
    private function guard(): void
    {
        abort_unless(auth()->check(), 403);
    }

    private function notify(string $message, string $type = 'success'): void
    {
        $this->dispatch('toast', message: $message, type: $type);
    }

    private function model(string $name): ?string
    {
        $class = "App\\Models\\{$name}";

        return class_exists($class) ? $class : null;
    }

    private function safe(callable $fn, mixed $default): mixed
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            report($e);

            return $default;
        }
    }
}