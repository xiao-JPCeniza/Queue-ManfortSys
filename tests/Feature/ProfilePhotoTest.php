<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_and_view_a_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.photo.update'), [
                'photo' => UploadedFile::fake()->image('avatar.jpg', 160, 160),
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertNotNull($user->profile_photo_path);
        $this->assertNotNull($user->profile_photo_url);
        Storage::disk('public')->assertExists($user->profile_photo_path);

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee($user->profile_photo_url, false);

        $this->actingAs($user)
            ->get($user->profile_photo_url)
            ->assertOk();
    }

    public function test_uploading_a_new_profile_photo_replaces_the_previous_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('first.jpg', 120, 120),
        ]);

        $firstPath = $user->fresh()->profile_photo_path;

        $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('second.jpg', 120, 120),
        ]);

        $user->refresh();

        $this->assertNotSame($firstPath, $user->profile_photo_path);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }
}
