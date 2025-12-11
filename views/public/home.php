<?php
// ==========================================
// 1. DATA PREPARATION
// ==========================================
$allData = $data['data'] ?? [];
$sliderData = array_slice($allData, 0, 5); 
$gridData = $allData; 
$title = ($page === 'search') ? 'Hasil: "' . htmlspecialchars($q) . '"' : 'Sedang Trending';
$currentSource = $source ?? 'dramabox';
?>

<style>
    /* Navigation Tabs */
    .nav-tabs { display: flex; justify-content: center; gap: 15px; margin: 20px 0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
    .tab-btn { background: transparent; border: none; color: #888; font-size: 1rem; font-weight: 600; cursor: pointer; padding: 8px 15px; position: relative; transition: 0.3s; display: flex; align-items: center; gap: 6px; }
    .tab-btn i { font-size: 1.2rem; }
    .tab-btn:hover { color: #fff; }
    .tab-btn.active { color: var(--primary); }
    .tab-btn.active::after { content: ''; position: absolute; bottom: -16px; left: 0; width: 100%; height: 3px; background: var(--primary); border-radius: 10px 10px 0 0; }

    /* Source Pill */
    .source-selector { display: flex; justify-content: center; margin-bottom: 30px; gap: 10px; }
    .src-btn { background: #1a1a1d; padding: 8px 20px; border-radius: 50px; color: #888; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 6px; border: 1px solid rgba(255,255,255,0.1); transition: 0.3s; }
    .src-btn.active { background: rgba(229,9,20,0.15); color: var(--primary); border-color: var(--primary); }
    .src-btn:hover:not(.active) { background: #333; color: #fff; }

    /* Sections */
    .view-section { display: none; animation: fadeIn 0.5s; }
    .view-section.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* Grid & Cards */
    .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; }
    @media(min-width: 768px) { .movie-grid { grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 20px; } }
    
    .movie-card { display: block; text-decoration: none; position: relative; transition: 0.3s; }
    .movie-card:hover { transform: translateY(-5px); }
    .card-img-wrap { position: relative; overflow: hidden; aspect-ratio: 2/3; background: #1a1a1a; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
    .card-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; opacity: 0; }
    .card-img-wrap img.loaded { opacity: 1; }
    .movie-card:hover .card-img-wrap img { transform: scale(1.1); }
    
    .card-overlay { position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; opacity: 0; transition: 0.3s; }
    .movie-card:hover .card-overlay { opacity: 1; }
    .play-circle { width: 45px; height: 45px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; box-shadow: 0 0 20px rgba(229, 9, 20, 0.5); }

    .card-detail { padding: 10px 5px; }
    .card-detail h3 { font-size: 0.9rem; margin: 0; line-height: 1.4; color: #ddd; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .card-detail small { color: #666; font-size: 0.75rem; display: block; margin-top: 5px; }

    /* Empty State */
    .empty-placeholder { grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #555; }
    .empty-placeholder i { font-size: 3rem; margin-bottom: 15px; display: block; opacity: 0.5; }
    
    /* Hero Slider */
    .hero-wrapper { margin-bottom: 30px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    .swiper-slide { height: 220px; background: #000; position: relative; }
    @media(min-width: 768px) { .swiper-slide { height: 380px; } }
    .slide-content { position: absolute; bottom: 0; left: 0; width: 100%; background: linear-gradient(to top, #0f1014 10%, transparent); padding: 20px; }
    .slide-title { color: #fff; font-size: 1.2rem; font-weight: bold; text-shadow: 0 2px 5px rgba(0,0,0,0.8); margin: 0; }
    .slide-meta { font-size: 0.8rem; color: #ccc; margin-bottom: 5px; }
</style>

<div class="container fade-in">
    
    <div class="nav-tabs">
        <button class="tab-btn active" onclick="switchTab('home')">
            <i class="ri-home-4-fill"></i> Beranda
        </button>
        <button class="tab-btn" onclick="switchTab('favorites')">
            <i class="ri-heart-fill"></i> Favorit
        </button>
        <button class="tab-btn" onclick="switchTab('history')">
            <i class="ri-history-line"></i> Riwayat
        </button>
    </div>

    <div id="view-home" class="view-section active">
        
        <div class="source-selector">
            <a href="?source=dramabox" class="src-btn <?= $currentSource == 'dramabox' ? 'active' : '' ?>">
                <i class="ri-server-fill"></i> Dramabox
            </a>
            <a href="?source=melolo" class="src-btn <?= $currentSource == 'melolo' ? 'active' : '' ?>">
                <i class="ri-global-fill"></i> Melolo
            </a>
        </div>

        <?php if($page !== 'search' && !empty($sliderData)): ?>
        <div class="hero-wrapper">
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <?php foreach($sliderData as $slide): 
                        $id = $slide['id'] ?? null; if(!$id) continue;
                        // URL Builder
                        $safeTitle = urlencode($slide['title']);
                        $safeCover = urlencode($slide['thumbnail']);
                        $desc = isset($slide['desc']) ? substr($slide['desc'], 0, 300) : '';
                        $safeDesc = urlencode($desc);
                        $watchLink = "?page=watch&id=$id&source=$currentSource&title=$safeTitle&cover=$safeCover&desc=$safeDesc";
                    ?>
                    <div class="swiper-slide">
                        <a href="<?= $watchLink ?>" style="display:block; width:100%; height:100%;">
                            <img src="<?= $slide['thumbnail'] ?>" style="width:100%; height:100%; object-fit:cover;" referrerpolicy="no-referrer">
                            <div class="slide-content">
                                <div class="slide-meta"><span style="background:var(--primary); padding:2px 6px; border-radius:3px;">HOT</span> <?= $slide['episode'] ?? 'Update' ?></div>
                                <h2 class="slide-title"><?= htmlspecialchars($slide['title']) ?></h2>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="section-header" style="margin-bottom:15px; display:flex; align-items:center; gap:10px;">
            <i class="ri-fire-fill" style="color:var(--primary);"></i> 
            <h3 style="margin:0; font-size:1.1rem; color:#fff;"><?= $title ?></h3>
        </div>

        <div class="movie-grid">
            <?php if(!empty($gridData)): ?>
                <?php foreach($gridData as $item): 
                    $id = $item['id'] ?? null;
                    if(!$id && isset($item['url'])) { parse_str(parse_url($item['url'], PHP_URL_QUERY), $u); $id = $u['q']??null; }
                    
                    $safeTitle = urlencode($item['title']);
                    $safeCover = urlencode($item['thumbnail']);
                    $desc = isset($item['desc']) ? substr($item['desc'], 0, 300) : '';
                    $safeDesc = urlencode($desc);
                    $watchLink = "?page=watch&id=$id&source=$currentSource&title=$safeTitle&cover=$safeCover&desc=$safeDesc";
                ?>
                <?php if($id): ?>
                    <a href="<?= $watchLink ?>" class="movie-card">
                        <div class="card-img-wrap">
                            <img src="<?= $item['thumbnail'] ?>" loading="lazy" referrerpolicy="no-referrer" onload="this.classList.add('loaded')" onerror="this.src='https://via.placeholder.com/300x450?text=No+Image';this.classList.add('loaded');">
                            <div class="card-overlay"><div class="play-circle"><i class="ri-play-fill"></i></div></div>
                            <div style="position:absolute; top:5px; right:5px; background:rgba(0,0,0,0.7); color:white; font-size:0.6rem; padding:2px 6px; border-radius:4px;">
                                <?= $item['episode'] ?? 'Baru' ?>
                            </div>
                        </div>
                        <div class="card-detail">
                            <h3><?= htmlspecialchars($item['title']) ?></h3>
                        </div>
                    </a>
                <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-placeholder">
                    <i class="ri-search-eye-line"></i>
                    <p>Belum ada konten di server ini.</p>
                    <a href="?source=<?= $currentSource == 'dramabox' ? 'melolo' : 'dramabox' ?>" style="color:var(--primary); text-decoration:none;">Ganti Server</a>
                </div>
            <?php endif; ?>
        </div>

        <div style="display:flex; justify-content:center; gap:10px; margin-top:40px;">
            <?php $baseUrl = "?source=$currentSource" . (isset($q) ? "&q=".urlencode($q) : ""); $curr = $p ?? 1; ?>
            <?php if($curr > 1): ?>
                <a href="<?= $baseUrl ?>&p=<?= $curr-1 ?>" style="background:#222; color:white; padding:8px 20px; border-radius:50px; text-decoration:none; font-size:0.9rem;">← Prev</a>
            <?php endif; ?>
            <span style="background:var(--primary); color:white; padding:8px 20px; border-radius:50px; font-weight:bold; font-size:0.9rem;"><?= $curr ?></span>
            <a href="<?= $baseUrl ?>&p=<?= $curr+1 ?>" style="background:#222; color:white; padding:8px 20px; border-radius:50px; text-decoration:none; font-size:0.9rem;">Next →</a>
        </div>
    </div>

    <div id="view-favorites" class="view-section">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; font-size:1.2rem;">Daftar Favorit Saya</h3>
            <button onclick="clearStorage('my_bookmarks')" style="background:none; border:none; color:#ff4757; cursor:pointer; font-size:0.8rem;">Hapus Semua</button>
        </div>
        <div id="favoritesGrid" class="movie-grid"></div>
        <div id="favoritesEmpty" class="empty-placeholder" style="display:none;">
            <i class="ri-heart-add-line"></i>
            <p>Belum ada drama favorit.<br>Klik tombol "Simpan" saat nonton.</p>
        </div>
    </div>

    <div id="view-history" class="view-section">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; font-size:1.2rem;">Terakhir Ditonton</h3>
            <button onclick="clearStorage('history')" style="background:none; border:none; color:#ff4757; cursor:pointer; font-size:0.8rem;">Hapus Riwayat</button>
        </div>
        <div id="historyGrid" class="movie-grid"></div>
        <div id="historyEmpty" class="empty-placeholder" style="display:none;">
            <i class="ri-history-line"></i>
            <p>Belum ada riwayat tontonan.</p>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    var swiper = new Swiper(".mySwiper", {
        loop: true, autoplay: { delay: 4000, disableOnInteraction: false }, 
        effect: "fade", pagination: { el: ".swiper-pagination", clickable: true },
    });

    // --- TAB SWITCHER LOGIC ---
    function switchTab(tabName) {
        // 1. Hide all sections
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        
        // 2. Show selected
        document.getElementById('view-' + tabName).classList.add('active');
        
        // 3. Highlight button (Cari button yg onclick-nya sesuai)
        const btns = document.querySelectorAll('.tab-btn');
        if(tabName === 'home') btns[0].classList.add('active');
        if(tabName === 'favorites') { btns[1].classList.add('active'); loadLocalData('my_bookmarks', 'favoritesGrid', 'favoritesEmpty'); }
        if(tabName === 'history') { btns[2].classList.add('active'); loadLocalData('history_item_', 'historyGrid', 'historyEmpty', true); }
    }

    // --- LOCAL STORAGE LOADER (Untuk Favorit & History) ---
    function loadLocalData(storageKey, containerId, emptyId, isPrefix = false) {
        const container = document.getElementById(containerId);
        const emptyState = document.getElementById(emptyId);
        container.innerHTML = ''; // Reset
        
        let items = [];

        if (isPrefix) {
            // Logic khusus History (Prefix key)
            // Ambil semua key, filter, sort time
            let keys = [];
            for(let i=0; i<localStorage.length; i++) {
                if(localStorage.key(i).startsWith(storageKey)) keys.push(localStorage.key(i));
            }
            // Sort by timestamp (jika ada, kalau gak ada ya default)
            keys.forEach(key => {
                try { items.push(JSON.parse(localStorage.getItem(key))); } catch(e){}
            });
            // Urutkan dari yg terbaru (timestamp desc)
            items.sort((a, b) => (b.timestamp || 0) - (a.timestamp || 0));

        } else {
            // Logic khusus Bookmark (Single key array)
            items = JSON.parse(localStorage.getItem(storageKey) || '[]');
            items.reverse(); // Terbaru di atas
        }

        if (items.length === 0) {
            emptyState.style.display = 'block';
            return;
        } else {
            emptyState.style.display = 'none';
        }

        items.forEach(data => {
            if(!data.id) return;
            
            // Generate Link (Gunakan params jika ada, atau buat manual)
            let link = data.params ? `?${data.params.substring(1)}` : `?page=watch&id=${data.id}&source=${data.source || 'dramabox'}&title=${encodeURIComponent(data.title)}&cover=${encodeURIComponent(data.cover)}`;
            
            // Label khusus
            let label = isPrefix ? `Lanjut Ep ${data.lastEp}` : `Server ${data.source}`;
            let color = isPrefix ? 'var(--primary)' : '#888';

            const html = `
                <a href="${link}" class="movie-card">
                    <div class="card-img-wrap">
                        <img src="${data.cover}" loading="lazy" referrerpolicy="no-referrer" class="loaded">
                        <div class="card-overlay"><div class="play-circle"><i class="ri-play-fill"></i></div></div>
                        ${isPrefix ? `<div style="position:absolute;top:5px;right:5px;background:var(--primary);font-size:0.6rem;padding:2px 5px;border-radius:3px;color:white;">Ep ${data.lastEp}</div>` : ''}
                    </div>
                    <div class="card-detail">
                        <h3>${data.title}</h3>
                        <small style="color:${color}">${label}</small>
                    </div>
                </a>
            `;
            container.innerHTML += html;
        });
    }

    // Fungsi Hapus Data
    function clearStorage(type) {
        if(!confirm('Yakin ingin menghapus semua data ini?')) return;
        
        if (type === 'history') {
            Object.keys(localStorage).forEach(key => {
                if(key.startsWith('history_item_') || key.startsWith('watched_')) localStorage.removeItem(key);
            });
            loadLocalData('history_item_', 'historyGrid', 'historyEmpty', true);
        } else {
            localStorage.removeItem(type);
            loadLocalData(type, 'favoritesGrid', 'favoritesEmpty');
        }
    }
</script>
