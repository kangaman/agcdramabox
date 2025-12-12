<?php
$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];
$role = $_SESSION['role'];

// --- 1. LOGIC: UPLOAD PROFIL (BARU) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $fullname = trim($_POST['fullname']);
    
    // Handle Upload Avatar
    $avatarPath = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $limit = 2 * 1024 * 1024; // 2MB
            if ($_FILES['avatar']['size'] <= $limit) {
                // Buat folder jika belum ada
                $dir = __DIR__ . '/../../assets/uploads/avatars/';
                if (!file_exists($dir)) mkdir($dir, 0777, true);
                
                $filename = "user_" . $uid . "_" . time() . "." . $ext;
                move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $filename);
                $avatarPath = "/assets/uploads/avatars/" . $filename;
            } else {
                setFlash('error', 'Gagal', 'Ukuran foto maksimal 2MB.');
            }
        } else {
            setFlash('error', 'Gagal', 'Format file tidak didukung (Gunakan JPG/PNG/WEBP).');
        }
    }

    // Update Database
    if ($avatarPath) {
        $stmt = $db->prepare("UPDATE users SET fullname=?, avatar=? WHERE id=?");
        $stmt->execute([$fullname, $avatarPath, $uid]);
    } else {
        $stmt = $db->prepare("UPDATE users SET fullname=? WHERE id=?");
        $stmt->execute([$fullname, $uid]);
    }
    
    setFlash('success', 'Berhasil', 'Profil Anda telah diperbarui.');
    echo "<script>window.location='/dashboard/settings';</script>"; exit;
}

// --- 2. LOGIC: GANTI PASSWORD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_password'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    if(!empty($new_pass)) {
        if(strlen($new_pass) < 6) { setFlash('error', 'Gagal', 'Password minimal 6 karakter.'); } 
        elseif($new_pass !== $confirm_pass) { setFlash('error', 'Gagal', 'Konfirmasi password tidak cocok.'); } 
        else {
            $hash = password_hash($new_pass, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $uid]);
            setFlash('success', 'Berhasil', 'Password diperbarui.');
        }
    }
    echo "<script>window.location='/dashboard/settings';</script>"; exit;
}

// --- 3. LOGIC: WEB SETTINGS (ADMIN) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_web_settings']) && $role === 'admin') {
    $site_name = $_POST['site_name'];
    $site_desc = $_POST['site_desc'];
    $maintenance = isset($_POST['maintenance_mode']) ? 1 : 0;
    $ad_header = $_POST['ad_header'];
    $ad_player = $_POST['ad_player'];
    
    $stmt = $db->prepare("UPDATE settings SET site_name=?, site_desc=?, maintenance_mode=?, ad_header=?, ad_player=? WHERE id=1");
    $stmt->execute([$site_name, $site_desc, $maintenance, $ad_header, $ad_player]);
    
    setFlash('success', 'Tersimpan', 'Konfigurasi website diperbarui.');
    echo "<script>window.location='/dashboard/settings';</script>"; exit;
}

