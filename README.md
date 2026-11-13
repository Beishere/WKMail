---
title: WKMail Disposisi Surat Universitas Katolik Widya Karya Malang
emoji: 📨
colorFrom: blue
colorTo: indigo
sdk: docker
app_port: 8080
pinned: false
---

# WKMail — Sistem Disposisi & Pelacakan Surat Online
### Universitas Katolik Widya Karya Malang (UKWK)

Aplikasi web manajemen dan disposisi surat masuk universitas berbasis **Laravel 11 + Blade + Tailwind CSS + Alpine.js + SQLite**, dirancang untuk tata kelola persuratan digital di lingkungan Universitas Katolik Widya Karya Malang (UKWK).

---

## 🚀 Fitur Utama
- **Alur Kerja Disposisi 5 Tingkat**: Pengirim (Fakultas/Unit/Yayasan/Mitra) → Admin Surat Rektorat (Filter Utama & Klasifikasi) → Rektor → Wakil Rektor (I/II/III) → Unit Teknis (BAA/BAU/BKA/LPPM/LPM).
- **Klasifikasi Surat Otomatis**: Kode Sumber (A/B/C) dan Kode Bidang Departemen (B/C/D/G/Q/K) dengan saran disposisi otomatis.
- **Integrasi Penuh Microsoft Word (.docx)**:
  - Ekspor/unduh draf surat ber-Kop Surat Resmi UKWK dalam format `.docx`.
  - Impor kembali berkas `.docx` hasil edit dengan ekstraksi otomatis ke form web.
- **Pelacakan Status Real-Time (*Audit Trail*)**: `Terkirim` → `Perlu Revisi` → `Divalidasi` → `Dibaca` → `Didisposisi` → `Diproses` / `Pending` → `Selesai` / `Diarsipkan`.
- **Arsip & Pemulihan Terkontrol**: Rektor dapat membatalkan pengarsipan dengan alasan; status sebelumnya dipulihkan dan tindakan dicatat di audit trail.
- **Desain Responsif & Tema Berdasarkan Tingkat Jabatan**: UI adaptif untuk layar PC, laptop, tablet, dan smartphone dengan palet resmi UKWK (*Navy Blue* `#2d338c` dan *Orange* `#ed6623`).

---

## 📋 Alur Proses Disposisi

```text
Pengirim (Kode A/B/C) ──► Admin Surat Rektorat (Filter Utama) ──► Rektor ──► Wakil Rektor (I/II/III) ──► Unit Teknis (BAA/BAU/BKA)
```

### Klasifikasi Kode
- **Kode Sumber Surat (`KATEGORI_SUMBER`)**:
  - **Kode A**: Lingkungan Yayasan PTK Adisucipto
  - **Kode B**: Internal Universitas (Fakultas, Unit, HMJ, Alumni)
  - **Kode C**: Pihak / Entitas Eksternal & Mitra
- **Kode Bidang Departemen (`KODE_BIDANG`)**:
  - **B**: Urusan Keuangan (→ WR II)
  - **C**: Urusan Kepegawaian (→ WR II)
  - **D**: Urusan Perlengkapan / Sarpras (→ WR II)
  - **G**: Kerja Sama (→ WR I)
  - **Q**: Akademik (→ WR I)
  - **K**: Kemahasiswaan (→ WR III)

---

## 👥 Akun Uji Lokal

Akun berikut hanya untuk pengujian lokal/demo. Email `@wkmail.ac.id` adalah identitas login uji, bukan kotak surat aktif. Password sengaja sederhana agar alur tiap peran mudah diuji; jangan gunakan akun ini di produksi.

Jika database lokal Anda masih menggunakan akun demo lama, perbarui email dan password akun seed tanpa menghapus surat atau riwayat:

```bash
php artisan db:seed --class="Database\Seeders\UpdateDemoAccountCredentialsSeeder"
```

