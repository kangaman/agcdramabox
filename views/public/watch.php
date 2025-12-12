<?php 
// ==========================================
// 1. DATA PREPARATION (Server Side)
// ==========================================
$raw = $data['data'] ?? $data ?? [];
$source = $_GET['source'] ?? 'dramabox'; 

// Normalisasi Data
if (isset($raw['dramaInfo'])) {
    $info = $raw['dramaInfo'];
    $chapters = $raw['chapters'] ?? [];
} else {
    $info = $raw; 
    $chapters = $raw['chapters'] ?? $raw['chapter_list'] ?? []; 
}

// Data Prioritas (URL > API)
$urlTitle = isset($_GET['title']) ? urldecode($_GET['title']) : '';
$urlCover = isset($_GET['cover']) ? urldecode($_GET['cover']) : '';

$title = !empty($urlTitle) ? $urlTitle : ($info['bookName'] ?? $info['title'] ?? 'Nonton Drama');
$rawCover = !empty($urlCover) ? $urlCover : ($info['cover'] ?? $info['thumbnail'] ?? '');

// Fix Cover Image
if (strpos($rawCover, '.heic') !== false) {
    $cleanUrl = str_replace(['http://', 'https://'], '', $rawCover);
    $cover = 'https://wsrv.nl/?url=' . urlencode($cleanUrl) . '&output=jpg&q=80';
} else {
    $cover = str_replace('http://', 'https://', $rawCover);
}

// Metadata
$intro = $info['introduction'] ?? $info['abstract'] ?? 'Deskripsi tidak tersedia.';
$rating = $info['score'] ?? '5.0';
$views = number_format($info['followCount'] ?? $info['read_count'] ?? 0);
$tags = $info['tags'] ?? $info['stat_infos'] ?? [];

// 2. AUTHENTICATION
require_once 'app/Auth.php';
$auth = new Auth();
$isVip = $auth->isVip();
$freeLimit = 5; // Batas episode gratis untuk user biasa
$urlId = $_GET['id'] ?? '';

// 3. HISTORY SYNC (DATABASE)
$lastEpDB = 0;
if (isset($_SESSION['user_id']) && $urlId) {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT episode FROM history WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$_SESSION['user_id'], $urlId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row) {
        $lastEpDB = $row['episode'];
    }
}

// 4. LOAD CONFIG IKLAN (Dari Index/Global)
global $webConfig;
?>

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

<div class="theater-bg" style="background-image: url('<?= $cover ?>')"></div>

