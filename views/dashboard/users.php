<?php
if($_SESSION['role'] !== 'admin') exit('Access Denied');
$db = (new Database())->getConnection();

// --- LOGIC HAPUS USER ---
if(isset($_POST['delete_id'])) {
    if($_POST['delete_id'] == $_SESSION['user_id']) {
        setFlash('error', 'Gagal', 'Tidak bisa menghapus akun sendiri');
    } else {
        $db->prepare("DELETE FROM users WHERE id=?")->execute([$_POST['delete_id']]);
        setFlash('success', 'Berhasil', 'Pengguna telah dihapus');
    }
    echo "<script>window.location='/dashboard/users';</script>";
    exit;
}

// --- LOGIC AKTIVASI CEPAT (+30 HARI) ---
if(isset($_POST['activate_id'])) {
    $date = date('Y-m-d H:i:s', strtotime("+30 days"));
    $db->prepare("UPDATE users SET role='vip', active_until=? WHERE id=?")->execute([$date, $_POST['activate_id']]);
    setFlash('success', 'Berhasil', 'User diaktifkan menjadi VIP 30 Hari');
    echo "<script>window.location='/dashboard/users';</script>";
    exit;
}

// AMBIL SEMUA DATA
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="header-flex">
    <div>
        <h2 class="page-title">👥 Manajemen Pengguna</h2>
        <p style="color:var(--text-muted)">Total: <strong><?= count($users) ?></strong> Pengguna Terdaftar</p>
    </div>
    <a href="/dashboard/user_form" class="btn btn-primary">
        <i class="ri-user-add-line"></i> Tambah User
    </a>
</div>

<div class="card">
    <table class="datatable display" style="width:100%">
        <thead>
            <tr>
                <th>Pengguna</th>
                <th>Role</th>
                <th>Status Langganan</th>
                <th>Bergabung</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($users as $u): ?>
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="avatar" style="width:35px; height:35px; font-size:0.9rem;">
                            <?= strtoupper(substr($u['username'], 0, 1)) ?>
                        </div>
                        <span style="font-weight:600; font-size:0.95rem;"><?= htmlspecialchars($u['username']) ?></span>
                    </div>
                </td>
                <td>
                    <?php 
                        // PERBAIKAN: Menggunakan if/else biasa agar support PHP lama
                        $roleColor = '#9ca3af'; // Default (Free)
                        if ($u['role'] === 'admin') {
                            $roleColor = '#a78bfa'; // Ungu
                        } elseif ($u['role'] === 'vip') {
                            $roleColor = '#ffd700'; // Emas
                        }
                    ?>
                    <span class="badge" style="color:<?= $roleColor ?>; border:1px solid <?= $roleColor ?>40; background:<?= $roleColor ?>10">
                        <?= strtoupper($u['role']) ?>
                    </span>
                </td>
                <td>
                    <?php if($u['active_until'] && strtotime($u['active_until']) > time()): ?>
                        <?php $days = floor((strtotime($u['active_until']) - time())/(60*60*24)); ?>
                        <span style="color:#4ade80; font-size:0.85rem; font-weight:500;">
                            ● Aktif (<?= $days ?> Hari)
                        </span>
                    <?php elseif($u['role'] == 'free'): ?>
                        <span style="color:#666; font-size:0.85rem;">-</span>
                    <?php else: ?>
                        <span style="color:#ef4444; font-size:0.85rem; font-weight:500;">
                            ● Expired
                        </span>
                    <?php endif; ?>
                </td>
                <td style="color:var(--text-muted); font-size:0.9rem;">
                    <?= date('d M Y', strtotime($u['created_at'])) ?>
                </td>
                <td>
                    <div style="display:flex; gap:8px;">
                        <a href="/dashboard/user_form&id=<?= $u['id'] ?>" class="btn btn-sm btn-secondary" title="Edit Detail">
                            <i class="ri-pencil-line"></i>
                        </a>
                        
                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="activate_id" value="<?= $u['id'] ?>">
                                <button class="btn btn-sm" style="background:var(--primary); color:white; border:none;" title="Set VIP 30 Hari">
                                    <i class="ri-vip-crown-fill"></i>
                                </button>
                            </form>

                            <form method="POST" onsubmit="return confirm('Yakin hapus user <?= htmlspecialchars($u['username']) ?>?');" style="margin:0;">
                                <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                                <button class="btn btn-sm btn-secondary" style="color:#ef4444; border-color:#ef4444;" title="Hapus User">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
