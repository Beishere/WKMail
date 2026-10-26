<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Surat extends Model
{
    public const TERKIRIM = 'Terkirim';
    public const PERLU_REVISI = 'Perlu Revisi';
    public const DIVALIDASI = 'Divalidasi';
    public const DIBACA = 'Dibaca';
    public const DIDISPOSISI = 'Didisposisi';
    public const DIPROSES = 'Diproses';
    public const PENDING = 'Pending';
    public const SELESAI = 'Selesai';
    public const DIARSIPKAN = 'Diarsipkan';

    public const STATUSES = [
        self::TERKIRIM, self::PERLU_REVISI, self::DIVALIDASI, self::DIBACA, self::DIDISPOSISI,
        self::DIPROSES, self::PENDING, self::SELESAI, self::DIARSIPKAN,
    ];

    /** Tahapan utama untuk progress tracker (sesuai diagram alur). */
    public const TAHAPAN = [
        self::TERKIRIM, self::DIVALIDASI, self::DIBACA, self::DIDISPOSISI, self::DIPROSES, self::SELESAI,
    ];

    public const STATUS_COLORS = [
        self::TERKIRIM => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        self::PERLU_REVISI => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        self::DIVALIDASI => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        self::DIBACA => 'bg-cyan-50 text-cyan-700 ring-cyan-600/20',
        self::DIDISPOSISI => 'bg-violet-50 text-violet-700 ring-violet-600/20',
        self::DIPROSES => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        self::PENDING => 'bg-orange-50 text-orange-700 ring-orange-600/20',
        self::SELESAI => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        self::DIARSIPKAN => 'bg-slate-100 text-slate-700 ring-slate-500/20',
    ];

    protected $table = 'surat';
    protected $primaryKey = 'id_surat';

    protected $fillable = [
        'nomor_surat', 'perihal', 'isi_surat', 'tanggal_surat', 'tanggal_diterima', 'sifat_surat',
        'kode_sumber', 'kode_bidang', 'id_pengirim', 'id_template', 'id_unit_saran', 'catatan_admin',
        'status_terkini',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_diterima' => 'datetime',
        ];
    }

    // ---------------------------------------------------------------- relasi

    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengirim', 'id_pengguna');
    }

    public function kategoriSumber(): BelongsTo
    {
        return $this->belongsTo(KategoriSumber::class, 'kode_sumber', 'kode_sumber');
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(KodeBidang::class, 'kode_bidang', 'kode_bidang');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateSurat::class, 'id_template', 'id_template');
    }

    public function unitSaran(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'id_unit_saran', 'id_unit');
    }

    public function lampiran(): HasMany
    {
        return $this->hasMany(Lampiran::class, 'id_surat', 'id_surat')->where('jenis', 'lampiran');
    }

    public function bukti(): HasMany
    {
        return $this->hasMany(Lampiran::class, 'id_surat', 'id_surat')->where('jenis', 'bukti');
    }

    public function semuaLampiran(): HasMany
    {
        return $this->hasMany(Lampiran::class, 'id_surat', 'id_surat');
    }

    public function disposisi(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'id_surat', 'id_surat');
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class, 'id_surat', 'id_surat')->orderBy('waktu')->orderBy('id_riwayat');
    }

    // ---------------------------------------------------------------- scope

    /** Batasi surat yang boleh dilihat oleh pengguna sesuai perannya. */
    public function scopeVisibleTo(Builder $query, Pengguna $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isRektor()) {
            return $query->whereNotIn('status_terkini', [self::TERKIRIM, self::PERLU_REVISI]);
        }

        if ($user->isWakilRektor() || $user->isUnitTeknis()) {
            return $query->whereHas('disposisi', function (Builder $q) use ($user) {
                $q->where('id_unit_tujuan', $user->id_unit)->orWhere('id_penerima', $user->id_pengguna);
            });
        }

        return $query->where('id_pengirim', $user->id_pengguna);
    }

    public function scopeCari(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('nomor_surat', 'like', "%{$keyword}%")
                ->orWhere('perihal', 'like', "%{$keyword}%")
                ->orWhereHas('pengirim', fn (Builder $p) => $p->where('nama', 'like', "%{$keyword}%"));
        });
    }

    // ---------------------------------------------------------------- helper

    public function isVisibleTo(Pengguna $user): bool
    {
        return static::query()->visibleTo($user)->whereKey($this->getKey())->exists();
    }

    public function getKodeKlasifikasiAttribute(): ?string
    {
        if (! $this->kode_sumber && ! $this->kode_bidang) {
            return null;
        }

        return ($this->kode_sumber ?? '?') . '-' . ($this->kode_bidang ?? '?');
    }

    public function getIsUrgentAttribute(): bool
    {
        return $this->sifat_surat === 'Urgent';
    }

    /** Indeks tahapan untuk progress tracker. */
    public function getTahapIndexAttribute(): int
    {
        return match ($this->status_terkini) {
            self::TERKIRIM, self::PERLU_REVISI => 0,
            self::DIVALIDASI => 1,
            self::DIBACA => 2,
            self::DIDISPOSISI => 3,
            self::DIPROSES, self::PENDING => 4,
            self::SELESAI, self::DIARSIPKAN => 5,
            default => 0,
        };
    }

    public static function statusColor(string $status): string
    {
        return self::STATUS_COLORS[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';
    }
}
