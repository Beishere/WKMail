<?php

namespace App\Services;

use App\Models\Disposisi;
use App\Models\Lampiran;
use App\Models\Notifikasi;
use App\Models\Pengguna;
use App\Models\RiwayatStatus;
use App\Models\Role;
use App\Models\Surat;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Mesin alur kerja disposisi surat.
 *
 * Terkirim -> (Perlu Revisi) -> Divalidasi -> Dibaca -> Didisposisi -> Diproses/Pending -> Selesai
 *                                                  \-> Diarsipkan (tidak perlu disposisi)
 *
 * Setiap perubahan status dicatat pada RIWAYAT_STATUS dan memicu NOTIFIKASI ke pihak terkait.
 */
class DisposisiService
{
    // ================================================================ PENGIRIM

    /** @param UploadedFile[] $files */
    public function kirimSurat(Pengguna $pengirim, array $data, array $files = []): Surat
    {
        return DB::transaction(function () use ($pengirim, $data, $files) {
            $surat = Surat::create([
                'nomor_surat' => ($data['nomor_surat'] ?? null) ?: $this->generateNomor(),
                'perihal' => $data['perihal'],
                'isi_surat' => $data['isi_surat'] ?? null,
                'tanggal_surat' => $data['tanggal_surat'] ?? now()->toDateString(),
                'sifat_surat' => $data['sifat_surat'] ?? 'Biasa',
                'id_template' => $data['id_template'] ?? null,
                'id_pengirim' => $pengirim->id_pengguna,
                'status_terkini' => Surat::TERKIRIM,
            ]);

            $this->simpanLampiran($surat, $files);
            $this->catat($surat, $pengirim, Surat::TERKIRIM, 'Surat dikirim oleh ' . $pengirim->nama);

            $this->notify(
                $this->penggunaRole(Role::ADMIN),
                $surat,
                ($surat->is_urgent ? '[URGENT] ' : '') . "Surat baru \"{$surat->perihal}\" dari {$pengirim->nama} menunggu validasi.",
                'surat_baru'
            );

            return $surat;
        });
    }

    /** @param UploadedFile[] $files */
    public function kirimUlang(Surat $surat, Pengguna $pengirim, array $data, array $files = []): Surat
    {
        return DB::transaction(function () use ($surat, $pengirim, $data, $files) {
            $surat->update([
                'perihal' => $data['perihal'],
                'isi_surat' => $data['isi_surat'] ?? null,
                'tanggal_surat' => $data['tanggal_surat'] ?? $surat->tanggal_surat,
                'sifat_surat' => $data['sifat_surat'] ?? $surat->sifat_surat,
                'status_terkini' => Surat::TERKIRIM,
            ]);

            $this->simpanLampiran($surat, $files);
            $this->catat($surat, $pengirim, Surat::TERKIRIM, 'Surat direvisi dan dikirim ulang oleh pengirim');

            $this->notify(
                $this->penggunaRole(Role::ADMIN),
                $surat,
                "Surat \"{$surat->perihal}\" telah direvisi oleh {$pengirim->nama} dan menunggu validasi ulang.",
                'surat_revisi'
            );

            return $surat;
        });
    }

    // ================================================================ ADMIN REKTORAT

    public function validasi(Surat $surat, Pengguna $admin, array $data): void
    {
        DB::transaction(function () use ($surat, $admin, $data) {
            $surat->update([
                'kode_sumber' => $data['kode_sumber'],
                'kode_bidang' => $data['kode_bidang'],
                'id_unit_saran' => $data['id_unit_saran'] ?? null,
                'catatan_admin' => $data['catatan_admin'] ?? null,
                'tanggal_diterima' => now(),
                'status_terkini' => Surat::DIVALIDASI,
            ]);

            $surat->load('kategoriSumber', 'bidang', 'unitSaran');
            $ket = "Divalidasi & diklasifikasi: kode {$surat->kode_klasifikasi} "
                . "({$surat->kategoriSumber?->nama_kategori} / {$surat->bidang?->nama_bidang})";
            if ($surat->unitSaran) {
                $ket .= ". Saran tujuan: {$surat->unitSaran->nama_unit}";
            }
            $this->catat($surat, $admin, Surat::DIVALIDASI, $ket);

            $this->notify(
                $this->penggunaRole(Role::REKTOR),
                $surat,
                ($surat->is_urgent ? '[URGENT] ' : '') . "Surat \"{$surat->perihal}\" telah divalidasi dan menunggu disposisi Anda.",
                'surat_divalidasi'
            );
            $this->notify($surat->pengirim, $surat, "Surat \"{$surat->perihal}\" telah divalidasi oleh Rektorat.", 'status');
        });
    }

