<?php

namespace App\Livewire;

use Livewire\Component;

class Event extends Component
{
    public $selectedEvent = null;

    // Champs du formulaire de réservation
    public $name = '';

    public $email = '';

    public $phone = '';

    public $successMessage = false;

    // Liste des événements
    public function getEventsProperty()
    {
        return [
            [
                'id' => 1,
                'title' => 'Maîtrise du Tambourin Prophétique',
                'type' => 'Atelier Pratique',
                'badgeClass' => 'bg-gold-500/10 text-gold-400 border-gold-500/30',
                'date' => '18 Oct 2026',
                'desc' => 'Apprenez les rythmes bibliques et la coordination des mouvements avec tambourin pour enflammer l\'assemblée.',
                'location' => 'Temple Antsahavola, Antananarivo',
                'time' => '14h00 - 17h00',
            ],
            [
                'id' => 2,
                'title' => 'Danse Contemporaine & Adoration',
                'type' => 'Masterclass',
                'badgeClass' => 'bg-brand-500/10 text-brand-400 border-brand-500/30',
                'date' => '25 Oct 2026',
                'desc' => 'Techniques corporelles avancées, expression théâtrale et transmission d\'émotions spirituelles par le corps.',
                'location' => 'Studio Iraka Voafidy, Ankorondrano',
                'time' => '09h00 - 13h00',
            ],
            [
                'id' => 3,
                'title' => 'Nuit de Louange et de Danse',
                'type' => 'Grand Rassemblement',
                'badgeClass' => 'bg-gold-500/10 text-gold-400 border-gold-500/30',
                'date' => '10 Nov 2026',
                'desc' => 'Une veillée spirituelle exceptionnelle réunissant plusieurs ministères de danse pour une communion intense.',
                'location' => 'Coliseum Antsonjory',
                'time' => '19h00 - 05h00',
            ],
        ];
    }

    public function openBooking($eventTitle)
    {
        $this->selectedEvent = $eventTitle;
        $this->resetForm();
    }

    public function closeBooking()
    {
        $this->selectedEvent = null;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->successMessage = false;
    }

    public function bookEvent()
    {
        // Validation basique
        $this->validate([
            'name' => 'required|min:2',
            'email' => 'required|email',
        ]);

        // Traitement de l'inscription

        $this->successMessage = true;

        // Réinitialisation après 2 secondes
        $this->dispatch('event-booked', title: $this->selectedEvent);
    }

    public function render()
    {
        return view('livewire.event')
            ->layout('layouts.app', ['title' => 'Événements - Iraka Voafidy']);
    }
}
