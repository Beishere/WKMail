<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public const ADMIN = 'admin';
    public const REKTOR = 'rektor';
    public const WAKIL_REKTOR = 'wakil_rektor';
    public const UNIT_TEKNIS = 'unit_teknis';
    public const PENGIRIM = 'pengirim';

    protected $table = 'role';
    protected $primaryKey = 'id_role';
    protected $fillable = ['kode_role', 'nama_role', 'deskripsi'];

    public function pengguna(): HasMany
    {
        return $this->hasMany(Pengguna::class, 'id_role', 'id_role');
    }
}