    public function tolak(Surat $surat, Pengguna $admin, string $catatan): void
    {
        DB::transaction(function () use ($surat, $admin, $catatan) {
            $surat->update(['status_terkini' => Surat::PERLU_REVISI, 'catatan_admin' => $catatan]);
            $this->catat($surat, $admin, Surat::PERLU_REVISI, 'Dikembalikan untuk revisi: ' . $catatan);
            $this->notify(
                $surat->pengirim,
                $surat,
                "Surat \"{$surat->perihal}\" perlu direvisi. Catatan: {$catatan}",
                'perlu_revisi'
            );
        });
    }

    // ================================================================ REKTOR

    public function tandaiDibaca(Surat $surat, Pengguna $rektor): void
    {
        if ($surat->status_terkini !== Surat::DIVALIDASI) {
            return;
        }

        $surat->update(['status_terkini' => Surat::DIBACA]);
        $this->catat($surat, $rektor, Surat::DIBACA, 'Surat dibaca oleh Rektor');
        $this->notify($surat->pengirim, $surat, "Surat \"{$surat->perihal}\" telah dibaca oleh Rektor.", 'status');
    }

    public function arsipkan(Surat $surat, Pengguna $rektor, ?string $catatan): void
    {
        DB::transaction(function () use ($surat, $rektor, $catatan) {
            $surat->update(['status_terkini' => Surat::DIARSIPKAN]);
            $this->catat($surat, $rektor, Surat::DIARSIPKAN, 'Tidak memerlukan disposisi, diarsipkan sebagai informasi'
                . ($catatan ? ". Catatan: {$catatan}" : ''));
            $this->notify($surat->pengirim, $surat, "Surat \"{$surat->perihal}\" telah diterima Rektor dan diarsipkan sebagai informasi.", 'selesai');
        });
    }

    /** Rektor -> Wakil Rektor (bisa lebih dari satu). */
    public function disposisiRektor(Surat $surat, Pengguna $rektor, array $data): void
    {
        DB::transaction(function () use ($surat, $rektor, $data) {
            $units = Unit::whereIn('id_unit', $data['unit_tujuan'])->get();

            foreach ($units as $unit) {
                $this->buatDisposisi($surat, $rektor, $unit, $data);
            }

            $surat->update(['status_terkini' => Surat::DIDISPOSISI]);
            $this->catat($surat, $rektor, Surat::DIDISPOSISI,
                'Rektor mendisposisikan ke ' . $units->pluck('nama_unit')->join(', ') . '. Instruksi: ' . $data['instruksi']);
            $this->notify($surat->pengirim, $surat, "Surat \"{$surat->perihal}\" telah didisposisikan oleh Rektor.", 'status');
        });
    }

    // ================================================================ WAKIL REKTOR / UNIT TEKNIS

    public function bacaDisposisi(Disposisi $disposisi, Pengguna $user): void
    {
        if ($disposisi->status !== Surat::TERKIRIM) {
            return;
        }

        $disposisi->update(['status' => Surat::DIBACA]);
        $this->catat($disposisi->surat, $user, Surat::DIBACA,
            "Disposisi dibaca oleh {$user->nama} ({$disposisi->unitTujuan->nama_unit})");
    }

