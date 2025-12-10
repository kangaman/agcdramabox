<?php 
// 1. DATA PREPARATION
$info = $data['data']['dramaInfo'] ?? [];
$chapters = $data['data']['chapters'] ?? [];

// 2. AUTH CHECK
require_once 'app/Auth.php';
$auth = new Auth();
$isVip = $auth->isVip();

// 3. SETTINGS
$freeLimit = 5; 
// Kita ambil ID langsung dari URL agar konsisten dengan database
$urlId = $_GET['id'] ?? '';
?>

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

<div class="theater-bg" style="background-image: url('<?= $info['cover'] ?? '' ?>')"></div>

<div class="theater-container fade-in" id="theaterContainer">
    
    <div class="player-wrapper">
        <div class="video-frame" id="videoFrame">
            <div id="loadingSpinner" class="loading-overlay" style="display:none;">
                <div class="spinner"></div>
            </div>

            <video id="mainPlayer" controls poster="<?= $info['cover'] ?? '' ?>" class="custom-video" playsinline>
                Your browser does not support the video tag.
            </video>
            
            <div id="playerOverlay" class="paywall-overlay" style="display: none;">
                <div class="paywall-content">
                    <i id="overlayIcon" class="ri-lock-2-fill icon-lock"></i>
                    <h2 id="overlayTitle">Konten Premium</h2>
                    <p id="overlayDesc">Silakan upgrade ke VIP untuk melanjutkan.</p>
                    <div id="overlayButtons" class="paywall-actions">
                        <a href="/dashboard/billing" class="btn-upgrade">Beli Paket VIP</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="player-toolbar">
            <div class="toolbar-left">
                <label class="switch-toggle" title="Putar episode selanjutnya otomatis">
                    <input type="checkbox" id="autoNext" checked>
                    <span class="slider"></span>
                    <span class="label-text">Auto Next</span>
                </label>
            </div>
            <div class="toolbar-right">
                <button onclick="toggleCinema()" class="tool-btn" title="Mode Bioskop">
                    <i class="ri-fullscreen-line"></i> Cinema Mode
                </button>
                <button onclick="shareDrama()" class="tool-btn" title="Bagikan">
                    <i class="ri-share-forward-line"></i> Share
                </button>
                <a href="https://t.me/jejakintel" target="_blank" class="tool-btn error-btn" title="Lapor Video Rusak">
                    <i class="ri-alarm-warning-line"></i> Lapor
                </a>
            </div>
        </div>

        <div class="player-nav-controls">
            <button id="btnPrev" onclick="navEpisode(-1)" class="nav-btn disabled" disabled>
                <i class="ri-skip-back-fill"></i> Eps Sebelumnya
            </button>
            <button id="btnNext" onclick="navEpisode(1)" class="nav-btn">
                Eps Selanjutnya <i class="ri-skip-forward-fill"></i>
            </button>
        </div>

        <div class="video-info">
            <h1><?= htmlspecialchars($info['bookName'] ?? 'Tanpa Judul') ?></h1>
            
            <div class="meta-row">
                <div class="meta-item"><small>Total</small><span><?= count($chapters) ?> Eps</span></div>
                <div class="meta-item"><small>Views</small><span><?= number_format($info['followCount'] ?? 0) ?></span></div>
                <div class="meta-item"><small>Rating</small><span style="color:#ffd700">★ <?= $info['score'] ?? '5.0' ?></span></div>
                <div class="meta-item"><small>Rilis</small><span><?= isset($info['shelfTime']) ? date('Y', strtotime($info['shelfTime'])) : '-' ?></span></div>
            </div>

            <p class="synopsis"><?= nl2br(htmlspecialchars($info['introduction'] ?? '-')) ?></p>

            <div class="tags-container">
                <?php 
                $allTags = array_merge($info['labels'] ?? [], $info['tags'] ?? []);
                foreach($allTags as $tag): 
                ?>
                    <a href="/cari/<?= urlencode($tag) ?>" class="tag-pill"><?= htmlspecialchars($tag) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="playlist-wrapper" id="playlistWrapper">
        <div class="playlist-header">
            <div class="ph-top">
                <h3>Episode</h3>
                <button onclick="toggleSort()" class="sort-btn" title="Balik Urutan"><i class="ri-sort-desc"></i></button>
            </div>
            <div class="ph-search">
                <i class="ri-search-line"></i>
                <input type="text" id="epsSearch" placeholder="Cari nomor..." onkeyup="filterEpisodes()">
            </div>
        </div>
        
        <div class="playlist-scroll" id="playlistContainer">
            <?php if(!empty($chapters)): ?>
                <?php foreach($chapters as $idx => $chap): 
                    $num = $idx + 1;
                    $isLocked = !$isVip && ($num > $freeLimit);
                    $videoUrl = $chap['mp4'] ?? $chap['url'] ?? $chap['link'] ?? '';
                    $hasLink = !empty($videoUrl);
                ?>
                <button onclick="playEpisode('<?= $videoUrl ?>', <?= $isLocked?'true':'false' ?>, <?= $hasLink?'true':'false' ?>, this, <?= $num ?>)" 
                        class="eps-item <?= $idx===0 ? 'active' : '' ?>" 
                        data-num="<?= $num ?>"
                        id="eps-btn-<?= $num ?>">
                    
                    <div class="eps-left">
                        <span class="eps-num"><?= str_pad($num, 2, '0', STR_PAD_LEFT) ?></span>
                        <div class="playing-anim"><span></span><span></span><span></span></div>
                    </div>
                    
                    <div class="eps-details">
                        <span class="eps-name">Episode <?= $num ?></span>
                        <span class="eps-status">
                            <?php if($isLocked): ?> <i class="ri-lock-fill" style="color:#ffd700"></i> VIP
                            <?php elseif(!$hasLink): ?> <i class="ri-eye-off-line" style="color:#666"></i> Belum Rilis
                            <?php else: ?> <i class="ri-play-circle-line" style="color:#4ade80"></i> Gratis <?php endif; ?>
                        </span>
                    </div>
                    
                    <i class="ri-eye-fill watched-icon" style="display:none; color:#4ade80; margin-left:auto;"></i>
                </button>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding:20px; text-align:center; color:#666;">Data kosong.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
