<?php 
// 1. DATA PREPARATION
$raw = $data['data'] ?? $data ?? [];
$source = $_GET['source'] ?? 'dramabox'; 

if (isset($raw['dramaInfo'])) {
    $info = $raw['dramaInfo'];
    $chapters = $raw['chapters'] ?? [];
} else {
    $info = $raw; 
    $chapters = $raw['chapters'] ?? $raw['chapter_list'] ?? []; 
}

$urlTitle = isset($_GET['title']) ? urldecode($_GET['title']) : '';
$urlCover = isset($_GET['cover']) ? urldecode($_GET['cover']) : '';
$title = !empty($urlTitle) ? $urlTitle : ($info['bookName'] ?? $info['title'] ?? 'Nonton Drama');
$rawCover = !empty($urlCover) ? $urlCover : ($info['cover'] ?? $info['thumbnail'] ?? '');

if (strpos($rawCover, '.heic') !== false) {
    $cleanUrl = str_replace(['http://', 'https://'], '', $rawCover);
    $cover = 'https://wsrv.nl/?url=' . urlencode($cleanUrl) . '&output=jpg&q=80';
} else {
    $cover = str_replace('http://', 'https://', $rawCover);
}

$intro = $info['introduction'] ?? $info['abstract'] ?? 'Deskripsi tidak tersedia.';
$rating = $info['score'] ?? '5.0';
$views = number_format($info['followCount'] ?? $info['read_count'] ?? 0);
$tags = $info['tags'] ?? $info['stat_infos'] ?? [];

// 2. AUTHENTICATION & LIMITS
require_once 'app/Auth.php';
$auth = new Auth();
$isLoggedIn = isset($_SESSION['user_id']); // Cek apakah user sudah login
$isVip = $auth->isVip();
$urlId = $_GET['id'] ?? '';

// --- LOGIKA PEMBATASAN EPISODE ---
if ($isVip) {
    $episodeLimit = 99999; // VIP: Unlimited
    $userStatus = 'vip';
} elseif ($isLoggedIn) {
    $episodeLimit = 20;    // Free User: Max 20 Eps
    $userStatus = 'free';
} else {
    $episodeLimit = 10;    // Guest: Max 10 Eps
    $userStatus = 'guest';
}

