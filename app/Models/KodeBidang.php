<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KodeBidang extends Model
{
    protected $table = 'kode_bidang';
    protected $primaryKey = 'kode_bidang';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['kode_bidang', 'nama_bidang', 'keterangan', 'id_unit_wr'];

    public function unitWr(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'id_unit_wr', 'id_unit');
    }

    public function surat(): HasMany
    {
        return $this->hasMany(Surat::class, 'kode_bidang', 'kode_bidang');
    }
}
