<?php
$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];
$role = $_SESSION['role'];

// --- LOGIC PHP TETAP SAMA (TIDAK PERLU DIUBAH BANYAK) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_web_settings']) && $role === 'admin') {
    $site_name = $_POST['site_name'];
    $site_desc = $_POST['site_desc'];
    $maintenance = isset($_POST['maintenance_mode']) ? 1 : 0;
    $ad_header = $_POST['ad_header'];
    $ad_player = $_POST['ad_player'];
    
    $stmt = $db->prepare("UPDATE settings SET site_name=?, site_desc=?, maintenance_mode=?, ad_header=?, ad_player=? WHERE id=1");
    $stmt->execute([$site_name, $site_desc, $maintenance, $ad_header, $ad_player]);
    
    setFlash('success', 'Tersimpan', 'Konfigurasi website berhasil diperbarui.');
    echo "<script>window.location='/dashboard/settings';</script>"; exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_password'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    if(!empty($new_pass)) {
        if(strlen($new_pass) < 6) { setFlash('error', 'Gagal', 'Password minimal 6 karakter.'); } 
        elseif($new_pass !== $confirm_pass) { setFlash('error', 'Gagal', 'Konfirmasi password tidak cocok.'); } 
        else {
            $hash = password_hash($new_pass, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $uid]);
            setFlash('success', 'Berhasil', 'Password diperbarui. Silakan login ulang nanti.');
        }
    }
    echo "<script>window.location='/dashboard/settings';</script>"; exit;
}

