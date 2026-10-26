<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lampiran extends Model
{
    protected $table = 'lampiran';
    protected $primaryKey = 'id_lampiran';
    protected $fillable = ['id_surat', 'id_disposisi', 'jenis', 'nama_file', 'path_file', 'ukuran_kb'];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class, 'id_surat', 'id_surat');
    }

    public function disposisi(): BelongsTo
    {
        return $this->belongsTo(Disposisi::class, 'id_disposisi', 'id_disposisi');
    }

    public function getUkuranLabelAttribute(): string
    {
        return $this->ukuran_kb >= 1024
            ? number_format($this->ukuran_kb / 1024, 1) . ' MB'
            : $this->ukuran_kb . ' KB';
    }
}
