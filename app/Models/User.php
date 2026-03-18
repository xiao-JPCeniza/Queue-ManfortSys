<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    private static ?bool $supportsRecoverablePassword = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'recoverable_password',
        'profile_photo_path',
        'role_id',
        'office_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'recoverable_password',
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
            'recoverable_password' => 'encrypted',
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
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->isOfficeAdmin() && $this->office_id === $office->id;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        return route('profile.photo.show', [
            'user' => $this,
            'v' => md5($this->profile_photo_path),
        ], false);
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $initials = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $initials .= strtoupper(substr($part, 0, 1));

            if (strlen($initials) >= 2) {
                break;
            }
        }

        return $initials !== '' ? $initials : 'U';
    }

    public static function supportsRecoverablePassword(): bool
    {
        return self::$supportsRecoverablePassword ??= Schema::hasColumn('users', 'recoverable_password');
    }

    public static function withRecoverablePassword(array $attributes, ?string $recoverablePassword): array
    {
        if (! self::supportsRecoverablePassword()) {
            unset($attributes['recoverable_password']);

            return $attributes;
        }

        $attributes['recoverable_password'] = $recoverablePassword;

        return $attributes;
    }
}
