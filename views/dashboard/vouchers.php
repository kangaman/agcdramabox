<?php
// Cek Akses Admin
if ($_SESSION['role'] !== 'admin') {
    echo "<script>window.location='/dashboard';</script>"; exit;
}

$db = (new Database())->getConnection();

// --- LOGIC: GENERATE VOUCHER ---
if (isset($_POST['generate'])) {
    $amount = intval($_POST['amount']); // Jumlah voucher
    $days = intval($_POST['days']);     // Durasi hari
    $prefix = strtoupper(trim($_POST['prefix'])); // Awalan kode (Opsional)
    
    $successCount = 0;
    
    // Siapkan Query
    $stmt = $db->prepare("INSERT INTO vouchers (code, days) VALUES (?, ?)");
    
    for ($i = 0; $i < $amount; $i++) {
        // Buat Kode Unik (Contoh: VIP30-X7Z9A)
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $code = ($prefix ? $prefix . '-' : 'VIP-') . $random;
        
        try {
            $stmt->execute([$code, $days]);
            $successCount++;
        } catch (Exception $e) {
            // Abaikan jika duplikat (jarang terjadi)
            continue;
        }
    }
    
    setFlash('success', 'Berhasil', "$successCount Voucher berhasil dibuat!");
    header("Location: /dashboard/vouchers");
    exit;
}

// --- LOGIC: HAPUS VOUCHER ---
if (isset($_POST['delete_id'])) {
    $db->prepare("DELETE FROM vouchers WHERE id=?")->execute([$_POST['delete_id']]);
    header("Location: /dashboard/vouchers");
    exit;
}

// AMBIL DATA VOUCHER
// Filter sederhana: Tampilkan yang aktif dulu
$vouchers = $db->query("
    SELECT v.*, u.username 
    FROM vouchers v 
    LEFT JOIN users u ON v.used_by = u.id 
    ORDER BY v.status ASC, v.created_at DESC 
    LIMIT 100
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="header-flex">
    <div>
        <h2 class="page-title">🎟️ Manajemen Voucher</h2>
        <p style="color:var(--text-muted)">Buat kode redeem untuk user.</p>
    </div>
</div>

<div class="grid-2" style="align-items: start;">
    
    <div class="card">
        <h3 style="margin-bottom:15px; border-bottom:1px solid var(--border); padding-bottom:10px;">
            <i class="ri-magic-line"></i> Generator Otomatis
        </h3>
        <form method="POST">
            <div class="form-group mb-3">
                <label>Jumlah Voucher</label>
                <input type="number" name="amount" value="10" min="1" max="100" class="form-control" required>
                <small style="color:#666">Maksimal 100 sekali generate.</small>
            </div>
            
            <div class="form-group mb-3">
                <label>Durasi VIP (Hari)</label>
                <input type="number" name="days" value="30" class="form-control" required>
            </div>

            <div class="form-group mb-4">
                <label>Prefix Kode (Opsional)</label>
                <input type="text" name="prefix" placeholder="Contoh: VIP30" class="form-control" style="text-transform:uppercase">
                <small style="color:#666">Hasil: VIP30-X7A9Z</small>
            </div>

            <button type="submit" name="generate" class="btn btn-primary btn-block">
                <i class="ri-add-circle-line"></i> Generate Sekarang
            </button>
        </form>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:15px; background:rgba(0,0,0,0.2); border-bottom:1px solid var(--border);">
            <h4 style="margin:0; font-size:1rem;">Daftar 100 Terakhir</h4>
        </div>
        <div class="table-responsive" style="max-height: 500px; overflow-y:auto;">
            <table class="table-modern" style="width:100%;">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Hari</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($vouchers as $v): ?>
                    <tr style="<?= $v['status']=='used' ? 'opacity:0.5; background:rgba(0,0,0,0.1);' : '' ?>">
                        <td>
                            <code style="background:rgba(255,255,255,0.1); padding:3px 6px; border-radius:4px; color:#e50914; font-weight:bold;">
                                <?= $v['code'] ?>
                            </code>
                        </td>
                        <td><?= $v['days'] ?> Hari</td>
                        <td>
                            <?php if($v['status'] === 'active'): ?>
                                <span class="badge" style="background:#22c55e; color:white;">Aktif</span>
                            <?php else: ?>
                                <span class="badge" style="background:#666; color:#ccc;">Dipakai</span>
                                <div style="font-size:0.7rem; margin-top:2px;">Oleh: <?= htmlspecialchars($v['username']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($v['status'] === 'active'): ?>
                            <button onclick="copyToClipboard('<?= $v['code'] ?>')" class="btn btn-sm btn-secondary" title="Salin">
                                <i class="ri-file-copy-line"></i>
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $v['id'] ?>">
                                <button class="btn btn-sm" style="color:#ef4444; background:none; border:none;" onclick="return confirm('Hapus voucher ini?')">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert("Kode disalin: " + text);
    });
}
</script>
