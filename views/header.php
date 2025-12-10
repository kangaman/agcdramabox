<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#e50914">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= Config::SITE_NAME ?>">
    
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('PWA Service Worker Registered!'))
                    .catch(err => console.log('PWA Error:', err));
            });
        }
    </script>

    <?php 
    // Default Values
    $metaTitle = isset($pageTitle) ? $pageTitle . ' - ' . Config::SITE_NAME : Config::SITE_NAME;
    $metaDesc = Config::SITE_DESC ?? "Nonton Drama Asia Subtitle Indonesia Gratis.";
    // Pastikan Anda punya file logo.png di folder assets/images/ atau ganti link ini
    $metaImage = 'https://' . $_SERVER['HTTP_HOST'] . '/assets/images/logo.png'; 
    
    // Jika sedang di halaman Nonton (Data dari watch.php)
    if(isset($info) && is_array($info)) {
        $metaTitle = htmlspecialchars($info['bookName']) . " - " . Config::SITE_NAME;
        $metaDesc = "Nonton " . htmlspecialchars($info['bookName']) . " Subtitle Indonesia. " . mb_strimwidth(htmlspecialchars($info['introduction']), 0, 150, "...");
        $metaImage = $info['cover'];
    }
    ?>
    
    <title><?= $metaTitle ?></title>
    <meta name="description" content="<?= $metaDesc ?>">
    
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= $metaTitle ?>">
    <meta property="og:description" content="<?= $metaDesc ?>">
    <meta property="og:image" content="<?= $metaImage ?>">
    <meta property="og:url" content="<?= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $metaTitle ?>">
    <meta name="twitter:image" content="<?= $metaImage ?>">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    
    <link rel="stylesheet" href="/assets/style.css?v=<?= time() ?>">


<script>
    // 1. Matikan Shortcut Tombol (F12, Ctrl+U, Ctrl+Shift+I, dll)
    document.addEventListener('keydown', function(e) {
        if (
            e.keyCode === 123 || // F12
            (e.ctrlKey && e.shiftKey && e.keyCode === 73) || // Ctrl+Shift+I (Inspect)
            (e.ctrlKey && e.shiftKey && e.keyCode === 74) || // Ctrl+Shift+J (Console)
            (e.ctrlKey && e.keyCode === 85) || // Ctrl+U (View Source)
            (e.ctrlKey && e.keyCode === 83)    // Ctrl+S (Save)
        ) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });

    // 2. Deteksi Jika DevTools Terbuka (Metode Deteksi Ukuran Layar)
    (function() {
        const threshold = 160; // Toleransi ukuran panel inspect
        
        const checkDevTools = () => {
            // Cek selisih ukuran window luar dan dalam
            const widthThreshold = window.outerWidth - window.innerWidth > threshold;
            const heightThreshold = window.outerHeight - window.innerHeight > threshold;
            
            // Jika selisihnya besar, berarti ada panel Inspect terbuka
            if (widthThreshold || heightThreshold) {
                try {
                    // HUKUMAN: Redirect ke halaman kosong atau tutup tab
                    // Ini akan terjadi SETELAH user klik "Inspect" di menu klik kanan
                    window.location.href = "about:blank";
                    document.body.innerHTML = "";
                } catch (err) {}
            }
        };

        // Cek secara berkala (setiap 500ms)
        setInterval(checkDevTools, 500);
        window.addEventListener('resize', checkDevTools);
    })();
    
    // BAGIAN "document.addEventListener('contextmenu'...)" SUDAH DIHAPUS
    // Sekarang Klik Kanan bisa digunakan.
    </script>

</head>
<body>
    
    <nav class="navbar">
        <div class="container nav-content">
            <a href="/" class="brand">
                <i class="ri-movie-2-fill brand-icon"></i>
                <span><?= Config::SITE_NAME ?></span>
            </a>
            
            <div class="search-wrapper">
                <form action="/" method="GET" onsubmit="handleSearch(this); return false;">
                    <i class="ri-search-line search-icon"></i>
                    <input type="text" name="q" placeholder="Cari judul drama..." required autocomplete="off">
                </form>
            </div>

            <div class="nav-links">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="/dashboard" class="btn-login">Dashboard</a>
                <?php else: ?>
                    <a href="/login" class="btn-login">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
    function handleSearch(form) {
        var k = form.q.value.trim();
        if(k) {
            // Redirect ke URL /cari/Judul%20Film
            window.location.href = '/cari/' + encodeURIComponent(k);
        }
    }
    </script>

    <main class="main-content">
