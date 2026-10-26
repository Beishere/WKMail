# Panduan Hosting & Deployment SIDISURAT Universitas

Tersedia **3 cara** agar aplikasi ini dapat diakses secara online oleh semua pihak (Pengirim, Admin Rektorat, Rektor, Wakil Rektor, dan Unit Teknis) dari HP maupun laptop mana pun.

---

## Opsi 1: Link Publik HTTPS Instan dari Laptop (Tanpa Daftar, Langsung Online)

Cocok untuk **demo sidang, pengujian bersama tim, atau presentasi ke dosen**.

1. Buka folder [`d:/SMT9/CAPSTONE/disposisi-surat`](file:///d:/SMT9/CAPSTONE/disposisi-surat) di File Explorer.
2. Klik 2x file **[`run-public.bat`](file:///d:/SMT9/CAPSTONE/disposisi-surat/run-public.bat)**.
3. Di jendela terminal akan muncul link publik berakhiran **`.trycloudflare.com`** (contoh: `https://xxxx-xxxx.trycloudflare.com`).
4. Bagikan link tersebut ke WhatsApp/grup — semua pihak dapat langsung membuka dan login dari HP atau laptop masing-masing selama jendela terminal tersebut tetap terbuka.

---

## Opsi 2: Cloud Hosting Gratis 24 Jam (Render.com / Railway.app via GitHub)

Project ini sudah dilengkapi [`Dockerfile`](file:///d:/SMT9/CAPSTONE/disposisi-surat/Dockerfile), [`docker-entrypoint.sh`](file:///d:/SMT9/CAPSTONE/disposisi-surat/docker-entrypoint.sh), dan [`render.yaml`](file:///d:/SMT9/CAPSTONE/disposisi-surat/render.yaml) yang otomatis menyiapkan database, menjalankan migrasi, dan mengisi akun demo saat pertama kali dinyalakan.

### Langkah-langkah:
1. Buat repository baru di **GitHub** (misalnya `sidisurat-universitas`).
2. Buka terminal di folder `d:\SMT9\CAPSTONE\disposisi-surat`, lalu jalankan:
   ```powershell
   git init
   git add .
   git commit -m "Initial commit SIDISURAT Universitas"
   git branch -M main
   git remote add origin https://github.com/USERNAME_ANDA/sidisurat-universitas.git
   git push -u origin main
   ```
3. Buka **https://render.com** (atau **https://railway.app**) dan login menggunakan akun GitHub Anda:
   - **Di Render.com**: Klik **New +** → **Blueprint** → pilih repository `sidisurat-universitas` → klik **Apply**. Render otomatis membaca file `render.yaml` dan men-deploy aplikasi Anda ke URL publik `https://sidisurat-universitas.onrender.com`.
   - **Di Railway.app**: Klik **New Project** → **Deploy from GitHub repo** → pilih repository `sidisurat-universitas` → di tab **Networking** klik **Generate Domain**.

---

## Opsi 3: Shared Hosting / cPanel (Domain Kampus atau Hosting Sendiri)

Project ini sudah dilengkapi file [`.htaccess`](file:///d:/SMT9/CAPSTONE/disposisi-surat/.htaccess) di root dan aset frontend yang sudah ter-compile di `public/build`.

1. Kompres seluruh isi folder `d:\SMT9\CAPSTONE\disposisi-surat` menjadi file `.zip` (kecuali `node_modules` dan `cloudflared.exe`).
2. Login ke **cPanel** → buka **File Manager** → masuk ke `public_html` (atau folder subdomain) → **Upload** dan **Extract** file `.zip` tersebut.
3. Pastikan versi PHP di menu **MultiPHP Manager / Select PHP Version** pada cPanel diatur ke **PHP 8.2** atau **PHP 8.3**.
4. Jika ingin tetap memakai **SQLite**, aplikasi langsung berjalan tanpa perlu membuat database MySQL. Jika ingin memakai **MySQL cPanel**, buat database di menu **MySQL Database Wizard**, lalu sesuaikan `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` pada file `.env`.
