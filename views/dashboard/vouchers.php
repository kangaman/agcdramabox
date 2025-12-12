<?php
if($_SESSION['role'] !== 'admin') exit('Access Denied');
$db = (new Database())->getConnection();

// --- LOGIC: GENERATE VOUCHER ---
if (isset($_POST['generate'])) {
    $amount = intval($_POST['amount']); 
    $days = intval($_POST['days']);
    $prefix = strtoupper(trim($_POST['prefix'])); 
    
    $successCount = 0;
    $stmt = $db->prepare("INSERT INTO vouchers (code, days) VALUES (?, ?)");
    
    for ($i = 0; $i < $amount; $i++) {
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $code = ($prefix ? $prefix . '-' : 'VIP-') . $random;
        try {
            $stmt->execute([$code, $days]);
            $successCount++;
        } catch (Exception $e) { continue; }
    }
    setFlash('success', 'Berhasil', "$successCount Voucher berhasil dibuat!");
    header("Location: /dashboard/vouchers");
    exit;
}

// --- LOGIC: HAPUS VOUCHER ---
if (isset($_POST['delete_id'])) {
    $db->prepare("DELETE FROM vouchers WHERE id=?")->execute([$_POST['delete_id']]);
    setFlash('success', 'Terhapus', 'Voucher berhasil dihapus.');
    header("Location: /dashboard/vouchers");
    exit;
}

// AMBIL DATA
$vouchers = $db->query("
    SELECT v.*, u.username 
    FROM vouchers v 
    LEFT JOIN users u ON v.used_by = u.id 
    ORDER BY v.status ASC, v.created_at DESC 
    LIMIT 100
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">🎟️ Voucher Manager</h2>
        <p style="color:#888; margin:0; font-size:0.9rem;">Kelola kode redeem untuk akses VIP.</p>
    </div>
</div>

<div class="voucher-layout">
    
    <aside class="generator-panel">
        <div class="card card-sticky">
            <div class="card-header">
                <h3><i class="ri-add-circle-fill" style="color:#e50914"></i> Buat Baru</h3>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label>Jumlah Voucher</label>
                    <div class="input-icon">
                        <i class="ri-hashtag"></i>
                        <input type="number" name="amount" value="5" min="1" max="50" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Durasi Akses</label>
                    <div class="input-icon">
                        <i class="ri-calendar-line"></i>
                        <select name="days">
                            <option value="3">3 Hari (Trial)</option>
                            <option value="7">7 Hari (Mingguan)</option>
                            <option value="30" selected>30 Hari (Bulanan)</option>
                            <option value="365">1 Tahun (Premium)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Prefix (Opsional)</label>
                    <div class="input-icon">
                        <i class="ri-text"></i>
                        <input type="text" name="prefix" placeholder="Contoh: SALE12" style="text-transform:uppercase;">
                    </div>
                </div>

                <button type="submit" name="generate" class="btn btn-primary full-width">
                    Generate Voucher
                </button>
            </form>
        </div>
    </aside>

    <div class="list-panel">
        <div class="card">
            <div class="card-header row-between">
                <h3>Riwayat 100 Terakhir</h3>
                <span class="badge-pill"><?= count($vouchers) ?> Item</span>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="35%">Tiket Voucher</th>
                            <th width="20%">Durasi</th>
                            <th width="35%">Status / Pengguna</th>
                            <th width="10%" style="text-align:center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($vouchers as $v): ?>
                        <tr class="<?= $v['status'] === 'used' ? 'row-dimmed' : '' ?>">
                            
                            <td>
                                <div class="ticket-visual">
                                    <div class="ticket-border"></div>
                                    <div class="ticket-content">
                                        <code><?= htmlspecialchars($v['code']) ?></code>
                                        <?php if($v['status'] === 'active'): ?>
                                        <button onclick="copyToClipboard('<?= $v['code'] ?>')" class="btn-copy">
                                            <i class="ri-file-copy-line"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="duration-badge">
                                    <i class="ri-hourglass-fill"></i> <?= $v['days'] ?> Hari
                                </div>
                            </td>

                            <td>
                                <?php if($v['status'] === 'used'): ?>
                                    <div class="status-used">
                                        <div class="badge-red"><i class="ri-close-circle-line"></i> Terpakai</div>
                                        <small>
                                            <i class="ri-user-smile-line"></i> <?= htmlspecialchars($v['username'] ?? 'Unknown') ?>
                                            <br>
                                            <span style="opacity:0.7; font-size:0.7rem;">
                                                <?= date('d M Y H:i', strtotime($v['used_at'])) ?>
                                            </span>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <div class="badge-green"><i class="ri-checkbox-circle-line"></i> Tersedia</div>
                                <?php endif; ?>
                            </td>

                            <td style="text-align:center">
                                <?php if($v['status'] === 'active'): ?>
                                <form method="POST" onsubmit="return confirm('Hapus voucher ini?');">
                                    <input type="hidden" name="delete_id" value="<?= $v['id'] ?>">
                                    <button class="btn-delete-icon" title="Hapus">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                    <i class="ri-check-double-line" style="color:#444"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($vouchers)): ?>
                        <tr><td colspan="4" style="text-align:center; padding:40px; color:#666;">Belum ada voucher dibuat.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* LAYOUT GRID UTAMA */
