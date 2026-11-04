<?php

namespace App\Livewire;

use Livewire\Component;

class Prayer extends Component
{
    public $author = '';

    public $message = '';

    public $type = 'priere';

    public $prayers = [
        [
            'id' => 1,
            'author' => 'Fara Nantenaina',
            'type' => 'priere',
            'message' => 'Prions pour notre nouveau spectacle de danse qui touchera les cœurs des jeunes égarés à Antananarivo.',
            'date' => 'Il y a 2 heures',
            'likes' => 14,
        ],
        [
            'id' => 2,
            'author' => 'Haja Andriana',
            'type' => 'temoignage',
            'message' => 'Gloire à Dieu ! Suite à l’atelier de danse prophétique, j’ai retrouvé la paix intérieure et la guérison de mon genou.',
            'date' => 'Il y a 1 jour',
            'likes' => 27,
        ],
        [
            'id' => 3,
            'author' => 'Voahirana R.',
            'type' => 'priere',
            'message' => 'Soutenez-nous dans la prière pour l’acquisition de nouveaux costumes traditionnels pour notre culte pascal.',
            'date' => 'Il y a 2 jours',
            'likes' => 9,
        ],
    ];

    public function addPost()
    {
        $this->validate([
            'author' => 'required|min:2',
            'message' => 'required|min:5',
            'type' => 'required|in:priere,temoignage',
        ]);

        // Ajout en haut de la liste
        array_unshift($this->prayers, [
            'id' => time(),
            'author' => $this->author,
            'type' => $this->type,
            'message' => $this->message,
            'date' => 'À l’instant',
            'likes' => 0,
        ]);

        // Réinitialisation du formulaire
        $this->author = '';
        $this->message = '';
        $this->type = 'priere';
    }

    public function likePost($id)
    {
        foreach ($this->prayers as &$item) {
            if ($item['id'] === $id) {
                $item['likes']++;
                break;
            }
        }
    }

    public function render()
    {
        return view('livewire.prayer')
            ->layout('layouts.app', ['title' => 'Mur de Prière - Iraka Voafidy']);
    }
}
