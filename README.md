# 🎬 AGC DramaBox v2 — Modern Streaming Platform with PWA & Admin Dashboard

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql)
![PWA](https://img.shields.io/badge/PWA-Optimized-5A0FC8?style=for-the-badge)
![Release](https://img.shields.io/badge/Release-v2.0-brightgreen?style=for-the-badge)
![Status](https://img.shields.io/badge/Status-Active-success?style=for-the-badge)
![License](https://img.shields.io/badge/License-Free-blue?style=for-the-badge)

AGC DramaBox v2 memperkenalkan standar baru dalam platform streaming berbasis PHP Native.  
Dirancang dengan pendekatan **ultra-lightweight**, **super responsif**, dan **mudah dikembangkan**, versi terbaru ini memadukan pengalaman menonton yang mulus dengan kontrol penuh melalui Admin Dashboard yang modern.

Dengan tampilan baru yang lebih elegan, performa yang jauh lebih cepat, serta integrasi PWA yang semakin stabil—AGC DramaBox v2 menjadi pilihan ideal untuk kebutuhan streaming mandiri, portal komunitas, project edukasi, atau produk komersial yang ingin tumbuh secara fleksibel.

---

# ⭐ Fitur Utama (Versi 2)

## 🔥 Pengalaman Pengguna (Frontend)
- **Desain baru yang lebih modern & profesional** — Meniru kualitas platform streaming premium.
- **Performa super cepat** — Optimasi loading dan caching bawaan.
- **PWA-ready**  
  Instal ke device seperti aplikasi native.
- **Navigasi lebih intuitif** — Fokus pada kemudahan menonton.
- **Halaman detail konten yang bersih & rapi**  
  Cocok untuk film, drama series, dokumenter, dan lainnya.

---

## 🛠️ Admin Dashboard (Backend)
- **Panel admin simpel & efisien** — Dirancang untuk bekerja cepat tanpa fitur yang membebani.
- **Manajemen konten terpusat** — Tambah/update konten tanpa plugin eksternal.
- **Sistem autentikasi aman** (session-based).
- **API modular & bersih**  
  Semua request dikendalikan melalui `ApiHandler.php`.

---

## 🚀 Keunggulan Teknis & Marketing Value
- **Ringan & cepat** → Cocok untuk shared hosting.
- **Tanpa dependensi besar** → Maintenance sangat mudah.
- **Struktur kode bersih** → Siap dikembangkan menjadi platform besar.
- **SEO-ready** dengan robots.txt & sitemap otomatis.
- **PWA bawaan** → Keunggulan kompetitif untuk user retention.
- **Tampilan profesional** → Meningkatkan kepercayaan pengguna.

---

# 📂 Struktur Direktori

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

# 🚀 Cara Deploy
1. Upload file ke hosting atau localhost.
2. Sesuaikan database pada `Config.php`.
3. Akses aplikasi langsung dari browser.
4. Login ke Admin Dashboard untuk mengelola konten.

Tidak ada instalasi tambahan — **plug and play**.

---

# 🛡️ Keamanan
- Folder sensitif dilindungi `.htaccess`
- Validasi input API
- Session-based authentication
- Struktur modular → meminimalkan risiko keamanan

---

# 🤝 Kontribusi & Pengembangan Lanjutan
Terbuka untuk:
- Integrasi player lanjutan (HLS / DASH)
- Modul analytics ringan
- Integrasi API konten eksternal
- Sistem kategori & filter konten
- Multi-admin role

---

# 📌 Changelog  
## **Versi 1 → Versi 2**

### ⭐ Pembaruan Besar di Versi 2
- UI baru yang lebih bersih dan profesional.
- Dashboard admin seluruhnya diperbarui.
- API internal dipusatkan & disederhanakan.
- Service Worker PWA diperbaiki & distabilkan.
- Kinerja aplikasi meningkat signifikan.
- Penyederhanaan struktur kode untuk skalabilitas jangka panjang.

### ❌ Fitur yang Dihapus (untuk efisiensi)
- Sistem VIP & membership.
- Player HLS.js dengan auto-next episode.
- Statistik admin berbasis Chart.js.
- DataTables interaktif.
- Sistem riwayat tontonan & favorit berbasis database.
- Backup database otomatis.
- Maintenance mode dengan mini-game.

### 🎯 Alasan Penghapusan
Agar aplikasi:
- lebih cepat,
- lebih ringan,
- lebih mudah dikembangkan,
- lebih stabil di environment hosting apa pun.

---

# 📝 Lisensi
Proyek ini bebas digunakan untuk tujuan pendidikan, komersial, dan pengembangan mandiri.

