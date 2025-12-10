<?php
$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];

// Logika Hapus History
if(isset($_POST['delete_id'])) {
    $db->prepare("DELETE FROM history WHERE id=? AND user_id=?")->execute([$_POST['delete_id'], $uid]);
    echo "<script>window.location='/dashboard/history';</script>";
}

// Logika Hapus Semua
if(isset($_POST['clear_all'])) {
    $db->prepare("DELETE FROM history WHERE user_id=?")->execute([$uid]);
    echo "<script>window.location='/dashboard/history';</script>";
}

// Ambil Data
$history = $db->query("SELECT * FROM history WHERE user_id = $uid ORDER BY updated_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="header-flex">
    <div>
        <h2 class="page-title">🕒 Riwayat Tontonan</h2>
        <p style="color:var(--text-muted)">Lanjutkan film yang belum selesai Anda tonton.</p>
    </div>
    <?php if(!empty($history)): ?>
    <form method="POST" onsubmit="return confirm('Hapus semua riwayat?');">
        <button name="clear_all" class="btn btn-secondary btn-sm">Bersihkan Riwayat</button>
    </form>
    <?php endif; ?>
</div>

<?php if(empty($history)): ?>
    <div class="card" style="text-align:center; padding:50px;">
        <i class="ri-history-line" style="font-size:4rem; color:#333; margin-bottom:15px; display:block;"></i>
        <h3 style="color:#666;">Belum ada riwayat</h3>
        <p style="color:#555; margin-bottom:20px;">Tonton drama dulu untuk menyimpannya disini.</p>
        <a href="/" class="btn btn-primary">Mulai Nonton</a>
    </div>
<?php else: ?>
    <div class="plans-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
        <?php foreach($history as $item): ?>
        <div class="card" style="padding:0; overflow:hidden; border:1px solid var(--border); transition:0.3s;">
            <div style="display:flex; padding:15px; gap:15px;">
                <a href="/nonton/<?= $item['book_id'] ?>" style="flex-shrink:0;">
                    <img src="<?= htmlspecialchars($item['cover']) ?>" 
                         style="width:70px; height:100px; object-fit:cover; border-radius:6px;">
                </a>
                
                <div style="flex:1; display:flex; flex-direction:column; justify-content:center;">
                    <h4 style="font-size:0.95rem; margin-bottom:5px; line-height:1.4;">
                        <a href="/nonton/<?= $item['book_id'] ?>" style="color:white; text-decoration:none;">
                            <?= htmlspecialchars($item['title']) ?>
                        </a>
                    </h4>
                    
                    <div style="margin-bottom:10px;">
                        <span class="badge" style="background:var(--primary); color:white;">Ep <?= $item['episode'] ?></span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <a href="/nonton/<?= $item['book_id'] ?>" style="color:var(--text-muted); font-size:0.8rem; text-decoration:none;">
                            <i class="ri-play-fill"></i> Lanjut
                        </a>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                            <button class="btn-sm" style="background:transparent; border:none; color:#666; cursor:pointer;">
                                <i class="ri-close-circle-line" style="font-size:1.2rem;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php 
                $percent = ($item['episode'] / max(1, $item['total_eps'])) * 100; 
                if($percent > 100) $percent = 100;
            ?>
            <div style="background:#222; height:4px; width:100%;">
                <div style="background:var(--primary); height:100%; width:<?= $percent ?>%;"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>