var video = document.getElementById('mainPlayer');
var hls = null;
// FIX PENTING: Gunakan ID dari URL PHP agar konsisten
var dramaId = '<?= $urlId ?>'; 

// 1. INIT
document.addEventListener('DOMContentLoaded', function() {
    if(!dramaId) console.error("ID Drama Kosong!");
    loadHistory(); 
    var firstEps = document.querySelector('.eps-item');
    if(firstEps) firstEps.click();
});

// 2. AUTO NEXT
video.addEventListener('ended', function() {
    if(document.getElementById('autoNext').checked) {
        navEpisode(1);
    }
});

function navEpisode(direction) {
    var current = document.querySelector('.eps-item.active');
    var target = null;
    var isReversed = document.getElementById('playlistContainer').classList.contains('reversed');
    
    if (direction === 1) { // NEXT
        target = isReversed ? current.previousElementSibling : current.nextElementSibling;
    } else { // PREV
        target = isReversed ? current.nextElementSibling : current.previousElementSibling;
    }

    if(target) {
        target.click();
        target.scrollIntoView({behavior: "smooth", block: "center"});
    }
}

function updateNavButtons(currentBtn) {
    var btnPrev = document.getElementById('btnPrev');
    var btnNext = document.getElementById('btnNext');
    var isReversed = document.getElementById('playlistContainer').classList.contains('reversed');

    var prevEl = isReversed ? currentBtn.nextElementSibling : currentBtn.previousElementSibling;
    var nextEl = isReversed ? currentBtn.previousElementSibling : currentBtn.nextElementSibling;

    btnPrev.disabled = !prevEl;
    btnPrev.classList.toggle('disabled', !prevEl);
    
    btnNext.disabled = !nextEl;
    btnNext.classList.toggle('disabled', !nextEl);
}

