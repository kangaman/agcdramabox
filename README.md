# 🎬 DramaFlix - Web Streaming Platform & PWA

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![PWA](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)
![Status](https://img.shields.io/badge/Status-Production%20Ready-success?style=for-the-badge)

**DramaFlix** adalah aplikasi web streaming modern yang dibangun menggunakan PHP Native dengan arsitektur MVC sederhana. Aplikasi ini dirancang untuk kecepatan, keamanan, dan pengalaman pengguna yang optimal, serta telah mendukung teknologi **PWA (Progressive Web App)** sehingga dapat diinstal di perangkat mobile layaknya aplikasi native.

---

## ✨ Fitur Unggulan

### 📱 Sisi Pengguna (Frontend)
* **Progressive Web App (PWA):** Dapat diinstal di Android & iOS (Add to Home Screen) dan berjalan offline (cache dasar).
* **Modern Dark UI:** Antarmuka gelap premium ala Netflix yang responsif di semua perangkat.
* **Smart Player:**
    * Support streaming HLS (`.m3u8`).
    * **Auto Next Episode** & Cinema Mode.
    * **Resume Playback:** Menyimpan posisi episode terakhir yang ditonton.
* **Fitur Personalisasi:**
    * Riwayat Tontonan (tersimpan di Database & LocalStorage).
    * Daftar Favorit ("My List").
* **Sistem Membership:** Akses konten premium (VIP) vs Gratis dengan halaman penawaran paket yang terintegrasi WhatsApp.

### 🛠️ Sisi Admin (Dashboard)
* **Dashboard Real-time:** Grafik pendaftaran user, statistik tontonan, dan log aktivitas member.
* **Manajemen Pengguna:** Tambah, edit, hapus user, dan aktivasi status VIP secara manual.
* **Manajemen Paket (Plans):** Membuat dan mengedit harga serta durasi paket langganan.
* **Backup & Restore System:**
    * Backup database otomatis ke server atau download lokal.
    * Restore database instan dari file backup yang tersedia.
* **Maintenance Mode Canggih:** Halaman perbaikan interaktif dengan mini-game "Snake" agar pengunjung tidak bosan.

---

## 🚀 Teknologi yang Digunakan

* **Backend:** PHP Native (PDO, OOP).
* **Database:** MySQL / MariaDB.
* **Frontend:** HTML5, CSS3 (Variables), JavaScript (Vanilla).
* **Libraries & Plugins:**
    * `HLS.js` (Video Player)
    * `Swiper.js` (Hero Slider)
    * `Chart.js` (Grafik Statistik)
    * `SweetAlert2` (Notifikasi Modern)
    * `DataTables` (Tabel Admin Interaktif)
    * `RemixIcon` (Ikon Vektor)

---

## 📂 Struktur Folder

```text
/
├── app/                # Logika Backend (Config, Database, Auth, ApiHandler)
├── assets/             # File Statis (CSS, JS, Images)
├── backups/            # Penyimpanan file backup database (.sql) - Terproteksi
├── views/              # Tampilan Halaman (View)
│   ├── auth/           # Halaman Login & Register
│   ├── dashboard/      # Panel Admin & User (Overview, Users, Plans, Backup)
│   └── public/         # Halaman Depan (Home, Watch, Terms)
├── .htaccess           # Konfigurasi Security, Routing, & Cache
├── index.php           # Router Utama & Entry Point
├── manifest.json       # Konfigurasi PWA
├── robots.txt          # SEO Crawling
├── sitemap.php         # Sitemap Generator Dinamis
└── sw.js               # Service Worker PWA
```

---

## ⚙️ Instalasi

1. Clone repository:
   ```bash
   git clone https://github.com/username/nama-repo.git
   ```

2. Upload ke server / hosting Anda.

3. Pastikan `.htaccess` aktif agar routing bekerja optimal.

4. Sesuaikan konfigurasi aplikasi di:
   ```
   app/Config.php
   ```

5. Jika menggunakan database, sesuaikan kredensial di:
   ```
   app/Database.php
   ```

6. Pastikan server menggunakan PHP **7.4+**.

---

## 📡 Cara Kerja Downloader

1. Pengguna menyalin URL video dari platform sosial.
2. Menempelkannya ke input utama di homepage.
3. Sistem memproses URL melalui API Handler.
4. Link unduhan tersedia dalam beberapa detik.

Semua proses parsing dilakukan oleh:
```
app/ApiHandler.php
```

---

## 🖥 Teknologi yang Digunakan

- PHP Native (tanpa framework)
- HTML5, CSS, JavaScript
- PWA (Service Worker + Manifest)
- Apache rewrite untuk routing
- SEO Optimization (robots.txt, sitemap)

---

## 📱 PWA Support

Aplikasi dapat di-install layaknya aplikasi mobile:

- `manifest.json` mengatur tampilan ikon dan metadata aplikasi.
- `sw.js` mengelola caching untuk loading cepat.

---

## 🔐 Keamanan

- Validasi input URL sebelum diproses.
- `.htaccess` membatasi akses langsung ke file sensitif.
- API Handler dibatasi untuk jenis request tertentu.

---

## 🛠 Catatan Pengembangan

Struktur aplikasi mudah diperluas ke fitur tambahan seperti:

- Konversi video ke MP3
- Riwayat download
- Shortlink generator
- Penjadwalan unduhan

---

## 🤝 Kontribusi

Kontribusi terbuka!  
Silakan buat **Issue** atau **Pull Request** untuk perbaikan bug, penambahan fitur, atau optimasi performa.

---

## 📄 Lisensi

Silakan tentukan lisensi sesuai kebutuhan (MIT / GPL / Proprietary).
