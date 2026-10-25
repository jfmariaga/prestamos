<div class="bg-white shadow-sm sm:rounded-lg p-6 max-w-2xl">
    <form wire:submit="save" class="space-y-4">
        <div>
            <x-input-label for="name" value="Nombre" />
            <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model="name" autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" type="email" class="mt-1 block w-full" wire:model="email" autocomplete="off" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="whatsapp_number" value="Número de WhatsApp (opcional)" />
            <x-text-input id="whatsapp_number" type="text" class="mt-1 block w-full" wire:model="whatsapp_number" placeholder="Ej: 573001234567" autocomplete="off" />
            <x-input-error :messages="$errors->get('whatsapp_number')" class="mt-1" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="password" value="Contraseña" />
                <x-text-input id="password" type="password" class="mt-1 block w-full" wire:model="password" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Confirmar contraseña" />
                <x-text-input id="password_confirmation" type="password" class="mt-1 block w-full" wire:model="password_confirmation" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Crear usuario</x-primary-button>
            <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
        </div>
    </form>
</div>
