# 🎬 AGC DramaBox v2  
### **Next-Generation Lightweight Streaming Platform • PWA-Optimized • Admin Dashboard Included**

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql)
![PWA](https://img.shields.io/badge/PWA-Optimized-5A0FC8?style=for-the-badge)
![UI](https://img.shields.io/badge/UI-Modern%20Dark%20Theme-black?style=for-the-badge)
![Release](https://img.shields.io/badge/Release-v2.0-brightgreen?style=for-the-badge)
![Status](https://img.shields.io/badge/Maintained-Yes-success?style=for-the-badge)
![License](https://img.shields.io/badge/License-Free-blue?style=for-the-badge)

AGC DramaBox v2 adalah platform streaming ringan yang dirancang untuk menghadirkan pengalaman menonton yang **modern, cepat, dan stabil** dengan teknologi PHP Native & PWA.  
Versi ini membawa pembaruan besar dengan fokus pada **desain premium**, **kecepatan**, dan **kemudahan pengembangan**, menjadikannya ideal untuk proyek streaming mandiri, portal komunitas, maupun produk komersial skala kecil hingga menengah.

---

# 🌟 **Fitur Unggulan**

## 🎥 **Pengalaman Pengguna (Frontend)**
- ✨ UI dark mode modern dan profesional  
- ⚡ Performa super cepat berkat optimasi aset & caching  
- 📱 Siap instal sebagai aplikasi melalui PWA  
- 🎬 Halaman home & detail konten yang bersih dan fokus pada user experience  
- 🔍 Navigasi intuitif dengan tampilan minimalis  

---

## 🛠️ **Admin Dashboard (Backend)**
- 🔐 Sistem login aman berbasis session  
- 📊 Dashboard ringan, bebas plugin berat  
- 🧩 API modular terpusat dalam `ApiHandler.php`  
- 🗂️ Manajemen konten efisien & cepat  
- ⚙️ Konfigurasi fleksibel melalui file sederhana tanpa kompleksitas framework  

---

# 🚀 **Keunggulan Teknis**
- Tanpa framework — **ultra lightweight**  
- SEO-friendly (sitemap & robots otomatis)  
- PWA stabil dengan offline caching  
- Struktur file bersih & mudah dikembangkan  
- Kompatibel dengan shared hosting  

---

# 📂 **Struktur Direktori**

```
app/
│── ApiHandler.php        → API internal
│── Auth.php              → Sistem login admin
│── Config.php            → Pengaturan utama
└── Database.php          → Koneksi database

assets/
│── style.css             → Styling frontend
└── dashboard.css         → Styling admin dashboard

views/
│── public/               → Tampilan frontend pengguna
│── dashboard/            → Panel admin
│── auth/                 → Halaman login
├── header.php
├── footer.php
└── home.php

backups/                  → Folder backup (dilindungi)
cache/                    → Cache hasil proses

index.php                 → Main router
manifest.json             → Metadata PWA
sw.js                     → Service Worker
sitemap.php               → Sitemap otomatis
robots.txt                → SEO rules
```

---

# 🚀 **Cara Deploy**
1. Upload semua file ke server / localhost.  
2. Edit konfigurasi database di:
```
app/Config.php
```
3. Buka URL aplikasi di browser.  
4. Login ke dashboard untuk mengelola konten.  

Aplikasi langsung berjalan — **tanpa build step, tanpa dependency tambahan**.

---

# 🔐 **Keamanan**
- Validasi input  
- Proteksi folder sensitif dengan `.htaccess`  
- Session login aman  
- Struktur modular → memperkecil risiko eksploitasi  

---

# 🧭 **Roadmap Fitur Mendatang**
| Fitur | Status |
|-------|--------|
| Player HLS/DASH modern | Opsional |
| Sistem kategori konten | Rencana |
| Global search | Rencana |
| Multi-admin role | Rencana |
| Analytics ringan | Opsional |
| Auto-grabber konten | Opsional |

---

# 📌 **Changelog: Versi 1 → Versi 2**

## ✨ Pembaruan Besar
- Desain UI diperbarui total  
- Dashboard admin dibuat lebih ringan & fokus  
- API internal direstrukturisasi agar lebih efisien  
- PWA lebih stabil dengan perbaikan caching  
- Performa aplikasi meningkat signifikan  
- Struktur folder dibuat lebih bersih & scalable  

## 🧹 Fitur dari Versi 1 yang Dihapus
- Sistem VIP & membership  
- Player HLS.js auto next episode  
- Statistik Chart.js  
- DataTables pada admin  
- Fitur riwayat tontonan & favorit  
- Backup database otomatis  
- Maintenance mode mini-game  

## 🎯 Alasan Penghapusan
Untuk menjadikan aplikasi:  
- lebih cepat  
- lebih stabil  
- lebih scalable  
- lebih fokus pada core functionality  

---

# 📝 **Lisensi**
AGC DramaBox v2 bebas digunakan untuk proyek komersial, pembelajaran, dan pengembangan mandiri.

---

Terima kasih telah menggunakan AGC DramaBox v2!  
Jika Anda ingin menambahkan fitur baru, membuka kontribusi, atau melakukan integrasi tingkat lanjut — silakan lanjutkan pengembangan sesuai kebutuhan Anda.

