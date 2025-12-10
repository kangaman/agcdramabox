<?php
if($_SESSION['role'] !== 'admin') exit('Access Denied');
$db = (new Database())->getConnection();

// CEK MODE: EDIT (Ada ID) atau TAMBAH (Tidak ada ID)
$id = $_GET['id'] ?? null;
$userData = null;
$title = '➕ Tambah User Baru';

if ($id) {
    $title = '✏️ Edit User';
    $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$userData) {
        setFlash('error', 'Error', 'User tidak ditemukan');
        header("Location: /dashboard/users"); exit;
    }
}

// LOGIC SIMPAN DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $expiry = !empty($_POST['active_until']) ? $_POST['active_until'] : NULL;
    $password = $_POST['password'];

    try {
        if ($id) {
            // --- UPDATE USER LAMA ---
            $sql = "UPDATE users SET username=?, role=?, active_until=? WHERE id=?";
            $params = [$username, $role, $expiry, $id];
            
            // Update password HANYA JIKA diisi
            if (!empty($password)) {
                $sql = "UPDATE users SET username=?, role=?, active_until=?, password=? WHERE id=?";
                $params = [$username, $role, $expiry, password_hash($password, PASSWORD_BCRYPT), $id];
            }
            $db->prepare($sql)->execute($params);
            setFlash('success', 'Berhasil', 'Data pengguna diperbarui.');
            
        } else {
            // --- TAMBAH USER BARU ---
            // Cek duplikat username
            $cek = $db->prepare("SELECT COUNT(*) FROM users WHERE username=?");
            $cek->execute([$username]);
            if($cek->fetchColumn() > 0) {
                setFlash('error', 'Gagal', 'Username sudah digunakan!');
                header("Location: /dashboard/user_form"); exit;
            }

            if (empty($password)) $password = '123456'; // Password default
            $sql = "INSERT INTO users (username, password, role, active_until) VALUES (?, ?, ?, ?)";
            $db->prepare($sql)->execute([$username, password_hash($password, PASSWORD_BCRYPT), $role, $expiry]);
            setFlash('success', 'Berhasil', 'User baru ditambahkan.');
        }
        
        header("Location: /dashboard/users");
        exit;

    } catch (Exception $e) {
        setFlash('error', 'Error Database', $e->getMessage());
    }
}
?>

<div class="header-flex">
    <h2 class="page-title"><?= $title ?></h2>
    <a href="/dashboard/users" class="btn btn-secondary">
        <i class="ri-arrow-left-line"></i> Kembali
    </a>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <form method="POST">
        <div style="margin-bottom: 20px;">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($userData['username'] ?? '') ?>" required placeholder="Masukkan username unik">
        </div>

        <div style="margin-bottom: 20px;">
            <label>Password <?= $id ? '<span style="color:#666; font-weight:normal;">(Kosongkan jika tidak ingin mengubah)</span>' : '<span style="color:var(--primary); font-weight:normal;">(Wajib diisi, default: 123456)</span>' ?></label>
            <input type="password" name="password" placeholder="***">
        </div>

        <div style="margin-bottom: 20px;">
            <label>Role / Jabatan</label>
            <select name="role">
                <option value="free" <?= ($userData['role']??'')=='free'?'selected':'' ?>>Free User (Gratis)</option>
                <option value="vip" <?= ($userData['role']??'')=='vip'?'selected':'' ?>>VIP Member (Berbayar)</option>
                <option value="admin" <?= ($userData['role']??'')=='admin'?'selected':'' ?>>Administrator</option>
            </select>
        </div>

        <div style="margin-bottom: 30px;">
            <label>Masa Aktif Sampai (Khusus VIP)</label>
            <input type="datetime-local" name="active_until" value="<?= $userData['active_until'] ?? '' ?>">
            <small style="color:#666; margin-top:5px; display:block;">
                <i class="ri-information-line"></i> Biarkan kosong jika Free User atau Admin permanen.
            </small>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            <i class="ri-save-3-line"></i> Simpan Data
        </button>
    </form>
</div>