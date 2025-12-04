<?php

namespace App\Livewire\Admin;

use App\Models\Jury;
use Livewire\Component;

class JuryCreate extends Component
{
    public $name = '';

    protected $rules = [
        'name' => ['required', 'string', 'max:255'],
    ];

    protected $messages = [
        'name.required' => 'Le nom du jury est obligatoire.',
        'name.max' => 'Le nom du jury ne doit pas dépasser 255 caractères.',
    ];

    public function submit(): void
    {
        $this->validate();

        Jury::create([
            'name' => $this->name,
            'status' => 'constituted',
        ]);

        session()->flash('success', 'Le jury a été créé avec succès.');
        $this->redirect(route('admin.juries'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.jury-create');
    }
}