// AMBIL DATA
$u = $db->query("SELECT * FROM users WHERE id = $uid")->fetch(PDO::FETCH_ASSOC);
$web = $db->query("SELECT * FROM settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h2 class="page-title">⚙️ Pengaturan</h2>
</div>

<div class="tabs-container">
    <button class="tab-btn active" onclick="switchTab('account')">
        <i class="ri-user-smile-line"></i> Profil Saya
    </button>
    <button class="tab-btn" onclick="switchTab('security')">
        <i class="ri-lock-password-line"></i> Keamanan
    </button>
    <?php if($role === 'admin'): ?>
    <button class="tab-btn" onclick="switchTab('website')">
        <i class="ri-global-line"></i> Sistem Web
    </button>
    <?php endif; ?>
</div>

<div class="settings-layout">

    <div id="tab-account" class="tab-content active">
        <div class="card">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="save_profile" value="1">
                
                <div class="profile-edit-grid">
                    <div class="avatar-section">
                        <div class="avatar-preview">
                            <?php if(!empty($u['avatar'])): ?>
                                <img src="<?= $u['avatar'] ?>" id="previewImg" alt="Avatar">
                            <?php else: ?>
                                <div class="avatar-placeholder"><?= strtoupper(substr($u['username'], 0, 1)) ?></div>
                            <?php endif; ?>
                            
                            <label for="uploadAvatar" class="btn-upload-icon">
                                <i class="ri-camera-fill"></i>
                            </label>
                            <input type="file" name="avatar" id="uploadAvatar" accept="image/*" onchange="previewFile()" hidden>
                        </div>
                        <p style="font-size:0.8rem; color:#888; margin-top:10px;">Klik kamera untuk ganti foto.<br>Maks. 2MB (JPG/PNG).</p>
                    </div>

                    <div class="info-section">
                        <div class="form-group">
                            <label>Username (Tidak bisa diubah)</label>
                            <input type="text" value="@<?= htmlspecialchars($u['username']) ?>" class="form-input" disabled style="opacity:0.6; cursor:not-allowed;">
                        </div>

                        <div class="form-group">
                            <label>Nama Tampilan / Panggilan</label>
                            <input type="text" name="fullname" value="<?= htmlspecialchars($u['fullname'] ?? '') ?>" class="form-input" placeholder="Nama Anda">
                        </div>

                        <div class="form-group">
                            <label>Status Akun</label>
                            <div style="background:rgba(255,255,255,0.05); padding:10px; border-radius:8px; display:flex; align-items:center; gap:10px;">
                                <?php if($u['role'] == 'vip'): ?>
                                    <i class="ri-vip-crown-fill" style="color:#facc15; font-size:1.5rem;"></i>
                                    <div>
                                        <div style="color:white; font-weight:bold;">VIP MEMBER</div>
                                        <small style="color:#aaa;">Aktif sampai <?= date('d M Y', strtotime($u['active_until'])) ?></small>
                                    </div>
                                <?php else: ?>
                                    <i class="ri-user-line" style="color:#999; font-size:1.5rem;"></i>
                                    <div>
                                        <div style="color:white; font-weight:bold;">Free User</div>
                                        <small style="color:#aaa;"><a href="/dashboard/billing" style="color:#e50914;">Upgrade ke VIP</a> untuk fitur lebih.</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Profil</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="tab-security" class="tab-content">
        <div class="card" style="max-width:500px;">
            <div class="card-header">
                <h3><i class="ri-shield-keyhole-line" style="color:#e50914"></i> Ganti Password</h3>
            </div>
            <form method="POST">
                <input type="hidden" name="save_password" value="1">
                
                <div class="form-group">
                    <label>Password Baru</label>
                    <div class="input-icon">
                        <i class="ri-lock-line"></i>
                        <input type="password" name="new_password" placeholder="Minimal 6 karakter" required class="form-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <div class="input-icon">
                        <i class="ri-lock-check-line"></i>
                        <input type="password" name="confirm_password" placeholder="Ulangi password baru" required class="form-input">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary full-width">Update Password</button>
            </form>
        </div>
    </div>

    <?php if($role === 'admin'): ?>
    <div id="tab-website" class="tab-content">
        <div class="card">
            <form method="POST">
                <input type="hidden" name="save_web_settings" value="1">
                
                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Nama Website</label>
                        <input type="text" name="site_name" value="<?= htmlspecialchars($web['site_name']) ?>" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label>Mode Maintenance</label>
                        <label class="toggle-switch">
                            <input type="checkbox" name="maintenance_mode" value="1" <?= $web['maintenance_mode'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                        <small style="margin-left:10px; color:#888;">Geser untuk mengunci website.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Deskripsi SEO</label>
                    <textarea name="site_desc" rows="2" class="form-input"><?= htmlspecialchars($web['site_desc']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Iklan Header (Atas Menu)</label>
                    <textarea name="ad_header" class="code-editor" placeholder="HTML/JS Code..."><?= htmlspecialchars($web['ad_header']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Iklan Player (Bawah Video)</label>
                    <textarea name="ad_player" class="code-editor" placeholder="HTML/JS Code..."><?= htmlspecialchars($web['ad_player']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Sistem</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
// JS TAB SWITCHER
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');
    event.currentTarget.classList.add('active');
}

// JS PREVIEW IMAGE
function previewFile() {
    const preview = document.getElementById('previewImg');
    const file = document.querySelector('input[type=file]').files[0];
    const reader = new FileReader();

    reader.addEventListener("load", function () {
        // Jika belum ada img tag (masih placeholder huruf), reload halaman atau manipulasi DOM
        // Simpelnya: user akan melihat preview setelah diupload, atau kita ganti src jika img ada
        if(preview) {
            preview.src = reader.result;
        } else {
            // Kalau awalnya huruf, kita biarkan saja (advanced logic butuh ganti div jadi img)
            alert("Gambar dipilih. Klik 'Simpan Profil' untuk melihat hasilnya.");
        }
    }, false);

    if (file) { reader.readAsDataURL(file); }
}
</script>

<style>
/* TABS NAVIGATION */
.tabs-container { display: flex; gap: 15px; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); }
.tab-btn { background: none; border: none; padding: 10px 15px; color: #888; font-weight: 600; cursor: pointer; border-bottom: 3px solid transparent; transition: 0.3s; display: flex; align-items: center; gap: 8px; font-size: 0.95rem; }
.tab-btn:hover { color: white; }
.tab-btn.active { color: #e50914; border-bottom-color: #e50914; }
.tab-content { display: none; animation: fadeEffect 0.4s; }
.tab-content.active { display: block; }
@keyframes fadeEffect { from {opacity: 0; transform: translateY(10px);} to {opacity: 1; transform: translateY(0);} }

/* PROFILE LAYOUT */
.profile-edit-grid { display: grid; grid-template-columns: 200px 1fr; gap: 40px; align-items: start; }
.avatar-section { text-align: center; }
.avatar-preview { 
    width: 120px; height: 120px; margin: 0 auto; position: relative; 
    border-radius: 50%; overflow: hidden; border: 3px solid #333;
}
.avatar-preview img { width: 100%; height: 100%; object-fit: cover; }
.avatar-placeholder { width: 100%; height: 100%; background: #e50914; color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 800; }
.btn-upload-icon {
    position: absolute; bottom: 0; left: 0; width: 100%; background: rgba(0,0,0,0.6); 
    color: white; padding: 5px; cursor: pointer; transition: 0.2s; opacity: 0;
}
.avatar-preview:hover .btn-upload-icon { opacity: 1; }

/* FORM GENERAL */
.form-input { width: 100%; background: #0f1014; border: 1px solid #333; padding: 12px; border-radius: 8px; color: white; outline: none; transition: 0.3s; }
.form-input:focus { border-color: #e50914; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; color: #ccc; margin-bottom: 8px; font-size: 0.9rem; font-weight: 500; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

/* SWITCH & CODE */
.toggle-switch { position: relative; display: inline-block; width: 50px; height: 26px; vertical-align: middle; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #333; transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #e50914; }
input:checked + .slider:before { transform: translateX(24px); }
.code-editor { font-family: monospace; background: #000; color: #4ade80; border: 1px solid #333; }

/* RESPONSIVE */
@media (max-width: 768px) {
    .profile-edit-grid { grid-template-columns: 1fr; gap: 20px; text-align: center; }
    .form-grid-2 { grid-template-columns: 1fr; }
    .avatar-preview { margin: 0 auto; }
}
</style>
