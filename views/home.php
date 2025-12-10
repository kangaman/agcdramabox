<?php
// Pisahkan data untuk Slider (5 teratas) dan Grid (sisanya)
$allDramas = $data['data'] ?? [];
$sliderData = array_slice($allDramas, 0, 7); // Ambil 7 untuk slider
?>

<div class="container">
    <?php if ($page === 'search'): ?>
        <div class="section-header">
            <h2 class="title-glow">Hasil Pencarian: "<?= htmlspecialchars($q) ?>"</h2>
        </div>
    <?php else: ?>
        <div class="hero-slider-container">
            <div class="swiper heroSwiper">
                <div class="swiper-wrapper">
                    <?php foreach ($sliderData as $slide): 
                         $id = $slide['id'] ?? null;
                         if(!$id && isset($slide['url'])) { parse_str(parse_url($slide['url'], PHP_URL_QUERY), $u); $id = $u['q'] ?? null; }
                    ?>
                    <div class="swiper-slide">
                        <a href="/nonton/<?= $id ?>" class="slide-content">
                            <img src="<?= htmlspecialchars($slide['thumbnail']) ?>" alt="<?= htmlspecialchars($slide['title']) ?>">
                            <div class="slide-overlay">
                                <span class="badge-hd">FULL HD</span>
                                <h3><?= htmlspecialchars($slide['title']) ?></h3>
                                <div class="play-btn"><i class="ri-play-fill"></i> Tonton</div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
        
        <div class="section-header mt-5">
            <h2 class="title-glow"><i class="ri-fire-fill text-primary"></i> Sedang Trending</h2>
        </div>
    <?php endif; ?>

    <div class="movie-grid">
        <?php if (!empty($allDramas)): ?>
            <?php foreach ($allDramas as $item): 
                $id = $item['id'] ?? null;
                if(!$id && isset($item['url'])) { parse_str(parse_url($item['url'], PHP_URL_QUERY), $u); $id = $u['q'] ?? null; }
            ?>
            <?php if($id): ?>
                <a href="/nonton/<?= $id ?>" class="movie-card" data-aos="fade-up">
                    <div class="card-img-wrap">
                        <img src="<?= htmlspecialchars($item['thumbnail']) ?>" loading="lazy" alt="<?= htmlspecialchars($item['title']) ?>">
                        <div class="card-overlay">
                            <button class="btn-play-sm"><i class="ri-play-fill"></i></button>
                        </div>
                        <span class="eps-badge"><?= $item['episode'] ?> Eps</span>
                    </div>
                    <div class="card-info">
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                    </div>
                </a>
            <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="ri-ghost-line"></i>
                <p>Data tidak ditemukan.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="pagination">
        <?php if(isset($p) && $p > 1): ?>
            <a href="?p=<?= $p-1 ?>" class="btn-nav"><i class="ri-arrow-left-s-line"></i> Prev</a>
        <?php endif; ?>
        <span class="page-info">Halaman <?= $p ?? 1 ?></span>
        <a href="?p=<?= isset($p) ? $p+1 : 2 ?>" class="btn-nav">Next <i class="ri-arrow-right-s-line"></i></a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    var swiper = new Swiper(".heroSwiper", {
        effect: "coverflow",
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: "auto",
        initialSlide: 2,
        coverflowEffect: {
            rotate: 20,
            stretch: 0,
            depth: 200,
            modifier: 1,
            slideShadows: true,
        },
        pagination: { el: ".swiper-pagination", clickable: true },
        autoplay: { delay: 3000, disableOnInteraction: false }
    });
</script>