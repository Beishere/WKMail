<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriSumber extends Model
{
    protected $table = 'kategori_sumber';
    protected $primaryKey = 'kode_sumber';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['kode_sumber', 'nama_kategori', 'keterangan'];

    public function surat(): HasMany
    {
        return $this->hasMany(Surat::class, 'kode_sumber', 'kode_sumber');
    }
}
