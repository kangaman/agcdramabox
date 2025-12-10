<?php
$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];

// --- LOGIKA SIMPAN DATA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    try {
        // Cek apakah user ingin ganti password
        if(!empty($new_pass)) {
            // 1. Validasi Panjang
            if(strlen($new_pass) < 6) {
                setFlash('error', 'Gagal', 'Password minimal 6 karakter.');
            } 
            // 2. Validasi Kecocokan
            elseif($new_pass !== $confirm_pass) {
                setFlash('error', 'Gagal', 'Konfirmasi password tidak cocok.');
            } 
            // 3. Eksekusi Update
            else {
                $hash = password_hash($new_pass, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hash, $uid]);
                setFlash('success', 'Berhasil', 'Password Anda telah diperbarui.');
            }
        } else {
            setFlash('info', 'Info', 'Tidak ada perubahan yang disimpan.');
        }
    } catch (Exception $e) {
        setFlash('error', 'Error', 'Terjadi kesalahan sistem.');
    }
    
    // Refresh halaman agar form bersih
    header("Location: /dashboard/settings");
    exit;
}

// AMBIL DATA TERBARU USER
$u = $db->query("SELECT * FROM users WHERE id = $uid")->fetch(PDO::FETCH_ASSOC);

// HITUNG SISA HARI (Untuk Info Card)
$daysLeft = 0;
$statusText = 'Free User';
$statusColor = '#666';

if($u['role'] == 'vip' && $u['active_until']) {
    $diff = strtotime($u['active_until']) - time();
    $daysLeft = floor($diff / (60 * 60 * 24));
    if($daysLeft > 0) {
        $statusText = "VIP ($daysLeft Hari)";
        $statusColor = '#ffd700'; // Emas
    } else {
        $statusText = "VIP (Expired)";
        $statusColor = '#ff4757'; // Merah
    }
} elseif($u['role'] == 'admin') {
    $statusText = "Administrator";
    $statusColor = '#a78bfa'; // Ungu
}
?>

<div class="header-flex">
    <div>
        <h2 class="page-title">⚙️ Pengaturan Akun</h2>
        <p style="color:var(--text-muted)">Kelola keamanan dan informasi akun Anda.</p>
    </div>
</div>

<div class="grid-2" style="align-items: start;">
    
    <div class="card">
        <h3 style="margin-bottom:20px; border-bottom:1px solid var(--border); padding-bottom:10px;">
            <i class="ri-lock-password-line"></i> Keamanan Login
        </h3>

        <form method="POST">
            <div style="margin-bottom: 20px;">
                <label>Username (Permanen)</label>
                <div style="position:relative;">
                    <input type="text" value="<?= htmlspecialchars($u['username']) ?>" disabled 
                           style="background: #1a1a1a !important; color: #888 !important; border-color: #333 !important; cursor: not-allowed; padding-left: 40px;">
                    <i class="ri-shield-user-line" style="position:absolute; left:12px; top:12px; color:#666;"></i>
                    <i class="ri-lock-fill" style="position:absolute; right:12px; top:12px; color:#666;"></i>
                </div>
                <small style="color:#555; margin-top:5px; display:block;">Username tidak dapat diubah demi keamanan sistem.</small>
            </div>

            <div style="margin-bottom: 20px;">
                <label>Password Baru</label>
                <div class="password-wrapper" style="position:relative;">
                    <input type="password" name="new_password" id="pass1" placeholder="Minimal 6 karakter" style="padding-right: 40px;">
                    <i class="ri-eye-off-line toggle-pass" onclick="togglePass('pass1', this)" 
                       style="position:absolute; right:12px; top:12px; cursor:pointer; color:#888;"></i>
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <label>Ulangi Password Baru</label>
                <div class="password-wrapper" style="position:relative;">
                    <input type="password" name="confirm_password" id="pass2" placeholder="Ketik ulang password baru" style="padding-right: 40px;">
                    <i class="ri-eye-off-line toggle-pass" onclick="togglePass('pass2', this)" 
                       style="position:absolute; right:12px; top:12px; cursor:pointer; color:#888;"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="ri-save-3-line"></i> Simpan Password Baru
            </button>
        </form>
    </div>

    <div class="card" style="text-align:center;">
        <div style="width:100px; height:100px; background:var(--primary); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:3rem; font-weight:bold; margin:0 auto 20px; border:4px solid var(--bg-dark);">
            <?= strtoupper(substr($u['username'], 0, 1)) ?>
        </div>
        
        <h3 style="margin-bottom:5px;"><?= htmlspecialchars($u['username']) ?></h3>
        
        <span class="badge" style="background:<?= $statusColor ?>20; color:<?= $statusColor ?>; border:1px solid <?= $statusColor ?>40; padding:5px 12px; border-radius:20px; font-size:0.8rem;">
            <?= $statusText ?>
        </span>

        <hr style="border:0; border-top:1px solid var(--border); margin:25px 0;">

        <div style="text-align:left;">
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span style="color:#888;">Bergabung</span>
                <span><?= date('d M Y', strtotime($u['created_at'])) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span style="color:#888;">Terakhir Login</span>
                <span>Sekarang</span> </div>
        </div>

        <a href="https://t.me/jejakintel" target="_blank" class="btn btn-secondary btn-block" style="margin-top:20px;">
            <i class="ri-customer-service-2-line"></i> Hubungi Admin
        </a>
    </div>
</div>

<script>
// Fitur: Lihat/Sembunyikan Password
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