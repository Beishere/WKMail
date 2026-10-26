<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateSurat extends Model
{
    protected $table = 'template_surat';
    protected $primaryKey = 'id_template';
    protected $fillable = [
        'nama_template',
        'perihal_default',
        'isi_template',
        'path_kop',
        'nama_file_template',
        'path_file_template',
    ];

    public function surat(): HasMany
    {
        return $this->hasMany(Surat::class, 'id_template', 'id_template');
    }

    /**
     * Isi template dengan placeholder otomatis.
     * Placeholder: {nama_pengirim}, {unit_pengirim}, {tanggal}, {perihal}, {nip}
     */
    public function render(Pengguna $pengguna, ?string $perihal = null): string
    {
        return strtr($this->isi_template, [
            '{nama_pengirim}' => $pengguna->nama,
            '{unit_pengirim}' => $pengguna->unit?->nama_unit ?? '-',
            '{nip}' => $pengguna->nip ?? '-',
            '{tanggal}' => now()->translatedFormat('d F Y'),
            '{perihal}' => $perihal ?? $this->perihal_default,
        ]);
    }
}