// 3. MAIN PLAYER FUNCTION
function playEpisode(url, isLocked, hasLink, btn, epsNum) {
    // UI Loading
    document.getElementById('loadingSpinner').style.display = 'flex';
    
    // UI Reset
    document.querySelectorAll('.eps-item').forEach(b => b.classList.remove('active'));
    if(btn) {
        btn.classList.add('active');
        markAsWatched(epsNum);
        updateNavButtons(btn);
    }

    var overlay = document.getElementById('playerOverlay');
    var btns = document.getElementById('overlayButtons');

    // Reset Overlay
    overlay.style.display = 'none';
    video.style.display = 'block';

    if (isLocked) {
        showOverlay('ri-vip-crown-2-fill', '#ffd700', 'Konten Premium', 'Upgrade VIP untuk lanjut nonton.', true);
        document.getElementById('loadingSpinner').style.display = 'none';
        return;
    }
    if (!hasLink) {
        showOverlay('ri-file-shred-line', '#666', 'Belum Tersedia', 'Server belum merilis video ini.', false);
        document.getElementById('loadingSpinner').style.display = 'none';
        return;
    }

    if (Hls.isSupported() && url.includes('.m3u8')) {
        if(hls) hls.destroy();
        hls = new Hls();
        hls.loadSource(url);
        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED, function() { 
            video.play(); 
            document.getElementById('loadingSpinner').style.display = 'none';
        });
    } else {
        video.src = url;
        video.play().then(() => {
            document.getElementById('loadingSpinner').style.display = 'none';
        }).catch(() => {
            document.getElementById('loadingSpinner').style.display = 'none';
        });
    }
}

// 4. HELPER FUNCTIONS
function showOverlay(iconClass, color, titleText, descText, showBtn) {
    var overlay = document.getElementById('playerOverlay');
    video.style.display = 'none';
    video.pause();
    overlay.style.display = 'flex';
    document.getElementById('overlayIcon').className = iconClass;
    document.getElementById('overlayIcon').style.color = color;
    document.getElementById('overlayTitle').innerText = titleText;
    document.getElementById('overlayDesc').innerText = descText;
    document.getElementById('overlayButtons').style.display = showBtn ? 'block' : 'none';
}

function toggleCinema() {
    document.getElementById('theaterContainer').classList.toggle('cinema-mode');
}

function filterEpisodes() {
    var input = document.getElementById('epsSearch').value.toLowerCase();
    var items = document.getElementsByClassName('eps-item');
    for (var i = 0; i < items.length; i++) {
        var name = items[i].querySelector('.eps-name').innerText.toLowerCase();
        var num = items[i].querySelector('.eps-num').innerText;
        if (name.includes(input) || num.includes(input)) {
            items[i].style.display = "";
        } else {
            items[i].style.display = "none";
        }
    }
}

function toggleSort() {
    var container = document.getElementById('playlistContainer');
    container.classList.toggle('reversed');
    var items = Array.from(container.children);
    container.innerHTML = '';
    items.reverse().forEach(item => container.appendChild(item));
    var current = document.querySelector('.eps-item.active');
    if(current) updateNavButtons(current);
}

