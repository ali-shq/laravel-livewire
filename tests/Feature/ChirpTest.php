<?php

namespace Tests\Feature;

use App\Models\Chirp;
use Tests\TestCase;
use App\Models\User;
use Livewire\Volt\Volt;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChirpTest extends TestCase
{
    public function test_un_logged_user_is_redirected(): void
    {
        $response = $this->get('chirps');

        $response->assertRedirectToRoute('login');

        $response->assertStatus(302);
    }


    public function test_logged_user_can_access(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $response = $this->get('chirps');

        $response->assertStatus(200);
    }


    public function test_logged_user_can_create_chirp(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('chirps.create')
            ->set('message', 'Test Chirp')
            ->call('store');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        
        
    }


    public function test_logged_user_cannot_create_invalid_chirp(): void
    {
        $user = User::factory()->create();

      

        $this->actingAs($user);

        $component = Volt::test('chirps.create')
            ->set('message', '')
            ->call('store');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        
    }


    public function test_logged_user_can_update_own_chirp(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('chirps.create')
            ->set('message', 'Test Chirp')
            ->call('store');


        $component = Volt::test('chirps.list');


        foreach ($component->chirps as $chirp) {

            if ($chirp->user->is($user)) {

                break;

            }
        }

        $component = Volt::test('chirps.edit', ['chirp' => $chirp])
                                ->set('message', 'Test Chirp')
                                ->call('update');

        $component->assertStatus(200)
                  ->assertNoRedirect();
        
    }


    public function test_logged_user_cannot_update_not_own_chirp(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('chirps.create')
            ->set('message', 'Test Chirp')
            ->call('store');


        $component = Volt::test('chirps.list');


        foreach ($component->chirps as $chirp) {

            if (!$chirp->user->is($user)) {

                break;

            }
        }

        $component = Volt::test('chirps.edit', ['chirp' => $chirp])
                                ->set('message', 'Test Chirp2')
                                ->call('update');

        $component->assertStatus(403)
                  ->assertNoRedirect();
        
    }


}
