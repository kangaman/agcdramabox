<div class="container fade-in">
    
    <?php if(!empty($data['data']) && $p == 1): ?>
    <div class="swiper hero-slider" style="margin: 20px 0 40px;">
        <div class="swiper-wrapper">
            <?php 
            // Ambil 5 drama pertama untuk dijadikan Banner
            $slides = array_slice($data['data'], 0, 5);
            foreach($slides as $slide): 
                $id = $slide['id'] ?? $slide['bookId'] ?? '';
                // Pastikan ada cover, jika tidak pakai placeholder
                $cover = !empty($slide['cover']) ? $slide['cover'] : '/assets/images/no-cover.jpg';
            ?>
            <div class="swiper-slide">
                <div class="hero-card" style="background-image: linear-gradient(to top, #0f1014 10%, rgba(0,0,0,0) 80%), url('<?= $cover ?>');">
                    <div class="hero-content">
                        <span class="badge" style="background:var(--primary); color:white; margin-bottom:10px; box-shadow: 0 0 10px var(--primary);">
                            🔥 Sedang Hits
                        </span>
                        <h1><?= htmlspecialchars($slide['title'] ?? $slide['bookName']) ?></h1>
                        <p class="hero-desc">
                            Saksikan keseruan drama terbaru ini dengan kualitas HD dan subtitle Indonesia. 
                            Nikmati pengalaman streaming terbaik hanya di DramaFlix.
                        </p>
                        <div style="display:flex; gap:15px; margin-top:20px;">
                            <a href="/nonton/<?= $id ?>" class="btn btn-primary" style="padding: 12px 30px; font-size: 1rem;">
                                <i class="ri-play-fill"></i> Putar Sekarang
                            </a>
                            <button class="btn btn-secondary" style="background:rgba(255,255,255,0.15); backdrop-filter: blur(5px);">
                                <i class="ri-add-line"></i> Info Detail
                            </button>
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
                <h2 class="section-title" style="margin:0; font-size:1.4rem;">
                    <i class="ri-history-line" style="color:#ffd700; margin-right:5px;"></i> Lanjutkan Menonton
                </h2>
                <button onclick="clearHistory()" style="background:none; border:none; color:#666; cursor:pointer; font-size:0.9rem;">
                    Hapus Semua
                </button>
            </div>
        </div>
        <div class="movie-grid" id="historyGrid" style="margin-top:20px;"></div>
    </div>

    <div class="tags-scroll-container">
        <a href="/search?q=Action" class="tag-pill">🔥 Action</a>
        <a href="/search?q=Romance" class="tag-pill">💕 Romance</a>
        <a href="/search?q=Comedy" class="tag-pill">🤣 Comedy</a>
        <a href="/search?q=Drama" class="tag-pill">🎭 Drama</a>
        <a href="/search?q=Thriller" class="tag-pill">🔪 Thriller</a>
        <a href="/search?q=Fantasy" class="tag-pill">🧚 Fantasy</a>
        <a href="/search?q=School" class="tag-pill">🏫 School</a>
        <a href="/search?q=Horror" class="tag-pill">👻 Horror</a>
    </div>

    <div class="section-header" style="margin-top: 20px;">
        <h2 class="section-title">
            <?= isset($_GET['q']) ? '🔍 Hasil Pencarian: "'.htmlspecialchars($_GET['q']).'"' : '✨ Rilis Terbaru' ?>
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

        <div class="pagination" style="margin-top: 50px; display:flex; justify-content:center; gap:15px;">
            <?php if($p > 1): ?>
                <a href="/?p=<?= $p-1 ?>" class="btn btn-secondary" style="background:#1b1e26;">← Sebelumnya</a>
            <?php endif; ?>
            <a href="/?p=<?= $p+1 ?>" class="btn btn-secondary" style="background:#1b1e26;">Selanjutnya →</a>
        </div>

    <?php else: ?>
        <div style="text-align:center; padding:80px 20px; color:#666;">
            <i class="ri-movie-off-line" style="font-size:4rem; margin-bottom:10px; display:block;"></i>
            <p style="font-size:1.1rem;">Maaf, drama yang kamu cari tidak ditemukan.</p>
            <a href="/" class="btn btn-primary" style="margin-top:20px;">Kembali ke Home</a>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    // 1. Inisialisasi Slider Hero
    new Swiper('.hero-slider', {
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        effect: 'fade', // Efek pudar yang elegan
        fadeEffect: { crossFade: true },
        speed: 1000
    });

    // 2. Logic "Lanjutkan Menonton" (Dari LocalStorage)
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('historyGrid');
        const section = document.getElementById('historySection');
        let hasData = false;

        // Loop semua data di LocalStorage
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            // Cari key yang kita simpan di watch.php
            if (key.startsWith('history_item_')) {
                try {
                    const data = JSON.parse(localStorage.getItem(key));
                    if(data && data.id) {
                        const html = `
                            <a href="/nonton/${data.id}" class="movie-card">
                                <div class="card-img-wrap">
                                    <img src="${data.cover}" loading="lazy" style="filter: brightness(0.7);">
                                    <div class="eps-tag" style="position:absolute; bottom:10px; right:10px; background:var(--primary); color:white; padding:2px 8px; border-radius:4px; font-size:0.7rem; font-weight:bold;">
                                        Ep ${data.lastEp}
                                    </div>
                                </div>
                                <div class="card-detail">
                                    <h3 style="color:#ddd; font-size:0.9rem;">${data.title}</h3>
                                    <small style="color:var(--primary);">Lanjutkan</small>
                                </div>
                            </a>
                        `;
                        // Masukkan ke awal (agar yang terakhir ditonton muncul duluan)
                        container.insertAdjacentHTML('afterbegin', html);
                        hasData = true;
                    }
                } catch(e) { console.error("Error parse history", e); }
            }
        }

        if(hasData) section.style.display = 'block';
    });

    // Hapus History
    function clearHistory() {
        if(confirm('Hapus riwayat tontonan di perangkat ini?')) {
            // Hapus item spesifik saja agar tidak menghapus settingan lain
            const keysToRemove = [];
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if(key.startsWith('history_item_') || key.startsWith('watched_')) {
                    keysToRemove.push(key);
                }
            }
            keysToRemove.forEach(k => localStorage.removeItem(k));
            window.location.reload();
        }
    }
</script>
