<?php
// ==========================================
// 1. DATA PREPARATION
// ==========================================
$allData = $data['data'] ?? [];
$sliderData = array_slice($allData, 0, 5); // Ambil 5 data teratas untuk slider
$gridData = $allData; // Data untuk grid
$title = ($page === 'search') ? 'Hasil Pencarian: "' . htmlspecialchars($q) . '"' : 'Sedang Trending';

// Ambil source saat ini (Default: dramabox)
$currentSource = $source ?? 'dramabox';
?>

<style>
    /* Source Switcher (Tab Style) */
    .source-switcher-container { display: flex; justify-content: center; margin: 30px 0 40px; }
    .source-switcher { background: #151518; padding: 5px; border-radius: 50px; display: inline-flex; gap: 5px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
    .switch-btn { padding: 10px 25px; border-radius: 50px; text-decoration: none; font-size: 0.9rem; font-weight: 600; color: #888; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; }
    .switch-btn i { font-size: 1.1rem; }
    .switch-btn:hover { color: #fff; background: rgba(255,255,255,0.05); }
    .switch-btn.active { background: var(--primary); color: white; box-shadow: 0 4px 15px rgba(229, 9, 20, 0.4); transform: scale(1.05); }

    /* Hero Slider */
    .hero-section { margin-bottom: 40px; border-radius: 16px; overflow: hidden; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    .swiper-slide { height: 450px; position: relative; background: #000; overflow: hidden; }
    .slide-backdrop { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: cover; background-position: center; opacity: 0.5; filter: blur(30px); transform: scale(1.1); }
    .slide-content { position: absolute; bottom: 0; left: 0; width: 100%; height: 100%; padding: 40px; background: linear-gradient(to right, #0f1014 30%, transparent 100%); display: flex; align-items: center; gap: 30px; }
    .slide-poster { width: 200px; height: 300px; border-radius: 12px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.6); flex-shrink: 0; display: none; border: 1px solid rgba(255,255,255,0.1); }
    .slide-poster img { width: 100%; height: 100%; object-fit: cover; }
    
    .slide-info { max-width: 600px; z-index: 2; text-shadow: 0 2px 4px rgba(0,0,0,0.8); }
    .slide-info h2 { font-size: 2.8rem; margin-bottom: 15px; line-height: 1.1; font-weight: 800; color: #fff; }
    .slide-meta { display: flex; gap: 15px; margin-bottom: 25px; font-size: 0.9rem; color: #ddd; font-weight: 500; }
    .slide-badge { background: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 4px; backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.1); }
    
    .btn-watch-hero { background: var(--primary); color: white; padding: 14px 35px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 1rem; display: inline-flex; align-items: center; gap: 10px; transition: 0.3s; box-shadow: 0 0 20px rgba(229, 9, 20, 0.4); border: 2px solid transparent; }
    .btn-watch-hero:hover { transform: translateY(-3px); background: transparent; border-color: var(--primary); color: var(--primary); }

    /* Movie Grid & Card */
    .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 20px; }
    .movie-card { display: block; text-decoration: none; transition: 0.3s; position: relative; background: #151518; border-radius: 10px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); }
    .movie-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.3); border-color: rgba(255,255,255,0.2); }
    
    .card-img-wrap { position: relative; overflow: hidden; aspect-ratio: 2/3; background: #222; }
    .card-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
    .movie-card:hover .card-img-wrap img { transform: scale(1.1); }
    
    .eps-tag { position: absolute; top: 10px; right: 10px; background: var(--primary); color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.5); z-index: 2; }
    
    .card-overlay { position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; opacity: 0; transition: 0.3s; z-index: 1; }
    .movie-card:hover .card-overlay { opacity: 1; }
    .play-button { width: 50px; height: 50px; background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; border: 2px solid white; transform: scale(0.8); transition: 0.3s; }
    .movie-card:hover .play-button { transform: scale(1); background: var(--primary); border-color: var(--primary); }
    
    .card-detail { padding: 12px; }
    .card-detail h3 { font-size: 0.95rem; margin: 0; line-height: 1.4; color: #eee; font-weight: 600; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; height: 2.8em; }

    /* Responsive */
    @media (min-width: 768px) { .slide-poster { display: block; } }
    @media (max-width: 768px) {
        .slide-info h2 { font-size: 1.8rem; }
        .swiper-slide { height: 350px; }
        .slide-content { background: linear-gradient(to top, #0f1014 10%, transparent 100%); align-items: flex-end; padding: 20px; }
        .movie-grid { grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .card-detail { padding: 8px; }
        .card-detail h3 { font-size: 0.8rem; }
    }
    @media (max-width: 480px) {
        .movie-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="container fade-in">
    
    <div class="source-switcher-container">
        <div class="source-switcher">
            <a href="?source=dramabox" class="switch-btn <?= $currentSource == 'dramabox' ? 'active' : '' ?>">
                <i class="ri-server-fill"></i> Dramabox
            </a>
            <a href="?source=melolo" class="switch-btn <?= $currentSource == 'melolo' ? 'active' : '' ?>">
                <i class="ri-global-fill"></i> Melolo
            </a>
        </div>
    </div>

    <div id="historySection" style="display:none; margin-bottom: 40px;">
        <div class="section-header">
            <h2 class="section-title"><i class="ri-history-line" style="color:#e50914"></i> Lanjutkan Menonton</h2>
            <button onclick="clearHistory()" style="background:none; border:none; color:#666; cursor:pointer; font-size:0.8rem;">Hapus Semua</button>
        </div>
        <div class="movie-grid" id="historyGrid"></div>
    </div>

    <?php if($page !== 'search' && !empty($sliderData)): ?>
    <div class="hero-section">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <?php foreach($sliderData as $slide): 
                    $id = $slide['id'] ?? null;
                    if(!$id) continue;

                    // --- [FIX] TITIP DATA URL (PENTING!) ---
                    $safeTitle = urlencode($slide['title']);
                    $safeCover = urlencode($slide['thumbnail']);
                    $desc = isset($item['desc']) ? substr($item['desc'], 0, 500) : ''; 
                    $safeDesc = urlencode($desc);
                    $watchLink = "?page=watch&id=" . $id . "&source=" . $currentSource . "&title=" . $safeTitle . "&cover=" . $safeCover;
                ?>
                <div class="swiper-slide">
                    <div class="slide-backdrop" style="background-image: url('<?= $slide['thumbnail'] ?>');"></div>
                    <div class="slide-content">
                        <div class="slide-poster">
                            <img src="<?= $slide['thumbnail'] ?>" referrerpolicy="no-referrer">
                        </div>
                        <div class="slide-info">
                            <div class="slide-meta">
                                <span class="slide-badge" style="color:#4ade80;">FULL HD</span>
                                <span class="slide-badge"><?= $slide['episode'] ?? 'Ongoing' ?> Eps</span>
                                <span class="slide-badge">Server <?= ucfirst($currentSource) ?></span>
                            </div>
                            <h2><?= htmlspecialchars($slide['title']) ?></h2>
                            <p style="color:#ccc; margin-bottom:20px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                Nonton <?= htmlspecialchars($slide['title']) ?> gratis sub indo hanya di sini.
                            </p>
                            <a href="<?= $watchLink ?>" class="btn-watch-hero">
                                <i class="ri-play-fill"></i> NONTON SEKARANG
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="section-header">
        <h2 class="section-title">
            <i class="ri-fire-fill" style="color:#e50914"></i> <?= $title ?>
        </h2>
    </div>

    <div class="movie-grid">
        <?php if(!empty($gridData)): ?>
            <?php foreach($gridData as $item): 
                $id = $item['id'] ?? null;
                // Fallback ID
                if(!$id && isset($item['url'])) { parse_str(parse_url($item['url'], PHP_URL_QUERY), $u); $id = $u['q']??null; }
                
                // --- [FIX] TITIP DATA URL UNTUK GRID ---
                $safeTitle = urlencode($item['title']);
                $safeCover = urlencode($item['thumbnail']);
                $desc = isset($item['desc']) ? substr($item['desc'], 0, 500) : ''; 
                $safeDesc = urlencode($desc);
                $watchLink = "?page=watch&id=" . $id . "&source=" . $currentSource . "&title=" . $safeTitle . "&cover=" . $safeCover;
            ?>
            <?php if($id): ?>
                <a href="<?= $watchLink ?>" class="movie-card">
                    <div class="card-img-wrap">
                        <img src="<?= $item['thumbnail'] ?>" loading="lazy" referrerpolicy="no-referrer" alt="<?= htmlspecialchars($item['title']) ?>">
                        
                        <div class="card-overlay"><div class="play-button"><i class="ri-play-fill"></i></div></div>
                        <div class="eps-tag"><?= $item['episode'] ?? 'Baru' ?></div>
                    </div>
                    <div class="card-detail">
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                    </div>
                </a>
            <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="grid-column: 1/-1; text-align: center; padding: 80px 0;">
                <i class="ri-movie-2-line" style="font-size: 4rem; color: #333; margin-bottom: 20px;"></i>
                <p style="color: #888; font-size: 1.1rem;">Belum ada konten dari server <b><?= ucfirst($currentSource) ?></b>.</p>
                <a href="?source=<?= $currentSource == 'dramabox' ? 'melolo' : 'dramabox' ?>" style="color: var(--primary); margin-top: 15px; display: inline-block; text-decoration:none; border:1px solid var(--primary); padding:10px 25px; border-radius:50px;">
                    Coba Server Lain &rarr;
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="pagination-area" style="margin-top: 50px; display: flex; justify-content: center; gap: 10px; align-items: center;">
        <?php 
            $baseUrl = "?source=" . $currentSource;
            if(isset($q) && $q) $baseUrl .= "&q=" . urlencode($q);
            $currPage = $p ?? 1;
        ?>
        
        <?php if($currPage > 1): ?>
            <a href="<?= $baseUrl ?>&p=<?= $currPage - 1 ?>" class="btn-nav" style="background:#222; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; transition:0.3s;">
                ← Sebelumnya
            </a>
        <?php endif; ?>
        
        <span class="page-current" style="color:#fff; font-weight:bold; background:var(--primary); padding:10px 20px; border-radius:8px;">
            Hal. <?= $currPage ?>
        </span>
        
        <a href="<?= $baseUrl ?>&p=<?= $currPage + 1 ?>" class="btn-nav" style="background:#222; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; transition:0.3s;">
            Selanjutnya →
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    var swiper = new Swiper(".mySwiper", {
        loop: true, 
        autoplay: { delay: 5000, disableOnInteraction: false }, 
        effect: "fade",
        fadeEffect: { crossFade: true },
        pagination: { el: ".swiper-pagination", clickable: true },
    });

    // LOAD HISTORY LOGIC (Dengan Perbaikan Link)
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('historyGrid');
        const section = document.getElementById('historySection');
        let hasData = false;

        // Ambil data history secara terbalik (terbaru dulu)
        let keys = [];
        for(let i=0; i<localStorage.length; i++) {
            if(localStorage.key(i).startsWith('history_item_')) keys.push(localStorage.key(i));
        }
        
        // Render
        keys.forEach(key => {
            try {
                const data = JSON.parse(localStorage.getItem(key));
                const histSource = data.source || 'dramabox';
                
                // ENCODE DATA PENTING AGAR TIDAK HILANG
                const safeT = encodeURIComponent(data.title);
                const safeC = encodeURIComponent(data.cover);
                
                const link = `?page=watch&id=${data.id}&source=${histSource}&title=${safeT}&cover=${safeC}`;

                const html = `
                    <a href="${link}" class="movie-card">
                        <div class="card-img-wrap">
                            <img src="${data.cover}" loading="lazy" referrerpolicy="no-referrer">
                            <div class="eps-tag">Eps ${data.lastEp}</div>
                            <div class="card-overlay"><div class="play-button"><i class="ri-play-fill"></i></div></div>
                        </div>
                        <div class="card-detail">
                            <h3>${data.title}</h3>
                            <small style="color:var(--primary); font-size:0.75rem;">Lanjut Menonton</small>
                        </div>
                    </a>
                `;
                container.innerHTML += html;
                hasData = true;
            } catch(e) {}
        });

        if(hasData) section.style.display = 'block';
    });

    function clearHistory() {
        if(confirm('Hapus semua riwayat tontonan?')) {
            Object.keys(localStorage).forEach(key => {
                if(key.startsWith('history_item_') || key.startsWith('watched_')) localStorage.removeItem(key);
            });
            window.location.reload();
        }
    }
</script>
