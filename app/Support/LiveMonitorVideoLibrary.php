<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class LiveMonitorVideoLibrary
{
    public const DISK = 'public';
    public const ACTIVE_VIDEO_PATH = 'live-monitor/idle-monitor-video.mp4';
    public const LIBRARY_DIRECTORY = 'live-monitor/library';
    public const MANIFEST_PATH = 'live-monitor/videos.json';

    public function listVideos(): Collection
    {
        $manifest = $this->loadManifest();
        $activeId = $manifest['active_id'] ?? null;

        return collect($manifest['videos'] ?? [])
            ->map(function (array $video) use ($activeId): array {
                $video['is_active'] = ($video['id'] ?? null) === $activeId;

                return $video;
            })
            ->sortByDesc(function (array $video): string {
                return (string) ($video['uploaded_at'] ?? '');
            })
            ->values();
    }

    public function upload(UploadedFile $uploadedFile): array
    {
        $manifest = $this->loadManifest();
        $videoId = (string) Str::uuid();
        $stagedPath = self::LIBRARY_DIRECTORY.'/'.$videoId.'.uploading.mp4';
        $storedPath = self::LIBRARY_DIRECTORY.'/'.$videoId.'.mp4';
        $stagedAbsolutePath = $this->absolutePath($stagedPath);
        $storedAbsolutePath = $this->absolutePath($storedPath);
        $originalName = $uploadedFile->getClientOriginalName();

        $this->deleteFile($stagedPath);
        $this->ensureDirectoryExists(dirname($storedAbsolutePath));

        try {
            $uploadedFile->move(dirname($stagedAbsolutePath), basename($stagedAbsolutePath));
        } catch (\Throwable $exception) {
            throw new RuntimeException('Unable to save the uploaded live monitor video.', 0, $exception);
        }

        if (! @rename($stagedAbsolutePath, $storedAbsolutePath)) {
            @unlink($stagedAbsolutePath);

            throw new RuntimeException('Unable to finalize the uploaded live monitor video.');
        }

        $entry = [
            'id' => $videoId,
            'original_name' => $originalName,
            'stored_path' => $storedPath,
            'uploaded_at' => now('Asia/Manila')->toIso8601String(),
            'size_bytes' => @filesize($storedAbsolutePath) ?: 0,
        ];

        $videos = collect($manifest['videos'] ?? [])
            ->push($entry)
            ->values()
            ->all();

        $manifest['videos'] = $videos;
        $manifest['active_id'] = $videoId;
        $this->writeManifest($manifest);

        return $entry;
    }

    public function findDuplicateUpload(UploadedFile $uploadedFile): ?array
    {
        $normalizedOriginalName = Str::lower(trim($uploadedFile->getClientOriginalName()));

        if ($normalizedOriginalName === '') {
            return null;
        }

        $duplicateVideo = $this->listVideos()->first(function (array $video) use ($normalizedOriginalName): bool {
            return Str::lower(trim((string) ($video['original_name'] ?? ''))) === $normalizedOriginalName;
        });

        return is_array($duplicateVideo) ? $duplicateVideo : null;
    }

    public function activate(string $videoId): bool
    {
        $manifest = $this->loadManifest();
        $video = collect($manifest['videos'] ?? [])->firstWhere('id', $videoId);

        if (! is_array($video)) {
            return false;
        }

        $manifest['active_id'] = $videoId;
        $this->writeManifest($manifest);

        return true;
    }

    public function delete(string $videoId): bool
    {
        $manifest = $this->loadManifest();
        $videos = collect($manifest['videos'] ?? []);
        $videoToDelete = $videos->firstWhere('id', $videoId);

        if (! is_array($videoToDelete)) {
            return false;
        }

        $remainingVideos = $videos
            ->reject(fn (array $video): bool => ($video['id'] ?? null) === $videoId)
            ->values();

        $storedPath = (string) ($videoToDelete['stored_path'] ?? '');

        if ($storedPath !== '' && $this->exists($storedPath)) {
            $this->deleteFile($storedPath);
        }

        $manifest['videos'] = $remainingVideos->all();

        if (($manifest['active_id'] ?? null) === $videoId) {
            $replacementVideo = $remainingVideos
                ->sortByDesc(fn (array $video): string => (string) ($video['uploaded_at'] ?? ''))
                ->first();

            if (is_array($replacementVideo)) {
                $manifest['active_id'] = $replacementVideo['id'] ?? null;
            } else {
                $manifest['active_id'] = null;
                $this->deleteFile(self::ACTIVE_VIDEO_PATH);
            }
        }

        $this->writeManifest($manifest);

        return true;
    }

    public function find(string $videoId): ?array
    {
        $video = $this->listVideos()->firstWhere('id', $videoId);

        return is_array($video) ? $video : null;
    }

    public function activeVideo(): ?array
    {
        return $this->listVideos()->firstWhere('is_active', true);
    }

    public function activeVideoPath(): ?string
    {
        $activeVideo = $this->activeVideo();

        if (is_array($activeVideo) && isset($activeVideo['stored_path'])) {
            return (string) $activeVideo['stored_path'];
        }

        return $this->exists(self::ACTIVE_VIDEO_PATH)
            ? self::ACTIVE_VIDEO_PATH
            : null;
    }

    public function exists(string $relativePath): bool
    {
        return is_file($this->absolutePath($relativePath));
    }

    public function absolutePath(string $relativePath): string
    {
        $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));

        return $this->rootPath().DIRECTORY_SEPARATOR.$relativePath;
    }

    private function loadManifest(): array
    {
        $this->syncLegacyActiveVideo();

        if (! $this->exists(self::MANIFEST_PATH)) {
            return [
                'active_id' => null,
                'videos' => [],
            ];
        }

        $manifest = json_decode((string) @file_get_contents($this->absolutePath(self::MANIFEST_PATH)), true);

        if (! is_array($manifest)) {
            return [
                'active_id' => null,
                'videos' => [],
            ];
        }

        $videos = collect($manifest['videos'] ?? [])
            ->filter(function (mixed $video): bool {
                return is_array($video)
                    && isset($video['id'], $video['stored_path'])
                    && $this->exists((string) $video['stored_path']);
            })
            ->values();

        $activeId = $manifest['active_id'] ?? null;

        if ($activeId !== null && ! $videos->contains(fn (array $video): bool => ($video['id'] ?? null) === $activeId)) {
            $activeId = $videos->sortByDesc(fn (array $video): string => (string) ($video['uploaded_at'] ?? ''))->value('id');
        }

        $normalizedManifest = [
            'active_id' => $activeId,
            'videos' => $videos->all(),
        ];

        if ($normalizedManifest !== $manifest) {
            $this->writeManifest($normalizedManifest);
        }

        return $normalizedManifest;
    }

    private function writeManifest(array $manifest): void
    {
        $manifestAbsolutePath = $this->absolutePath(self::MANIFEST_PATH);
        $this->ensureDirectoryExists(dirname($manifestAbsolutePath));

        file_put_contents(
            $manifestAbsolutePath,
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function syncLegacyActiveVideo(): void
    {
        if ($this->exists(self::MANIFEST_PATH) || ! $this->exists(self::ACTIVE_VIDEO_PATH)) {
            return;
        }

        $videoId = 'legacy-'.Str::lower(Str::random(10));
        $storedPath = self::LIBRARY_DIRECTORY.'/'.$videoId.'.mp4';

        if (! $this->exists($storedPath)) {
            $storedAbsolutePath = $this->absolutePath($storedPath);
            $this->ensureDirectoryExists(dirname($storedAbsolutePath));

            if (! @copy($this->absolutePath(self::ACTIVE_VIDEO_PATH), $storedAbsolutePath)) {
                throw new RuntimeException('Unable to migrate the legacy live monitor video.');
            }
        }

        $this->writeManifest([
            'active_id' => $videoId,
            'videos' => [[
                'id' => $videoId,
                'original_name' => basename(self::ACTIVE_VIDEO_PATH),
                'stored_path' => $storedPath,
                'uploaded_at' => now('Asia/Manila')->toIso8601String(),
                'size_bytes' => @filesize($this->absolutePath($storedPath)) ?: 0,
            ]],
        ]);
    }

    private function rootPath(): string
    {
        return rtrim(
            (string) config('filesystems.disks.'.self::DISK.'.root', storage_path('app/public')),
            '/\\'
        );
    }

    private function ensureDirectoryExists(string $absoluteDirectory): void
    {
        if (is_dir($absoluteDirectory)) {
            return;
        }

        if (! mkdir($absoluteDirectory, 0755, true) && ! is_dir($absoluteDirectory)) {
            throw new RuntimeException('Unable to prepare live monitor video storage.');
        }
    }

    private function deleteFile(string $relativePath): void
    {
        $absolutePath = $this->absolutePath($relativePath);

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}
