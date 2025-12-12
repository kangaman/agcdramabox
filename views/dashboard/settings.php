<?php
$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];
$role = $_SESSION['role'];

// --- 1. LOGIKA SIMPAN PENGATURAN WEB (KHUSUS ADMIN) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_web_settings']) && $role === 'admin') {
    $site_name = $_POST['site_name'];
    $site_desc = $_POST['site_desc'];
    $maintenance = isset($_POST['maintenance_mode']) ? 1 : 0;
    $ad_header = $_POST['ad_header'];
    $ad_player = $_POST['ad_player'];
    
    // Update tabel settings (ID selalu 1)
    $stmt = $db->prepare("UPDATE settings SET site_name=?, site_desc=?, maintenance_mode=?, ad_header=?, ad_player=? WHERE id=1");
    $stmt->execute([$site_name, $site_desc, $maintenance, $ad_header, $ad_player]);
    
    setFlash('success', 'Tersimpan', 'Konfigurasi website berhasil diperbarui.');
    header("Location: /dashboard/settings");
    exit;
}

// --- 2. LOGIKA GANTI PASSWORD (UNTUK SEMUA USER) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_password'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    try {
        if(!empty($new_pass)) {
            if(strlen($new_pass) < 6) {
                setFlash('error', 'Gagal', 'Password minimal 6 karakter.');
            } 
            elseif($new_pass !== $confirm_pass) {
                setFlash('error', 'Gagal', 'Konfirmasi password tidak cocok.');
            } 
            else {
                $hash = password_hash($new_pass, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hash, $uid]);
                setFlash('success', 'Berhasil', 'Password Anda telah diperbarui.');
            }
        } else {
            setFlash('info', 'Info', 'Tidak ada perubahan password.');
        }
    } catch (Exception $e) {
        setFlash('error', 'Error', 'Terjadi kesalahan sistem.');
    }
    
    header("Location: /dashboard/settings");
    exit;
}

