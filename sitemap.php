<?php
// 1. MULAI BUFFER: Menahan output agar tidak langsung dikirim ke browser
ob_start();

require_once 'app/Config.php';
require_once 'app/ApiHandler.php';

// 2. BERSIHKAN BUFFER: Menghapus spasi/enter 'tak sengaja' dari file yang di-require di atas
ob_end_clean();

// 3. SET HEADER: Pastikan browser/Google tahu ini XML
header("Content-Type: application/xml; charset=utf-8");

// Deteksi Protocol & Domain
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain = $protocol . "://" . $_SERVER['HTTP_HOST'];

// Mulai Output XML yang SEBENARNYA
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= htmlspecialchars($domain) ?>/</loc>
        <changefreq>hourly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= htmlspecialchars($domain) ?>/terms</loc>
        <priority>0.3</priority>
    </url>
    <url>
        <loc><?= htmlspecialchars($domain) ?>/privacy</loc>
        <priority>0.3</priority>
    </url>

    <?php
    // Logika Data Dinamis
    $api = new ApiHandler();
    
    // Loop halaman 1 s.d 2
    for ($i = 1; $i <= 2; $i++) { 
        $data = $api->getHome($i);
        
        if(isset($data['data']) && is_array($data['data'])) {
            foreach($data['data'] as $drama) {
                // Ambil ID atau fallback ke Query String
                $id = $drama['id'] ?? null;
                
                if(!$id && isset($drama['url'])) { 
                    $parsedUrl = parse_url($drama['url'], PHP_URL_QUERY);
                    if ($parsedUrl) {
                        parse_str($parsedUrl, $u); 
                        $id = $u['q'] ?? null; 
                    }
                }
                
                if($id) {
                    // Validasi link agar aman dari karakter aneh
                    $link = $domain . "/nonton/" . $id;
                    
                    echo "<url>";
                    echo "<loc>" . htmlspecialchars($link) . "</loc>";
                    // Menggunakan W3C Datetime format yang lebih disukai Google
                    echo "<lastmod>" . date('c') . "</lastmod>"; 
                    echo "<changefreq>daily</changefreq>";
                    echo "<priority>0.8</priority>";
                    echo "</url>";
                }
            }
        }
    }
    ?>
</urlset>
