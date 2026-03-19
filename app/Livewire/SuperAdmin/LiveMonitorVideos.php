<?php

namespace App\Livewire\SuperAdmin;

use App\Support\LiveMonitorVideoLibrary;
use Livewire\Component;
use Livewire\WithFileUploads;

class LiveMonitorVideos extends Component
{
    use WithFileUploads;

    private const IDLE_MONITOR_VIDEO_MAX_KB = 3 * 1024 * 1024;

    public mixed $idleMonitorVideoUpload = null;

    public function uploadIdleMonitorVideo(): void
    {
        if (! $this->idleMonitorVideoUpload) {
            $this->addError('idleMonitorVideoUpload', 'Please choose an MP4 video to upload.');

            return;
        }

        $this->validate(
            [
                'idleMonitorVideoUpload' => ['required', 'file', 'mimes:mp4', 'max:'.self::IDLE_MONITOR_VIDEO_MAX_KB],
            ],
            [
                'idleMonitorVideoUpload.mimes' => 'The idle monitor video must be an MP4 file.',
                'idleMonitorVideoUpload.max' => 'The idle monitor video must not be larger than 3 GB.',
            ]
        );

        if ($this->videoLibrary()->findDuplicateUpload($this->idleMonitorVideoUpload)) {
            $this->addError('idleMonitorVideoUpload', 'File already exists in the live monitor library.');

            return;
        }

        $video = $this->videoLibrary()->upload($this->idleMonitorVideoUpload);

        $this->reset('idleMonitorVideoUpload');
        $this->resetValidation('idleMonitorVideoUpload');
        $this->dispatch('live-monitor-video-saved');

        session()->flash('success', ($video['original_name'] ?? 'The uploaded video').' is now the active live monitor video.');
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

        session()->flash('success', ($video['original_name'] ?? 'The selected video').' is now active on live monitors after idle time.');
    }

    public function render()
    {
        $videos = $this->videoLibrary()->listVideos();
        $activeVideo = $this->videoLibrary()->activeVideo();

        return view('livewire.super-admin.live-monitor-videos', [
            'videos' => $videos,
            'activeVideo' => $activeVideo,
            'activeVideoUrl' => route('media.tourism-video'),
        ]);
    }

    private function videoLibrary(): LiveMonitorVideoLibrary
    {
        return app(LiveMonitorVideoLibrary::class);
    }
}
