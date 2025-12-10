<?php
$db = (new Database())->getConnection();
$plans = $db->query("SELECT * FROM plans WHERE is_active=1 ORDER BY price ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="header-flex">
    <h2 class="page-title">💎 Langganan Premium</h2>
</div>

<p style="color:var(--text-muted); margin-bottom: 30px;">
    Pilih paket terbaik untuk pengalaman nonton tanpa batas. Hubungi admin via Telegram untuk aktivasi.
</p>

<div class="plans-grid">
    <?php foreach($plans as $p): ?>
    <div class="plan-card" style="border-top: 4px solid var(--primary);">
        <div class="pc-header">
            <h3><?= htmlspecialchars($p['name']) ?></h3>
            <span class="pc-price">Rp <?= number_format($p['price']) ?></span>
        </div>
        
        <div class="pc-body">
            <div style="text-align:center; margin: 20px 0;">
                <span style="font-size:2.5rem; font-weight:800; color:white;"><?= $p['duration'] ?></span>
                <span style="color:#888; font-size:1rem;">Hari</span>
            </div>
            
            <ul class="feature-list">
                <?php foreach(explode(',', $p['features']) as $f): ?>
                    <li><i class="ri-checkbox-circle-fill"></i> <?= trim($f) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="pc-footer">
            <?php 
            $msg = "Halo Admin, saya ingin berlangganan paket *" . $p['name'] . "* seharga Rp " . number_format($p['price']) . " untuk username: *" . $_SESSION['username'] . "*. Mohon info pembayarannya.";
            ?>
            
            <button onclick="orderTelegram('<?= $msg ?>')" class="btn btn-primary btn-block">
                <i class="ri-telegram-fill"></i> Beli via Telegram
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function orderTelegram(message) {
    // 1. Copy pesan ke clipboard
    navigator.clipboard.writeText(message).then(function() {
        // 2. Beritahu user
        alert('Pesan pemesanan telah disalin! Silakan TEMPEL (PASTE) di chat Telegram.');
        
        // 3. Buka Telegram
        window.open('http://t.me/jejakintel', '_blank');
    }, function(err) {
        // Fallback jika copy gagal, tetap buka telegram
        window.open('http://t.me/jejakintel', '_blank');
    });
}
</script>