<div class="container fade-in">
    
    <?php if(!empty($data['data']) && $p == 1): ?>
    <div class="swiper hero-slider">
        <div class="swiper-wrapper">
            <?php 
            $slides = array_slice($data['data'], 0, 5); // Ambil 5 teratas
            foreach($slides as $slide): 
                $id = $slide['id'] ?? $slide['bookId'] ?? '';
                $cover = !empty($slide['cover']) ? $slide['cover'] : '/assets/images/no-cover.jpg';
            ?>
            <div class="swiper-slide">
                <div class="hero-bg" style="background-image: url('<?= $cover ?>');">
                    <div class="hero-overlay"></div>
                </div>
                
                <div class="hero-content">
                    <div class="badge-hero">🔥 Sedang Trending</div>
                    <h1><?= htmlspecialchars($slide['title'] ?? $slide['bookName']) ?></h1>
                    <div class="hero-meta">
                        <span>HD</span> • <span>Sub Indo</span> • <span>⭐ <?= $slide['score'] ?? '5.0' ?></span>
                    </div>
                    <p class="hero-desc">
                        Nikmati drama pilihan terbaik dengan kualitas tinggi. Tonton sekarang tanpa gangguan iklan yang mengganggu.
                    </p>
                    <div class="hero-actions">
                        <a href="/nonton/<?= $id ?>" class="btn btn-primary btn-lg">
                            <i class="ri-play-fill"></i> Putar
                        </a>
                        <button class="btn btn-glass btn-lg">
                            <i class="ri-add-line"></i> Daftar Saya
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
    </div>
    <?php endif; ?>

    <div id="historySection" style="display:none; margin-top: -30px; position: relative; z-index: 10;">
        <div class="section-header">
            <h2 class="section-title"><i class="ri-history-line" style="color:#ffd700"></i> Lanjutkan Menonton</h2>
            <button onclick="clearHistory()" class="btn-text">Hapus</button>
        </div>
        <div class="horizontal-scroll" id="historyGrid"></div>
    </div>

    <div class="tags-wrapper">
        <div class="tags-scroll">
            <a href="/search?q=Action" class="tag-pill">🔥 Action</a>
            <a href="/search?q=Romance" class="tag-pill">💕 Romance</a>
            <a href="/search?q=Comedy" class="tag-pill">🤣 Comedy</a>
            <a href="/search?q=Drama" class="tag-pill">🎭 Drama</a>
            <a href="/search?q=Thriller" class="tag-pill">🔪 Thriller</a>
            <a href="/search?q=Fantasy" class="tag-pill">🧚 Fantasy</a>
            <a href="/search?q=School" class="tag-pill">🏫 School</a>
            <a href="/search?q=Horror" class="tag-pill">👻 Horror</a>
            <a href="/search?q=Mystery" class="tag-pill">🕵️ Mystery</a>
        </div>
    </div>

    <div class="section-header" style="margin-top: 20px;">
        <h2 class="section-title">
            <?= isset($_GET['q']) ? '🔍 Hasil: "'.htmlspecialchars($_GET['q']).'"' : '✨ Rilis Terbaru' ?>
        </h2>
    </div>

    <?php if(!empty($data['data'])): ?>
        <div class="movie-grid">
            <?php foreach($data['data'] as $item): 
                $id = $item['id'] ?? $item['bookId'] ?? '';
                $title = $item['title'] ?? $item['bookName'] ?? '';
                $cover = !empty($item['cover']) ? $item['cover'] : '/assets/images/no-cover.jpg';
                $rating = $item['score'] ?? '5.0';
            ?>
            <a href="/nonton/<?= $id ?>" class="movie-card">
                <div class="card-img-wrap">
                    <img src="<?= $cover ?>" alt="<?= htmlspecialchars($title) ?>" loading="lazy">
                    <div class="rating-badge">★ <?= $rating ?></div>
                    <div class="play-icon"><i class="ri-play-circle-fill"></i></div>
                </div>
                <div class="card-detail">
                    <h3><?= htmlspecialchars($title) ?></h3>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="pagination">
            <?php if($p > 1): ?>
                <a href="/?p=<?= $p-1 ?>" class="btn btn-secondary">← Sebelumnya</a>
            <?php endif; ?>
            <a href="/?p=<?= $p+1 ?>" class="btn btn-secondary">Selanjutnya →</a>
        </div>

    <?php else: ?>
        <div class="empty-state">
            <i class="ri-movie-off-line"></i>
            <p>Tidak ada drama ditemukan.</p>
            <a href="/" class="btn btn-primary">Kembali ke Home</a>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    // 1. Init Hero Slider
    new Swiper('.hero-slider', {
        loop: true,
        autoplay: { delay: 6000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        effect: 'fade', 
        fadeEffect: { crossFade: true },
        speed: 1000
    });

    // 2. Logic History (Horizontal)
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
                            <a href="/nonton/${data.id}" class="history-card">
                                <div class="h-img">
                                    <img src="${data.cover}" loading="lazy">
                                    <div class="h-overlay"><i class="ri-play-fill"></i></div>
                                </div>
                                <div class="h-info">
                                    <h4>${data.title}</h4>
                                    <span>Lanjut Ep ${data.lastEp}</span>
                                    <div class="progress-line"><div style="width:50%"></div></div>
                                </div>
                            </a>
                        `;
                        container.insertAdjacentHTML('afterbegin', html);
                        hasData = true;
                    }
                } catch(e) {}
            }
        }
        if(hasData) section.style.display = 'block';
    });

    function clearHistory() {
        if(confirm('Hapus semua riwayat di perangkat ini?')) {
            const keys = [];
            for (let i=0; i<localStorage.length; i++) {
                if(localStorage.key(i).startsWith('history_item_') || localStorage.key(i).startsWith('watched_')) 
                    keys.push(localStorage.key(i));
            }
            keys.forEach(k => localStorage.removeItem(k));
            window.location.reload();
        }
    }
</script>