    /** Wakil Rektor -> Unit Teknis. */
    public function teruskan(Disposisi $induk, Pengguna $wr, array $data): void
    {
        DB::transaction(function () use ($induk, $wr, $data) {
            $surat = $induk->surat;
            $units = Unit::whereIn('id_unit', $data['unit_tujuan'])->get();

            foreach ($units as $unit) {
                $this->buatDisposisi($surat, $wr, $unit, $data, $induk);
            }

            $induk->update(['status' => Surat::DIDISPOSISI]);
            $this->catat($surat, $wr, Surat::DIDISPOSISI,
                "{$induk->unitTujuan->nama_unit} meneruskan disposisi ke " . $units->pluck('nama_unit')->join(', ')
                . '. Instruksi: ' . $data['instruksi']);
            $this->sinkronStatusSurat($surat);
        });
    }

    public function updateProgres(Disposisi $disposisi, Pengguna $user, string $status, ?string $keterangan): void
    {
        DB::transaction(function () use ($disposisi, $user, $status, $keterangan) {
            $disposisi->update(['status' => $status]);
            $surat = $disposisi->surat;

            $this->catat($surat, $user, $status,
                "{$disposisi->unitTujuan->nama_unit}: status '{$status}'" . ($keterangan ? " — {$keterangan}" : ''));

            $this->sinkronStatusSurat($surat);

            $this->notify(
                collect([$surat->pengirim, $disposisi->pemberi])->filter()->unique('id_pengguna'),
                $surat,
                "Surat \"{$surat->perihal}\" sedang ditindaklanjuti oleh {$disposisi->unitTujuan->nama_unit} (status: {$status}).",
                'status'
            );
        });
    }

    /** @param UploadedFile[] $files */
    public function selesaikan(Disposisi $disposisi, Pengguna $user, ?string $keterangan, array $files = []): void
    {
        DB::transaction(function () use ($disposisi, $user, $keterangan, $files) {
            $surat = $disposisi->surat;
            $disposisi->update(['status' => Surat::SELESAI]);
            $this->simpanLampiran($surat, $files, 'bukti', $disposisi);

            $this->catat($surat, $user, Surat::SELESAI,
                "{$disposisi->unitTujuan->nama_unit} menyelesaikan tindak lanjut" . ($keterangan ? ": {$keterangan}" : '')
                . (count($files) ? ' (' . count($files) . ' bukti diunggah)' : ''));

            // Naikkan status ke disposisi induk bila seluruh turunannya selesai
            $induk = $disposisi->induk;
            while ($induk) {
                if ($induk->turunan()->where('status', '!=', Surat::SELESAI)->exists()) {
                    break;
                }
                $induk->update(['status' => Surat::SELESAI]);
                $induk = $induk->induk;
            }

            if ($disposisi->pemberi) {
                $this->notify($disposisi->pemberi, $surat,
                    "{$disposisi->unitTujuan->nama_unit} telah menyelesaikan disposisi surat \"{$surat->perihal}\".", 'status');
            }

            $this->sinkronStatusSurat($surat, $user);
        });
    }

    // ================================================================ HELPER

    /**
     * Hitung status surat berdasarkan status seluruh disposisi.
     */
    public function sinkronStatusSurat(Surat $surat, ?Pengguna $user = null): void
    {
        $semua = $surat->disposisi()->get();
        if ($semua->isEmpty()) {
            return;
        }

        $akar = $semua->whereNull('id_induk_disposisi');
        $statusBaru = match (true) {
            $akar->every(fn ($d) => $d->status === Surat::SELESAI) => Surat::SELESAI,
            $semua->contains('status', Surat::DIPROSES) => Surat::DIPROSES,
            $semua->contains('status', Surat::PENDING) => Surat::PENDING,
            $semua->contains('status', Surat::SELESAI) => Surat::DIPROSES,
            default => Surat::DIDISPOSISI,
        };

        if ($statusBaru === $surat->status_terkini) {
            return;
        }

        $surat->update(['status_terkini' => $statusBaru]);

        if ($statusBaru === Surat::SELESAI) {
            $this->catat($surat, $user, Surat::SELESAI, 'Seluruh disposisi telah selesai ditindaklanjuti. Surat dinyatakan SELESAI.');
            $this->notify($surat->pengirim, $surat, "Surat \"{$surat->perihal}\" telah SELESAI ditindaklanjuti.", 'selesai');
            $this->notify($this->penggunaRole(Role::ADMIN)->merge($this->penggunaRole(Role::REKTOR)), $surat,
                "Surat \"{$surat->perihal}\" telah selesai ditindaklanjuti.", 'selesai');
        }
    }

