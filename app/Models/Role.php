<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->slug === 'super_admin';
    }

    public function isQueueMaster(): bool
    {
        return $this->slug === 'queue_master';
    }

    public function isOfficeAdmin(): bool
    {
        return $this->slug === 'office_admin';
    }
}
