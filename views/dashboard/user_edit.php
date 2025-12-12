<?php
// File: views/dashboard/user_edit.php
if($_SESSION['role'] !== 'admin') exit('Access Denied');
$db = (new Database())->getConnection();
$id = $_GET['id'] ?? null;

if (!$id) { echo "User ID tidak ditemukan"; exit; }

// --- LOGIC: UPDATE USER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $role = $_POST['role'];
    $is_banned = isset($_POST['is_banned']) ? 1 : 0; // Tangkap checkbox banned
    
    // Logika Tambah Hari VIP (Manual Balance)
    $current_expiry = $_POST['active_until_old'] ?: date('Y-m-d H:i:s');
    $add_days = intval($_POST['add_vip_days']);
    
    if ($add_days > 0) {
        // Jika admin isi form tambah hari, hitung tanggal baru
        $expiry = date('Y-m-d H:i:s', strtotime($current_expiry . " + $add_days days"));
    } else {
        // Jika tidak, pakai input tanggal manual
        $expiry = $_POST['active_until'] ?: NULL;
    }

    // Query Update (termasuk is_banned)
    $sql = "UPDATE users SET username=?, role=?, active_until=?, is_banned=? WHERE id=?";
    $params = [$username, $role, $expiry, $is_banned, $id];

    if (!empty($_POST['password'])) {
        $sql = "UPDATE users SET username=?, role=?, active_until=?, is_banned=?, password=? WHERE id=?";
        $params = [$username, $role, $expiry, $is_banned, password_hash($_POST['password'], PASSWORD_BCRYPT), $id];
    }

    $db->prepare($sql)->execute($params);
    echo "<script>alert('Data berhasil disimpan!'); window.location='/dashboard/users';</script>";
}

// Ambil Data User
$user = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$id]);
$u = $user->fetch(PDO::FETCH_ASSOC);
?>

<div class="edit-user-container">
    <div class="card">
        <div class="header-flex">
            <h2>✏️ Edit User: <?= htmlspecialchars($u['username']) ?></h2>
            <a href="/dashboard/users" class="btn btn-secondary">Kembali</a>
        </div>
        
        <form method="POST" class="form-grid">
            <input type="hidden" name="active_until_old" value="<?= $u['active_until'] ?>">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($u['username']) ?>" required>
            </div>

            <div class="form-group" style="background: rgba(220, 38, 38, 0.1); padding: 10px; border-radius: 8px; border: 1px solid rgba(220, 38, 38, 0.3);">
                <label style="color: #ff4757; font-weight: bold;">Status Akun</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_banned" id="bannedCheck" value="1" <?= $u['is_banned'] ? 'checked' : '' ?> style="width: 20px; height: 20px;">
                    <label for="bannedCheck" style="margin:0; cursor: pointer;">Banned / Blokir User Ini</label>
                </div>
                <small style="color: #888;">User yang diblokir tidak akan bisa login.</small>
            </div>
            
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="free" <?= $u['role']=='free'?'selected':'' ?>>Free Member</option>
                    <option value="vip" <?= $u['role']=='vip'?'selected':'' ?>>VIP Member</option>
                    <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Administrator</option>
                </select>
            </div>

            <div class="form-group" style="background: rgba(229, 9, 20, 0.05); padding: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                <label style="color: var(--primary);">👑 Manajemen Masa Aktif VIP</label>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;">
                    <div>
                        <label>Tambah Durasi (Hari)</label>
                        <input type="number" name="add_vip_days" placeholder="Contoh: 30">
                        <small style="color:#888">Isi angka (misal: 30) untuk menambah 30 hari dari sekarang/masa aktif terakhir.</small>
                    </div>
                    <div>
                        <label>Atau Set Tanggal Manual</label>
                        <input type="datetime-local" name="active_until" value="<?= $u['active_until'] ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Reset Password (Opsional)</label>
                <input type="password" name="password" placeholder="***">
            </div>

            <div class="form-full">
                <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
