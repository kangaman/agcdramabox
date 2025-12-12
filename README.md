# 🎬 AGC DramaBox — Web Streaming Platform + Admin Dashboard + PWA

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql)
![PWA](https://img.shields.io/badge/PWA-Enabled-5A0FC8?style=for-the-badge&logo=pwa)
![Status](https://img.shields.io/badge/Build-Active-success?style=for-the-badge)

**AGC DramaBox** adalah platform streaming video berbasis PHP Native lengkap dengan Admin Dashboard, API modular, dan dukungan Progressive Web App (PWA).

---

## ✨ Fitur Utama

### 🔹 Fitur Pengguna (Frontend)
- UI dark mode modern & responsif  
- PWA (Add to Home Screen, offline cache)  
- Halaman beranda, detail konten, dan fitur pencarian  
- Loading cepat dengan caching Service Worker  
- Kompatibel mobile & desktop  

---

### 🔹 Fitur Admin (Backend)
- Login & autentikasi (`app/Auth.php`)  
- CRUD konten video melalui dashboard  
- API internal modular (`app/ApiHandler.php`)  
- Manajemen database fleksibel (`app/Database.php`)  
- Konfigurasi global yang mudah (`app/Config.php`)  

---

### 🔹 Fitur Tambahan
- **SEO Ready** — robots.txt, sitemap generator  
- Folder cache & backup dilindungi  
- Struktur folder rapi dan mudah dikembangkan  
- Dukungan AGC (Auto Grab Content) opsional  

---

## 🏗 Struktur Direktori

```
agcdramabox/
│── app/
│   ├── ApiHandler.php
│   ├── Auth.php
│   ├── Config.php
│   └── Database.php
│
│── assets/
│   ├── style.css
│   └── dashboard.css
│
│── views/
│   ├── public/
│   ├── dashboard/
│   ├── auth/
│   ├── header.php
│   ├── footer.php
│   ├── home.php
│   └── 404.php
│
│── manifest.json
│── sw.js
│── sitemap.php
│── robots.txt
│── index.php
│── backups/
│── cache/
```

---

## ⚙️ Instalasi & Setup

### 1️⃣ Persyaratan Sistem
- PHP 8.0+  
- MySQL / MariaDB  
- Apache/Nginx dengan mod_rewrite aktif  
- Ekstensi PHP yang direkomendasikan:
  - PDO  
  - cURL  

---

### 2️⃣ Konfigurasi Database
Edit file berikut:

```
app/Config.php
```

Sesuaikan:
- Host  
- Username  
- Password  
- Nama database  

---

### 3️⃣ Deploy ke Hosting / Local
Upload semua file ke:

```
public_html/  atau  htdocs/
```

Akses aplikasi:

```
http://localhost/agcdramabox
```

Akses Dashboard Admin:

```
http://domain.com/dashboard
```

---

## 📡 Dokumentasi API

### 🔹 GET — Daftar Video
```
GET /api/videos
```

### 🔹 GET — Detail Video
```
GET /api/video?id=123
```

### 🔹 POST — Login Admin
```
POST /auth/login
```

Body:
```
username=
password=
```

### 🔹 Response Format Default
```json
{
  "status": "success",
  "data": []
}
```

---

## 📦 PWA Integration

Menggunakan:
- `manifest.json`
- `sw.js`

Fitur:
- Add to Home Screen  
- Offline mode  
- Cache file statis & view dasar  

---

## 🔒 Keamanan

Sudah diterapkan:
- Validasi login & session  
- Filter input API  
- Proteksi folder sensitif (`.htaccess`)  
- Sanitasi parameter URL  

Disarankan tambahan:
- Rate limiting API  
- CSRF Token  
- ReCAPTCHA pada login admin  

---

## 🔧 Roadmap / Rencana Pengembangan
- Auto grabber (AGC) lebih cerdas  
- Pagination & filter lanjutan di dashboard  
- Statistik viewer analytics  
- Mode multi-role admin  
- Integrasi CDN untuk video  

---

## 🤝 Kontribusi
Pull Request sangat diterima!  

---

## 📝 Lisensi
Bebas digunakan & dimodifikasi untuk pengembangan dan edukasi.

---

## 📷 Screenshot (Opsional)
Tambahkan screenshot ke folder `assets/` lalu reference di README bila diperlukan.
