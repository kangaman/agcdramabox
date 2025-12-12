<?php
if($_SESSION['role'] !== 'admin') exit('Access Denied');
$db = (new Database())->getConnection();

// --- LOGIC: TAMBAH PAKET ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_plan'])) {
    $stmt = $db->prepare("INSERT INTO plans (name, price, duration, features) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'], 
        str_replace('.', '', $_POST['price']), // Hapus titik format ribuan jika ada
        $_POST['duration'], 
        $_POST['features']
    ]);
    setFlash('success', 'Tersimpan', 'Paket baru berhasil ditambahkan.');
    echo "<script>window.location='/dashboard/plans';</script>";
    exit;
} 

// --- LOGIC: HAPUS PAKET ---
elseif (isset($_POST['delete_id'])) {
    $db->prepare("DELETE FROM plans WHERE id=?")->execute([$_POST['delete_id']]);
    setFlash('success', 'Terhapus', 'Paket berhasil dihapus.');
    echo "<script>window.location='/dashboard/plans';</script>";
    exit;
}

$plans = $db->query("SELECT * FROM plans ORDER BY price ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">📦 Kelola Paket Langganan</h2>
        <p style="color:#888; margin:0;">Atur pilihan paket yang akan ditawarkan ke pengguna.</p>
    </div>
</div>

<div class="plan-layout">
    
    <div class="plan-list">
        <?php foreach($plans as $p): ?>
        <div class="card plan-admin-card">
            
            <div class="plan-content">
                <div class="plan-head">
                    <h3 class="plan-title"><?= htmlspecialchars($p['name']) ?></h3>
                    <div class="plan-price">
                        <span class="currency">Rp</span>
                        <span class="amount"><?= number_format($p['price']) ?></span>
                        <span class="period">/ <?= $p['duration'] ?> Hari</span>
                    </div>
                </div>

                <ul class="plan-features">
                    <?php foreach(explode(',', $p['features']) as $f): ?>
                        <li><i class="ri-checkbox-circle-fill"></i> <?= trim($f) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="plan-actions">
                <form method="POST" onsubmit="return confirm('Yakin hapus paket <?= htmlspecialchars($p['name']) ?>?');">
                    <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                    <button class="btn-icon-text btn-red" title="Hapus Paket">
                        <i class="ri-delete-bin-line"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if(empty($plans)): ?>
            <div class="empty-placeholder">
                <i class="ri-inbox-archive-line"></i>
                <p>Belum ada paket langganan.<br>Silakan buat baru di formulir samping.</p>
            </div>
        <?php endif; ?>
    </div>

    <aside class="plan-form-container">
        <div class="card card-sticky">
            <div class="card-header">
                <h3><i class="ri-add-line" style="color:#e50914"></i> Tambah Baru</h3>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label>Nama Paket</label>
                    <input type="text" name="name" required placeholder="Misal: Paket Sultan" class="form-input">
                </div>
                
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <div class="input-group">
                        <span class="prefix">Rp</span>
                        <input type="number" name="price" required placeholder="50000" class="form-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Durasi Aktif (Hari)</label>
                    <div class="input-group">
                        <input type="number" name="duration" required placeholder="30" class="form-input">
                        <span class="suffix">Hari</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Fitur Unggulan</label>
                    <textarea name="features" rows="4" required placeholder="Tanpa Iklan,&#10;Kualitas 4K,&#10;Support TV" class="form-input"></textarea>
                    <small style="color:#666; display:block; margin-top:5px;">Pisahkan dengan koma (,) atau enter.</small>
                </div>
                
                <button type="submit" name="add_plan" class="btn btn-primary full-width">
                    <i class="ri-save-line"></i> Simpan Paket
                </button>
            </form>
        </div>
    </aside>

</div>

<style>
/* Layout Split */
.plan-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 30px;
    align-items: start;
}

/* Card Paket (Admin View) */
.plan-admin-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #1e2129;
    border: 1px solid rgba(255,255,255,0.05);
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 20px;
    transition: transform 0.2s;
}
.plan-admin-card:hover { transform: translateY(-3px); border-color: rgba(255,255,255,0.1); }

.plan-content { flex: 1; display: flex; align-items: center; gap: 30px; }
.plan-head { min-width: 180px; }
.plan-title { margin: 0 0 5px 0; font-size: 1.1rem; color: #fff; }
.plan-price { color: #e50914; font-weight: 800; display: flex; align-items: baseline; gap: 2px; }
.plan-price .amount { font-size: 1.6rem; }
.plan-price .period { color: #666; font-size: 0.85rem; font-weight: normal; margin-left: 5px; }

/* Feature List Horizontal */
.plan-features {
    list-style: none; padding: 0; margin: 0;
    display: flex; flex-wrap: wrap; gap: 15px;
    border-left: 1px solid rgba(255,255,255,0.1);
    padding-left: 30px;
}
.plan-features li { font-size: 0.9rem; color: #aaa; display: flex; align-items: center; gap: 6px; }
.plan-features li i { color: #4ade80; }

/* Form Styles */
.card-sticky { position: sticky; top: 20px; }
.card-header h3 { margin: 0 0 20px; font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; color: white; }

.form-group { margin-bottom: 15px; }
.form-group label { display: block; color: #ccc; margin-bottom: 6px; font-size: 0.85rem; font-weight: 600; }
.form-input { 
    width: 100%; background: #0f1014; border: 1px solid #333; color: white; 
    padding: 10px 12px; border-radius: 6px; outline: none; font-family: inherit;
}
.form-input:focus { border-color: #e50914; background: #000; }

/* Input Group (Rp / Hari) */
.input-group { display: flex; align-items: center; background: #0f1014; border: 1px solid #333; border-radius: 6px; overflow: hidden; }
.input-group .prefix, .input-group .suffix {
    background: #2a2d35; color: #aaa; padding: 0 12px; font-size: 0.85rem; font-weight: 600;
    display: flex; align-items: center; height: 42px;
}
.input-group input { border: none; background: transparent; height: 42px; }

/* Placeholder Kosong */
.empty-placeholder {
    text-align: center; padding: 50px; border: 2px dashed #333; border-radius: 12px; color: #666;
}
.empty-placeholder i { font-size: 3rem; margin-bottom: 10px; display: block; }

/* Responsive Mobile */
@media (max-width: 900px) {
    .plan-layout { grid-template-columns: 1fr; }
    .plan-admin-card { flex-direction: column; align-items: flex-start; gap: 20px; }
    .plan-content { flex-direction: column; align-items: flex-start; gap: 15px; width: 100%; }
    .plan-features { border-left: none; padding-left: 0; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; width: 100%; }
    .plan-actions { width: 100%; text-align: right; }
}
</style>
