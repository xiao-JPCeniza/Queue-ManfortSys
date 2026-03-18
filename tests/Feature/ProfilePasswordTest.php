<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_their_password(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
        ]);

        $this->actingAs($user)
            ->post(route('profile.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Password updated.');

        $user->refresh();

        $this->assertTrue(Hash::check('new-password-123', $user->password));
        $this->assertFalse(Hash::check('old-password', $user->password));
        $this->assertSame('new-password-123', $user->recoverable_password);
    }

    public function test_password_update_requires_the_current_password_to_match(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
        ]);

        $this->actingAs($user)
            ->from(route('profile'))
            ->post(route('profile.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('profile'))
            ->assertSessionHasErrors('current_password');

        $user->refresh();

        $this->assertTrue(Hash::check('old-password', $user->password));
    }
}
