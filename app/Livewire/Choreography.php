<?php

namespace App\Livewire;

use Livewire\Component;

class Choreography extends Component
{
    public $search = '';

    public $selectedCategory = 'Tous';

    public $activeModal = null;

    public function getCategoriesProperty()
    {
        return ['Tous', 'Gospel', 'Contemporain', 'Traditionnel', 'Urbain'];
    }

    public function getItemsProperty()
    {
        return [
            ['id' => 1, 'title' => 'Victoire de la Croix', 'category' => 'Gospel', 'level' => 'Intermédiaire', 'duration' => '4:30', 'verse' => 'Colossiens 2:15', 'img' => 'https://placehold.co/600x400/4c1d95/fff?text=Victoire+Croix', 'desc' => 'Une chorégraphie puissante célébrant la rédemption et la victoire de Jésus sur la mort.'],
            ['id' => 2, 'title' => 'Souffle Nouveau', 'category' => 'Contemporain', 'level' => 'Tous niveaux', 'duration' => '5:15', 'verse' => 'Ézéchiel 37:5', 'img' => 'https://placehold.co/600x400/6d28d9/fff?text=Souffle+Nouveau', 'desc' => 'Mouvements fluides et expressifs symbolisant l’Esprit Saint qui insuffle la vie dans nos vies.'],
            ['id' => 3, 'title' => 'Joie Céleste Malgache', 'category' => 'Traditionnel', 'level' => 'Avancé', 'duration' => '3:50', 'verse' => 'Psaume 100:1', 'img' => 'https://placehold.co/600x400/7c3aed/fff?text=Joie+Malagasy', 'desc' => 'Rythmes traditionnels revisités pour exprimer une louange exubérante et joyeuse envers le Roi.'],
            ['id' => 4, 'title' => 'Roi des Rois (Street Worship)', 'category' => 'Urbain', 'level' => 'Intermédiaire', 'duration' => '3:45', 'verse' => 'Apocalypse 19:16', 'img' => 'https://placehold.co/600x400/8b5cf6/fff?text=Roi+des+Rois', 'desc' => 'Danse urbaine dynamique, moderne et engagée pour porter la bonne nouvelle dans la rue.'],
            ['id' => 5, 'title' => 'Grâce Abondante', 'category' => 'Contemporain', 'level' => 'Débutant', 'duration' => '4:10', 'verse' => 'Éphésiens 2:8', 'img' => 'https://placehold.co/600x400/5b21b6/fff?text=Grace+Abondante', 'desc' => 'Un hymne visuel à la grâce imméritée, parfait pour les temps d’offrande et de méditation.'],
            ['id' => 6, 'title' => 'Tambourins de Louange', 'category' => 'Gospel', 'level' => 'Tous niveaux', 'duration' => '4:00', 'verse' => 'Psaume 149:3', 'img' => 'https://placehold.co/600x400/3b0764/fff?text=Tambourins', 'desc' => 'Apprentissage des techniques de tambourin et de mouvements synchronisés pour le culte du dimanche.'],
        ];
    }

    public function getFilteredItemsProperty()
    {
        return collect($this->items)->filter(function ($item) {
            $matchesCat = $this->selectedCategory === 'Tous' || $item['category'] === $this->selectedCategory;
            $matchesSearch = empty($this->search) ||
                str_contains(strtolower($item['title']), strtolower($this->search)) ||
                str_contains(strtolower($item['desc']), strtolower($this->search));

            return $matchesCat && $matchesSearch;
        });
    }

    public function selectItem($id)
    {
        $this->activeModal = collect($this->items)->firstWhere('id', $id);
    }

    public function closeModal()
    {
        $this->activeModal = null;
    }

    public function render()
    {
        return view('livewire.choreography', ['title' => 'Chorégraphies - Iraka Voafidy']);
    }
}
