<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'personnel_id',
        'role',
        'name',
        'email',
        'password',
        'fcm_token',
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

    public function getRoleDisplayAttribute(): string
    {
        $roles = collect(explode(',', (string) ($this->role ?? '')))
            ->map(function ($role) {
                $normalizedRole = strtolower(trim((string) $role));

                return $normalizedRole === ''
                    ? null
                    : ucwords(str_replace('_', ' ', $normalizedRole));
            })
            ->filter()
            ->values();

        return $roles->isNotEmpty() ? $roles->implode(', ') : 'User';
    }

    public function getProfileInitialsAttribute(): string
    {
        $name = trim((string) ($this->name ?? 'User'));
        if ($name === '') {
            return 'U';
        }

        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstInitial = strtoupper(substr((string) ($parts[0] ?? $name), 0, 1));
        $lastPart = (string) ($parts[count($parts) - 1] ?? '');
        $lastInitial = $lastPart !== '' ? strtoupper(substr($lastPart, 0, 1)) : '';

        $initials = $firstInitial . $lastInitial;

        return $initials !== '' ? $initials : 'U';
    }

    public function getResolvedProfileImageUrlAttribute(): ?string
    {
        $directUrlCandidates = [
            trim((string) ($this->getAttribute('profile_photo_url') ?? '')),
            trim((string) ($this->getAttribute('profile_image_url') ?? '')),
            trim((string) ($this->getAttribute('avatar_url') ?? '')),
        ];

        foreach ($directUrlCandidates as $candidate) {
            if ($candidate !== '') {
                return $candidate;
            }
        }

        $pathCandidates = [
            trim((string) ($this->getAttribute('profile_photo_path') ?? '')),
            trim((string) ($this->getAttribute('avatar') ?? '')),
            trim((string) ($this->getAttribute('image_url') ?? '')),
            trim((string) ($this->getAttribute('profile_image') ?? '')),
        ];

        foreach ($pathCandidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            if (Str::startsWith($candidate, ['http://', 'https://', 'data:'])) {
                return $candidate;
            }

            $normalized = ltrim($candidate, '/');
            if ($normalized === '') {
                continue;
            }

            try {
                return Storage::url($normalized);
            } catch (\Throwable) {
                if (Str::startsWith($normalized, 'storage/')) {
                    return asset($normalized);
                }

                return asset('storage/' . $normalized);
            }
        }

        return null;
    }
}
