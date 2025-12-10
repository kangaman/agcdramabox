<div class="container fade-in">
    
    <?php if(!empty($data['data']) && $p == 1): ?>
    <div class="swiper hero-slider" style="margin: 20px 0 40px;">
        <div class="swiper-wrapper">
            <?php 
            $slides = array_slice($data['data'], 0, 5);
            foreach($slides as $slide): 
                $id = $slide['id'] ?? $slide['bookId'] ?? '';
                $rawCover = $slide['cover'] ?? '';
                
                // FALLBACK IMAGE (Pakai gambar default jika kosong)
                $cover = !empty($rawCover) ? $rawCover : 'https://placehold.co/600x900/1a1a1a/FFF?text=No+Cover';
                
                // AMANKAN URL DARI TANDA KUTIP (Penting utk inline CSS)
                $bgStyle = "background-image: linear-gradient(to top, #0f1014 10%, rgba(0,0,0,0) 80%), url('" . htmlspecialchars($cover, ENT_QUOTES) . "');";
            ?>
            <div class="swiper-slide">
                <div class="hero-card" style="<?= $bgStyle ?>">
                    <div class="hero-content">
                        <span class="badge" style="background:var(--primary); color:white; margin-bottom:10px;">🔥 Sedang Hits</span>
                        <h1><?= htmlspecialchars($slide['title'] ?? $slide['bookName']) ?></h1>
                        <p class="hero-desc">
                            Saksikan drama terbaru ini dengan kualitas HD dan subtitle Indonesia. 
                            Streaming lancar dan gratis hanya di DramaFlix.
                        </p>
                        <div style="display:flex; gap:15px; margin-top:20px;">
                            <a href="/nonton/<?= $id ?>" class="btn btn-primary"><i class="ri-play-fill"></i> Putar</a>
                            <button class="btn btn-secondary"><i class="ri-add-line"></i> Info</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
    </div>
    <?php endif; ?>

    <div id="historySection" style="display:none; margin-bottom:40px;">
        <div class="section-header" style="border-left: 4px solid #ffd700; padding-left: 15px;">
            <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                <h2 class="section-title" style="margin:0; font-size:1.4rem;">Lanjutkan Menonton</h2>
                <button onclick="clearHistory()" style="background:none; border:none; color:#666; cursor:pointer;">Hapus</button>
            </div>
        </div>
        <div class="movie-grid" id="historyGrid" style="margin-top:20px;"></div>
    </div>

    <div class="tags-scroll-container">
        <a href="/search?q=Action" class="tag-pill">🔥 Action</a>
        <a href="/search?q=Romance" class="tag-pill">💕 Romance</a>
        <a href="/search?q=Comedy" class="tag-pill">🤣 Comedy</a>
        <a href="/search?q=Drama" class="tag-pill">🎭 Drama</a>
        <a href="/search?q=School" class="tag-pill">🏫 School</a>
        <a href="/search?q=Fantasy" class="tag-pill">🧚 Fantasy</a>
    </div>

    <div class="section-header" style="margin-top:20px;">
        <h2 class="section-title">
            <?= isset($_GET['q']) ? '🔍 Hasil: "'.htmlspecialchars($_GET['q']).'"' : '✨ Rilis Terbaru' ?>
        </h2>
    </div>

    <?php if(!empty($data['data'])): ?>
        <div class="movie-grid">
            <?php foreach($data['data'] as $item): 
                $id = $item['id'] ?? $item['bookId'] ?? '';
                $title = $item['title'] ?? $item['bookName'] ?? '';
                $rating = $item['score'] ?? '5.0';
                
                // Gambar dengan Fallback CDN
                $cover = !empty($item['cover']) ? $item['cover'] : 'https://placehold.co/400x600/222/999?text=No+Image';
            ?>
            <a href="/nonton/<?= $id ?>" class="movie-card">
                <div class="card-img-wrap">
                    <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($title) ?>" loading="lazy" onerror="this.src='https://placehold.co/400x600/333/FFF?text=Error'">
                    
                    <div class="rating-badge">★ <?= $rating ?></div>
                    <div class="card-overlay">
                        <div class="play-button"><i class="ri-play-fill"></i></div>
                    </div>
                </div>
                <div class="card-detail">
                    <h3><?= htmlspecialchars($title) ?></h3>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="pagination" style="margin-top:50px; display:flex; justify-content:center; gap:15px;">
            <?php if($p > 1): ?>
                <a href="/?p=<?= $p-1 ?>" class="btn btn-secondary">← Prev</a>
            <?php endif; ?>
            <a href="/?p=<?= $p+1 ?>" class="btn btn-secondary">Next →</a>
        </div>

    <?php else: ?>
        <div style="text-align:center; padding:80px; color:#666;">
            <i class="ri-ghost-line" style="font-size:3rem;"></i>
            <p>Data tidak ditemukan.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    new Swiper('.hero-slider', {
        loop: true, autoplay: { delay: 4000 },
        pagination: { el: '.swiper-pagination', clickable: true },
        effect: 'fade', fadeEffect: { crossFade: true }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('historyGrid');
        const section = document.getElementById('historySection');
        let hasData = false;

        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith('history_item_')) {
                try {
                    const data = JSON.parse(localStorage.getItem(key));
                    if(data && data.id) {
                        const html = `
                            <a href="/nonton/${data.id}" class="movie-card">
                                <div class="card-img-wrap">
                                    <img src="${data.cover}" onerror="this.src='https://placehold.co/400x600/333/FFF?text=Error'">
                                    <div class="eps-tag">Ep ${data.lastEp}</div>
                                </div>
                                <div class="card-detail">
                                    <h3 style="color:#ddd; font-size:0.9rem;">${data.title}</h3>
                                    <small style="color:var(--primary);">Lanjutkan</small>
                                </div>
                            </a>
                        `;
                        container.insertAdjacentHTML('afterbegin', html);
                        hasData = true;
                    }
                } catch(e){}
            }
        }
        if(hasData) section.style.display = 'block';
    });

    function clearHistory() {
        if(confirm('Hapus riwayat?')) {
            Object.keys(localStorage).forEach(k => {
                if(k.startsWith('history_item_') || k.startsWith('watched_')) localStorage.removeItem(k);
            });
            window.location.reload();
        }
    }
</script>
