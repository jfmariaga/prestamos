<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $loan = Loan::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee($loan->borrower->name);
        $response->assertSee('Tasa de morosidad');
        $response->assertSee('Top 5 deudores por saldo pendiente');
        $response->assertSee('Próximos vencimientos (15 días)');
        $response->assertSeeInOrder(['<canvas', 'x-ref="canvas"'], false);

        // The date-range filter must entangle with the `.live` modifier,
        // otherwise changing it never pushes an update to the server.
        $response->assertSee(".entangle('rangeStart').live", false);
        $response->assertSee(".entangle('rangeEnd').live", false);
    }

    public function test_loan_create_and_show_pages_load(): void
    {
        $user = User::factory()->create();
        $loan = Loan::factory()->create();

        $this->actingAs($user)->get('/loans/create')
            ->assertStatus(200)
            ->assertSee('flatpickr', false);
        $this->actingAs($user)->get("/loans/{$loan->id}")->assertStatus(200);
    }
}
