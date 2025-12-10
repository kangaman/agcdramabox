<?php
// Data Separation
$allData = $data['data'] ?? [];
$sliderData = array_slice($allData, 0, 5);
$gridData = $allData;
$title = ($page === 'search') ? 'Hasil Pencarian: "' . htmlspecialchars($q) . '"' : 'Sedang Trending';
?>

<div class="container fade-in">
    
    <div id="historySection" style="display:none; margin-bottom: 40px; margin-top: 20px;">
        <div class="section-header">
            <h2 class="section-title" style="font-size:1.2rem;"><i class="ri-history-line" style="color:#e50914"></i> Lanjutkan Menonton</h2>
            <button onclick="clearHistory()" style="background:none; border:none; color:#666; cursor:pointer; font-size:0.8rem;">Hapus</button>
        </div>
        <div class="movie-grid" id="historyGrid" style="grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));"></div>
    </div>

    <?php if($page !== 'search' && !empty($sliderData)): ?>
    <div class="hero-section">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <?php foreach($sliderData as $slide): 
                    $id = $slide['id'] ?? null;
                    if(!$id && isset($slide['url'])) { parse_str(parse_url($slide['url'], PHP_URL_QUERY), $u); $id = $u['q']??null; }
                ?>
                <div class="swiper-slide">
                    <div class="slide-backdrop" style="background-image: url('<?= $slide['thumbnail'] ?>');"></div>
                    <div class="slide-content">
                        <div class="slide-poster"><img src="<?= $slide['thumbnail'] ?>"></div>
                        <div class="slide-info">
                            <span class="badge-hd">FULL HD</span>
                            <h1><?= htmlspecialchars($slide['title']) ?></h1>
                            <p class="slide-meta"><span><?= $slide['episode'] ?> Eps</span> • Terbaru</p>
                            <a href="/nonton/<?= $id ?>" class="btn-watch-hero"><i class="ri-play-fill"></i> Tonton Sekarang</a>
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
        <h2 class="section-title"><i class="ri-fire-fill" style="color:#e50914"></i> <?= $title ?></h2>
    </div>

    <div class="movie-grid">
        <?php if(!empty($gridData)): ?>
            <?php foreach($gridData as $item): 
                $id = $item['id'] ?? null;
                if(!$id && isset($item['url'])) { parse_str(parse_url($item['url'], PHP_URL_QUERY), $u); $id = $u['q']??null; }
            ?>
            <?php if($id): ?>
                <a href="/nonton/<?= $id ?>" class="movie-card">
                    <div class="card-img-wrap">
                        <img src="<?= $item['thumbnail'] ?>" loading="lazy">
                        <div class="card-overlay"><div class="play-button"><i class="ri-play-fill"></i></div></div>
                        <div class="eps-tag"><?= $item['episode'] ?> Eps</div>
                    </div>
                    <div class="card-detail">
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                    </div>
                </a>
            <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state"><i class="ri-movie-2-line"></i><p>Tidak ditemukan.</p></div>
        <?php endif; ?>
    </div>

    <div class="pagination-area">
        <?php if(($p ?? 1) > 1): ?>
            <a href="?p=<?= ($p??1)-1 ?><?= isset($q)?'&q='.$q:'' ?>" class="btn-nav">← Prev</a>
        <?php endif; ?>
        <span class="page-current">Page <?= $p ?? 1 ?></span>
        <a href="?p=<?= ($p??1)+1 ?><?= isset($q)?'&q='.$q:'' ?>" class="btn-nav">Next →</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    var swiper = new Swiper(".mySwiper", {
        loop: true, autoplay: { delay: 4000 }, effect: "fade",
        pagination: { el: ".swiper-pagination", clickable: true },
    });

    // LOAD HISTORY LOGIC
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('historyGrid');
        const section = document.getElementById('historySection');
        let hasData = false;

        // Loop LocalStorage (Cari key 'history_item_')
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith('history_item_')) {
                const data = JSON.parse(localStorage.getItem(key));
                const html = `
                    <a href="/nonton/${data.id}" class="movie-card">
                        <div class="card-img-wrap">
                            <img src="${data.cover}" loading="lazy">
                            <div class="eps-tag">Ep ${data.lastEp}</div>
                            <div class="card-overlay"><div class="play-button"><i class="ri-play-fill"></i></div></div>
                        </div>
                        <div class="card-detail">
                            <h3>${data.title}</h3>
                            <small style="color:#e50914">Lanjut Ep ${data.lastEp}</small>
                        </div>
                    </a>
                `;
                container.innerHTML += html;
                hasData = true;
            }
        }
        if(hasData) section.style.display = 'block';
    });

    function clearHistory() {
        if(confirm('Hapus riwayat?')) {
            Object.keys(localStorage).forEach(key => {
                if(key.startsWith('history_item_') || key.startsWith('watched_')) localStorage.removeItem(key);
            });
            window.location.reload();
        }
    }
</script>