<div class="theater-container fade-in" id="theaterContainer">
    
    <div class="player-wrapper">
        <div class="video-frame" id="videoFrame">
            <div id="loadingSpinner" class="loading-overlay" style="display:none;">
                <div class="spinner"></div><p style="margin-top:15px; color:#fff;">Memuat Video...</p>
            </div>
            
            <video id="mainPlayer" controls poster="<?= $cover ?>" class="custom-video" playsinline></video>
            
            <div id="playerOverlay" class="paywall-overlay" style="display: none;">
                <div class="paywall-content">
                    <i id="overlayIcon" class="ri-lock-2-fill icon-lock"></i>
                    <h2 id="overlayTitle">Konten Premium</h2>
                    <p id="overlayDesc">Upgrade VIP untuk melanjutkan.</p>
                    <div id="overlayButtons" class="paywall-actions">
                        <a href="/dashboard/billing" class="btn-upgrade">Beli VIP</a>
                    </div>
                </div>
            </div>
        </div>

        <?php if(!empty($webConfig['ad_player'])): ?>
        <div class="ad-player-slot">
            <small>ADVERTISEMENT</small>
            <div class="ad-content">
                <?= $webConfig['ad_player'] ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="player-toolbar">
            <div class="toolbar-left">
                <div class="skip-controls">
                    <button onclick="skipTime(-10)" class="tool-btn" title="Mundur 10s"><i class="ri-replay-10-line"></i> -10s</button>
                    <button onclick="skipTime(10)" class="tool-btn" title="Maju 10s">+10s <i class="ri-forward-10-line"></i></button>
                </div>
                
                <label class="switch-toggle">
                    <input type="checkbox" id="autoNext" checked>
                    <span class="slider"></span>
                    <span class="label-text">Auto Next</span>
                </label>
            </div>

            <div class="toolbar-right">
                <select id="speedSelect" onchange="changeSpeed(this)" class="tool-select">
                    <option value="0.5">0.5x</option>
                    <option value="1.0" selected>Normal</option>
                    <option value="1.25">1.25x</option>
                    <option value="1.5">1.5x</option>
                    <option value="2.0">2.0x</option>
                </select>

                <button onclick="toggleCinema()" class="tool-btn" title="Mode Bioskop"><i class="ri-fullscreen-line"></i> <span class="label-text">Cinema</span></button>
                <button onclick="toggleBookmark()" class="tool-btn" id="btnBookmark" title="Simpan ke Favorit">
                    <i class="ri-bookmark-line"></i> <span class="label-text">Simpan</span>
                </button>
                <button onclick="reportVideo()" class="tool-btn report-btn" title="Lapor Rusak">
                    <i class="ri-alarm-warning-line"></i> <span class="label-text">Lapor</span>
                </button>
            </div>
        </div>

        <div class="player-nav-controls">
            <button id="btnPrev" onclick="navEpisode(-1)" class="nav-btn disabled" disabled><i class="ri-skip-back-fill"></i> Prev Episode</button>
            <button id="btnNext" onclick="navEpisode(1)" class="nav-btn">Next Episode <i class="ri-skip-forward-fill"></i></button>
        </div>

        <div class="video-info">
            <h1 class="drama-title"><?= htmlspecialchars($title) ?></h1>
            <div class="meta-badges">
                <span class="badge-server">SERVER: <?= strtoupper($source) ?></span>
                <span class="badge-rating"><i class="ri-star-fill"></i> <?= $rating ?></span>
                <span class="badge-info"><i class="ri-eye-line"></i> <?= $views ?></span>
                <span class="badge-info"><i class="ri-film-line"></i> <?= count($chapters) ?> Eps</span>
            </div>
            
            <?php if(!empty($tags)): ?>
            <div class="tags-container">
                <?php 
                if(is_string($tags)) $tags = explode(',', $tags);
                foreach($tags as $t) {
                    if(!is_array($t)) echo "<a href='/?page=search&q=".urlencode(trim($t))."&source=$source' class='tag-pill'>".htmlspecialchars(trim($t))."</a>"; 
                }
                ?>
            </div>
            <?php endif; ?>
            
            <div class="synopsis-box">
                <h3>Sinopsis</h3>
                <p><?= nl2br(htmlspecialchars($intro)) ?></p>
            </div>
        </div>
    </div>

    <div class="playlist-wrapper" id="playlistWrapper">
        <div class="playlist-header">
            <h3>Daftar Episode</h3>
            <div class="ph-search"><i class="ri-search-line"></i><input type="text" id="epsSearch" placeholder="Cari episode..." onkeyup="filterEpisodes()"></div>
        </div>
        <div class="playlist-scroll" id="playlistContainer">
            <?php if(!empty($chapters)): ?>
                <?php foreach($chapters as $idx => $chap): 
                    $num = $idx + 1;
                    $isLocked = !$isVip && ($num > $freeLimit);
                    $vidId = ($source === 'melolo') ? ($chap['vid'] ?? $chap['id'] ?? '') : '';
                    $videoUrl = ($source !== 'melolo') ? ($chap['mp4'] ?? $chap['url'] ?? '') : '';
                    $hasLink = !empty($vidId) || !empty($videoUrl);
                ?>
                <button onclick="playEpisode('<?= $videoUrl ?>', '<?= $vidId ?>', <?= $isLocked?'true':'false' ?>, <?= $hasLink?'true':'false' ?>, this, <?= $num ?>)" 
                        class="eps-item <?= $idx===0 ? 'active' : '' ?>" data-num="<?= $num ?>" id="eps-btn-<?= $num ?>">
                    <div class="eps-left"><span class="eps-num"><?= str_pad($num, 2, '0', STR_PAD_LEFT) ?></span><div class="playing-anim"><span></span><span></span><span></span></div></div>
                    <div class="eps-details"><span class="eps-name">Episode <?= $num ?></span><span class="eps-status"><?= $isLocked ? '<i class="ri-lock-fill" style="color:#ffd700"></i> VIP' : '<i class="ri-play-circle-line" style="color:#4ade80"></i> Gratis' ?></span></div>
                    <i class="ri-eye-fill watched-icon" style="display:none; color:#4ade80; margin-left:auto;"></i>
                </button>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding:40px 20px; text-align:center; color:#666;">Episode belum tersedia.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// --- GLOBAL VARIABLES ---
