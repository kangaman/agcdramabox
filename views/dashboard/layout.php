<?php if(!isset($_SESSION['user_id'])) { header("Location: /login"); exit; } ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= defined('Config::SITE_NAME') ? Config::SITE_NAME : 'DramaFlix' ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="/assets/dashboard.css?v=<?= time() ?>">
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <i class="ri-movie-2-fill" style="margin-right:10px;"></i> 
            <?= defined('Config::SITE_NAME') ? Config::SITE_NAME : 'Dashboard' ?>
        </div>
        
        <nav class="menu">
            <div class="menu-label">MENU UTAMA</div>
            <a href="/dashboard" class="<?= (!isset($view) || $view=='overview') ? 'active' : '' ?>">
                <i class="ri-dashboard-line"></i> <span>Ringkasan</span>
            </a>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="menu-label">ADMINISTRATOR</div>
            <a href="/dashboard/search_analytics" class="<?= (isset($view) && $view=='search_analytics') ? 'active' : '' ?>">
                <i class="ri-bar-chart-grouped-line"></i> <span>Analitik Pencarian</span>
            </a>
            <a href="/dashboard/users" class="<?= (isset($view) && $view=='users') ? 'active' : '' ?>">
                <i class="ri-group-line"></i> <span>Kelola User</span>
            </a>

            <a href="/dashboard/vouchers" class="<?= (isset($view) && $view=='vouchers') ? 'active' : '' ?>">
                <i class="ri-coupon-3-line"></i> <span>Kelola Voucher</span>
            </a>

            <a href="/dashboard/reports" class="<?= (isset($view) && $view=='reports') ? 'active' : '' ?>">
                <i class="ri-alarm-warning-line"></i> <span>Laporan Video</span>
                <?php 
                // Badge notifikasi jumlah laporan pending
                // Menggunakan try-catch agar tidak error jika tabel 'reports' belum ada
                try {
                    if(isset($db)) {
                        $pCount = $db->query("SELECT COUNT(*) FROM reports WHERE status='pending'")->fetchColumn();
                        if($pCount > 0) echo "<span style='background:#ff4757; color:white; font-size:0.7rem; padding:2px 6px; border-radius:10px; margin-left:auto;'>$pCount</span>";
                    }
                } catch (Exception $e) {}
                ?>
            </a>

            <a href="/dashboard/backup" class="<?= (isset($view) && $view=='backup') ? 'active' : '' ?>">
                <i class="ri-database-2-line"></i> <span>Backup & Restore</span>
            </a>
            
            <a href="/dashboard/plans" class="<?= (isset($view) && $view=='plans') ? 'active' : '' ?>">
                <i class="ri-price-tag-3-line"></i> <span>Kelola Paket</span>
            </a>
            <?php endif; ?>

            <div class="menu-label">AKUN SAYA</div>
            
            <a href="/dashboard/history" class="<?= (isset($view) && $view=='history') ? 'active' : '' ?>">
                <i class="ri-history-line"></i> <span>Riwayat Tontonan</span>
            </a>
            
            <a href="/dashboard/favorites" class="<?= (isset($view) && $view=='favorites') ? 'active' : '' ?>">
                <i class="ri-heart-line"></i> <span>Daftar Saya</span>
            </a>
            
            <a href="/dashboard/billing" class="<?= (isset($view) && $view=='billing') ? 'active' : '' ?>">
                <i class="ri-vip-crown-line"></i> <span>Langganan</span>
            </a>

            <a href="/dashboard/redeem" class="<?= (isset($view) && $view=='redeem') ? 'active' : '' ?>">
                <i class="ri-ticket-2-line"></i> <span>Tukar Voucher</span>
            </a>

            <a href="/dashboard/settings" class="<?= (isset($view) && $view=='settings') ? 'active' : '' ?>">
                <i class="ri-settings-3-line"></i> <span>Pengaturan</span>
            </a>
        </nav>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button class="toggle-btn" onclick="toggleSidebar()"><i class="ri-menu-line"></i></button>
                <div class="search-global">
                    <i class="ri-search-line"></i>
                    <input type="text" placeholder="Cari menu...">
                </div>
            </div>
            <div class="topbar-right">
                <a href="/" target="_blank" class="btn btn-sm btn-secondary" title="Lihat Website">
                    <i class="ri-external-link-line"></i> <span style="display:none;">Web</span>
                </a>
                <div class="user-profile">
                    <div style="text-align:right; font-size:0.9rem;">
                        <div style="font-weight:bold;"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
                        <div style="font-size:0.75rem; color:var(--text-muted);"><?= strtoupper($_SESSION['role'] ?? 'GUEST') ?></div>
                    </div>
                    <div class="avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?></div>
                    <a href="/logout" title="Logout" style="color:#ff4757; margin-left:10px;"><i class="ri-logout-circle-r-line"></i></a>
                </div>
            </div>
        </header>

        <main class="content">
            <?php 
            // Validasi variabel $view dari index.php
            $currentView = $view ?? 'overview';
            
            // Cek ketersediaan file view
            $file = __DIR__ . '/' . $currentView . '.php';
            
            if (file_exists($file)) {
                include $file;
            } else {
                echo "
                <div class='card' style='text-align:center; padding:40px;'>
                    <i class='ri-file-search-line' style='font-size:3rem; color:#666; margin-bottom:20px; display:block;'></i>
                    <h3>Halaman Tidak Ditemukan</h3>
                    <p style='color:#888;'>File view '<b>$currentView.php</b>' belum ada di folder views/dashboard.</p>
                </div>";
            }
            ?>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Toggle Sidebar Mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // Inisialisasi DataTables jika tabel ada
        $(document).ready(function() {
            if ($('.datatable').length > 0) {
                $('.datatable').DataTable({
                    responsive: true,
                    language: { search: "", searchPlaceholder: "Cari data...", lengthMenu: "_MENU_ baris" }
                });
            }
        });

        // TOAST NOTIFICATION (Pengganti Alert)
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
            timerProgressBar: true, background: '#1b1e26', color: '#fff'
        });

        <?php if(isset($_SESSION['swal'])): ?>
            Toast.fire({
                icon: '<?= $_SESSION['swal']['icon'] ?>',
                title: '<?= $_SESSION['swal']['title'] ?>'
            });
            <?php unset($_SESSION['swal']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
