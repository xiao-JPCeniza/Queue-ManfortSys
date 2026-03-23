<?php

namespace App\Livewire\SuperAdmin;

use App\Support\LiveMonitorVideoLibrary;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class LiveMonitorVideos extends Component
{
    use WithFileUploads;

    public const IDLE_MONITOR_VIDEO_MAX_KB = 3 * 1024 * 1024;

    public mixed $idleMonitorVideoUpload = null;

    public function uploadIdleMonitorVideo(): void
    {
        if (! $this->idleMonitorVideoUpload) {
            $this->addError('idleMonitorVideoUpload', 'Please choose an MP4 video to upload.');

            return;
        }

        $this->validate(
            $this->idleMonitorVideoUploadRules(),
            $this->idleMonitorVideoUploadMessages()
        );

        if ($this->videoLibrary()->findDuplicateUpload($this->idleMonitorVideoUpload)) {
            $this->addError('idleMonitorVideoUpload', 'File already exists in the live monitor library.');

            return;
        }

        try {
            $video = $this->videoLibrary()->upload($this->idleMonitorVideoUpload);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'idleMonitorVideoUpload' => 'Unable to save the uploaded video.',
            ]);
        }

        $this->reset('idleMonitorVideoUpload');
        $this->resetValidation('idleMonitorVideoUpload');
        $this->dispatch('live-monitor-video-saved');

        session()->flash('success', ($video['original_name'] ?? 'The uploaded video').' was added to the live monitor playlist and will play first.');
    }

    public function deleteVideo(string $videoId): void
    {
        $video = $this->videoLibrary()->find($videoId);

        if (! $video) {
            session()->flash('error', 'The selected video could not be found.');

            return;
        }

        $this->videoLibrary()->delete($videoId);

        session()->flash('success', ($video['original_name'] ?? 'The selected video').' was deleted from the live monitor library.');
    }

    public function setActiveVideo(string $videoId): void
    {
        $video = $this->videoLibrary()->find($videoId);

        if (! $video) {
            session()->flash('error', 'The selected video could not be found.');

            return;
        }

        $this->videoLibrary()->activate($videoId);

        session()->flash('success', ($video['original_name'] ?? 'The selected video').' will now play first on live monitors after idle time.');
    }

    public function render()
    {
        $videos = $this->videoLibrary()->listVideos();
        $activeVideo = $this->videoLibrary()->activeVideo();

        return view('livewire.super-admin.live-monitor-videos', [
            'videos' => $videos,
            'activeVideo' => $activeVideo,
            'playlistPreviewUrl' => route('super-admin.live-monitor-videos.playlist-preview'),
            'serverUploadLimitLabel' => $this->serverUploadLimitLabel(),
            'hasServerUploadLimitMismatch' => $this->hasServerUploadLimitMismatch(),
        ]);
    }

    private function videoLibrary(): LiveMonitorVideoLibrary
    {
        return app(LiveMonitorVideoLibrary::class);
    }

    public static function idleMonitorVideoUploadRules(): array
    {
        return [
            'idleMonitorVideoUpload' => ['required', 'file', 'extensions:mp4', 'max:'.self::IDLE_MONITOR_VIDEO_MAX_KB],
        ];
    }

    public static function idleMonitorVideoUploadMessages(): array
    {
        return [
            'idleMonitorVideoUpload.extensions' => 'The idle monitor video must be an MP4 file.',
            'idleMonitorVideoUpload.max' => 'The idle monitor video must not be larger than 3 GB.',
        ];
    }

    private function hasServerUploadLimitMismatch(): bool
    {
        return $this->serverUploadLimitInKilobytes() < self::IDLE_MONITOR_VIDEO_MAX_KB;
    }

    private function serverUploadLimitLabel(): string
    {
        $serverUploadLimit = $this->serverUploadLimitInKilobytes();

        if ($serverUploadLimit >= 1024 * 1024) {
            return number_format($serverUploadLimit / (1024 * 1024), 2).' GB';
        }

        if ($serverUploadLimit >= 1024) {
            return number_format($serverUploadLimit / 1024, 0).' MB';
        }

        return number_format($serverUploadLimit).' KB';
    }

    private function serverUploadLimitInKilobytes(): int
    {
        $uploadMax = $this->iniSizeToKilobytes((string) ini_get('upload_max_filesize'));
        $postMax = $this->iniSizeToKilobytes((string) ini_get('post_max_size'));

        return max(1, min($uploadMax, $postMax));
    }

    private function iniSizeToKilobytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return self::IDLE_MONITOR_VIDEO_MAX_KB;
        }

        $unit = strtolower(substr($value, -1));
        $numericValue = (float) $value;

        return match ($unit) {
            'g' => (int) round($numericValue * 1024 * 1024),
            'm' => (int) round($numericValue * 1024),
            'k' => (int) round($numericValue),
            default => max(1, (int) round($numericValue / 1024)),
        };
    }
}
