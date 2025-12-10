<?php
if($_SESSION['role'] !== 'admin') exit('Access Denied');
$db = (new Database())->getConnection();
$id = $_GET['id'] ?? null;

if (!$id) { echo "User ID tidak ditemukan"; exit; }

// --- LOGIC: UPDATE USER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $role = $_POST['role'];
    $expiry = $_POST['active_until'] ?: NULL;
    
    // Update Data Dasar
    $sql = "UPDATE users SET username=?, role=?, active_until=? WHERE id=?";
    $params = [$username, $role, $expiry, $id];

    // Jika Password Diisi, Update Password
    if (!empty($_POST['password'])) {
        $sql = "UPDATE users SET username=?, role=?, active_until=?, password=? WHERE id=?";
        $params = [$username, $role, $expiry, password_hash($_POST['password'], PASSWORD_BCRYPT), $id];
    }

    $db->prepare($sql)->execute($params);
    echo "<script>alert('Data berhasil disimpan!'); window.location='/dashboard/users';</script>";
}

// Ambil Data User Saat Ini
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
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($u['username']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Password Baru (Kosongkan jika tidak ingin ubah)</label>
                <input type="password" name="password" placeholder="***">
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="free" <?= $u['role']=='free'?'selected':'' ?>>Free Member</option>
                    <option value="vip" <?= $u['role']=='vip'?'selected':'' ?>>VIP Member</option>
                    <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Administrator</option>
                </select>
            </div>

            <div class="form-group">
                <label>Masa Aktif Sampai</label>
                <input type="datetime-local" name="active_until" value="<?= $u['active_until'] ?>">
                <small style="color:#888">Format: Bulan/Hari/Tahun Jam:Menit</small>
            </div>

            <div class="form-full">
                <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>