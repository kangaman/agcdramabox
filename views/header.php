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
    // [UPDATE] Ambil Konfigurasi Dinamis dari Database ($webConfig dari index.php)
    // Jika $webConfig belum ada, fallback ke Config bawaan
    $siteName = $webConfig['site_name'] ?? Config::SITE_NAME;
    $siteDesc = $webConfig['site_desc'] ?? Config::SITE_DESC;
    
    // Setup Meta Tags
    $metaTitle = isset($pageTitle) ? $pageTitle . ' - ' . $siteName : $siteName;
    $metaDesc = $siteDesc;
    
    // Default Image (Logo)
    $metaImage = 'https://' . $_SERVER['HTTP_HOST'] . '/assets/images/logo.png'; 
    
    // Jika sedang di halaman Nonton (Data dari watch.php), overwrite meta tags
    if(isset($info) && is_array($info)) {
        $metaTitle = htmlspecialchars($info['bookName']) . " - " . $siteName;
        // Ambil 150 karakter pertama dari sinopsis untuk deskripsi
        $metaDesc = "Nonton " . htmlspecialchars($info['bookName']) . " Subtitle Indonesia. " . mb_strimwidth(htmlspecialchars($info['introduction']), 0, 150, "...");
        $metaImage = $info['cover'];
    }
    ?>

    <meta name="apple-mobile-web-app-title" content="<?= $siteName ?>">
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

    <?php if(!empty($webConfig['ad_header'])): ?>
    <div class="ad-container-header" style="text-align:center; padding:0; background:#000;">
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
            // Redirect ke URL ?page=search&q=... atau format SEO friendly
            // Karena index.php menghandle page=search, kita gunakan input hidden di form atau redirect manual
            // Jika format URL Anda /cari/Judul, gunakan ini:
            window.location.href = '/?page=search&q=' + encodeURIComponent(k);
        }
    }
    </script>

    <main class="main-content">
