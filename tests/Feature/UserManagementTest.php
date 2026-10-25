<?php

namespace Tests\Feature;

use App\Livewire\Users\UserForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_lists_existing_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/users');

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_a_new_user_can_be_created(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(UserForm::class)
            ->set('name', 'Nuevo Usuario')
            ->set('email', 'nuevo@example.com')
            ->set('whatsapp_number', '3009998888')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('users.index'));

        $created = User::where('email', 'nuevo@example.com')->firstOrFail();

        $this->assertSame('Nuevo Usuario', $created->name);
        $this->assertSame('3009998888', $created->whatsapp_number);
        $this->assertNotNull($created->email_verified_at);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password123', $created->password));
    }

    public function test_email_must_be_unique(): void
    {
        $admin = User::factory()->create();
        $existing = User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(UserForm::class)
            ->set('name', 'Otro Usuario')
            ->set('email', $existing->email)
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_password_must_be_confirmed(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(UserForm::class)
            ->set('name', 'Otro Usuario')
            ->set('email', 'otro@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'mismatch')
            ->call('save')
            ->assertHasErrors(['password']);
    }
}
