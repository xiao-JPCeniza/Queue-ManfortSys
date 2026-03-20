<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\LiveMonitorVideos;
use App\Models\Role;
use App\Models\User;
use App\Support\LiveMonitorVideoLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminLiveMonitorVideosTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_the_live_monitor_videos_page(): void
    {
        $this->fakeLiveMonitorStorage();

        $superAdmin = $this->createSuperAdminUser();

        $this->actingAs($superAdmin)
            ->get(route('super-admin.live-monitor-videos'))
            ->assertOk()
            ->assertSee('Live Monitor Videos')
            ->assertSee('+ Add Video')
            ->assertSee('No uploaded idle videos yet.')
            ->assertSee('Offices')
            ->assertSee('User Management');
    }

    public function test_super_admin_can_upload_and_list_live_monitor_videos(): void
    {
        $storageRoot = $this->fakeLiveMonitorStorage();

        $superAdmin = $this->createSuperAdminUser();

        $this->actingAs($superAdmin)->post(route('super-admin.live-monitor-videos.upload'), [
            'idleMonitorVideoUpload' => UploadedFile::fake()->create('tourism-a.mp4', 1024, 'video/mp4'),
        ])->assertRedirect(route('super-admin.live-monitor-videos'));

        $this->actingAs($superAdmin)->post(route('super-admin.live-monitor-videos.upload'), [
            'idleMonitorVideoUpload' => UploadedFile::fake()->create('tourism-b.mp4', 2048, 'video/mp4'),
        ])->assertRedirect(route('super-admin.live-monitor-videos'));

        $this->actingAs($superAdmin)
            ->get(route('super-admin.live-monitor-videos'))
            ->assertOk()
            ->assertSee('tourism-a.mp4')
            ->assertSee('tourism-b.mp4');

        $this->assertFileDoesNotExist($this->liveMonitorAbsolutePath($storageRoot, LiveMonitorVideoLibrary::ACTIVE_VIDEO_PATH));

        $libraryFiles = collect(File::files($this->liveMonitorAbsolutePath($storageRoot, LiveMonitorVideoLibrary::LIBRARY_DIRECTORY)));
        $this->assertCount(2, $libraryFiles);

        $manifest = $this->readManifest($storageRoot);

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

    public function test_super_admin_can_upload_an_idle_monitor_video_through_the_browser_form(): void
    {
        $storageRoot = $this->fakeLiveMonitorStorage();

        $superAdmin = $this->createSuperAdminUser();

        $response = $this->actingAs($superAdmin)->post(route('super-admin.live-monitor-videos.upload'), [
            'idleMonitorVideoUpload' => UploadedFile::fake()->create('browser-upload.mp4', 1024, 'video/mp4'),
        ]);

        $response->assertRedirect(route('super-admin.live-monitor-videos'));

        $this->followRedirects($response)
            ->assertSee('browser-upload.mp4 is now the active live monitor video.');

        $manifest = $this->readManifest($storageRoot);

        $this->assertCount(1, $manifest['videos']);
        $this->assertSame('browser-upload.mp4', collect($manifest['videos'])->firstWhere('id', $manifest['active_id'])['original_name']);
    }

    public function test_super_admin_can_upload_an_idle_monitor_video_through_an_xhr_request(): void
    {
        $storageRoot = $this->fakeLiveMonitorStorage();

        $superAdmin = $this->createSuperAdminUser();

        $response = $this->actingAs($superAdmin)->post(
            route('super-admin.live-monitor-videos.upload'),
            [
                'idleMonitorVideoUpload' => UploadedFile::fake()->create('xhr-upload.mp4', 1024, 'video/mp4'),
            ],
            [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'xhr-upload.mp4 is now the active live monitor video.',
                'redirect_url' => route('super-admin.live-monitor-videos'),
            ]);

        $manifest = $this->readManifest($storageRoot);

        $this->assertCount(1, $manifest['videos']);
        $this->assertSame('xhr-upload.mp4', collect($manifest['videos'])->firstWhere('id', $manifest['active_id'])['original_name']);
    }

    public function test_super_admin_cannot_upload_an_idle_monitor_video_larger_than_3_gb(): void
    {
        $storageRoot = $this->fakeLiveMonitorStorage();

        $superAdmin = $this->createSuperAdminUser();

        $response = $this->from(route('super-admin.live-monitor-videos'))
            ->actingAs($superAdmin)
            ->post(route('super-admin.live-monitor-videos.upload'), [
                'idleMonitorVideoUpload' => UploadedFile::fake()->create('oversized-idle-video.mp4', 3145729, 'video/mp4'),
            ]);

        $response
            ->assertRedirect(route('super-admin.live-monitor-videos'))
            ->assertSessionHasErrors(['idleMonitorVideoUpload' => 'The idle monitor video must not be larger than 3 GB.']);

        $this->assertFileDoesNotExist($this->liveMonitorAbsolutePath($storageRoot, LiveMonitorVideoLibrary::MANIFEST_PATH));
    }

    public function test_super_admin_cannot_upload_the_same_video_filename_twice(): void
    {
        $storageRoot = $this->fakeLiveMonitorStorage();

        $superAdmin = $this->createSuperAdminUser();

        $this->actingAs($superAdmin)->post(route('super-admin.live-monitor-videos.upload'), [
            'idleMonitorVideoUpload' => UploadedFile::fake()->create('duplicate-video.mp4', 1024, 'video/mp4'),
        ])->assertRedirect(route('super-admin.live-monitor-videos'));

        $response = $this->from(route('super-admin.live-monitor-videos'))
            ->actingAs($superAdmin)
            ->post(route('super-admin.live-monitor-videos.upload'), [
                'idleMonitorVideoUpload' => UploadedFile::fake()->create('duplicate-video.mp4', 1024, 'video/mp4'),
            ]);

        $response
            ->assertRedirect(route('super-admin.live-monitor-videos'))
            ->assertSessionHasErrors(['idleMonitorVideoUpload' => 'File already exists in the live monitor library.']);

        $manifest = $this->readManifest($storageRoot);

        $this->assertCount(1, $manifest['videos']);
        $this->assertSame('duplicate-video.mp4', $manifest['videos'][0]['original_name']);
    }

    public function test_super_admin_can_activate_and_delete_uploaded_live_monitor_videos(): void
    {
        $storageRoot = $this->fakeLiveMonitorStorage();

        $superAdmin = $this->createSuperAdminUser();

        $this->actingAs($superAdmin)->post(route('super-admin.live-monitor-videos.upload'), [
            'idleMonitorVideoUpload' => UploadedFile::fake()->create('first-video.mp4', 512, 'video/mp4'),
        ])->assertRedirect(route('super-admin.live-monitor-videos'));

        $this->actingAs($superAdmin)->post(route('super-admin.live-monitor-videos.upload'), [
            'idleMonitorVideoUpload' => UploadedFile::fake()->create('second-video.mp4', 768, 'video/mp4'),
        ])->assertRedirect(route('super-admin.live-monitor-videos'));

        $manifest = $this->readManifest($storageRoot);
        $firstVideo = collect($manifest['videos'])->firstWhere('original_name', 'first-video.mp4');
        $secondVideo = collect($manifest['videos'])->firstWhere('original_name', 'second-video.mp4');

        Livewire::test(LiveMonitorVideos::class)
            ->call('setActiveVideo', $firstVideo['id'])
            ->assertSee('first-video.mp4');

        $manifestAfterActivation = $this->readManifest($storageRoot);
        $this->assertSame($firstVideo['id'], $manifestAfterActivation['active_id']);

        Livewire::test(LiveMonitorVideos::class)
            ->call('deleteVideo', $firstVideo['id'])
            ->assertSee('first-video.mp4 was deleted from the live monitor library.')
            ->assertSee('second-video.mp4');

        $this->assertFileDoesNotExist($this->liveMonitorAbsolutePath($storageRoot, $firstVideo['stored_path']));
        $this->assertFileExists($this->liveMonitorAbsolutePath($storageRoot, $secondVideo['stored_path']));
        $this->assertFileDoesNotExist($this->liveMonitorAbsolutePath($storageRoot, LiveMonitorVideoLibrary::ACTIVE_VIDEO_PATH));

        $manifestAfterDelete = $this->readManifest($storageRoot);

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

    private function fakeLiveMonitorStorage(): string
    {
        $storageRoot = storage_path('framework/testing/live-monitor-videos/'.Str::uuid());

        File::ensureDirectoryExists($storageRoot);

        config()->set('filesystems.disks.'.LiveMonitorVideoLibrary::DISK.'.root', $storageRoot);

        return $storageRoot;
    }

    private function liveMonitorAbsolutePath(string $storageRoot, string $relativePath): string
    {
        return rtrim($storageRoot, '\\/').DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
    }

    private function readManifest(string $storageRoot): array
    {
        return json_decode(
            (string) file_get_contents($this->liveMonitorAbsolutePath($storageRoot, LiveMonitorVideoLibrary::MANIFEST_PATH)),
            true
        );
    }
}
