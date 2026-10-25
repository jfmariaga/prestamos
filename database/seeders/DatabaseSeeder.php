<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with the initial administrator account.
     *
     * Configure ADMIN_NAME / ADMIN_EMAIL / ADMIN_PASSWORD in .env before running
     * this in production. If ADMIN_PASSWORD is left empty on first run, a random
     * password is generated and printed once — save it immediately, it cannot be
     * recovered. Re-running this seeder never changes an existing admin's
     * password unless ADMIN_PASSWORD is explicitly set.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');

        if (! $email) {
            $this->command->error('Define ADMIN_EMAIL en el archivo .env antes de ejecutar este seeder.');

            return;
        }

        $name = env('ADMIN_NAME', 'Administrador');
        $whatsappNumber = env('ADMIN_WHATSAPP_NUMBER');
        $explicitPassword = env('ADMIN_PASSWORD');

        $user = User::query()->where('email', $email)->first();
        $generatedPassword = null;

        if ($user) {
            $user->name = $name;
            $user->whatsapp_number = $whatsappNumber;

            if ($explicitPassword) {
                $user->password = Hash::make($explicitPassword);
            }
        } else {
            $password = $explicitPassword ?: ($generatedPassword = Str::password(16));

            $user = new User([
                'name' => $name,
                'email' => $email,
                'whatsapp_number' => $whatsappNumber,
                'password' => Hash::make($password),
            ]);
        }

        $user->email_verified_at ??= now();
        $user->save();

        $this->command->info("Usuario administrador listo: {$email}");

        if ($generatedPassword) {
            $this->command->warn("Contraseña generada automáticamente: {$generatedPassword}");
            $this->command->warn('Guárdala ahora — no se volverá a mostrar. Cámbiala desde el perfil después de iniciar sesión.');
        }
    }
}
