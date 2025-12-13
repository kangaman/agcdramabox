<?php
$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];

// --- LOGIKA HAPUS ---
if(isset($_POST['delete_id'])) {
    $db->prepare("DELETE FROM history WHERE id=? AND user_id=?")->execute([$_POST['delete_id'], $uid]);
    echo "<script>window.location='/dashboard/history';</script>";
    exit;
}

if(isset($_POST['clear_all'])) {
    $db->prepare("DELETE FROM history WHERE user_id=?")->execute([$uid]);
    echo "<script>window.location='/dashboard/history';</script>";
    exit;
}

// AMBIL DATA
$history = $db->query("SELECT * FROM history WHERE user_id = $uid ORDER BY updated_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Pisahkan Item Terakhir (Untuk Hero Section) dan Sisanya
$lastWatched = !empty($history) ? $history[0] : null;
$otherHistory = !empty($history) ? array_slice($history, 1) : [];
?>

<div class="page-header">
    <div>
        <h2 class="page-title">⏳ Jejak Tontonan</h2>
        <p style="color:#888; margin:0;">Jangan biarkan cerita menggantung. Lanjutkan keseruannya!</p>
    </div>
    <?php if(!empty($history)): ?>
    <form method="POST" onsubmit="return confirm('Yakin hapus SEMUA riwayat?');">
        <button name="clear_all" class="btn-ghost-danger">
            <i class="ri-delete-bin-line"></i> Bersihkan Semua
        </button>
    </form>
    <?php endif; ?>
</div>

<?php if(empty($history)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <i class="ri-film-line"></i>
            <div class="empty-glow"></div>
        </div>
        <h3>Masih Kosong, Nih!</h3>
        <p>Kamu belum menonton apapun. Yuk, cari drama seru sekarang.</p>
        <a href="/" class="btn btn-primary pulse-btn">Mulai Petualangan</a>
    </div>

<?php else: ?>

    <?php if($lastWatched): 
        $percent = ($lastWatched['episode'] / max(1, $lastWatched['total_eps'])) * 100;
        if($percent > 100) $percent = 100;
        $watchUrl = "/?page=watch&id=" . $lastWatched['book_id'];
    ?>
    <div class="hero-history">
        <div class="hero-bg" style="background-image: url('<?= htmlspecialchars($lastWatched['cover']) ?>');"></div>
        <div class="hero-content">
            <div class="hero-poster">
                <img src="<?= htmlspecialchars($lastWatched['cover']) ?>" alt="Poster">
            </div>
            <div class="hero-info">
                <span class="label-last-watched"><i class="ri-history-line"></i> TERAKHIR DITONTON</span>
                <h2 class="hero-title"><?= htmlspecialchars($lastWatched['title']) ?></h2>
                <div class="hero-meta">
                    <span class="ep-badge">Episode <?= $lastWatched['episode'] ?></span>
                    <span class="time-badge"><?= date('d M Y, H:i', strtotime($lastWatched['updated_at'])) ?></span>
                </div>
                
                <div class="progress-container">
                    <div class="progress-bar" style="width: <?= $percent ?>%"></div>
                </div>
                <small style="color:#aaa; font-size:0.8rem;">Progres: <?= floor($percent) ?>% Selesai</small>

                <div class="hero-actions">
                    <a href="<?= $watchUrl ?>" class="btn btn-primary btn-lg">
                        <i class="ri-play-fill"></i> Lanjut Nonton
                    </a>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $lastWatched['id'] ?>">
                        <button class="btn btn-icon-only" title="Hapus dari riwayat">
                            <i class="ri-close-line"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($otherHistory)): ?>
        <h3 style="margin: 40px 0 20px; color:#fff; font-size:1.2rem; border-left:4px solid var(--primary); padding-left:10px;">Riwayat Sebelumnya</h3>
        
        <div class="history-grid">
            <?php foreach($otherHistory as $item): 
                $percent = ($item['episode'] / max(1, $item['total_eps'])) * 100;
                $watchUrl = "/?page=watch&id=" . $item['book_id'];
            ?>
            <div class="history-card">
                <div class="card-image">
                    <img src="<?= htmlspecialchars($item['cover']) ?>" loading="lazy">
                    <a href="<?= $watchUrl ?>" class="overlay-play">
                        <i class="ri-play-circle-fill"></i>
                    </a>
                    <div class="progress-line">
                        <div class="fill" style="width: <?= $percent ?>%"></div>
                    </div>
                </div>
                
                <div class="card-details">
                    <h4 class="card-title">
                        <a href="<?= $watchUrl ?>"><?= htmlspecialchars($item['title']) ?></a>
                    </h4>
                    <div class="card-footer">
                        <span class="ep-text">Ep <?= $item['episode'] ?></span>
                        <form method="POST">
                            <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                            <button class="btn-delete-mini"><i class="ri-delete-bin-line"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>

<style>
/* --- 1. HERO SECTION (HIGHLIGHT) --- */
.hero-history {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 30px;
    background: #1a1b20;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    border: 1px solid rgba(255,255,255,0.1);
}
.hero-bg {
    position: absolute; top:0; left:0; width:100%; height:100%;
    background-size: cover; background-position: center;
    filter: blur(50px) brightness(0.4); opacity: 0.6; z-index: 0;
}
.hero-content {
    position: relative; z-index: 1;
    display: flex; gap: 30px; padding: 30px;
    align-items: center;
}
.hero-poster img {
    width: 140px; height: 210px; object-fit: cover;
    border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    transition: transform 0.3s;
}
.hero-poster img:hover { transform: scale(1.05) rotate(2deg); }

.hero-info { flex: 1; }
.label-last-watched {
    font-size: 0.75rem; font-weight: 800; color: #ffd700; 
    letter-spacing: 1px; display: block; margin-bottom: 10px;
}
.hero-title {
    font-size: 2rem; margin: 0 0 15px; color: white; line-height: 1.2;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
}
.hero-meta { display: flex; gap: 15px; margin-bottom: 20px; font-size: 0.9rem; color: #ccc; }
.ep-badge { background: var(--primary); color: white; padding: 2px 8px; border-radius: 4px; font-weight: bold; }

.progress-container {
    height: 6px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; margin-bottom: 5px; max-width: 400px;
}
.progress-bar { height: 100%; background: #22c55e; border-radius: 10px; }

.hero-actions { margin-top: 25px; display: flex; gap: 15px; }
.btn-lg { padding: 12px 30px; font-size: 1rem; }
.btn-icon-only {
    width: 45px; height: 45px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.2);
    background: rgba(0,0,0,0.3); color: #fff; cursor: pointer; transition: 0.3s;
    display: flex; align-items: center; justify-content: center;
}
.btn-icon-only:hover { background: #ff4757; border-color: #ff4757; }

/* --- 2. GRID CARDS --- */
.history-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 20px;
}
.history-card {
    background: #1e2129; border-radius: 10px; overflow: hidden;
    border: 1px solid rgba(255,255,255,0.05); transition: 0.3s;
    display: flex; flex-direction: column;
}
.history-card:hover { transform: translateY(-5px); border-color: rgba(255,255,255,0.2); }

.card-image { position: relative; aspect-ratio: 2/3; overflow: hidden; }
.card-image img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
.history-card:hover .card-image img { opacity: 0.6; transform: scale(1.1); }

.overlay-play {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    font-size: 3rem; color: white; opacity: 0; transition: 0.3s; text-decoration: none;
}
.history-card:hover .overlay-play { opacity: 1; }

.progress-line { position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background: rgba(0,0,0,0.5); }
.progress-line .fill { height: 100%; background: var(--primary); }

.card-details { padding: 10px; flex: 1; display: flex; flex-direction: column; }
.card-title {
    font-size: 0.9rem; margin: 0 0 5px; line-height: 1.4;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.card-title a { color: #ddd; text-decoration: none; transition: 0.2s; }
.card-title a:hover { color: var(--primary); }

.card-footer { margin-top: auto; display: flex; justify-content: space-between; align-items: center; }
.ep-text { font-size: 0.75rem; color: #888; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; }
.btn-delete-mini {
    background: none; border: none; color: #666; cursor: pointer; transition: 0.2s; font-size: 1rem;
}
.btn-delete-mini:hover { color: #ff4757; transform: scale(1.1); }

/* --- 3. UTILS & RESPONSIVE --- */
.btn-ghost-danger {
    background: transparent; border: 1px solid #ff4757; color: #ff4757;
    padding: 5px 15px; border-radius: 6px; cursor: pointer; transition: 0.3s;
}
.btn-ghost-danger:hover { background: #ff4757; color: white; }

.empty-state { text-align: center; padding: 80px 20px; }
.empty-icon {
    width: 80px; height: 80px; margin: 0 auto 20px; position: relative;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,0.05); border-radius: 50%; font-size: 2.5rem; color: #666;
}
.pulse-btn { animation: pulse 2s infinite; }
@keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }

@media (max-width: 768px) {
    .hero-content { flex-direction: column; text-align: center; padding: 20px; }
    .hero-poster img { width: 100px; height: 150px; margin: 0 auto; }
    .hero-meta, .hero-actions { justify-content: center; }
    .progress-container { margin: 5px auto; }
    .history-grid { grid-template-columns: repeat(2, 1fr); } /* 2 Kolom di HP */
}
</style>
