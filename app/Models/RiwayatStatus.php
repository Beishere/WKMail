<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatus extends Model
{
    public $timestamps = false;

    protected $table = 'riwayat_status';
    protected $primaryKey = 'id_riwayat';
    protected $fillable = ['id_surat', 'id_pengguna', 'status', 'waktu', 'keterangan'];

    protected function casts(): array
    {
        return ['waktu' => 'datetime'];
    }

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class, 'id_surat', 'id_surat');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}
