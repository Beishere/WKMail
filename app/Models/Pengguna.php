<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use Notifiable;

    protected $table = 'pengguna';
    protected $primaryKey = 'id_pengguna';
    protected $authPasswordName = 'password_hash';

    protected $fillable = [
        'nip', 'nama', 'email', 'password_hash', 'id_role', 'id_unit', 'status_aktif',
    ];

    protected $hidden = ['password_hash', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'status_aktif' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'id_unit', 'id_unit');
    }

    public function surat(): HasMany
    {
        return $this->hasMany(Surat::class, 'id_pengirim', 'id_pengguna');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_pengguna', 'id_pengguna');
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role?->kode_role, $roles, true);
    }

    public function isAdmin(): bool { return $this->hasRole(Role::ADMIN); }
    public function isRektor(): bool { return $this->hasRole(Role::REKTOR); }
    public function isWakilRektor(): bool { return $this->hasRole(Role::WAKIL_REKTOR); }
    public function isUnitTeknis(): bool { return $this->hasRole(Role::UNIT_TEKNIS); }
    public function isPengirim(): bool { return $this->hasRole(Role::PENGIRIM); }

    public function getInisialAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->nama));
        $ini = mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1);

        return mb_strtoupper($ini ?: 'U');
    }
}