// 3. HISTORY SYNC
$lastEpDB = 0;
if ($isLoggedIn && $urlId) {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT episode FROM history WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$_SESSION['user_id'], $urlId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row) $lastEpDB = $row['episode'];
}

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
                    <h2 id="overlayTitle">Konten Terkunci</h2>
                    <p id="overlayDesc">Upgrade VIP untuk melanjutkan.</p>
                    
                    <div id="overlayButtons" class="paywall-actions">
                        <a href="/login" id="btnLoginOverlay" class="btn-secondary" style="display:none; margin-right:10px;">Login Gratis</a>
                        <a href="/dashboard/billing" class="btn-upgrade">Beli VIP</a>
                    </div>
                </div>
            </div>
        </div>

        <?php if(!empty($webConfig['ad_player']) && !$isVip): ?>
        <div class="ad-player-slot">
            <small>IKLAN (Hilang jika VIP)</small>
            <div class="ad-content"><?= $webConfig['ad_player'] ?></div>
        </div>
        <?php endif; ?>

        <div class="player-toolbar">
            <div class="toolbar-group toolbar-left">
                <div class="skip-controls">
                    <button onclick="skipTime(-10)" class="tool-btn btn-control" title="Mundur 10s">
                        <i class="ri-replay-10-line"></i> <span class="label-text">-10s</span>
                    </button>
                    <button onclick="skipTime(10)" class="tool-btn btn-control" title="Maju 10s">
                        <i class="ri-forward-10-line"></i> <span class="label-text">+10s</span>
                    </button>
                </div>
                <label class="switch-toggle" title="Otomatis putar episode selanjutnya">
                    <input type="checkbox" id="autoNext" checked>
                    <span class="slider"></span>
                    <span class="label-text">Auto Next</span>
                </label>
            </div>

            <div class="toolbar-group toolbar-right">
                <select id="speedSelect" onchange="changeSpeed(this)" class="tool-select" title="Kecepatan Pemutaran">
                    <option value="1.0" selected>1.0x</option>
                    <option value="1.25">1.25x</option>
                    <option value="1.5">1.5x</option>
                    <option value="2.0">2.0x</option>
                </select>

                <div class="divider-vertical"></div>

                <div class="view-modes">
                    <button onclick="togglePip()" class="tool-btn btn-view" title="Picture in Picture (PiP)">
                        <i class="ri-picture-in-picture-2-line"></i> <span class="label-text">PiP</span>
                    </button>
                    <button onclick="toggleCinema()" class="tool-btn btn-view" id="btnCinema" title="Mode Bioskop (Cinema)">
                        <i class="ri-aspect-ratio-line"></i> <span class="label-text">Cinema</span>
                    </button>
                    <button onclick="toggleFullscreen()" class="tool-btn btn-view" title="Layar Penuh (Fullscreen)">
                        <i class="ri-fullscreen-line"></i> <span class="label-text">Full</span>
                    </button>
                </div>

                <div class="divider-vertical"></div>

                <div class="action-buttons">
                    <button onclick="toggleBookmark()" class="tool-btn btn-action" id="btnBookmark" title="Simpan ke Favorit">
                        <i class="ri-bookmark-line"></i> <span class="label-text">Simpan</span>
                    </button>
                    <button onclick="reportVideo()" class="tool-btn btn-danger report-btn" title="Lapor Video Rusak">
                        <i class="ri-alarm-warning-line"></i> <span class="label-text">Lapor</span>
                    </button>
                </div>
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
                
                <?php if($isVip): ?>
                    <span class="badge-status status-vip">AKUN VIP</span>
                <?php elseif($isLoggedIn): ?>
                    <span class="badge-status status-free">AKUN FREE (Limit 20 Eps)</span>
                <?php else: ?>
                    <span class="badge-status status-guest" style="background:#666; color:white; border:1px solid #888;">GUEST (Limit 10 Eps)</span>
                <?php endif; ?>
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
                    
                    // --- LOGIKA KUNCI ---
                    $isLocked = ($num > $episodeLimit); // Terkunci jika melebihi batas tipe user
                    
                    $vidId = ($source === 'melolo') ? ($chap['vid'] ?? $chap['id'] ?? '') : '';
                    $videoUrl = ($source !== 'melolo') ? ($chap['mp4'] ?? $chap['url'] ?? '') : '';
                    $hasLink = !empty($vidId) || !empty($videoUrl);
                ?>
                <button onclick="playEpisode('<?= $videoUrl ?>', '<?= $vidId ?>', <?= $isLocked?'true':'false' ?>, <?= $hasLink?'true':'false' ?>, this, <?= $num ?>)" 
                        class="eps-item" data-num="<?= $num ?>" id="eps-btn-<?= $num ?>">
                    
                    <div class="eps-left">
                        <span class="eps-num"><?= str_pad($num, 2, '0', STR_PAD_LEFT) ?></span>
                        <div class="playing-anim"><span></span><span></span><span></span></div>
                    </div>
                    
                    <div class="eps-details">
                        <span class="eps-name">Episode <?= $num ?></span>
                        <span class="badge-status <?= $isLocked ? 'status-vip' : 'status-free' ?>">
                            <?= $isLocked ? '<i class="ri-lock-fill"></i> TERKUNCI' : '<i class="ri-play-circle-line"></i> GRATIS' ?>
                        </span>
                    </div>

                    <div class="watched-indicator">
                        <i class="ri-eye-fill"></i>
                    </div>
                
                </button>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding:40px 20px; text-align:center; color:#666;">Episode belum tersedia.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// --- GLOBAL VARS ---
var dramaId = '<?= $urlId ?>'; 
var currentSource = '<?= $source ?>';
var dramaTitle = '<?= addslashes($title) ?>';
var lastEpServer = <?= intval($lastEpDB) ?>; 
var dramaCover = '<?= addslashes($cover) ?>';
var totalEps = <?= count($chapters) ?>;
var isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
var userStatus = '<?= $userStatus ?>'; // guest | free | vip

var video = document.getElementById('mainPlayer');
var hls = null;
var currentEpNum = 0;

// 1. INIT
document.addEventListener('DOMContentLoaded', function() {
    loadHistory(); 
    checkBookmark();
    
    var startEp = 1;
    if (lastEpServer > 0) startEp = lastEpServer;
    else {
        var localLast = getLastWatchedEp();
        if (localLast > 0) startEp = localLast;
    }

    var btn = document.getElementById('eps-btn-' + startEp);
    if(btn) {
        btn.click();
        setTimeout(() => btn.scrollIntoView({behavior: 'smooth', block: 'center'}), 500);
    } else {
        var first = document.querySelector('.eps-item');
        if(first) first.click();
    }
});