var dramaId = '<?= $urlId ?>'; 
var currentSource = '<?= $source ?>';
var dramaTitle = '<?= addslashes($title) ?>';
var lastEpServer = <?= $lastEpDB ?>; // Data dari DB
var dramaCover = '<?= addslashes($cover) ?>';
var totalEps = <?= count($chapters) ?>;
var isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;

var video = document.getElementById('mainPlayer');
var hls = null;
var currentEpNum = 0;

// 1. INIT
document.addEventListener('DOMContentLoaded', function() {
    loadHistory(); 
    checkBookmark();
    
    // Logika Auto-Resume: Prioritas Server > LocalStorage > Ep 1
    var startEp = 1;
    if (lastEpServer > 0) {
        startEp = lastEpServer;
    } else {
        var localLast = getLastWatchedEp();
        if (localLast > 0) startEp = localLast;
    }

    var btn = document.getElementById('eps-btn-' + startEp);
    if(btn) {
        btn.click();
        // Scroll ke tombol
        setTimeout(() => {
            btn.scrollIntoView({behavior: 'smooth', block: 'center'});
        }, 500);
    } else {
        var first = document.querySelector('.eps-item');
        if(first) first.click();
    }
});

// Fitur: Simpan Detik Terakhir (Resume Playback)
video.addEventListener('timeupdate', function() {
    if(video.currentTime > 5 && !video.paused && currentEpNum > 0) {
        localStorage.setItem('resume_' + dramaId + '_' + currentEpNum, video.currentTime);
    }
});

// Fitur: Controls
function skipTime(seconds) { video.currentTime += seconds; }
function changeSpeed(el) { video.playbackRate = parseFloat(el.value); }

// Fitur: Auto Next
video.addEventListener('ended', function() {
    if(document.getElementById('autoNext').checked) navEpisode(1);
});

// 2. MAIN PLAY FUNCTION
function playEpisode(directUrl, vidId, isLocked, hasLink, btn, epsNum) {
    currentEpNum = epsNum;

    // UI Updates
    document.getElementById('loadingSpinner').style.display = 'flex';
    document.getElementById('playerOverlay').style.display = 'none';
    document.querySelectorAll('.eps-item').forEach(b => b.classList.remove('active'));
    
    if(btn) { 
        btn.classList.add('active'); 
        markAsWatched(epsNum); 
        updateNavButtons(btn); 
    }
    
    video.style.display = 'block'; 
    video.pause();

    // Cek Resume Time
    var resumeTime = localStorage.getItem('resume_' + dramaId + '_' + epsNum);

    // Handler Kondisi
    if (isLocked) { showOverlay('ri-vip-crown-2-fill', '#ffd700', 'Konten Premium', 'Upgrade VIP untuk menonton.', true); return; }
    if (!hasLink) { showOverlay('ri-file-shred-line', '#666', 'Belum Tersedia', 'Episode ini belum dirilis.', false); return; }

    if (currentSource === 'melolo' && vidId) {
        fetch(`/index.php?page=api_get_stream&source=melolo&id=${vidId}`)
            .then(res => res.json())
            .then(resp => {
                let streamUrl = resp.main_url || resp.data?.main_url || resp.url || '';
                if(streamUrl) {
                    if (streamUrl.startsWith('http://')) streamUrl = streamUrl.replace('http://', 'https://');
                    loadVideo(streamUrl, resumeTime);
                } else {
                    console.error("Link failed:", resp);
                    showOverlay('ri-error-warning-line', '#ff4757', 'Gagal', 'Video tidak ditemukan.', false);
                }
            })
            .catch(err => showOverlay('ri-wifi-off-line', '#ff4757', 'Error', 'Gagal koneksi server.', false));
    } else {
        loadVideo(directUrl, resumeTime);
    }
}

