# PHP Lightweight Application

Aplikasi ini merupakan sistem berbasis PHP yang menggunakan arsitektur sederhana dengan pemisahan komponen **Core (App)**, **Views**, **Assets**, dan **API Handler**. Aplikasi dirancang agar mudah dikembangkan, ringan dijalankan pada shared hosting, dan fleksibel untuk berbagai kebutuhan web seperti dashboard, portal informasi, atau aplikasi data.

## 🚀 Fitur Utama

- **Struktur modular** menggunakan folder `app/` untuk logika inti aplikasi.
- **Routing sederhana** melalui `index.php`.
- **Autentikasi dasar** pada `app/Auth.php`.
- **Koneksi database terpusat** melalui `app/Database.php`.
- **API Handler** (`app/ApiHandler.php`).
- **Tampilan dinamis** di folder `views/`.
- **PWA-ready** melalui `manifest.json` dan `sw.js`.
- **SEO support** dengan `sitemap.php` dan `robots.txt`.
- **Pengaturan server** melalui `.htaccess`.

## 📂 Struktur Direktori

```
.
├── app/
│   ├── Config.php
│   ├── Auth.php
│   ├── Database.php
│   └── ApiHandler.php
├── assets/
├── views/
│   ├── home.php
│   ├── header.php
│   ├── footer.php
│   └── 404.php
├── index.php
├── manifest.json
├── sw.js
├── robots.txt
├── sitemap.php
├── .htaccess
└── backups/
```

## ⚙️ Instalasi

1. Clone repository:
   ```bash
   git clone https://github.com/username/nama-repo.git
   ```

2. Upload ke hosting bila perlu.

3. Sesuaikan konfigurasi database di:
   ```php
   app/Config.php
   ```

4. Pastikan permission folder sesuai kebutuhan.

## 📡 Endpoint API

Semua request API diproses oleh:
```
app/ApiHandler.php
```

Contoh request:
```http
POST /index.php?action=nama_fungsi
```

## 🎨 Template & Tampilan

Folder `views/` berisi:
- `header.php`
- `footer.php`
- `home.php`
- `404.php`

## 📱 PWA Support

- `manifest.json`
- `sw.js`

## 🛠️ Development Notes

- Disarankan PHP 7.4+.
- Validasi input sangat penting.
- Backup dapat disimpan di folder `backups/`.

## 🤝 Kontribusi

Pull request dan issue sangat diterima.

## 📄 Lisensi

Silakan tentukan lisensi sesuai kebutuhan.