// 5. HISTORY SYSTEM (LOCAL + DATABASE)
function markAsWatched(num) {
    // A. LocalStorage (Untuk Homepage & Tanda Mata)
    var history = JSON.parse(localStorage.getItem('watched_' + dramaId) || '[]');
    if(!history.includes(num)) {
        history.push(num);
        localStorage.setItem('watched_' + dramaId, JSON.stringify(history));
    }
    
    var dramaData = {
        id: dramaId,
        title: '<?= addslashes($info['bookName'] ?? 'Drama') ?>',
        cover: '<?= $info['cover'] ?? '' ?>',
        lastEp: num,
        timestamp: new Date().getTime()
    };
    localStorage.setItem('history_item_' + dramaId, JSON.stringify(dramaData));

    // Update UI
    var icon = document.querySelector(`#eps-btn-${num} .watched-icon`);
    if(icon) icon.style.display = 'block';

    // B. Database (Untuk Dashboard - FIX INI YANG SEBELUMNYA HILANG)
    var isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    
    if (isLoggedIn && dramaId) {
        var apiData = {
            id: dramaId,
            title: '<?= addslashes($info['bookName'] ?? 'Drama Tanpa Judul') ?>',
            cover: '<?= $info['cover'] ?? '' ?>',
            episode: num,
            total: <?= count($chapters) ?>
        };

        fetch('/index.php?page=api_save_history', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(apiData)
        })
        .then(res => res.json())
        .then(d => {
            if(!d.status) console.error("Gagal simpan DB:", d.msg);
        })
        .catch(err => console.error("Error Fetch:", err));
    }
}

function loadHistory() {
    var history = JSON.parse(localStorage.getItem('watched_' + dramaId) || '[]');
    history.forEach(num => {
        var el = document.querySelector(`#eps-btn-${num} .watched-icon`);
        if(el) el.style.display = 'block';
    });
}

function shareDrama() {
    if (navigator.share) {
        navigator.share({ title: '<?= htmlspecialchars($info['bookName'] ?? '') ?>', url: window.location.href });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Link tersalin!');
    }
}
</script>

<style>
/* --- THEATER LAYOUT --- */
.theater-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100vh; background-size: cover; filter: blur(80px) brightness(0.2); z-index: -1; }
.theater-container { display: grid; grid-template-columns: 1fr 340px; gap: 25px; max-width: 1400px; margin: 0 auto; padding: 20px; transition: 0.3s; }

/* CINEMA MODE */
.theater-container.cinema-mode { grid-template-columns: 1fr; }
.theater-container.cinema-mode .playlist-wrapper { display: none; }
.theater-container.cinema-mode .video-frame { height: 80vh; }

