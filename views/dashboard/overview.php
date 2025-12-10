<?php
$db = (new Database())->getConnection();

// --- LOGIKA KHUSUS ADMIN ---
if ($_SESSION['role'] === 'admin') {
    // 1. STATISTIK UTAMA
    $totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalVip = $db->query("SELECT COUNT(*) FROM users WHERE role='vip'")->fetchColumn();
    $activeVip = $db->query("SELECT COUNT(*) FROM users WHERE role='vip' AND active_until > NOW()")->fetchColumn();
    $totalHistory = $db->query("SELECT COUNT(*) FROM history")->fetchColumn();

    // 2. CHART PERTUMBUHAN USER (7 HARI TERAKHIR)
    $chartData = [];
    $chartLabels = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $count = $db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = '$date'")->fetchColumn();
        $chartLabels[] = date('d M', strtotime($date));
        $chartData[] = $count;
    }

    // 3. TOP 5 DRAMA TERPOPULER
    $topDramas = $db->query("
        SELECT title, cover, COUNT(user_id) as viewers 
        FROM history 
        GROUP BY book_id 
        ORDER BY viewers DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 4. AKTIVITAS TERBARU (OPTIMALISASI QUERY: AMBIL ROLE JUGA)
    $recentActivity = $db->query("
        SELECT h.*, u.username, u.role 
        FROM history h 
        LEFT JOIN users u ON h.user_id = u.id 
        ORDER BY h.updated_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
} 

// --- LOGIKA USER BIASA ---
else {
    $isVip = $_SESSION['role'] === 'vip';
    $daysLeft = 0;
    $percent = 0;

    if ($isVip && isset($_SESSION['vip_until'])) {
        $diff = strtotime($_SESSION['vip_until']) - time();
        $daysLeft = max(0, floor($diff / (60 * 60 * 24)));
        $percent = min(100, ($daysLeft / 30) * 100);
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="header-flex">
    <div>
        <h2 class="page-title">📊 Dashboard <?= ucfirst($_SESSION['role']) ?></h2>
        <p style="color:var(--text-muted)">Ringkasan performa website hari ini.</p>
    </div>
    
    <?php if($_SESSION['role'] !== 'admin'): ?>
    <a href="/dashboard/billing" class="btn btn-primary">
        <i class="ri-vip-crown-2-line"></i> Upgrade VIP
    </a>
    <?php endif; ?>
</div>

<?php if($_SESSION['role'] === 'admin'): ?>

    <div class="plans-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 25px;">
        <div class="card" style="display:flex; align-items:center; gap:15px; padding:20px;">
            <div style="background:rgba(59, 130, 246, 0.1); width:50px; height:50px; display:flex; align-items:center; justify-content:center; border-radius:12px; color:#3b82f6;">
                <i class="ri-group-fill" style="font-size:1.5rem;"></i>
            </div>
            <div>
                <h3 style="font-size:1.5rem; margin:0; line-height:1;"><?= $totalUsers ?></h3>
                <small style="color:var(--text-muted); font-size:0.8rem;">Total Pengguna</small>
            </div>
        </div>

        <div class="card" style="display:flex; align-items:center; gap:15px; padding:20px;">
            <div style="background:rgba(255,215,0,0.1); width:50px; height:50px; display:flex; align-items:center; justify-content:center; border-radius:12px; color:#ffd700;">
                <i class="ri-vip-crown-fill" style="font-size:1.5rem;"></i>
            </div>
            <div>
                <h3 style="font-size:1.5rem; margin:0; line-height:1;"><?= $activeVip ?></h3>
                <small style="color:var(--text-muted); font-size:0.8rem;">VIP Aktif</small>
            </div>
        </div>

        <div class="card" style="display:flex; align-items:center; gap:15px; padding:20px;">
            <div style="background:rgba(229, 9, 20, 0.1); width:50px; height:50px; display:flex; align-items:center; justify-content:center; border-radius:12px; color:#e50914;">
                <i class="ri-play-circle-fill" style="font-size:1.5rem;"></i>
            </div>
            <div>
                <h3 style="font-size:1.5rem; margin:0; line-height:1;"><?= $totalHistory ?></h3>
                <small style="color:var(--text-muted); font-size:0.8rem;">Total Ditonton</small>
            </div>
        </div>
        
        <div class="card" style="display:flex; align-items:center; gap:15px; padding:20px;">
            <div style="background:rgba(34, 197, 94, 0.1); width:50px; height:50px; display:flex; align-items:center; justify-content:center; border-radius:12px; color:#22c55e;">
                <i class="ri-money-dollar-circle-line" style="font-size:1.5rem;"></i>
            </div>
            <div>
                <h3 style="font-size:1.5rem; margin:0; line-height:1;">Rp <?= number_format($totalVip * 30000/1000) ?>k</h3>
                <small style="color:var(--text-muted); font-size:0.8rem;">Estimasi Omset</small>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 style="font-size:1.1rem; margin:0;"><i class="ri-line-chart-line"></i> Pertumbuhan User</h3>
                <small style="color:var(--text-muted)">7 Hari Terakhir</small>
            </div>
            <canvas id="userChart" height="150"></canvas>
        </div>

        <div class="card">
            <h3 style="margin-bottom:20px; font-size:1.1rem;"><i class="ri-fire-fill" style="color:#e50914"></i> Paling Populer</h3>
            <div style="display:flex; flex-direction:column; gap:15px;">
                <?php foreach($topDramas as $idx => $drama): ?>
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.05);">
                    <span style="font-size:1.2rem; font-weight:800; color:<?= $idx<3?'var(--primary)':'#444' ?>; width:20px;">#<?= $idx+1 ?></span>
                    <img src="<?= htmlspecialchars($drama['cover']) ?>" style="width:40px; height:55px; object-fit:cover; border-radius:4px; background:#111;">
                    <div style="flex:1; overflow:hidden;">
                        <div style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-weight:600; font-size:0.9rem; color:white;">
                            <?= htmlspecialchars($drama['title']) ?>
                        </div>
                        <small style="color:var(--text-muted); font-size:0.8rem;"><?= number_format($drama['viewers']) ?> x Ditonton</small>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($topDramas)) echo "<small style='color:#666'>Belum ada data.</small>"; ?>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0;"><i class="ri-history-line"></i> Aktivitas Member Terbaru</h3>
            <a href="/dashboard/history" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        
        <div class="table-responsive">
            <table class="table-modern" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:25%">User</th>
                        <th>Sedang Menonton</th>
                        <th style="width:20%; text-align:right;">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recentActivity as $act): ?>
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:35px; height:35px; background:rgba(255,255,255,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:0.9rem; color:var(--primary);">
                                    <?= strtoupper(substr($act['username'],0,1)) ?>
                                </div>
                                <div style="display:flex; flex-direction:column;">
                                    <span style="font-weight:600; font-size:0.95rem; color:white;"><?= htmlspecialchars($act['username']) ?></span>
                                    <span style="font-size:0.75rem; color:var(--text-muted);"><?= ucfirst($act['role']) ?></span>
                                </div>
                            </div>
                        </td>
                        
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <img src="<?= htmlspecialchars($act['cover']) ?>" style="width:35px; height:50px; object-fit:cover; border-radius:4px; background:#111;">
                                <div style="display:flex; flex-direction:column;">
                                    <span style="font-weight:500; font-size:0.9rem; color:#ddd; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:250px;">
                                        <?= htmlspecialchars($act['title']) ?>
                                    </span>
                                    <span style="font-size:0.75rem; color:var(--primary); font-weight:bold;">
                                        <i class="ri-play-circle-line"></i> Episode <?= $act['episode'] ?>
                                    </span>
                                </div>
                            </div>
                        </td>
                        
                        <td style="text-align:right; color:var(--text-muted); font-size:0.85rem;">
                            <?= time_elapsed_string($act['updated_at']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($recentActivity)): ?>
                        <tr><td colspan="3" style="text-align:center; padding:30px; color:#666;">Belum ada aktivitas tontonan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Chart Config
        const ctx = document.getElementById('userChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(229, 9, 20, 0.5)');
        gradient.addColorStop(1, 'rgba(229, 9, 20, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'User Baru',
                    data: <?= json_encode($chartData) ?>,
                    borderColor: '#e50914',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#e50914',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#333' }, ticks: { color: '#888' } },
                    x: { grid: { display: false }, ticks: { color: '#888' } }
                }
            }
        });
    </script>