| Tingkat / Peran | Nama Pejabat / Unit | Email Uji | Password Uji |
|---|---|---|---|
| **Rektor UKWK (Tingkat I)** | Prof. Dr. Ir. Kukuk Yudiono, M.S. | `rektor@wkmail.ac.id` | `rektor123` |
| **Admin Surat Rektorat (Tingkat II)** | Sekretariat Rektorat | `admin@wkmail.ac.id` | `admin123` |
| **Wakil Rektor I (Tingkat III)** | Harsa Dhani, Ph.D. (Akademik & Kerja Sama) | `wr1@wkmail.ac.id` | `wr1123` |
| **Wakil Rektor II (Tingkat III)** | Galuh Budi Astuti, M.M. (Keuangan & Sarpras) | `wr2@wkmail.ac.id` | `wr2123` |
| **Wakil Rektor III (Tingkat III)** | Dr. Celina Tri Siwi, M.Hum. (Kemahasiswaan) | `wr3@wkmail.ac.id` | `wr3123` |
| **Unit Teknis BAA (Tingkat IV)** | Biro Administrasi Akademik | `baa@wkmail.ac.id` | `baa123` |
| **Unit Teknis BAU (Tingkat IV)** | Biro Administrasi Umum & Keuangan | `bau@wkmail.ac.id` | `bau123` |
| **Unit Teknis BKA (Tingkat IV)** | Biro Kemahasiswaan & Alumni | `bka@wkmail.ac.id` | `bka123` |
| **Unit Teknis LPPM (Tingkat IV)** | Lembaga Penelitian dan Pengabdian Masyarakat | `lppm@wkmail.ac.id` | `lppm123` |
| **Unit Teknis LPM (Tingkat IV)** | Lembaga Penjaminan Mutu | `lpm@wkmail.ac.id` | `lpm123` |
| **Unit Teknis PUSTIK (Tingkat IV)** | Pusat Teknologi Informasi dan Komunikasi | `pustik@wkmail.ac.id` | `pustik123` |
| **Yayasan (Tingkat Kehormatan)** | Yayasan PTK Adisucipto | `yayasan@wkmail.ac.id` | `yayasan123` |
| **Fakultas Teknik (Tingkat V)** | Fakultas Teknik (Pengirim) | `teknik@wkmail.ac.id` | `teknik123` |
| **Fakultas Ekonomi (Tingkat V)** | Fakultas Ekonomi & Bisnis | `feb@wkmail.ac.id` | `feb123` |
| **Fakultas Hukum (Tingkat V)** | Dr. Diah Imaningrum Susanti, S.H., M.Hum. | `fh@wkmail.ac.id` | `fh123` |
| **Fakultas Pertanian (Tingkat V)** | Fakultas Pertanian | `pertanian@wkmail.ac.id` | `pertanian123` |
| **HMJ Sistem Informasi (Tingkat V)** | HMJ Sistem Informasi eCommerce | `hmjsi@wkmail.ac.id` | `hmjsi123` |
| **Ikatan Alumni (Tingkat V)** | Pengurus Alumni UKWK | `alumni@wkmail.ac.id` | `alumni123` |
| **Mitra Eksternal (Kode C)** | Mitra Kerjasama Nasional & Internasional | `mitra@wkmail.ac.id` | `mitra123` |

---

## 🛠️ Panduan Instalasi untuk Anggota Tim

### 1. Prasyarat Sistem
- **PHP** >= 8.2 (ekstensi `zip`, `sqlite3`, `curl`, `mbstring`, `dom`)
- **Composer** (https://getcomposer.org/)
- **Node.js** >= 18 & **NPM** (https://nodejs.org/)
- **Git** (https://git-scm.com/)

### 2. Langkah Clone & Setup

```bash
# 1. Clone repositori ke komputer Anda
git clone https://github.com/Beishere/WKMail.git
cd WKMail

# 2. Pasang dependensi PHP (Composer)
composer install

# 3. Pasang dependensi Frontend (Node/NPM)
npm install

# 4. Salin berkas lingkungan (.env)
cp .env.example .env

# 5. Generate Application Key
php artisan key:generate

# 6. Jalankan migrasi database SQLite beserta data seed demo
php artisan migrate:fresh --seed

# 7. Kompilasi aset CSS dan Javascript
npm run build

# 8. Jalankan server lokal
php artisan serve
```

Akses aplikasi di browser melalui: **http://localhost:8000** (atau port yang ditampilkan terminal).
