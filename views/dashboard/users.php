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

// AMBIL DATA
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="header-flex">
    <div>
        <h2 class="page-title">👥 Manajemen Pengguna</h2>
        <p style="color:var(--text-muted)">Total: <strong><?= count($users) ?></strong> Pengguna Terdaftar</p>
    </div>
    <a href="/dashboard/user_form" class="btn btn-primary">
        <i class="ri-user-add-line"></i> <span>Tambah User</span>
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="datatable display" style="width:100%">
            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>Role (Hak Akses)</th>
                    <th>Status VIP</th>
                    <th>Login Terakhir</th>
                    <th>Aksi Kontrol</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($users as $u): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div class="avatar-circle">
                                <?= strtoupper(substr($u['username'], 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight:600; color:white;"><?= htmlspecialchars($u['username']) ?></div>
                                <div style="font-size:0.75rem; color:#666;">ID: #<?= $u['id'] ?></div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <?php if($u['role'] === 'admin'): ?>
                            <span class="badge-role role-admin">
                                <i class="ri-shield-check-fill"></i> Admin
                            </span>
                        <?php elseif($u['role'] === 'vip'): ?>
                            <span class="badge-role role-vip">
                                <i class="ri-vip-crown-2-fill"></i> VIP Member
                            </span>
                        <?php else: ?>
                            <span class="badge-role role-free">
                                <i class="ri-user-3-line"></i> Gratisan
                            </span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if($u['active_until'] && strtotime($u['active_until']) > time()): ?>
                            <?php $days = ceil((strtotime($u['active_until']) - time())/(60*60*24)); ?>
                            <div style="display:flex; align-items:center; gap:5px; color:#4ade80;">
                                <i class="ri-checkbox-circle-fill"></i>
                                <div>
                                    <div style="font-size:0.85rem; font-weight:bold;">Aktif</div>
                                    <div style="font-size:0.7rem; opacity:0.8;">Sisa <?= $days ?> Hari</div>
                                </div>
                            </div>
                        <?php elseif($u['role'] == 'free'): ?>
                            <span style="color:#666; font-size:0.85rem;">-</span>
                        <?php else: ?>
                            <div style="display:flex; align-items:center; gap:5px; color:#ef4444;">
                                <i class="ri-close-circle-fill"></i>
                                <span style="font-size:0.85rem; font-weight:bold;">Expired</span>
                            </div>
                        <?php endif; ?>
                    </td>
                    
                    <td>
                        <?php if(!empty($u['last_login'])): ?>
                            <div style="font-size:0.85rem; color:#ddd; font-weight:500;">
                                <i class="ri-calendar-check-line" style="color:#666; margin-right:3px;"></i>
                                <?= date('d M H:i', strtotime($u['last_login'])) ?>
                            </div>
                            <div style="font-size:0.7rem; color:#666; font-family:monospace; margin-top:2px;">
                                IP: <?= htmlspecialchars($u['last_ip'] ?? '-') ?>
                            </div>
                        <?php else: ?>
                            <span style="color:var(--text-muted); font-size:0.8rem; font-style:italic;">Belum login</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div style="display:flex; gap:8px;">
                            <a href="/dashboard/user_form&id=<?= $u['id'] ?>" class="btn-icon-text btn-gray" title="Edit Detail">
                                <i class="ri-pencil-fill"></i> <span>Edit</span>
                            </a>
                            
                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="activate_id" value="<?= $u['id'] ?>">
                                    <button class="btn-icon-text btn-gold" title="Tambah VIP 30 Hari">
                                        <i class="ri-vip-diamond-fill"></i> <span>VIP+</span>
                                    </button>
                                </form>

                                <form method="POST" onsubmit="return confirm('Yakin hapus user <?= htmlspecialchars($u['username']) ?>?');" style="margin:0;">
                                    <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                                    <button class="btn-icon-text btn-red" title="Hapus User">
                                        <i class="ri-delete-bin-2-fill"></i>
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
</div>