$u = $db->query("SELECT * FROM users WHERE id = $uid")->fetch(PDO::FETCH_ASSOC);
$web = $db->query("SELECT * FROM settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h2 class="page-title">⚙️ Pengaturan</h2>
</div>

<div class="tabs-container">
    <button class="tab-btn active" onclick="switchTab('account')">
        <i class="ri-user-settings-line"></i> Akun Saya
    </button>
    <?php if($role === 'admin'): ?>
    <button class="tab-btn" onclick="switchTab('website')">
        <i class="ri-global-line"></i> Konfigurasi Web
    </button>
    <button class="tab-btn" onclick="switchTab('ads')">
        <i class="ri-advertisement-line"></i> Manajemen Iklan
    </button>
    <?php endif; ?>
</div>

<div class="settings-layout">

    <div id="tab-account" class="tab-content active">
        <div class="card profile-card">
            <div class="profile-header">
                <div class="avatar-large">
                    <?= strtoupper(substr($u['username'], 0, 1)) ?>
                </div>
                <div class="profile-info">
                    <h3><?= htmlspecialchars($u['username']) ?></h3>
                    <span class="role-badge <?= $u['role'] ?>"><?= strtoupper($u['role']) ?></span>
                    <p>Bergabung sejak <?= date('d M Y', strtotime($u['created_at'])) ?></p>
                </div>
            </div>
            
            <form method="POST" class="password-form">
                <input type="hidden" name="save_password" value="1">
                <h4>Ganti Password</h4>
                
                <div class="form-group">
                    <label>Password Baru</label>
                    <div class="input-icon">
                        <i class="ri-lock-line"></i>
                        <input type="password" name="new_password" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <div class="input-icon">
                        <i class="ri-lock-check-line"></i>
                        <input type="password" name="confirm_password" placeholder="Ulangi password baru" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <?php if($role === 'admin'): ?>
    <div id="tab-website" class="tab-content">
        <div class="card">
            <form method="POST">
                <input type="hidden" name="save_web_settings" value="1">
                <div class="form-split">
                    <div class="form-group">
                        <label>Nama Website</label>
                        <input type="text" name="site_name" value="<?= htmlspecialchars($web['site_name']) ?>" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label>Mode Maintenance</label>
                        <label class="toggle-switch">
                            <input type="checkbox" name="maintenance_mode" value="1" <?= $web['maintenance_mode'] ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                        <small style="display:block; margin-top:5px; color:#888;">Aktifkan jika sedang perbaikan.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Deskripsi SEO</label>
                    <textarea name="site_desc" rows="3" class="form-input"><?= htmlspecialchars($web['site_desc']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Konfigurasi</button>
            </form>
        </div>
    </div>

    <div id="tab-ads" class="tab-content">
        <form method="POST">
            <input type="hidden" name="save_web_settings" value="1">
            
            <div class="ads-grid">
                <div class="card ad-card">
                    <div class="ad-header">
                        <i class="ri-layout-top-line"></i> Iklan Header (Global)
                    </div>
                    <p class="ad-desc">Muncul di bagian atas semua halaman. Cocok untuk banner 728x90 atau script Pop-under.</p>
                    <textarea name="ad_header" class="code-editor" placeholder=""><?= htmlspecialchars($web['ad_header']) ?></textarea>
                </div>

                <div class="card ad-card">
                    <div class="ad-header">
                        <i class="ri-movie-line"></i> Iklan Player (Nonton)
                    </div>
                    <p class="ad-desc">Muncul tepat di bawah video player. Gunakan banner responsif atau tombol download.</p>
                    <textarea name="ad_player" class="code-editor" placeholder=""><?= htmlspecialchars($web['ad_player']) ?></textarea>
                </div>
            </div>

            <div class="save-bar">
                <button type="submit" class="btn btn-primary btn-lg">Simpan Semua Iklan</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

</div>

<script>
function switchTab(tabId) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    // Show selected
    document.getElementById('tab-' + tabId).classList.add('active');
    event.currentTarget.classList.add('active');
}
</script>

<style>
/* TABS STYLE */
.tabs-container {
    display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1px;
}
.tab-btn {
    background: transparent; border: none; color: #888; padding: 10px 20px;
    font-size: 1rem; font-weight: 600; cursor: pointer; border-bottom: 3px solid transparent;
    display: flex; align-items: center; gap: 8px; transition: 0.3s;
}
.tab-btn:hover { color: white; }
.tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
.tab-content { display: none; animation: fadeIn 0.3s; }
.tab-content.active { display: block; }

/* PROFILE CARD */
.profile-card { max-width: 600px; }
.profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
.avatar-large { width: 80px; height: 80px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; color: white; }
.profile-info h3 { margin: 0 0 5px 0; font-size: 1.5rem; }
.profile-info p { margin: 5px 0 0; color: #888; font-size: 0.9rem; }
.role-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; }
.role-badge.admin { background: #6d28d9; color: #d8b4fe; }
.role-badge.vip { background: #b45309; color: #fcd34d; }
.role-badge.free { background: #374151; color: #9ca3af; }

/* FORM ELEMENTS */
.form-group { margin-bottom: 20px; }
.form-group label { display: block; color: #ccc; margin-bottom: 8px; font-weight: 500; }
.form-input, .input-icon input { width: 100%; background: #0f1014; border: 1px solid #333; color: white; padding: 12px; border-radius: 8px; outline: none; transition: 0.3s; }
.form-input:focus, .input-icon input:focus { border-color: var(--primary); }
.input-icon { position: relative; }
.input-icon i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #666; }
.input-icon input { padding-left: 40px; }

/* TOGGLE SWITCH */
.toggle-switch { position: relative; display: inline-block; width: 50px; height: 26px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #333; transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: var(--primary); }
input:checked + .slider:before { transform: translateX(24px); }

/* ADS MANAGEMENT */
.ads-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; }
.ad-header { font-weight: 700; font-size: 1.1rem; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
.ad-desc { font-size: 0.9rem; color: #888; margin-bottom: 15px; min-height: 40px; }
.code-editor { font-family: monospace; background: #000; color: #4ade80; padding: 15px; border-radius: 8px; border: 1px solid #333; width: 100%; height: 150px; resize: vertical; }
.save-bar { margin-top: 30px; text-align: right; }

@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
