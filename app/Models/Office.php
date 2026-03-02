<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'prefix',
        'description',
        'next_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class)->orderBy('created_at');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function waitingCount(): int
    {
        return $this->queueEntries()->where('status', 'waiting')->count();
    }

    public function getQueueJoinUrl(): string
    {
        return route('queue.join', ['office' => $this->slug]);
    }

    public function generateNextQueueNumber(): string
    {
        $prefix = $this->prefix ?: strtoupper(substr($this->slug, 0, 4));
        $num = $this->next_number;
        $this->increment('next_number');
        return $prefix . '-' . str_pad((string) $num, 3, '0', STR_PAD_LEFT);
    }
}