    protected function buatDisposisi(Surat $surat, Pengguna $pemberi, Unit $unit, array $data, ?Disposisi $induk = null): Disposisi
    {
        $penerima = $unit->pengguna()->where('status_aktif', true)->orderBy('id_pengguna')->first();

        $disposisi = Disposisi::create([
            'id_surat' => $surat->id_surat,
            'id_induk_disposisi' => $induk?->id_disposisi,
            'id_pemberi' => $pemberi->id_pengguna,
            'id_penerima' => $penerima?->id_pengguna,
            'id_unit_tujuan' => $unit->id_unit,
            'instruksi' => $data['instruksi'],
            'catatan' => $data['catatan'] ?? null,
            'tanggal_disposisi' => now(),
            'batas_waktu' => $data['batas_waktu'] ?? null,
            'status' => Surat::TERKIRIM,
        ]);

        $this->notify(
            $unit->pengguna()->where('status_aktif', true)->get(),
            $surat,
            ($surat->is_urgent ? '[URGENT] ' : '') . "Disposisi baru dari {$pemberi->nama}: \"{$surat->perihal}\".",
            'disposisi_baru'
        );

        return $disposisi;
    }

    /** @param UploadedFile[] $files */
    protected function simpanLampiran(Surat $surat, array $files, string $jenis = 'lampiran', ?Disposisi $disposisi = null): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $path = $file->store("surat/{$surat->id_surat}/{$jenis}", 'local');
            Lampiran::create([
                'id_surat' => $surat->id_surat,
                'id_disposisi' => $disposisi?->id_disposisi,
                'jenis' => $jenis,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'ukuran_kb' => (int) ceil($file->getSize() / 1024),
            ]);
        }
    }

    public function catat(Surat $surat, ?Pengguna $user, string $status, ?string $keterangan = null): RiwayatStatus
    {
        return RiwayatStatus::create([
            'id_surat' => $surat->id_surat,
            'id_pengguna' => $user?->id_pengguna,
            'status' => $status,
            'waktu' => now(),
            'keterangan' => $keterangan,
        ]);
    }

    /** @param Pengguna|Collection<int, Pengguna>|null $users */
    public function notify(Pengguna|Collection|null $users, ?Surat $surat, string $pesan, string $tipe): void
    {
        if (! $users) {
            return;
        }

        $users = $users instanceof Pengguna ? collect([$users]) : $users;

        foreach ($users->unique('id_pengguna') as $user) {
            Notifikasi::create([
                'id_pengguna' => $user->id_pengguna,
                'id_surat' => $surat?->id_surat,
                'pesan' => $pesan,
                'tipe_notifikasi' => $tipe,
                'sudah_dibaca' => false,
                'waktu' => now(),
            ]);
        }
    }

    protected function penggunaRole(string $kodeRole): Collection
    {
        return Pengguna::where('status_aktif', true)
            ->whereHas('role', fn ($q) => $q->where('kode_role', $kodeRole))
            ->get();
    }

    protected function generateNomor(): string
    {
        $prefix = 'SRT/' . now()->format('Y/m') . '/';
        $urut = Surat::where('nomor_surat', 'like', $prefix . '%')->count() + 1;

        do {
            $nomor = $prefix . str_pad((string) $urut++, 4, '0', STR_PAD_LEFT);
        } while (Surat::where('nomor_surat', $nomor)->exists());

        return $nomor;
    }
}
