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
?>

<div class="page-header">
    <div>
        <h2 class="page-title">🕒 Lanjutkan Menonton</h2>
        <p style="color:#888; margin:0;">Daftar drama yang belum selesai Anda tonton.</p>
    </div>
    <?php if(!empty($history)): ?>
    <form method="POST" onsubmit="return confirm('Yakin hapus SEMUA riwayat?');">
        <button name="clear_all" class="btn btn-secondary btn-sm">
            <i class="ri-delete-bin-line"></i> Bersihkan Semua
        </button>
    </form>
    <?php endif; ?>
</div>

<?php if(empty($history)): ?>
    <div class="empty-state-card">
        <div class="icon-circle">
            <i class="ri-movie-2-line"></i>
        </div>
        <h3>Belum ada riwayat tontonan</h3>
        <p>Jejak tontonan Anda akan muncul di sini.</p>
        <a href="/" class="btn btn-primary">Mulai Nonton Sekarang</a>
    </div>
<?php else: ?>
    <div class="history-grid">
        <?php foreach($history as $item): ?>
        <div class="history-card">
            <div class="poster-wrapper">
                <a href="/watch?id=<?= $item['book_id'] ?>" class="poster-link">
                    <img src="<?= htmlspecialchars($item['cover']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
                    
                    <div class="play-overlay">
                        <i class="ri-play-circle-fill"></i>
                    </div>
                </a>
                
                <form method="POST" class="delete-btn-form">
                    <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                    <button type="submit" class="btn-remove" title="Hapus dari riwayat">
                        <i class="ri-close-line"></i>
                    </button>
                </form>

                <?php 
                    $total = max(1, $item['total_eps']);
                    $current = $item['episode'];
                    $percent = ($current / $total) * 100; 
                    if($percent > 100) $percent = 100;
                ?>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: <?= $percent ?>%;"></div>
                </div>
            </div>

            <div class="history-info">
                <h4 class="drama-title">
                    <a href="/watch?id=<?= $item['book_id'] ?>"><?= htmlspecialchars($item['title']) ?></a>
                </h4>
                <div class="drama-meta">
                    <span class="episode-tag">Ep <?= $item['episode'] ?></span>
                    <span class="time-ago"><?= date('d M', strtotime($item['updated_at'])) ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
/* 1. GRID LAYOUT */
.history-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); /* Ukuran kartu responsif */
    gap: 20px;
}

/* 2. HISTORY CARD */
.history-card {
    background: transparent;
    transition: transform 0.3s ease;
}
.history-card:hover { transform: translateY(-5px); }

/* 3. POSTER AREA */
.poster-wrapper {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    aspect-ratio: 2/3; /* Rasio Poster Standar */
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}
.poster-wrapper img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.3s ease;
}
.poster-wrapper:hover img { transform: scale(1.05); filter: brightness(0.7); }

/* Play Overlay */
.play-overlay {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    font-size: 3rem; color: rgba(255,255,255,0.9); opacity: 0; transition: 0.3s; pointer-events: none;
}
.poster-wrapper:hover .play-overlay { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }

/* Tombol Hapus (X) */
.delete-btn-form { position: absolute; top: 5px; right: 5px; z-index: 10; }
.btn-remove {
    background: rgba(0,0,0,0.6); color: #fff; border: none;
    width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: 0.2s; opacity: 0; /* Hidden by default */
}
.btn-remove:hover { background: #e50914; transform: scale(1.1); }
.poster-wrapper:hover .btn-remove { opacity: 1; } /* Show on hover */

/* Progress Bar */
.progress-bar-bg {
    position: absolute; bottom: 0; left: 0; width: 100%; height: 4px;
    background: rgba(255,255,255,0.2);
}
.progress-bar-fill {
    height: 100%; background: #e50914; /* Warna Merah Netflix */
}

/* 4. INFO AREA */
.history-info { margin-top: 10px; }
.drama-title {
    margin: 0 0 5px 0; font-size: 0.95rem; line-height: 1.3;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; /* Judul 1 Baris */
}
.drama-title a { color: #fff; text-decoration: none; }
.drama-title a:hover { color: #e50914; }

.drama-meta { display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #888; }
.episode-tag { background: #333; color: #ddd; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }

/* 5. EMPTY STATE (Jika Kosong) */
.empty-state-card {
    text-align: center; padding: 60px 20px;
    background: #1e2129; border-radius: 12px; border: 1px dashed rgba(255,255,255,0.1);
}
.icon-circle {
    width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 2.5rem; color: #666; margin-bottom: 20px;
}
.empty-state-card h3 { margin: 0 0 10px 0; color: #fff; }
.empty-state-card p { color: #888; margin-bottom: 25px; }

/* RESPONSIVE MOBILE */
@media (max-width: 600px) {
    .history-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; } /* 2 Kolom di HP */
    .btn-remove { opacity: 1; background: rgba(0,0,0,0.4); } /* Selalu tampilkan tombol hapus di HP */
}
</style>
