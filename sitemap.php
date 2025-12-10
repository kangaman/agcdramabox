<?php
require_once 'app/Config.php';
require_once 'app/ApiHandler.php';

header("Content-type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <?php
    // Ambil data terbaru dari API untuk dijadikan sitemap
    $api = new ApiHandler();
    $data = $api->getHome(1); // Ambil halaman 1
    
    if(isset($data['data'])) {
        foreach($data['data'] as $drama) {
            // Ambil ID
            $id = $drama['id'] ?? null;
            if(!$id && isset($drama['url'])) { 
                parse_str(parse_url($drama['url'], PHP_URL_QUERY), $u); 
                $id = $u['q'] ?? null; 
            }
            
            if($id) {
                $link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/nonton/" . $id;
                echo "<url>";
                echo "<loc>" . htmlspecialchars($link) . "</loc>";
                echo "<changefreq>weekly</changefreq>";
                echo "<priority>0.8</priority>";
                echo "</url>";
            }
        }
    }
    ?>
</urlset>