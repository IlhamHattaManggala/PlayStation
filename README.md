# PlayStation Rental Management System

Sistem Manajemen Rental PlayStation adalah aplikasi web berbasis **Laravel 13** yang dirancang untuk mengelola operasional bisnis rental PS secara digital, efisien, dan modern. Sistem ini mendukung pemantauan billing real-time, integrasi pesanan makanan/minuman (F&B), pengelolaan transaksi sewa bawa pulang dengan jaminan kartu identitas, ekspor laporan keuangan ke PDF & Excel yang rapi, serta kemampuan **PWA (Progressive Web App) offline** dengan sinkronisasi antrean otomatis saat koneksi internet terputus.

---

## 🚀 Fitur Utama

### 1. Monitoring Billing Real-time & Fullscreen Billing Board
*   **Billing Timer:** Pemantauan durasi waktu bermain unit PS di tempat berjalan detik demi detik secara real-time langsung di dashboard utama lengkap dengan estimasi billing yang terus ter-update tanpa reload halaman.
*   **Fullscreen Billing Board (`/admin/billing`):** Layar khusus fullscreen (papan billing) tanpa sidebar dan header untuk memonitor status seluruh unit secara penuh pada monitor sekunder kasir yang melakukan *auto-refresh* setiap 5 detik.
*   **Aksi Cepat AJAX:** Mulai Main, Checkout (Selesai), Tambah Pesanan F&B, dan Kembalikan Sewa secara instan berbasis AJAX dengan dialog konfirmasi SweetAlert2 yang intuitif.

### 2. Pemesanan F&B Terintegrasi (Merged Billing)
*   Kasir dapat menambahkan pesanan makanan, minuman, atau jajanan langsung ke unit PlayStation yang sedang aktif bermain di tempat.
*   Pada saat checkout (`endPlay`), sistem secara otomatis menghitung total durasi bermain ditambah dengan jumlah tagihan seluruh produk F&B yang dipesan (Merged Billing) ke dalam satu struk pembayaran tunggal.
*   Stok produk F&B akan terpotong secara otomatis sesuai dengan jumlah pesanan dan akan kembali bertambah apabila transaksi dibatalkan.

### 3. Manajemen Unit, Kategori & Tarif (AJAX CRUD)
*   **Kelola Unit PS:** CRUD unit/ruangan PlayStation (Nama Unit, Tipe PS3/PS4/PS5, Status, Keterangan) dengan penguncian keamanan (unit terpakai tidak dapat dihapus atau diubah tipenya).
*   **Kelola Tarif (Rates):** Pengaturan tarif sewa per jam (main di tempat), sewa harian (bawa pulang), maupun tarif sewa setengah hari berdasarkan jenis tipe PlayStation.
*   **Kelola Produk F&B:** Manajemen inventaris makanan/minuman dengan kategori produk, harga, dan pelacakan sisa stok.

### 4. Transaksi Sewa Bawa Pulang & Jaminan Identitas
*   Pendaftaran sewa bawa pulang wajib menyertakan unggahan foto bukti jaminan identitas (KTP, Kartu Pelajar, dll) yang langsung disimpan ke penyimpanan publik.
*   Akses cepat verifikasi: Kasir dapat langsung meninjau berkas jaminan penyewa dengan mengklik tautan *“Lihat Jaminan”* di baris riwayat transaksi.
*   Mendukung durasi sewa pecahan (misalnya `0.5` hari atau `1.5` hari) dengan kalkulasi harga proporsional menggunakan kombinasi tarif harian dan tarif setengah hari.

### 5. Laporan Keuangan Dinamis (Filter, PDF & Excel)
*   **Live Filter & Live Search:** Filter laporan keuangan instan (Harian, Mingguan, Bulanan, Tahunan) dan pencarian langsung di tabel tanpa memicu reload halaman web.
*   **Ekspor PDF / Cetak:** Layout khusus media print (`@media print`) yang menyembunyikan elemen dashboard (sidebar, navbar, filter) untuk menghasilkan dokumen PDF/cetak fisik yang formal dan rapi.
*   **Ekspor Excel (.xls)**: Menghasilkan berkas Excel yang diformat dengan menggabungkan sel (*merge cells*) pada bagian header, serta memformat kolom nominal secara native dengan simbol Rupiah (`Rp.`) menggunakan properti Excel `mso-number-format`.

