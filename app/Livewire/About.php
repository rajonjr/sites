<?php

namespace App\Livewire;

use Livewire\Component;

class About extends Component
{
    public function getTeamMembersProperty()
    {
        return [
            [
                'name' => 'Pasteur Rija Rakoto',
                'role' => 'Fondateur & Directeur Spirituel',
                'image' => 'https://placehold.co/400x400/5b21b6/fff?text=Pasteur+Leader',
                'desc' => 'Plus de 15 ans au service de l\'enseignement prophétique et de la louange dans l\'océan Indien.',
            ],
            [
                'name' => 'Prisca Rasoanaivo',
                'role' => 'Chorégraphe Principale',
                'image' => 'https://placehold.co/400x400/6d28d9/fff?text=Coach+Danse',
                'desc' => 'Spécialiste en danse contemporaine et traditionnelle malgache dédiée au culte.',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.about')
            ->layout('layouts.app', ['title' => 'À Propos - Iraka Voafidy']);
    }
}