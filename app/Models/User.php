<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'office_id',
        'window_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role && $this->role->slug === 'super_admin';
    }

    public function isQueueMaster(): bool
    {
        return $this->role && $this->role->slug === 'queue_master';
    }

    public function isOfficeAdmin(): bool
    {
        return $this->role && $this->role->slug === 'office_admin';
    }

    public function canAccessOffice(Office $office): bool
    {
        if ($this->isSuperAdmin() || $this->isQueueMaster()) {
            return true;
        }
        return $this->isOfficeAdmin() && $this->office_id === $office->id;
    }
}
