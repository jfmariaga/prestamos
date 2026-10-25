<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class UserForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $whatsapp_number = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'whatsapp_number' => $data['whatsapp_number'] !== '' ? $data['whatsapp_number'] : null,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        session()->flash('success', 'Usuario creado correctamente.');

        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.users.user-form');
    }
}