<?php else: ?>
    <div class="grid-2">
        <div class="card" style="background: linear-gradient(135deg, var(--bg-card) 0%, #000 100%); border:1px solid var(--border);">
            <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
                <h3>Status Paket</h3>
                <span class="badge" style="background:<?= $isVip?'#ffd700':'#333' ?>; color:<?= $isVip?'#000':'#fff' ?>">
                    <?= $isVip ? 'PREMIUM VIP' : 'FREE GUEST' ?>
                </span>
            </div>
            <?php if($isVip): ?>
                <div style="text-align:center; margin:30px 0;">
                    <div style="font-size:3rem; font-weight:bold; color:var(--primary);">
                        <?= $daysLeft ?> <span style="font-size:1rem; color:#888;">Hari Lagi</span>
                    </div>
                    <p style="color:#666; font-size:0.9rem;">Berakhir pada: <?= date('d M Y', strtotime($_SESSION['vip_until'])) ?></p>
                </div>
                <div style="background:#333; height:8px; border-radius:10px; overflow:hidden; margin-top:20px;">
                    <div style="width:<?= $percent ?>%; background:var(--primary); height:100%; border-radius:10px;"></div>
                </div>
            <?php else: ?>
                <div style="text-align:center; padding: 20px 0;">
                    <i class="ri-lock-2-line" style="font-size:3rem; color:#666;"></i>
                    <p style="margin:10px 0; color:#ccc;">Akun Anda terbatas. Upgrade untuk akses penuh.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h3><i class="ri-history-line"></i> Lanjut Nonton</h3>
            <?php 
                $lastWatch = $db->query("SELECT * FROM history WHERE user_id=".$_SESSION['user_id']." ORDER BY updated_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div style="margin-top:15px; display:flex; flex-direction:column; gap:10px;">
                <?php foreach($lastWatch as $w): ?>
                <a href="/nonton/<?= $w['book_id'] ?>" style="display:flex; gap:10px; text-decoration:none; color:white; padding:10px; border-radius:8px; background:rgba(255,255,255,0.05); transition:0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">
                    <img src="<?= htmlspecialchars($w['cover']) ?>" style="width:40px; height:50px; object-fit:cover; border-radius:4px;">
                    <div style="display:flex; flex-direction:column; justify-content:center;">
                        <div style="font-weight:bold; font-size:0.9rem; margin-bottom:3px;"><?= htmlspecialchars($w['title']) ?></div>
                        <small style="color:var(--primary); font-size:0.8rem;">Episode <?= $w['episode'] ?></small>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php if(empty($lastWatch)) echo "<small style='color:#666'>Belum ada riwayat.</small>"; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
// FUNGSI WAKTU
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'tahun', 'm' => 'bulan', 'w' => 'minggu',
        'd' => 'hari', 'h' => 'jam', 'i' => 'menit', 's' => 'detik',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v;
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
}
?>