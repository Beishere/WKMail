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

    /**
     * Konfigurasi tema warna dashboard, sidebar, dan badge sesuai jabatan & tingkat administrator UKWK.
     */
    public function theme(): array
    {
        if ($this->isRektor()) {
            return [
                'level' => 'Tingkat I • Pimpinan Tertinggi Universitas',
                'jabatan' => 'Eksekutif Rektorat UKWK',
                'sidebar_bg' => 'bg-gradient-to-b from-[#171a4a] via-[#2d338c] to-[#12153a]',
                'topbar_border' => 'border-b-2 border-[#ed6623]',
                'banner_bg' => 'bg-gradient-to-r from-[#171a4a] via-[#2d338c] to-[#ed6623]',
                'nav_active' => 'bg-[#ed6623] text-white shadow-sm font-semibold',
                'level_badge' => 'bg-[#ed6623] text-white ring-[#ff854d]/50',
                'role_badge' => 'bg-amber-50 text-amber-900 ring-amber-400/40',
                'avatar_bg' => 'bg-[#ed6623] text-white',
                'stat_border' => 'border-t-2 border-t-[#2d338c]',
            ];
        }

        if ($this->isAdmin()) {
            return [
                'level' => 'Tingkat II • Administrator Utama Sistem',
                'jabatan' => 'Filter Utama & Sekretariat Rektorat',
                'sidebar_bg' => 'bg-gradient-to-b from-[#3e2214] via-[#7c2d12] to-[#2a150b]',
                'topbar_border' => 'border-b-2 border-[#ed6623]',
                'banner_bg' => 'bg-gradient-to-r from-[#3e2214] via-[#b43e12] to-[#ed6623]',
                'nav_active' => 'bg-[#ed6623] text-white shadow-sm font-semibold',
                'level_badge' => 'bg-amber-300 text-slate-950 ring-amber-200/60',
                'role_badge' => 'bg-orange-50 text-orange-900 ring-orange-400/40',
                'avatar_bg' => 'bg-[#ed6623] text-white',
                'stat_border' => 'border-t-2 border-t-[#ed6623]',
            ];
        }

        if ($this->isWakilRektor()) {
            return [
                'level' => 'Tingkat III • Pimpinan Koordinasi Bidang',
                'jabatan' => 'Jajaran Wakil Rektor UKWK',
                'sidebar_bg' => 'bg-gradient-to-b from-[#2e1065] via-[#4c1d95] to-[#1e1b4b]',
                'topbar_border' => 'border-b-2 border-violet-500',
                'banner_bg' => 'bg-gradient-to-r from-[#2e1065] via-[#5b21b6] to-[#ed6623]',
                'nav_active' => 'bg-violet-500 text-white shadow-sm font-semibold',
                'level_badge' => 'bg-violet-400/30 text-violet-100 ring-violet-300/40',
                'role_badge' => 'bg-violet-50 text-violet-900 ring-violet-400/40',
                'avatar_bg' => 'bg-violet-700 text-white',
                'stat_border' => 'border-t-2 border-t-violet-600',
            ];
        }

        if ($this->isUnitTeknis()) {
            return [
                'level' => 'Tingkat IV • Pelaksana Administrasi Biro & Lembaga',
                'jabatan' => 'Biro & Unit Pendukung UKWK',
                'sidebar_bg' => 'bg-gradient-to-b from-[#064e3b] via-[#065f46] to-[#042f2e]',
                'topbar_border' => 'border-b-2 border-emerald-500',
                'banner_bg' => 'bg-gradient-to-r from-[#064e3b] via-[#047857] to-[#0d9488]',
                'nav_active' => 'bg-emerald-500 text-white shadow-sm font-semibold',
                'level_badge' => 'bg-emerald-400/25 text-emerald-100 ring-emerald-300/40',
                'role_badge' => 'bg-emerald-50 text-emerald-900 ring-emerald-400/40',
                'avatar_bg' => 'bg-emerald-700 text-white',
                'stat_border' => 'border-t-2 border-t-emerald-600',
            ];
        }

        if ($this->unit?->jenis_unit === 'yayasan') {
            return [
                'level' => 'Tingkat Kehormatan • Badan Penyelenggara',
                'jabatan' => 'Yayasan PTK Adisucipto Malang',
                'sidebar_bg' => 'bg-gradient-to-b from-[#451a03] via-[#78350f] to-[#1e2260]',
                'topbar_border' => 'border-b-2 border-amber-500',
                'banner_bg' => 'bg-gradient-to-r from-[#78350f] via-[#b45309] to-[#2d338c]',
                'nav_active' => 'bg-amber-500 text-slate-950 shadow-sm font-semibold',
                'level_badge' => 'bg-amber-300 text-slate-950 ring-amber-200/60',
                'role_badge' => 'bg-amber-50 text-amber-900 ring-amber-400/40',
                'avatar_bg' => 'bg-amber-600 text-white',
                'stat_border' => 'border-t-2 border-t-amber-600',
            ];
        }

        return [
            'level' => 'Tingkat V • Civitas Akademika & Mitra Pengirim',
            'jabatan' => 'Fakultas / Prodi / HMJ / Alumni / Mitra',
            'sidebar_bg' => 'bg-gradient-to-b from-[#0c4a6e] via-[#075985] to-[#171a4a]',
            'topbar_border' => 'border-b-2 border-[#ed6623]',
            'banner_bg' => 'bg-gradient-to-r from-[#0c4a6e] via-[#2d338c] to-[#ed6623]',
            'nav_active' => 'bg-[#ed6623] text-white shadow-sm font-semibold',
            'level_badge' => 'bg-sky-400/25 text-sky-100 ring-sky-300/40',
            'role_badge' => 'bg-sky-50 text-sky-900 ring-sky-400/40',
            'avatar_bg' => 'bg-[#2d338c] text-white',
            'stat_border' => 'border-t-2 border-t-[#0284c7]',
        ];
    }
}
