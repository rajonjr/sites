<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.admin.layouts.app')]
#[Title('Tableau de bord')]
class Dashboard extends Component
{
    use WithPagination;

    private const PERIODS = [7, 30, 90];
    private const SORTABLE = ['name', 'email', 'role', 'created_at'];

    /** Période du graphique et des indicateurs, en jours */
    #[Url(as: 'periode')]
    public int $period = 30;

    #[Url(as: 'q', history: false)]
    public string $search = '';

    #[Url(as: 'role')]
    public string $roleFilter = 'all';

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // cycle de vie

    public function mount(): void
    {
        $this->authorizeAdmin();

        if (! in_array($this->period, self::PERIODS, true)) {
            $this->period = 30;
        }
    }

    public function updatedPeriod(): void
    {
        if (! in_array($this->period, self::PERIODS, true)) {
            $this->period = 30;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    // actions

    public function approve(int $id): void
    {
        $this->authorizeAdmin();

        $model = $this->model('PrayerRequest');
        $request = $model ? $model::find($id) : null;

        if (! $request) {
            $this->notify('Cette intention n’existe plus.', 'error');

            return;
        }

        $request->forceFill(['is_approved' => true])->save();
        Cache::forget('admin.layout.counts');
        $this->notify('Intention publiée.');
    }

    public function reject(int $id): void
    {
        $this->authorizeAdmin();

        $model = $this->model('PrayerRequest');
        $request = $model ? $model::find($id) : null;

        if (! $request) {
            $this->notify('Cette intention n’existe plus.', 'error');

            return;
        }

        $request->delete();
        Cache::forget('admin.layout.counts');
        $this->notify('Intention refusée et supprimée.');
    }

    public function setRole(int $id, string $role): void
    {
        $this->authorizeAdmin();

        if (! in_array($role, ['admin', 'user'], true)) {
            return;
        }

        if ($id === auth()->id()) {
            $this->notify('Vous ne pouvez pas modifier votre propre rôle.', 'error');

            return;
        }

        $user = User::find($id);

        if (! $user) {
            $this->notify('Ce membre n’existe plus.', 'error');

            return;
        }

        $user->forceFill(['role' => $role])->save();
        $this->notify($role === 'admin' ? "{$user->name} est maintenant administrateur." : "{$user->name} est maintenant membre.");
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE, true)) {
            return;
        }

        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'roleFilter');
        $this->resetPage();
    }

    // données

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function kpis(): array
    {
        $now = now();
        $start = $now->copy()->subDays($this->period - 1)->startOfDay();
        $prevStart = $start->copy()->subDays($this->period);

        $total = $this->safe(fn () => User::count(), 0);
        $new = $this->safe(fn () => User::where('created_at', '>=', $start)->count(), 0);
        $prev = $this->safe(fn () => User::whereBetween('created_at', [$prevStart, $start->copy()->subSecond()])->count(), 0);

        $events = $this->model('Event');
        $choreos = $this->model('Choreography');
        $prayers = $this->model('PrayerRequest');

        return [
            ['label' => 'Membres',               'value' => $total, 'delta' => null, 'hint' => 'inscrits au total',
             'icon' => 'M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8zM21 20v-2a4 4 0 00-3-3.9M16 2.1a4 4 0 010 7.8'],
            ['label' => 'Nouveaux membres',      'value' => $new, 'delta' => $this->delta($new, $prev), 'hint' => "sur {$this->period} jours",
             'icon' => 'M12 5v14M5 12h14'],
            ['label' => 'Chorégraphies',         'value' => $choreos ? $this->safe(fn () => $choreos::count(), 0) : 0, 'delta' => null, 'hint' => 'publiées',
             'icon' => 'M9 18V5l12-2v13M9 18a3 3 0 11-6 0 3 3 0 016 0zm12-2a3 3 0 11-6 0 3 3 0 016 0z'],
            ['label' => 'Événements à venir',    'value' => $events ? $this->safe(fn () => $events::where('date', '>=', $now)->count(), 0) : 0, 'delta' => null, 'hint' => 'programmés',
             'icon' => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z'],
            ['label' => 'Intentions à modérer',  'value' => $prayers ? $this->safe(fn () => $prayers::where('is_approved', false)->count(), 0) : 0, 'delta' => null, 'hint' => 'en attente', 'alert' => true,
             'icon' => 'M12 21s-7-4.4-7-10a4 4 0 017-2.6A4 4 0 0119 11c0 5.6-7 10-7 10z'],
        ];
    }

    /** Inscriptions par jour sur la période choisie */
    #[Computed]
    public function series(): Collection
    {
        $from = now()->copy()->subDays($this->period - 1)->startOfDay();

        $perDay = $this->safe(fn () => User::where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')->groupBy('d')->pluck('c', 'd'), collect());

        return collect(range(0, $this->period - 1))->map(function ($i) use ($from, $perDay) {
            $day = $from->copy()->addDays($i);

            return [
                'label' => $day->locale('fr')->translatedFormat('j M'),
                'count' => (int) ($perDay[$day->toDateString()] ?? 0),
            ];
        })->values();
    }

    #[Computed]
    public function members(): LengthAwarePaginator
    {
        $term = addcslashes($this->search, '%_\\');

        return User::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")))
            ->when($this->roleFilter !== 'all', fn ($q) => $q->where('role', $this->roleFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('id')
            ->paginate(8);
    }

    #[Computed]
    public function pending(): Collection
    {
        $model = $this->model('PrayerRequest');

        if (! $model) {
            return collect();
        }

        return $this->safe(fn () => $model::where('is_approved', false)->latest()->take(5)->get()->map(fn ($r) => [
            'id'   => $r->id,
            'who'  => ($r->is_anonymous ?? false) || ! $r->name ? 'Anonyme' : $r->name,
            'text' => Str::limit($r->content, 180),
            'when' => optional($r->created_at)->locale('fr')->diffForHumans(),
        ]), collect());
    }

    #[Computed]
    public function upcomingEvents(): Collection
    {
        $model = $this->model('Event');

        if (! $model) {
            return collect();
        }

        return $this->safe(fn () => $model::where('date', '>=', now())->orderBy('date')->take(5)->get()->map(function ($e) {
            $d = Carbon::parse($e->date)->locale('fr');

            return [
                'title' => $e->title,
                'place' => $e->location ?? $e->place ?? '',
                'day'   => $d->format('d'),
                'month' => $d->translatedFormat('M'),
                'time'  => $d->format('H:i'),
            ];
        }), collect());
    }

    #[Computed]
    public function activity(): Collection
    {
        $items = collect();

        $items = $items->merge($this->safe(fn () => User::latest()->take(6)->get()->map(fn ($u) => [
            'type' => 'user', 'text' => "{$u->name} a rejoint la communauté", 'at' => $u->created_at,
        ]), collect()));

        foreach (['Event' => 'Événement ajouté', 'Choreography' => 'Chorégraphie ajoutée'] as $name => $label) {
            $model = $this->model($name);
            $type = $name === 'Event' ? 'event' : 'choreo';

            if ($model) {
                $items = $items->merge($this->safe(fn () => $model::latest()->take(6)->get()->map(fn ($m) => [
                    'type' => $type, 'text' => "{$label} : {$m->title}", 'at' => $m->created_at,
                ]), collect()));
            }
        }

        if ($prayers = $this->model('PrayerRequest')) {
            $items = $items->merge($this->safe(fn () => $prayers::latest()->take(6)->get()->map(fn ($r) => [
                'type' => 'prayer', 'text' => 'Nouvelle intention de prière reçue', 'at' => $r->created_at,
            ]), collect()));
        }

        return $items->filter(fn ($a) => $a['at'])->sortByDesc('at')->take(8)->map(fn ($a) => [
            'type' => $a['type'], 'text' => $a['text'], 'when' => $a['at']->locale('fr')->diffForHumans(),
        ])->values();
    }

    /** @return array<int, array{0: string, 1: string}> */
    #[Computed]
    public function system(): array
    {
        return [
            ['Laravel', app()->version()],
            ['PHP', PHP_VERSION],
            ['Environnement', app()->environment()],
            ['Fuseau horaire', (string) config('app.timezone')],
        ];
    }

    #[Computed]
    public function debugWarning(): bool
    {
        return config('app.debug') && app()->environment('production');
    }

    // ------------------------------------------------------------------ rendu

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'greeting' => match (true) {
                (int) now()->format('G') < 12 => 'Bonjour',
                (int) now()->format('G') < 18 => 'Bon après-midi',
                default                       => 'Bonsoir',
            },
            'today' => now()->locale('fr')->translatedFormat('l j F Y'),
        ]);
    }

    // outils

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403, 'Accès réservé aux administrateurs.');
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

    private function delta(int $current, int $previous): ?int
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : null;
        }

        return (int) round((($current - $previous) / $previous) * 100);
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