<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    public $timestamps = false;

    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';
    protected $fillable = ['id_pengguna', 'id_surat', 'pesan', 'tipe_notifikasi', 'sudah_dibaca', 'waktu'];

    protected function casts(): array
    {
        return [
            'sudah_dibaca' => 'boolean',
            'waktu' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class, 'id_surat', 'id_surat');
    }
}