.voucher-layout {
    display: grid;
    grid-template-columns: 320px 1fr; /* Kiri tetap 320px, Kanan sisa layar */
    gap: 25px;
    align-items: start; /* Penting agar sticky berfungsi */
}

/* GENERATOR PANEL (KIRI) */
.card-sticky {
    position: sticky;
    top: 20px; /* Menempel saat discroll */
    background: #1e2129;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 25px;
}
.card-header h3 { font-size:1.1rem; color:white; margin:0 0 20px 0; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:15px; }

/* INPUT FORM MODERN */
.form-group { margin-bottom: 20px; }
.form-group label { display: block; color: #ccc; margin-bottom: 8px; font-size: 0.9rem; font-weight: 500; }
.input-icon { position: relative; }
.input-icon i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #666; }
.input-icon input, .input-icon select {
    width: 100%; background: #0f1014; border: 1px solid #333; color: white;
    padding: 12px 12px 12px 40px; border-radius: 8px; outline: none; transition: 0.3s;
}
.input-icon input:focus, .input-icon select:focus { border-color: #e50914; background: #000; }
.full-width { width: 100%; justify-content: center; padding: 12px; margin-top: 10px; }

/* TABEL LIST (KANAN) */
.row-between { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.badge-pill { background: #333; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; color: #aaa; }

/* TIKET VISUAL */
.ticket-visual {
    display: flex; height: 40px; align-items: stretch;
    background: rgba(255,255,255,0.03); border-radius: 6px; overflow: hidden;
    border: 1px solid rgba(255,255,255,0.05); width: fit-content;
}
.ticket-border { width: 4px; background: #e50914; }
.ticket-content { display: flex; align-items: center; padding: 0 12px; gap: 10px; }
.ticket-content code { font-family: 'Courier New', monospace; font-size: 1rem; font-weight: 700; letter-spacing: 1px; color: #fff; }
.btn-copy { background: none; border: none; color: #666; cursor: pointer; font-size: 1.1rem; transition:0.2s; }
.btn-copy:hover { color: #e50914; transform: scale(1.1); }

/* BADGES & STATUS */
.duration-badge { color: #fbbf24; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 6px; }

.badge-green { background: rgba(34, 197, 94, 0.1); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.2); padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; width: fit-content; display: flex; gap: 5px; align-items: center;}
.badge-red { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; width: fit-content; display: flex; gap: 5px; align-items: center; margin-bottom: 5px; }

.status-used small { color: #888; display: block; line-height: 1.3; }

/* TOMBOL AKSI */
.btn-delete-icon {
    width: 35px; height: 35px; border-radius: 8px; border: 1px solid rgba(239,68,68,0.2);
    background: rgba(239,68,68,0.1); color: #ef4444; cursor: pointer; transition: 0.2s;
    display: inline-flex; align-items: center; justify-content: center;
}
.btn-delete-icon:hover { background: #ef4444; color: white; }

.row-dimmed { opacity: 0.5; }

/* RESPONSIVE MOBILE */
@media (max-width: 900px) {
    .voucher-layout { grid-template-columns: 1fr; }
    .card-sticky { position: static; }
}
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const el = document.createElement('div');
        el.innerText = '✅ Kode Disalin';
        el.style.cssText = 'position:fixed; bottom:20px; right:20px; background:#22c55e; color:#fff; padding:10px 20px; border-radius:8px; z-index:9999; font-weight:bold; box-shadow:0 5px 15px rgba(0,0,0,0.3);';
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 2000);
    });
}
</script>
