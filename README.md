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

## ⚙️ Instalasi & Konfigurasi

### 1. **Persiapan Database**
Buat database baru di MySQL, contoh: `dramaflix_db`.

Buat tabel berikut:

**users**  
(id, username, password, role, active_until, created_at)

**history**  
(id, user_id, book_id, title, cover, episode, total_eps, updated_at)

**plans**  
(id, name, price, duration, features)

**favorites** (opsional)

---

### 2. **Konfigurasi Koneksi**
Edit file:

```
app/Database.php
```

Isi kredensial database Anda.

---

### 3. **Setup Folder Backup**
```
mkdir backups
chmod 755 backups
```

---

### 4. **Konfigurasi Web Server**
Pastikan **mod_rewrite aktif**.

`.htaccess` sudah mengatur:

- HTTPS Enforcement (HSTS)
- Clean URL tanpa `.php`
- Gzip Compression
- Cache Control

---

### 5. **Maintenance Mode (Opsional)**

Edit:

```php
const MAINTENANCE_MODE = true;
```

---

## 🛡️ Keamanan

DramaFlix menerapkan perlindungan berikut:

- **SQL Injection Protection** via PDO Prepared Statement  
- **XSS Filtering & Output Escaping**  
- **Brute Force Prevention** pada login  
- **Secure Headers** (CSP, HSTS, X-Frame-Options)  
- **Secure Session Handling**

---

## 🤝 Kontribusi

Kontribusi sangat diterima!  
Silakan fork, buat fitur baru, atau kirim Pull Request.

---

## 📝 Lisensi

Dibuat untuk edukasi dan pengembangan. Bebas digunakan & dimodifikasi.

