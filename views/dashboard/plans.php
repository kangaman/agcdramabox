<?php
if($_SESSION['role'] !== 'admin') exit('Access Denied');
$db = (new Database())->getConnection();

// Handle Post Logic (Sama seperti sebelumnya)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_plan'])) {
        $stmt = $db->prepare("INSERT INTO plans (name, price, duration, features) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['price'], $_POST['duration'], $_POST['features']]);
        echo "<script>window.location='/dashboard/plans';</script>";
    } 
    elseif (isset($_POST['delete_id'])) {
        $db->prepare("DELETE FROM plans WHERE id=?")->execute([$_POST['delete_id']]);
        echo "<script>window.location='/dashboard/plans';</script>";
    }
}
$plans = $db->query("SELECT * FROM plans ORDER BY price ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="header-flex">
    <h2>📦 Kelola Paket Langganan</h2>
</div>

<div class="grid-2">
    
    <div>
        <?php foreach($plans as $p): ?>
        <div class="card" style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h3 style="font-size: 1.3rem; margin-bottom: 5px;"><?= htmlspecialchars($p['name']) ?></h3>
                <h4 style="color: var(--primary); font-size: 1.5rem; margin-bottom: 15px;">
                    Rp <?= number_format($p['price']) ?>
                    <span style="font-size: 0.8rem; color: #888; font-weight: normal;">/ <?= $p['duration'] ?> Hari</span>
                </h4>
                
                <ul class="feature-list">
                    <?php foreach(explode(',', $p['features']) as $f): ?>
                        <li><i class="ri-checkbox-circle-fill"></i> <?= trim($f) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <form method="POST" onsubmit="return confirm('Hapus paket ini?');">
                <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                <button class="btn btn-secondary btn-sm" title="Hapus Paket">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </form>
        </div>
        <?php endforeach; ?>
        
        <?php if(empty($plans)): ?>
            <div class="card" style="text-align: center; color: #666; border-style: dashed;">
                <i class="ri-inbox-line" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>
                Belum ada paket yang dibuat.
            </div>
        <?php endif; ?>
    </div>

    <div>
        <div class="card" style="position: sticky; top: 20px;">
            <h3 style="margin-bottom: 20px; border-bottom: 1px solid #333; padding-bottom: 15px;">
                <i class="ri-add-circle-line"></i> Tambah Paket
            </h3>
            
            <form method="POST">
                <div style="margin-bottom: 15px;">
                    <label>Nama Paket</label>
                    <input type="text" name="name" required placeholder="Contoh: Paket Sultan">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label>Harga (Rupiah)</label>
                    <input type="number" name="price" required placeholder="50000">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label>Durasi (Hari)</label>
                    <input type="number" name="duration" required placeholder="30">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label>Fitur (Pisahkan Koma)</label>
                    <textarea name="features" rows="4" required placeholder="Tanpa Iklan, Full HD, Support TV"></textarea>
                    <small style="color: #666; font-size: 0.75rem;">Contoh: Fitur A, Fitur B, Fitur C</small>
                </div>
                
                <button type="submit" name="add_plan" class="btn btn-primary btn-block">
                    Simpan Paket
                </button>
            </form>
        </div>
    </div>
</div>