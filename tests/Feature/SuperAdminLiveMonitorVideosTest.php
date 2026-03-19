<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\LiveMonitorVideos;
use App\Models\Role;
use App\Models\User;
use App\Support\LiveMonitorVideoLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminLiveMonitorVideosTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_the_live_monitor_videos_page(): void
    {
        Storage::fake(LiveMonitorVideoLibrary::DISK);

        $superAdmin = $this->createSuperAdminUser();

        $this->actingAs($superAdmin)
            ->get(route('super-admin.live-monitor-videos'))
            ->assertOk()
            ->assertSee('Live Monitor Videos')
            ->assertSee('+ Add Video')
            ->assertSee('Slot')
            ->assertSee('No uploaded idle videos yet.')
            ->assertSee('Offices')
            ->assertSee('User Management');
    }

    public function test_super_admin_can_upload_and_list_live_monitor_videos(): void
    {
        Storage::fake(LiveMonitorVideoLibrary::DISK);

        $superAdmin = $this->createSuperAdminUser();

        $this->actingAs($superAdmin);

        Livewire::test(LiveMonitorVideos::class)
            ->set('idleMonitorVideoUpload', UploadedFile::fake()->create('tourism-a.mp4', 1024, 'video/mp4'))
            ->call('uploadIdleMonitorVideo')
            ->assertHasNoErrors()
            ->assertSee('tourism-a.mp4')
            ->set('idleMonitorVideoUpload', UploadedFile::fake()->create('tourism-b.mp4', 2048, 'video/mp4'))
            ->call('uploadIdleMonitorVideo')
            ->assertHasNoErrors()
            ->assertSee('tourism-a.mp4')
            ->assertSee('tourism-b.mp4');

        Storage::disk(LiveMonitorVideoLibrary::DISK)->assertMissing(LiveMonitorVideoLibrary::ACTIVE_VIDEO_PATH);

        $libraryFiles = collect(Storage::disk(LiveMonitorVideoLibrary::DISK)->allFiles(LiveMonitorVideoLibrary::LIBRARY_DIRECTORY));
        $this->assertCount(2, $libraryFiles);

        $manifest = json_decode((string) Storage::disk(LiveMonitorVideoLibrary::DISK)->get(LiveMonitorVideoLibrary::MANIFEST_PATH), true);

        $this->assertCount(2, $manifest['videos']);
        $this->assertSame('tourism-b.mp4', collect($manifest['videos'])->firstWhere('id', $manifest['active_id'])['original_name']);

        $firstVideoId = $manifest['videos'][0]['id'];

        $this->get(route('media.tourism-video'))
            ->assertOk()
            ->assertHeader('content-type', 'video/mp4');

        $this->actingAs($superAdmin)
            ->get(route('super-admin.live-monitor-videos.preview', $firstVideoId))
            ->assertOk()
            ->assertHeader('content-type', 'video/mp4');
    }

    public function test_super_admin_cannot_upload_an_idle_monitor_video_larger_than_3_gb(): void
    {
        Storage::fake(LiveMonitorVideoLibrary::DISK);

        $superAdmin = $this->createSuperAdminUser();

        $this->actingAs($superAdmin);

        Livewire::test(LiveMonitorVideos::class)
            ->set('idleMonitorVideoUpload', UploadedFile::fake()->create('oversized-idle-video.mp4', 3145729, 'video/mp4'))
            ->call('uploadIdleMonitorVideo')
            ->assertHasErrors(['idleMonitorVideoUpload'])
            ->assertSee('The idle monitor video must not be larger than 3 GB.');

        Storage::disk(LiveMonitorVideoLibrary::DISK)->assertMissing(LiveMonitorVideoLibrary::MANIFEST_PATH);
    }

    public function test_super_admin_cannot_upload_the_same_video_filename_twice(): void
    {
        Storage::fake(LiveMonitorVideoLibrary::DISK);

        $superAdmin = $this->createSuperAdminUser();

        $this->actingAs($superAdmin);

        Livewire::test(LiveMonitorVideos::class)
            ->set('idleMonitorVideoUpload', UploadedFile::fake()->create('duplicate-video.mp4', 1024, 'video/mp4'))
            ->call('uploadIdleMonitorVideo')
            ->assertHasNoErrors()
            ->set('idleMonitorVideoUpload', UploadedFile::fake()->create('duplicate-video.mp4', 1024, 'video/mp4'))
            ->call('uploadIdleMonitorVideo')
            ->assertHasErrors(['idleMonitorVideoUpload'])
            ->assertSee('File already exists in the live monitor library.');

        $manifest = json_decode((string) Storage::disk(LiveMonitorVideoLibrary::DISK)->get(LiveMonitorVideoLibrary::MANIFEST_PATH), true);

        $this->assertCount(1, $manifest['videos']);
        $this->assertSame('duplicate-video.mp4', $manifest['videos'][0]['original_name']);
    }

    public function test_super_admin_can_activate_and_delete_uploaded_live_monitor_videos(): void
    {
        Storage::fake(LiveMonitorVideoLibrary::DISK);

        $superAdmin = $this->createSuperAdminUser();

        $this->actingAs($superAdmin);

        Livewire::test(LiveMonitorVideos::class)
            ->set('idleMonitorVideoUpload', UploadedFile::fake()->create('first-video.mp4', 512, 'video/mp4'))
            ->call('uploadIdleMonitorVideo')
            ->set('idleMonitorVideoUpload', UploadedFile::fake()->create('second-video.mp4', 768, 'video/mp4'))
            ->call('uploadIdleMonitorVideo');

        $manifest = json_decode((string) Storage::disk(LiveMonitorVideoLibrary::DISK)->get(LiveMonitorVideoLibrary::MANIFEST_PATH), true);
        $firstVideo = collect($manifest['videos'])->firstWhere('original_name', 'first-video.mp4');
        $secondVideo = collect($manifest['videos'])->firstWhere('original_name', 'second-video.mp4');

        Livewire::test(LiveMonitorVideos::class)
            ->call('setActiveVideo', $firstVideo['id'])
            ->assertSee('first-video.mp4');

        $manifestAfterActivation = json_decode((string) Storage::disk(LiveMonitorVideoLibrary::DISK)->get(LiveMonitorVideoLibrary::MANIFEST_PATH), true);
        $this->assertSame($firstVideo['id'], $manifestAfterActivation['active_id']);

        Livewire::test(LiveMonitorVideos::class)
            ->call('deleteVideo', $firstVideo['id'])
            ->assertSee('first-video.mp4 was deleted from the live monitor library.')
            ->assertSee('second-video.mp4');

        Storage::disk(LiveMonitorVideoLibrary::DISK)->assertMissing($firstVideo['stored_path']);
        Storage::disk(LiveMonitorVideoLibrary::DISK)->assertExists($secondVideo['stored_path']);
        Storage::disk(LiveMonitorVideoLibrary::DISK)->assertMissing(LiveMonitorVideoLibrary::ACTIVE_VIDEO_PATH);

        $manifestAfterDelete = json_decode((string) Storage::disk(LiveMonitorVideoLibrary::DISK)->get(LiveMonitorVideoLibrary::MANIFEST_PATH), true);

        $this->assertCount(1, $manifestAfterDelete['videos']);
        $this->assertSame($secondVideo['id'], $manifestAfterDelete['active_id']);
    }

    private function createSuperAdminUser(): User
    {
        $role = Role::firstOrCreate([
            'slug' => 'super_admin',
        ], [
            'name' => 'Super Admin',
            'description' => 'System-wide administrator',
        ]);

        Role::firstOrCreate([
            'slug' => 'office_admin',
        ], [
            'name' => 'Office Admin',
            'description' => 'Office-specific administrator',
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
            'office_id' => null,
        ]);
    }
}