function loadVideo(url, startTime) {
    if(!url) { document.getElementById('loadingSpinner').style.display = 'none'; return; }
    
    function onReady() {
        document.getElementById('loadingSpinner').style.display = 'none';
        if(startTime) video.currentTime = parseFloat(startTime);
        video.play().catch(e => console.log("Autoplay blocked"));
    }

    if (Hls.isSupported() && url.includes('.m3u8')) {
        if(hls) hls.destroy();
        hls = new Hls(); hls.loadSource(url); hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED, onReady);
        hls.on(Hls.Events.ERROR, function(event, data) { if(data.fatal) video.src = url; });
    } else {
        video.src = url; 
        video.addEventListener('loadedmetadata', onReady, {once:true});
        video.play().catch(e=>{});
    }
}

// 3. HISTORY & SYNC
function markAsWatched(num) {
    if(!dramaId) return;

    // A. LocalStorage
    var k = 'watched_' + dramaId;
    var h = JSON.parse(localStorage.getItem(k) || '[]');
    if(!h.includes(num)) { 
        h.push(num); 
        localStorage.setItem(k, JSON.stringify(h)); 
    }
    
    // Simpan Metadata (Untuk Home > Lanjut Nonton)
    var dramaData = {
        id: dramaId,
        title: dramaTitle,
        cover: dramaCover,
        lastEp: num,
        timestamp: new Date().getTime(),
        source: currentSource
    };
    localStorage.setItem('history_item_' + dramaId, JSON.stringify(dramaData));

    // Update Icon
    var ic = document.querySelector(`#eps-btn-${num} .watched-icon`);
    if(ic) ic.style.display = 'block';

    // B. Database Sync (Jika Login)
    if (isLoggedIn) {
        var apiData = {
            id: dramaId, title: dramaTitle, cover: dramaCover,
            episode: num, total: totalEps
        };
        fetch('/index.php?page=api_save_history', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(apiData)
        });
    }
}

function loadHistory() {
    if(!dramaId) return;
    
    var localData = JSON.parse(localStorage.getItem('watched_' + dramaId) || '[]');

    // Gabungkan data server ke local jika belum ada
    if (lastEpServer > 0 && !localData.includes(lastEpServer)) {
        localData.push(lastEpServer);
        localStorage.setItem('watched_' + dramaId, JSON.stringify(localData));
    }

    // Tampilkan tanda mata
    localData.forEach(n => { 
        var el = document.querySelector(`#eps-btn-${n} .watched-icon`); 
        if(el) el.style.display='block'; 
    });
}

function getLastWatchedEp() {
    var h = JSON.parse(localStorage.getItem('history_item_' + dramaId) || '{}');
    return h.lastEp || 0;
}

// 4. BOOKMARK & UTILS
function toggleBookmark() {
    let bookmarks = JSON.parse(localStorage.getItem('my_bookmarks') || '[]');
    const index = bookmarks.findIndex(b => b.id === dramaId);
    
    if (index === -1) {
        bookmarks.push({
            id: dramaId, title: dramaTitle, cover: dramaCover,
            source: currentSource, timestamp: new Date().getTime()
        });
        alert('Disimpan ke Favorit!');
    } else {
        bookmarks.splice(index, 1);
        alert('Dihapus dari Favorit.');
    }
    localStorage.setItem('my_bookmarks', JSON.stringify(bookmarks));
    checkBookmark();
}

function checkBookmark() {
    let bookmarks = JSON.parse(localStorage.getItem('my_bookmarks') || '[]');
    const btn = document.querySelector('#btnBookmark i');
    if (bookmarks.find(b => b.id === dramaId)) {
        btn.className = 'ri-bookmark-fill';
        btn.style.color = 'var(--primary)';
    } else {
        btn.className = 'ri-bookmark-line';
        btn.style.color = '#aaa';
    }
}

function reportVideo() {
    var msg = `Lapor Min, Video Error:\nJudul: ${dramaTitle}\nEpisode: ${currentEpNum}\nID: ${dramaId}`;
    var url = `https://t.me/jejakintel?text=` + encodeURIComponent(msg); // Ganti username telegram Anda
    if(confirm("Video error? Laporkan ke Admin via Telegram?")) {
        window.open(url, '_blank');
    }
}