// 2. PLAY LOGIC
function playEpisode(directUrl, vidId, isLocked, hasLink, btn, epsNum) {
    currentEpNum = epsNum;

    // Reset UI
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

    var resumeTime = localStorage.getItem('resume_' + dramaId + '_' + epsNum);

    // --- HANDLE KONDISI TERKUNCI ---
    if (isLocked) { 
        let title = 'Konten Terkunci';
        let desc = 'Upgrade VIP untuk melanjutkan.';
        let showLoginBtn = false;

        if (userStatus === 'guest') {
            title = 'Batas Akses Tamu';
            desc = 'Anda mencapai batas 10 Episode. Login untuk nonton sampai Ep 20, atau Beli VIP untuk akses penuh.';
            showLoginBtn = true;
        } else if (userStatus === 'free') {
            title = 'Batas Akses Free';
            desc = 'Jatah 20 Episode gratis habis. Silakan beli paket VIP murah meriah!';
        }

        showOverlay('ri-lock-star-fill', '#ffd700', title, desc, true, showLoginBtn);
        return; 
    }

    if (!hasLink) { showOverlay('ri-file-shred-line', '#666', 'Belum Tersedia', 'Episode belum rilis.', false, false); return; }

    if (currentSource === 'melolo' && vidId) {
        fetch(`/index.php?page=api_get_stream&source=melolo&id=${vidId}`)
            .then(res => res.json())
            .then(resp => {
                let streamUrl = resp.main_url || resp.data?.main_url || resp.url || '';
                if(streamUrl) {
                    if (streamUrl.startsWith('http://')) streamUrl = streamUrl.replace('http://', 'https://');
                    loadVideo(streamUrl, resumeTime);
                } else {
                    showOverlay('ri-error-warning-line', '#ff4757', 'Gagal', 'Video error.', false, false);
                }
            })
            .catch(() => showOverlay('ri-wifi-off-line', '#ff4757', 'Error', 'Gagal koneksi.', false, false));
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
    } else {
        video.src = url; 
        video.addEventListener('loadedmetadata', onReady, {once:true});
        video.play().catch(e=>{});
    }
}

// 3. HISTORY SYSTEM
function markAsWatched(num) {
    if(!dramaId) return;
    var k = 'watched_' + dramaId;
    var h = JSON.parse(localStorage.getItem(k) || '[]');
    if(!h.includes(num)) { h.push(num); localStorage.setItem(k, JSON.stringify(h)); }
    localStorage.setItem('history_item_' + dramaId, JSON.stringify({
        id: dramaId, title: dramaTitle, cover: dramaCover, lastEp: num, timestamp: Date.now(), source: currentSource
    }));
    var btn = document.getElementById('eps-btn-' + num);
    if(btn) btn.classList.add('watched');
    if (isLoggedIn) {
        fetch('/index.php?page=api_save_history', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: dramaId, title: dramaTitle, cover: dramaCover, episode: num, total: totalEps })
        });
    }
}

function loadHistory() {
    if(!dramaId) return;
    var localData = JSON.parse(localStorage.getItem('watched_' + dramaId) || '[]');
    if (lastEpServer > 0 && !localData.includes(lastEpServer)) {
        localData.push(lastEpServer);
        localStorage.setItem('watched_' + dramaId, JSON.stringify(localData));
    }
    localData.forEach(n => { 
        var btn = document.getElementById('eps-btn-' + n);
        if(btn) btn.classList.add('watched');
    });
}

function getLastWatchedEp() {
    var h = JSON.parse(localStorage.getItem('history_item_' + dramaId) || '{}');
    return h.lastEp || 0;
}

// UTILS & BUTTON LOGIC
video.addEventListener('timeupdate', function() {
    if(video.currentTime > 5 && !video.paused && currentEpNum > 0) localStorage.setItem('resume_' + dramaId + '_' + currentEpNum, video.currentTime);
});
video.addEventListener('ended', function() { if(document.getElementById('autoNext').checked) navEpisode(1); });

function skipTime(s) { video.currentTime += s; }
function changeSpeed(el) { video.playbackRate = parseFloat(el.value); }

