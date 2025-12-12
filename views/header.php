<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#e50914">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <?php 
    // 1. AMBIL KONFIGURASI UTAMA
    // Menggunakan data dari database ($webConfig) atau fallback ke Config.php
    $siteName = $webConfig['site_name'] ?? Config::SITE_NAME;
    $siteDesc = $webConfig['site_desc'] ?? Config::SITE_DESC;
    
    // Cek status VIP untuk logika iklan
    $isVipUser = isset($_SESSION['role']) && $_SESSION['role'] === 'vip';
    
    // 2. LOGIKA META TAG SEO DINAMIS
    
    // Default Values (Halaman Home)
    $metaTitle = $siteName . ' - ' . $siteDesc;
    $metaDesc = $siteDesc;
    $metaImage = 'https://' . $_SERVER['HTTP_HOST'] . '/assets/images/logo.png'; 
    $urlNow = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $metaKeywords = "nonton drama china, streaming drakor, drama asia sub indo, posdrama, drachin terbaru, nonton gratis";

    // KONDISI 1: HALAMAN NONTON (Watch)
    if(isset($info) && is_array($info)) {
        $cleanTitle = htmlspecialchars($info['bookName']);
        
        // [SEO TITLE] Format: Judul - Nonton Subtitle Indonesia | NamaWeb
        $metaTitle = "$cleanTitle - Nonton Subtitle Indonesia | $siteName";
        
        // [SEO DESC] Ambil sinopsis, bersihkan tag HTML, potong 160 karakter
        $cleanIntro = strip_tags($info['introduction'] ?? '');
        // Hapus newlines berlebih
        $cleanIntro = preg_replace('/\s+/', ' ', $cleanIntro);
        $summary = mb_strimwidth($cleanIntro, 0, 160, "...");
        
        $metaDesc = "Nonton streaming $cleanTitle subtitle Indonesia gratis. $summary Streaming drama Asia kualitas HD di $siteName.";
        
        $metaImage = $info['cover'];
        
        // Tambahkan judul ke keywords
        $metaKeywords .= ", " . strtolower($cleanTitle) . " sub indo, nonton " . strtolower($cleanTitle);
    }
    
    // KONDISI 2: HALAMAN PENCARIAN
    elseif (isset($_GET['page']) && $_GET['page'] === 'search') {
        $keyword = htmlspecialchars($_GET['q'] ?? '');
        $metaTitle = "Cari: $keyword - Nonton Sub Indo | $siteName";
        $metaDesc = "Hasil pencarian drama $keyword subtitle Indonesia terlengkap dan gratis di $siteName.";
    }

    // KONDISI 3: HALAMAN STATIS LAIN (Terms/Login/dll)
    elseif (isset($pageTitle)) {
        $metaTitle = "$pageTitle | $siteName";
    }
    ?>

    <meta name="apple-mobile-web-app-title" content="<?= $siteName ?>">
    
    <title><?= $metaTitle ?></title>
    <meta name="description" content="<?= $metaDesc ?>">
    <meta name="keywords" content="<?= $metaKeywords ?>">
    <meta name="robots" content="index, follow">
    
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= $metaTitle ?>">
    <meta property="og:description" content="<?= $metaDesc ?>">
    <meta property="og:image" content="<?= $metaImage ?>">
    <meta property="og:url" content="<?= $urlNow ?>">
    <meta property="og:site_name" content="<?= $siteName ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $metaTitle ?>">
    <meta name="twitter:description" content="<?= $metaDesc ?>">
    <meta name="twitter:image" content="<?= $metaImage ?>">

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('PWA Service Worker Registered!'))
                    .catch(err => console.log('PWA Error:', err));
            });
        }
    </script>

    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="stylesheet" href="/assets/style.css?v=<?= time() ?>">
</head>
<body>

    <?php if(!empty($webConfig['ad_header']) && !$isVipUser): ?>
    <div class="ad-container-header" style="text-align:center; padding:0; background:#000; z-index: 999; position: relative;">
        <?= $webConfig['ad_header'] ?>
    </div>
    <?php endif; ?>
    
    <nav class="navbar">
        <div class="container nav-content">
            <a href="/" class="brand">
                <i class="ri-movie-2-fill brand-icon"></i>
                <span><?= $siteName ?></span>
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
            // Redirect ke URL ?page=search&q=... 
            window.location.href = '/?page=search&q=' + encodeURIComponent(k);
        }
    }
    </script>

    <main class="main-content">