// AMBIL DATA USER & SETTINGS
$u = $db->query("SELECT * FROM users WHERE id = $uid")->fetch(PDO::FETCH_ASSOC);
$web = $db->query("SELECT * FROM settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

// STATUS BADGE USER
$statusText = 'Free User';
$statusColor = '#666';

if($u['role'] == 'vip' && $u['active_until']) {
    $diff = strtotime($u['active_until']) - time();
    $daysLeft = floor($diff / (60 * 60 * 24));
    if($daysLeft > 0) {
        $statusText = "VIP ($daysLeft Hari)";
        $statusColor = '#ffd700'; 
    } else {
        $statusText = "VIP (Expired)";
        $statusColor = '#ff4757'; 
    }
} elseif($u['role'] == 'admin') {
    $statusText = "Administrator";
    $statusColor = '#a78bfa'; 
}
?>

<div class="header-flex">
    <div>
        <h2 class="page-title">⚙️ Pengaturan</h2>
        <p style="color:var(--text-muted)">Kelola akun dan konfigurasi sistem.</p>
    </div>
</div>

<div class="grid-2" style="align-items: start;">
    
    <div style="display:flex; flex-direction:column; gap:20px;">
        
        <div class="card" style="text-align:center;">
            <div style="width:80px; height:80px; background:var(--primary); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2.5rem; font-weight:bold; margin:0 auto 15px; border:3px solid var(--bg-dark);">
                <?= strtoupper(substr($u['username'], 0, 1)) ?>
            </div>
            
            <h3 style="margin-bottom:5px;"><?= htmlspecialchars($u['username']) ?></h3>
            
            <span class="badge" style="background:<?= $statusColor ?>20; color:<?= $statusColor ?>; border:1px solid <?= $statusColor ?>40; padding:4px 10px; border-radius:20px; font-size:0.75rem;">
                <?= $statusText ?>
            </span>

            <div style="margin-top:20px; text-align:left; font-size:0.9rem; color:#888;">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span>Bergabung</span>
                    <span style="color:#ccc"><?= date('d M Y', strtotime($u['created_at'])) ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom:20px; border-bottom:1px solid var(--border); padding-bottom:10px;">
                <i class="ri-lock-password-line"></i> Keamanan Login
            </h3>
            <form method="POST">
                <input type="hidden" name="save_password" value="1">
                
                <div style="margin-bottom: 15px;">
                    <label>Username</label>
                    <input type="text" value="<?= htmlspecialchars($u['username']) ?>" disabled style="opacity:0.6; cursor:not-allowed;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label>Password Baru</label>
                    <div class="password-wrapper" style="position:relative;">
                        <input type="password" name="new_password" id="pass1" placeholder="Min. 6 karakter" style="padding-right: 40px;">
                        <i class="ri-eye-off-line toggle-pass" onclick="togglePass('pass1', this)" style="position:absolute; right:12px; top:12px; cursor:pointer; color:#888;"></i>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label>Ulangi Password</label>
                    <div class="password-wrapper" style="position:relative;">
                        <input type="password" name="confirm_password" id="pass2" placeholder="Ketik ulang" style="padding-right: 40px;">
                        <i class="ri-eye-off-line toggle-pass" onclick="togglePass('pass2', this)" style="position:absolute; right:12px; top:12px; cursor:pointer; color:#888;"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary btn-block">
                    <i class="ri-save-2-line"></i> Simpan Password
                </button>
            </form>
        </div>
    </div>

    <?php if($role === 'admin'): ?>
    <div class="card">
        <h3 style="margin-bottom:20px; border-bottom:1px solid var(--border); padding-bottom:10px;">
            <i class="ri-settings-4-line"></i> Konfigurasi Website
        </h3>
        
        <form method="POST">
            <input type="hidden" name="save_web_settings" value="1">
            
            <div style="margin-bottom: 15px;">
                <label>Nama Website</label>
                <input type="text" name="site_name" value="<?= htmlspecialchars($web['site_name'] ?? 'DramaFlix') ?>" required placeholder="Contoh: DramaFlix">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Deskripsi SEO (Meta Description)</label>
                <textarea name="site_desc" rows="2" class="form-control" placeholder="Deskripsi singkat web untuk Google..."><?= htmlspecialchars($web['site_desc'] ?? '') ?></textarea>
            </div>

            <div style="margin-bottom: 20px; background: rgba(229, 9, 20, 0.1); padding: 15px; border-radius: 8px; border: 1px solid rgba(229, 9, 20, 0.3);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="maintenance_mode" id="maintCheck" value="1" <?= ($web['maintenance_mode'] ?? 0) ? 'checked' : '' ?> style="width: 20px; height: 20px; cursor:pointer;">
                    <div>
                        <label for="maintCheck" style="margin:0; cursor:pointer; font-weight:bold; color:#ff4757;">Mode Maintenance</label>
                        <small style="display:block; color:#bbb;">Jika aktif, website hanya bisa diakses oleh Admin. Pengunjung akan melihat halaman perbaikan.</small>
                    </div>
                </div>
            </div>

            <hr style="border:0; border-top:1px solid var(--border); margin:20px 0;">
            
            <h4 style="margin-bottom:15px; color:var(--primary); font-size:1rem;">📺 Manajemen Iklan (Ads)</h4>
            
            <div style="margin-bottom: 25px; background: rgba(255,255,255,0.03); padding: 15px; border-radius: 8px;">
                <label style="font-weight:bold; color:#fff;">1. Iklan Header (Global)</label>
                <p style="font-size:0.8rem; color:#aaa; margin-bottom:10px; line-height:1.4;">
                    Muncul di <b>SEMUA HALAMAN</b> (di atas menu). <br>
                    ✅ <b>Cocok untuk:</b> Banner 728x90, 320x50, atau Script Pop-Under/Direct Link.<br>
                    ⚠️ <b>Target:</b> Semua pengunjung (Guest & Free Member).
                </p>
                <textarea name="ad_header" rows="4" class="form-control" style="font-family:monospace; font-size:0.85rem; background:#000; color:#0f0;" placeholder=""><?= htmlspecialchars($web['ad_header'] ?? '') ?></textarea>
            </div>

            <div style="margin-bottom: 20px; background: rgba(255,255,255,0.03); padding: 15px; border-radius: 8px;">
                <label style="font-weight:bold; color:#fff;">2. Iklan Player (Halaman Nonton)</label>
                <p style="font-size:0.8rem; color:#aaa; margin-bottom:10px; line-height:1.4;">
                    Muncul di <b>BAWAH VIDEO PLAYER</b>.<br>
                    ✅ <b>Cocok untuk:</b> Banner 300x250, 468x60, atau Tombol Download.<br>
                    ⛔ <b>Dilarang:</b> Iklan Pop-Up (Mengganggu tombol Play).
                </p>
                <textarea name="ad_player" rows="4" class="form-control" style="font-family:monospace; font-size:0.85rem; background:#000; color:#0f0;" placeholder=""><?= htmlspecialchars($web['ad_player'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="ri-save-3-line"></i> Simpan Konfigurasi Web
            </button>
        </form>
    </div>
    <?php endif; ?>

</div>

<script>
function togglePass(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
        icon.style.color = 'var(--primary)';
    } else {
        input.type = "password";
        icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
        icon.style.color = '#888';
    }
}
</script>
