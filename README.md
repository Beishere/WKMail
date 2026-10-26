# SIDISURAT — Sistem Disposisi Surat Online Universitas

Aplikasi web manajemen dan disposisi surat masuk universitas berbasis **Laravel 11 + Blade + Tailwind CSS + Alpine.js + SQLite**, dirancang sesuai spesifikasi lengkap pada diagram (`Diagram Alur`, `ERD`, `Mind Map`, `Use Case`, `Activity Diagram`, `Flowchart`, `Cross-Functional`, dan `Sequence Diagram`).

---

## Alur Proses Disposisi Surat

```text
Pengirim (Kode A/B/C) ──► Admin Surat Rektorat (Filter Utama) ──► Rektor ──► Wakil Rektor (I/II/III) ──► Unit Teknis (BAA/BAU/HMJ)
```

Status pelacakan surat (`Online Tracking`):
`Terkirim` → (`Perlu Revisi`) → `Divalidasi` → `Dibaca` → `Didisposisi` → `Diproses` / `Pending` → `Selesai` (atau `Diarsipkan`).

### Klasifikasi Kode
- **Kode Sumber Surat (`KATEGORI_SUMBER`)**:
  - **Kode A**: Lingkungan Yayasan
  - **Kode B**: Internal Universitas (Fakultas, Unit, HMJ, Alumni)
  - **Kode C**: Pihak / Entitas Eksternal
- **Kode Bidang Departemen (`KODE_BIDANG`)**:
  - **B**: Urusan Keuangan (→ WR II)
  - **C**: Urusan Kepegawaian (→ WR II)
  - **D**: Urusan Perlengkapan / Sarpras (→ WR II)
  - **G**: Kerja Sama (→ WR I)
  - **Q**: Akademik (→ WR I)
  - **K**: Kemahasiswaan (→ WR III)

---

## Cara Menjalankan Aplikasi

1. Masuk ke direktori proyek:
   ```powershell
   cd d:\SMT9\CAPSTONE\disposisi-surat
   ```
2. Jika ingin mereset database & mengisi ulang data demo:
   ```powershell
   php artisan migrate:fresh --seed
   ```
3. Jalankan server pengembangan Laravel:
   ```powershell
   php artisan serve
   ```
4. Buka di browser: **http://127.0.0.1:8000**

*(Halaman `/login` telah dilengkapi tombol **Login Cepat 1-Klik** untuk semua peran aktor).*

---

## Daftar Akun Demo (Password semua akun: `password`)

| Peran / Aktor | Unit | Email |
|---|---|---|
| **Admin Surat Rektorat** *(Filter Utama)* | Rektorat | `admin@univ.ac.id` |
| **Rektor** *(Pemberi Disposisi)* | Rektorat | `rektor@univ.ac.id` |
| **Wakil Rektor I** *(Akademik)* | Wakil Rektor I | `wr1@univ.ac.id` |
| **Wakil Rektor II** *(Keuangan & Sarpras)* | Wakil Rektor II | `wr2@univ.ac.id` |
| **Wakil Rektor III** *(Kemahasiswaan)* | Wakil Rektor III | `wr3@univ.ac.id` |
| **Staf Unit Teknis BAA** | Biro Administrasi Akademik | `baa@univ.ac.id` |
| **Staf Unit Teknis BAU** | Biro Administrasi Umum & Keuangan | `bau@univ.ac.id` |
| **Staf Unit Teknis HMJ** | Bagian Kemahasiswaan | `hmj@univ.ac.id` |
| **Pengirim (Kode A)** | Sekretariat Yayasan | `yayasan@univ.ac.id` |
| **Pengirim (Kode B)** | Fakultas Teknik | `ft@univ.ac.id` |
| **Pengirim (Kode B)** | Fakultas Ekonomi dan Bisnis | `feb@univ.ac.id` |
| **Pengirim (Kode B)** | HMJ Teknik Informatika | `hmjti@univ.ac.id` |
| **Pengirim (Kode B)** | Ikatan Alumni | `alumni@univ.ac.id` |
| **Pengirim (Kode C)** | PT Mitra Teknologi Nusantara | `mitra@perusahaan.co.id` |
