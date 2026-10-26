<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disposisi extends Model
{
    protected $table = 'disposisi';
    protected $primaryKey = 'id_disposisi';

    protected $fillable = [
        'id_surat', 'id_induk_disposisi', 'id_pemberi', 'id_penerima', 'id_unit_tujuan',
        'instruksi', 'catatan', 'tanggal_disposisi', 'batas_waktu', 'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_disposisi' => 'datetime',
            'batas_waktu' => 'date',
        ];
    }

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class, 'id_surat', 'id_surat');
    }

    public function induk(): BelongsTo
    {
        return $this->belongsTo(Disposisi::class, 'id_induk_disposisi', 'id_disposisi');
    }

    public function turunan(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'id_induk_disposisi', 'id_disposisi');
    }

    public function pemberi(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pemberi', 'id_pengguna');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_penerima', 'id_pengguna');
    }

    public function unitTujuan(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'id_unit_tujuan', 'id_unit');
    }

    public function bukti(): HasMany
    {
        return $this->hasMany(Lampiran::class, 'id_disposisi', 'id_disposisi');
    }

    /** Disposisi yang ditujukan ke pengguna (melalui unit atau penerima langsung). */
    public function scopeUntuk(Builder $query, Pengguna $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('id_unit_tujuan', $user->id_unit)->orWhere('id_penerima', $user->id_pengguna);
        });
    }

    public function isUntuk(Pengguna $user): bool
    {
        return $this->id_unit_tujuan === $user->id_unit || $this->id_penerima === $user->id_pengguna;
    }

    public function getTerlambatAttribute(): bool
    {
        return $this->batas_waktu
            && ! in_array($this->status, [Surat::SELESAI, Surat::DIDISPOSISI], true)
            && $this->batas_waktu->endOfDay()->isPast();
    }
}