// Helpers
function navEpisode(dir) { var cur = document.querySelector('.eps-item.active'); if(!cur) return; var target = dir===1 ? cur.nextElementSibling : cur.previousElementSibling; if(target) target.click(); }
function updateNavButtons(btn) { document.getElementById('btnPrev').disabled = !btn.previousElementSibling; document.getElementById('btnNext').disabled = !btn.nextElementSibling; }
function showOverlay(icon, col, title, desc, btn) { 
    var ov = document.getElementById('playerOverlay'); video.style.display = 'none'; video.pause(); ov.style.display = 'flex'; 
    document.getElementById('overlayIcon').className = icon; document.getElementById('overlayIcon').style.color = col; 
    document.getElementById('overlayTitle').innerText = title; document.getElementById('overlayDesc').innerText = desc; 
    document.getElementById('overlayButtons').style.display = btn ? 'block' : 'none'; 
    document.getElementById('loadingSpinner').style.display = 'none'; 
}
function toggleCinema() { document.getElementById('theaterContainer').classList.toggle('cinema-mode'); }
function filterEpisodes() { var v = document.getElementById('epsSearch').value.toLowerCase(); document.querySelectorAll('.eps-item').forEach(el => { var t = el.innerText.toLowerCase(); el.style.display = t.includes(v) ? "" : "none"; }); }
</script>