function toggleCinema() { 
    document.getElementById('theaterContainer').classList.toggle('cinema-mode'); 
    document.getElementById('btnCinema').classList.toggle('active-view'); 
}
function toggleFullscreen() { if (!document.fullscreenElement) { video.requestFullscreen(); } else { document.exitFullscreen(); } }
function togglePip() { if (document.pictureInPictureElement) { document.exitPictureInPicture(); } else if (document.pictureInPictureEnabled) { video.requestPictureInPicture(); } }

function navEpisode(dir) { var cur = document.querySelector('.eps-item.active'); if(!cur) return; var target = dir===1 ? cur.nextElementSibling : cur.previousElementSibling; if(target) target.click(); }
function updateNavButtons(btn) { document.getElementById('btnPrev').disabled = !btn.previousElementSibling; document.getElementById('btnNext').disabled = !btn.nextElementSibling; }

function toggleBookmark() {
    let b = JSON.parse(localStorage.getItem('my_bookmarks') || '[]');
    const idx = b.findIndex(x => x.id === dramaId);
    if (idx === -1) { 
        b.push({ id: dramaId, title: dramaTitle, cover: dramaCover, source: currentSource, timestamp: Date.now() }); 
        alert('Disimpan ke Favorit!'); 
    } else { 
        b.splice(idx, 1); 
        alert('Dihapus dari Favorit.'); 
    }
    localStorage.setItem('my_bookmarks', JSON.stringify(b)); 
    checkBookmark();
}

function checkBookmark() {
    let b = JSON.parse(localStorage.getItem('my_bookmarks') || '[]');
    const btn = document.getElementById('btnBookmark');
    const icon = btn.querySelector('i');
    const label = btn.querySelector('.label-text');
    if (b.find(x => x.id === dramaId)) { 
        btn.classList.add('active-bookmark'); 
        icon.className = 'ri-bookmark-fill'; 
        label.innerText = 'Tersimpan';
    } else { 
        btn.classList.remove('active-bookmark');
        icon.className = 'ri-bookmark-line'; 
        label.innerText = 'Simpan';
    }
}

// FUNGSI OVERLAY LEBIH CERDAS
function showOverlay(icon, col, title, desc, showVipBtn, showLoginBtn) { 
    var ov = document.getElementById('playerOverlay'); video.style.display = 'none'; video.pause(); ov.style.display = 'flex'; 
    document.getElementById('overlayIcon').className = icon; document.getElementById('overlayIcon').style.color = col; 
    document.getElementById('overlayTitle').innerText = title; document.getElementById('overlayDesc').innerText = desc; 
    document.getElementById('overlayButtons').style.display = 'block'; 
    
    // Toggle tombol spesifik
    var btnLogin = document.getElementById('btnLoginOverlay');
    var btnVip = document.querySelector('.btn-upgrade');
    
    if(btnLogin) btnLogin.style.display = showLoginBtn ? 'inline-block' : 'none';
    if(btnVip) btnVip.style.display = showVipBtn ? 'inline-block' : 'none';

    document.getElementById('loadingSpinner').style.display = 'none'; 
}

function filterEpisodes() { var v = document.getElementById('epsSearch').value.toLowerCase(); document.querySelectorAll('.eps-item').forEach(el => { var t = el.innerText.toLowerCase(); el.style.display = t.includes(v) ? "" : "none"; }); }
function reportVideo() { if(confirm("Lapor video rusak?")) window.open(`https://t.me/jejakintel?text=${encodeURIComponent('Lapor Error: '+dramaTitle+' Ep '+currentEpNum)}`, '_blank'); }
</script>