### 6. PWA Offline & Sinkronisasi Otomatis (Sequential Sync)
*   **Akses Offline:** Memanfaatkan Service Worker dengan strategi caching *Network-First* agar aplikasi kasir tetap bisa dibuka dan beroperasi meskipun tanpa jaringan internet.
*   **Antrean Offline (IndexedDB / Dexie.js):** Semua transaksi (Mulai Main, Tambah Order F&B, Checkout, dll) yang dilakukan dalam kondisi offline disimpan secara lokal di browser.
*   **Sequential Sync:** Begitu koneksi internet terdeteksi pulih (`online`), sistem secara otomatis mengunggah seluruh antrean transaksi di IndexedDB secara berurutan untuk menjaga integritas data di database utama.
*   **Indikator Koneksi:** Tampilan badge status koneksi (*Online*, *Syncing*, *Offline*) di navbar utama agar kasir tahu kondisi sinkronisasi aplikasi.

---

## 🛠️ Spesifikasi Teknologi

*   **Backend:** PHP 8.3+, Laravel 13.x
*   **Database:** MySQL / MariaDB
*   **Frontend Styling:** Tailwind CSS v4 (via Vite)
*   **Frontend Logic:** Vanilla Javascript, AJAX (Fetch API), Dexie.js (IndexedDB library)
*   **UI Components:** FontAwesome 6, SweetAlert2, Google Fonts (Outfit)

---

## ⚙️ Cara Menjalankan Aplikasi Secara Lokal

### 1. Prasyarat
Pastikan server lokal Anda sudah terinstall:
*   PHP >= 8.2 (direkomendasikan PHP 8.3)
*   Composer
*   Node.js & NPM
*   MySQL/MariaDB Database Server (misalnya menggunakan XAMPP atau Laragon)

### 2. Kloning dan Instalasi Dependensi
Jalankan di terminal Anda:
```bash
# Install package PHP
composer install

# Install package Javascript
npm install
```

### 3. Konfigurasi Lingkungan (`.env`)
Salin file `.env.example` menjadi `.env` dan sesuaikan pengaturan database Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rental_playstation
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi & Seeder Database
Buat database baru bernama `rental_playstation` di database server Anda, kemudian jalankan:
```bash
php artisan migrate:fresh --seed
```
*Perintah di atas akan menyusun struktur tabel sekaligus mengisi data default meliputi:*
*   1 Akun Admin
*   Daftar Tarif PlayStation (PS3, PS4, PS5)
*   Daftar Unit PlayStation default
*   Daftar Inventaris Produk F&B default
*   Pengaturan Awal Identitas Aplikasi

### 5. Kompilasi Aset Frontend (Vite)
Jalankan compiler aset:
```bash
# Untuk mode pengembangan (live reload)
npm run dev

# ATAU untuk membuat build produksi
npm run build
```

### 6. Jalankan Server Laravel
Jalankan perintah serve Laravel:
```bash
php artisan serve
```
Akses aplikasi melalui browser Anda pada tautan: **`http://127.0.0.1:8000`** (Akan dialihkan otomatis ke halaman login admin di **`http://127.0.0.1:8000/admin`**).

### 7. Kredensial Login Default
*   **Email:** `admin@gmail.com`
*   **Password:** `password`

*Anda dapat mengubah email dan password admin ini kapan saja melalui menu **Pengaturan Aplikasi** di sidebar panel admin setelah berhasil masuk.*

---

## 🧪 Pengujian Fitur (Automated Tests)

Sistem ini dilengkapi dengan 17 pengujian fitur otomatis (*Feature Tests*) untuk memverifikasi keakuratan kalkulasi billing, pemesanan F&B, konstrain keamanan unit, serta pengaturan aplikasi.

Jalankan perintah berikut untuk menguji seluruh suite pengujian:
```bash
php artisan test
```

Semua tes dijamin lulus 100% untuk menjaga integritas logika bisnis aplikasi.

---

## 📂 Struktur Rute Prefiks `/admin`

Seluruh rute operasional kasir dilindungi di bawah middleware otentikasi dengan prefiks `/admin`:

*   **Autentikasi:** `/admin/login` & `/admin/logout`
*   **Utama:** Dashboard Utama (`/admin`), Fullscreen Billing Board (`/admin/billing`)
*   **Kelola Data:** Unit PS (`/admin/units`), Tarif PS (`/admin/rates`), Produk F&B (`/admin/products`)
*   **Operasional:** Riwayat Transaksi (`/admin/transactions`), Laporan Pendapatan (`/admin/reports`), Pengaturan Aplikasi (`/admin/settings`)

---

*Dikembangkan dengan penuh dedikasi untuk efisiensi bisnis Rental PlayStation Anda.* 🎮💸