<style>
/* --- BASE PLAYER CSS --- */
.theater-bg { position:fixed; top:0; left:0; width:100%; height:100vh; background-size:cover; filter:blur(80px) brightness(0.3); z-index:-1; }
.theater-container { display:grid; grid-template-columns:1fr 340px; gap:30px; max-width:1400px; margin:0 auto; padding:30px 20px; }
.theater-container.cinema-mode { grid-template-columns:1fr; }
.theater-container.cinema-mode .playlist-wrapper { display:none; }
.video-frame { position:relative; aspect-ratio:16/9; background:#000; border-radius:16px; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.1); }
.custom-video { width:100%; height:100%; }
.loading-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:#000; z-index:5; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.spinner { width:40px; height:40px; border:4px solid rgba(255,255,255,0.1); border-left-color:var(--primary); border-radius:50%; animation:spin 1s linear infinite; }
@keyframes spin { 100% { transform:rotate(360deg); } }

/* --- AD SLOT --- */
.ad-player-slot { margin-top:15px; text-align:center; background:#0a0a0c; padding:15px; border-radius:12px; border:1px solid rgba(255,255,255,0.05); }
.ad-player-slot small { display:block; color:#444; font-size:0.7rem; margin-bottom:5px; letter-spacing:1px; }

/* --- TOOLBAR --- */
.player-toolbar { display:flex; justify-content:space-between; align-items:center; background:#151518; padding:12px 20px; border-radius:0 0 16px 16px; margin-top:-5px; border:1px solid rgba(255,255,255,0.1); border-top:none; flex-wrap:wrap; gap:10px; }
.toolbar-left, .toolbar-right { display:flex; align-items:center; gap:10px; }
.tool-btn { background:transparent; border:none; color:#aaa; cursor:pointer; display:inline-flex; align-items:center; gap:6px; padding:6px 10px; transition:0.3s; font-size:0.9rem; border-radius:6px; }
.tool-btn:hover { color:white; background:rgba(255,255,255,0.05); }
.tool-btn.report-btn:hover { color:#ff4757; }
.tool-select { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; outline: none; }
.tool-select option { background: #151518; }

/* --- SKIP & TOGGLE --- */
.skip-controls { display:flex; gap:5px; border-right:1px solid rgba(255,255,255,0.1); padding-right:15px; margin-right:5px; }
.switch-toggle { display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.9rem; color:#ddd; }
.switch-toggle input { display:none; }
.switch-toggle .slider { width:36px; height:20px; background:#444; border-radius:20px; position:relative; transition:0.3s; display:inline-block; }
.switch-toggle .slider:before { content:""; position:absolute; width:16px; height:16px; border-radius:50%; background:white; top:2px; left:2px; transition:0.3s; }
.switch-toggle input:checked + .slider { background:var(--primary); }
.switch-toggle input:checked + .slider:before { transform:translateX(16px); }

/* --- NAVIGATION & INFO --- */
.player-nav-controls { display:flex; gap:15px; margin-top:20px; }
.nav-btn { flex:1; padding:14px; background:rgba(255,255,255,0.05); color:white; border:1px solid rgba(255,255,255,0.1); border-radius:10px; cursor:pointer; font-weight:600; transition:0.3s; display:flex; align-items:center; justify-content:center; gap:10px; }
.nav-btn:hover:not(:disabled) { background:var(--primary); border-color:var(--primary); }
.nav-btn.disabled { opacity:0.3; cursor:not-allowed; }

.video-info { margin-top:30px; }
.drama-title { font-size:2rem; font-weight:800; margin-bottom:15px; line-height:1.2; text-shadow:0 2px 10px rgba(0,0,0,0.5); }
.meta-badges { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
.meta-badges span { padding:5px 12px; border-radius:6px; font-size:0.8rem; font-weight:600; display:inline-flex; align-items:center; gap:5px; }
.badge-server { background:var(--primary); color:white; }
.badge-rating { background:#ffd700; color:#000; }
.badge-info { background:rgba(255,255,255,0.1); color:#ccc; }
.tags-container { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:25px; }
.tag-pill { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); padding:6px 16px; border-radius:50px; font-size:0.85rem; color:#ccc; text-decoration:none; transition:0.3s; }
.tag-pill:hover { background:var(--primary); color:white; border-color:var(--primary); }
.synopsis-box h3 { font-size:1.1rem; margin-bottom:10px; color:#fff; border-left:4px solid var(--primary); padding-left:10px; }
.synopsis-box p { color:#ccc; line-height:1.7; font-size:1rem; }

/* --- PLAYLIST --- */
.playlist-wrapper { background:#151518; border-radius:16px; height:700px; display:flex; flex-direction:column; border:1px solid rgba(255,255,255,0.1); overflow:hidden; }
.playlist-header { padding:20px; background:rgba(0,0,0,0.2); border-bottom:1px solid rgba(255,255,255,0.05); }
.ph-search { position:relative; margin-top:10px; }
.ph-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#666; }
.ph-search input { width:100%; background:#0a0a0c; border:1px solid rgba(255,255,255,0.1); padding:10px 10px 10px 35px; color:white; border-radius:8px; font-size:0.9rem; transition:0.3s; }
.ph-search input:focus { border-color:var(--primary); outline:none; }
.playlist-scroll { flex:1; overflow-y:auto; padding:10px; }
.playlist-scroll::-webkit-scrollbar { width:5px; }
.playlist-scroll::-webkit-scrollbar-thumb { background:#444; border-radius:10px; }
.eps-item { display:flex; align-items:center; gap:15px; width:100%; background:transparent; border:none; padding:15px; color:#eee; cursor:pointer; text-align:left; border-radius:10px; transition:0.2s; margin-bottom:5px; border:1px solid transparent; }
.eps-item:hover { background:rgba(255,255,255,0.05); }
.eps-item.active { background:rgba(229,9,20,0.1); border-color:var(--primary); }
.eps-num { font-family:monospace; font-size:1.1rem; color:#666; font-weight:bold; width:30px; }
.active .eps-num { color:var(--primary); }
.eps-name { font-weight:600; font-size:0.95rem; }
.eps-status { font-size:0.75rem; color:#888; margin-left:auto; display:flex; align-items:center; gap:5px; }
.paywall-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.95); display:flex; flex-direction:column; align-items:center; justify-content:center; z-index:10; }
.btn-upgrade { background:var(--primary); color:white; padding:12px 30px; border-radius:50px; text-decoration:none; margin-top:20px; display:inline-block; font-weight:bold; font-size:1rem; box-shadow:0 5px 20px rgba(229,9,20,0.4); }

/* --- RESPONSIVE --- */
@media(max-width:900px){ .theater-container{grid-template-columns:1fr;} .playlist-wrapper{height:500px;} }
@media(max-width:600px){ 
    .label-text { display:none; } 
    .skip-controls { border:none; padding:0; margin:0; } 
    .tool-btn { font-size:0.8rem; padding:8px; }
    .ad-player-slot { margin: 10px 0; padding: 10px; }
}
</style>
