# 🎬 AGC DramaBox v2 — Platform Streaming + Admin Dashboard + PWA

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?style=for-the-badge&logo=mysql)
![PWA](https://img.shields.io/badge/PWA-Supported-5A0FC8?style=for-the-badge)
![Version](https://img.shields.io/badge/Version-2.0-brightgreen?style=for-the-badge)

**AGC DramaBox v2** adalah versi terbaru dari platform streaming berbasis PHP Native yang dirancang ringan, cepat, dan modular. Versi ini hadir dengan pembaruan besar pada frontend, backend, API, dan performa aplikasi.

---

# 📌 Changelog  
## **Versi 1 → Versi 2 (Perubahan Besar)**

### 🔥 **Perubahan di Versi 2**
- Desain UI baru yang lebih modern dan responsif.
- Dashboard admin diringankan & diperbarui total.
- API internal dipusatkan dalam `ApiHandler.php`.
- Struktur aplikasi dirapikan agar lebih scalable.
- Service Worker PWA lebih stabil.
- Penambahan caching & optimasi performa.
- Penghapusan fitur yang memperberat aplikasi (VIP system, statistik berat, player HLS.js, Chart.js, DataTables).
- Struktur folder disederhanakan.

### 🧩 **Fitur yang Dihapus dari Versi 1**
- Sistem membership VIP.
- Auto next episode player & HLS.js.
- Statistik grafik admin (Chart.js).
- DataTables tabel interaktif.
- Sistem riwayat tontonan & favorit terintegrasi database.
- Maintenance mode dengan mini-game.
- Sistem backup database otomatis.

### 🎯 **Fokus Baru di Versi 2**
- Kecepatan akses.
- Kesederhanaan struktur kode.
- Kemudahan pengembangan.
- Dashboard minimalis namun efisien.
- Integrasi PWA yang stabil.

---

# ✨ Fitur Utama AGC DramaBox v2

## 📱 **Frontend (Pengguna)**
- UI modern, ringan, responsif.
- PWA siap diinstal seperti aplikasi.
- Halaman home & detail video clean dan cepat.
- Performa tinggi berkat caching halaman & asset.

---

## 🛠️ **Backend (Admin)**
- Login admin sederhana & aman.
- Dashboard baru untuk manajemen konten.
- API terpusat (`ApiHandler.php`).
- Konfigurasi mudah melalui `Config.php`.

---

## 🔎 Optimasi & Infrastruktur
- PWA support: manifest + service worker.
- SEO basic:
  - sitemap otomatis  
  - robots.txt  
- Struktur folder rapi & mudah dipelihara.
- Keamanan dasar dengan `.htaccess` & sanitasi input.

---

# 📂 Struktur Folder

```
app/
│── ApiHandler.php
│── Auth.php
│── Config.php
└── Database.php

assets/
│── dashboard.css
└── style.css

views/
│── public/
│── dashboard/
│── auth/
├── header.php
├── footer.php
└── home.php

backups/
cache/

index.php
manifest.json
robots.txt
sitemap.php
sw.js
```

---

# 🚀 Cara Menggunakan

1. Upload project ke hosting / localhost.  
2. Atur database di `Config.php`.  
3. Buka aplikasi di browser.  
4. Gunakan dashboard admin untuk mengelola konten.

Versi ini dirancang **plug-and-play** tanpa setup kompleks.

---

# 🤝 Kontribusi

Kontribusi sangat dibuka untuk:
- fitur player baru  
- integrasi API konten  
- analitik ringan  
- penambahan modul admin  

---

# 📝 Lisensi  
Bebas digunakan untuk belajar, pengembangan, atau proyek internal.

