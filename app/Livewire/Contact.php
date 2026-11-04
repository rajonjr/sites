<?php

namespace App\Livewire;

use Livewire\Component;

class Contact extends Component
{
    public $name = '';

    public $email = '';

    public $subject = '';

    public $message = '';

    public $successMessage = false;

    public function submitForm()
    {
        $this->validate([
            'name' => 'required|min:2',
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|min:10',
        ]);

        //Mail::to()->send()

        $this->successMessage = true;

        // Réinitialisation des champs du formulaire
        $this->name = '';
        $this->email = '';
        $this->subject = '';
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.contact')
            ->layout('layouts.app', ['title' => 'Contact - Iraka Voafidy']);
    }
}
