<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;

class UserIndex extends Component
{
    public function render()
    {
        return view('livewire.users.user-index', [
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }
}
