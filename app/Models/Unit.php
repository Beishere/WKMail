<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    public const JENIS = [
        'rektorat' => 'Rektorat',
        'wakil_rektor' => 'Wakil Rektor',
        'unit_teknis' => 'Unit Teknis',
        'fakultas' => 'Fakultas',
        'hmj' => 'HMJ',
        'alumni' => 'Alumni',
        'yayasan' => 'Yayasan',
        'eksternal' => 'Eksternal',
    ];

    protected $table = 'unit';
    protected $primaryKey = 'id_unit';
    protected $fillable = ['nama_unit', 'jenis_unit', 'id_induk'];

    public function induk(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'id_induk', 'id_unit');
    }

    public function anak(): HasMany
    {
        return $this->hasMany(Unit::class, 'id_induk', 'id_unit');
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(Pengguna::class, 'id_unit', 'id_unit');
    }

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS[$this->jenis_unit] ?? $this->jenis_unit;
    }

    /** Kode sumber surat default berdasarkan jenis unit pengirim (A/B/C). */
    public function getKodeSumberDefaultAttribute(): string
    {
        return match ($this->jenis_unit) {
            'yayasan' => 'A',
            'eksternal' => 'C',
            default => 'B',
        };
    }
}