<style>
/* CSS Update untuk Tombol Login di Overlay */
.btn-secondary {
    background: #333; color: white; padding: 12px 25px; border-radius: 50px; text-decoration: none; display: inline-block; font-weight: bold; font-size: 1rem;
    border: 1px solid rgba(255,255,255,0.2); transition: 0.3s;
}
.btn-secondary:hover { background: #555; }

/* CSS Toolbar & Lainnya TETAP SAMA seperti sebelumnya */
.player-toolbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; background: #1a1b20; padding: 12px 20px; border-radius: 0 0 16px 16px; margin-top: -5px; border: 1px solid rgba(255,255,255,0.1); border-top: none; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
.toolbar-group { display: flex; align-items: center; gap: 8px; }
.divider-vertical { width: 2px; height: 24px; background: rgba(255,255,255,0.2); margin: 0 8px; border-radius: 2px; }
.tool-btn { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); color: #fff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 8px 14px; transition: all 0.3s ease; font-size: 0.95rem; border-radius: 8px; font-weight: 500; }
.tool-btn:hover { color: white; background: rgba(255,255,255,0.08); }
.tool-select { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 0.9rem; outline: none; font-weight: 500; }
.tool-select option { background: #1a1b20; color: #fff; }
.tool-select:hover { background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.3); }
.btn-control:hover { background: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.4); }
.btn-view:hover { background: rgba(0, 210, 255, 0.2); color: #00d2ff; border-color: #00d2ff; box-shadow: 0 0 10px rgba(0, 210, 255, 0.4); }
.btn-view.active-view { background: #00d2ff; color: #000 !important; border-color: #00d2ff; font-weight: bold; box-shadow: 0 0 15px rgba(0, 210, 255, 0.6); }
.btn-action:hover { background: rgba(229, 9, 20, 0.2); color: #ff4757; border-color: #ff4757; }
.btn-action.active-bookmark { background: #e50914; color: white !important; border: 1px solid #e50914; box-shadow: 0 0 15px rgba(229, 9, 20, 0.6); }
.btn-danger:hover { background: rgba(255, 165, 0, 0.2); color: #ffa500; border-color: #ffa500; box-shadow: 0 0 10px rgba(255, 165, 0, 0.4); }
.skip-controls { display:flex; gap:5px; margin-right:5px; }
.switch-toggle { display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.9rem; color:#fff; font-weight: 500; }
.switch-toggle input { display:none; }
.switch-toggle .slider { width:42px; height:24px; background:#333; border-radius:24px; position:relative; transition:0.3s; border: 2px solid #555; }
.switch-toggle .slider:before { content:""; position:absolute; width:16px; height:16px; border-radius:50%; background:white; top:2px; left:2px; transition:0.3s; }
.switch-toggle input:checked + .slider { background:#00d2ff; border-color: #00d2ff; }
.switch-toggle input:checked + .slider:before { transform:translateX(18px); }
.badge-status { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; display: inline-flex; align-items: center; gap: 5px; margin-left: auto; }
.badge-status.status-vip { background: #ffd700; color: #000; border: 1px solid #eab308; box-shadow: 0 0 8px rgba(255, 215, 0, 0.3); }
.badge-status.status-free { background: #22c55e; color: #fff; border: 1px solid #16a34a; }
.watched-indicator { margin-left: 12px; color: #4ade80; font-size: 1.2rem; display: none; }
.eps-item.watched .watched-indicator { display: block !important; }
.eps-item.watched .eps-name { color: #888; }
.theater-bg { position:fixed; top:0; left:0; width:100%; height:100vh; background-size:cover; filter:blur(80px) brightness(0.3); z-index:-1; }
.theater-container { display:grid; grid-template-columns:1fr 340px; gap:30px; max-width:1400px; margin:0 auto; padding:30px 20px; transition: 0.3s; }
.theater-container.cinema-mode { grid-template-columns:1fr; }
.theater-container.cinema-mode .playlist-wrapper { display:none; }
.video-frame { position:relative; aspect-ratio:16/9; background:#000; border-radius:16px; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.1); }
.custom-video { width:100%; height:100%; }
.loading-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:#000; z-index:5; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.spinner { width:40px; height:40px; border:4px solid rgba(255,255,255,0.1); border-left-color:var(--primary); border-radius:50%; animation:spin 1s linear infinite; }
@keyframes spin { 100% { transform:rotate(360deg); } }
.ad-player-slot { margin-top:15px; text-align:center; background:#0a0a0c; padding:15px; border-radius:12px; border:1px solid rgba(255,255,255,0.05); }
.ad-player-slot small { display:block; color:#444; font-size:0.7rem; margin-bottom:5px; letter-spacing:1px; }
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
.paywall-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.95); display:flex; flex-direction:column; align-items:center; justify-content:center; z-index:10; }
.btn-upgrade { background:var(--primary); color:white; padding:12px 30px; border-radius:50px; text-decoration:none; margin-top:20px; display:inline-block; font-weight:bold; font-size:1rem; box-shadow:0 5px 20px rgba(229,9,20,0.4); }
@media(max-width:900px){ .theater-container{grid-template-columns:1fr;} .playlist-wrapper{height:500px;} }
@media(max-width:600px){ .label-text { display:none; } .skip-controls { border:none; padding:0; margin:0; } .tool-btn { font-size:0.8rem; padding:8px; } .ad-player-slot { margin: 10px 0; padding: 10px; } .divider-vertical { display: none; } }
</style>