.video-frame { position: relative; aspect-ratio: 16/9; background: #000; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.1); }
.custom-video { width: 100%; height: 100%; }

/* LOADING SPINNER */
.loading-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #000; z-index: 5; display: flex; align-items: center; justify-content: center; }
.spinner { width: 40px; height: 40px; border: 4px solid rgba(255,255,255,0.1); border-left-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* TOOLBAR & NAV */
.player-toolbar { display: flex; justify-content: space-between; align-items: center; background: #151518; padding: 10px 15px; border-radius: 0 0 12px 12px; margin-top: -5px; border: 1px solid rgba(255,255,255,0.1); border-top: none; }
.player-nav-controls { display: flex; gap: 10px; margin-top: 15px; }
.nav-btn { flex: 1; padding: 12px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.05); color: white; border-radius: 8px; cursor: pointer; transition: 0.2s; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 8px; }
.nav-btn:hover:not(:disabled) { background: var(--primary); border-color: var(--primary); }
.nav-btn.disabled { opacity: 0.3; cursor: not-allowed; }

.tool-btn { background: transparent; border: none; color: #aaa; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; font-size: 0.85rem; padding: 5px 10px; border-radius: 4px; transition: 0.2s; }
.tool-btn:hover { color: white; background: rgba(255,255,255,0.1); }
.error-btn:hover { color: #ff4757; background: rgba(255, 71, 87, 0.1); }

/* SWITCH */
.switch-toggle { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.85rem; color: #ddd; }
.switch-toggle input { display: none; }
.switch-toggle .slider { width: 30px; height: 16px; background: #555; border-radius: 20px; position: relative; transition: 0.3s; display: inline-block; }
.switch-toggle .slider:before { content: ""; position: absolute; width: 12px; height: 12px; border-radius: 50%; background: white; top: 2px; left: 2px; transition: 0.3s; }
.switch-toggle input:checked + .slider { background: var(--primary); }
.switch-toggle input:checked + .slider:before { transform: translateX(14px); }

/* INFO SECTION */
.video-info h1 { font-size: 2rem; color: white; margin-top: 20px; margin-bottom: 15px; }
.meta-row { display: flex; gap: 20px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
.meta-item { display: flex; flex-direction: column; }
.meta-item small { color: #888; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; }
.meta-item span { color: white; font-weight: bold; }
.synopsis { color: #ccc; line-height: 1.6; margin-bottom: 20px; font-size: 0.95rem; }
.tags-container { display: flex; flex-wrap: wrap; gap: 10px; }
.tag-pill { background: rgba(255,255,255,0.1); color: #ddd; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; transition: 0.2s; border: 1px solid transparent; text-decoration: none; }
.tag-pill:hover { background: var(--primary); color: white; border-color: var(--primary); }

/* PLAYLIST */
.playlist-wrapper { background: #151518; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); height: 650px; display: flex; flex-direction: column; overflow: hidden; }
.playlist-header { padding: 15px; background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.05); }
.ph-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.ph-search { position: relative; }
.ph-search input { width: 100%; background: #000; border: 1px solid #333; padding: 8px 10px 8px 30px; border-radius: 4px; color: white; font-size: 0.85rem; }
.ph-search i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #666; }
.sort-btn { background: transparent; border: none; color: #aaa; cursor: pointer; font-size: 1.2rem; }
.sort-btn:hover { color: white; }

.playlist-scroll { flex: 1; overflow-y: auto; padding: 10px; }
.playlist-scroll::-webkit-scrollbar { width: 4px; }
.playlist-scroll::-webkit-scrollbar-thumb { background: #444; }

.eps-item { display: flex; align-items: center; gap: 12px; width: 100%; background: transparent; border: none; padding: 12px 15px; border-radius: 8px; cursor: pointer; text-align: left; transition: 0.2s; margin-bottom: 2px; }
.eps-item:hover { background: rgba(255,255,255,0.05); }
.eps-item.active { background: rgba(229, 9, 20, 0.15); border-left: 3px solid var(--primary); }
.eps-num { font-family: monospace; font-size: 1.1rem; color: #555; font-weight: bold; width: 25px; }
.eps-name { display: block; color: #eee; font-size: 0.9rem; margin-bottom: 2px; }
.eps-status { font-size: 0.7rem; color: #888; display: flex; align-items: center; gap: 5px; }
.active .eps-num { color: var(--primary); }
.active .eps-name { color: white; font-weight: bold; }

.paywall-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 10; text-align: center; padding: 20px; }
.icon-lock { font-size: 3rem; margin-bottom: 10px; color: #ffd700; }
.btn-upgrade { display: inline-block; margin-top: 15px; padding: 10px 25px; background: var(--primary); color: white; border-radius: 50px; font-weight: bold; text-decoration: none; }
.playing-anim { display: none; gap: 2px; height: 12px; align-items: flex-end; }
.active .playing-anim { display: flex; }
.active .eps-num { display: none; }
.playing-anim span { width: 3px; background: var(--primary); animation: wave 1s infinite ease-in-out; }
.playing-anim span:nth-child(2) { animation-delay: 0.2s; height: 60%; }
.playing-anim span:nth-child(3) { animation-delay: 0.4s; height: 30%; }
@keyframes wave { 0%, 100% { height: 20%; } 50% { height: 100%; } }

@media (max-width: 900px) {
    .theater-container { grid-template-columns: 1fr; }
    .playlist-wrapper { height: 450px; }
}
</style>