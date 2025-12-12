<?php
// File: views/dashboard/search_analytics.php
if($_SESSION['role'] !== 'admin') exit('Access Denied');
$db = (new Database())->getConnection();

// 1. TOP 10 KATA KUNCI (Paling sering dicari)
$topKeywords = $db->query("
    SELECT keyword, COUNT(*) as total, MAX(created_at) as last_search 
    FROM search_logs 
    GROUP BY keyword 
    ORDER BY total DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// 2. RIWAYAT TERBARU (Real-time)
$recentLogs = $db->query("
    SELECT * FROM search_logs ORDER BY created_at DESC LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="header-flex">
    <div>
        <h2 class="page-title">📈 Analitik Pencarian</h2>
        <p style="color:var(--text-muted)">Pantau apa yang sedang tren di kalangan pengguna.</p>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <h3 style="margin-bottom:20px; border-bottom:1px solid var(--border); padding-bottom:10px;">
            <i class="ri-fire-fill" style="color:#e50914"></i> Paling Banyak Dicari
        </h3>
        <table class="table-modern" style="width:100%">
            <thead>
                <tr>
                    <th>Kata Kunci</th>
                    <th style="text-align:center">Total</th>
                    <th style="text-align:right">Terakhir</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($topKeywords as $k): ?>
                <tr>
                    <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($k['keyword']) ?></td>
                    <td style="text-align:center"><span class="badge" style="background:rgba(229,9,20,0.1); color:#e50914"><?= $k['total'] ?>x</span></td>
                    <td style="text-align:right; font-size:0.85rem; color:#888;"><?= date('d M H:i', strtotime($k['last_search'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-bottom:20px; border-bottom:1px solid var(--border); padding-bottom:10px;">
            <i class="ri-history-line"></i> Riwayat Terbaru
        </h3>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <?php foreach($recentLogs as $log): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.05);">
                <div>
                    <div style="color:white; font-weight:500;">
                        <?= htmlspecialchars($log['keyword']) ?>
                        <span style="font-size:0.7rem; padding:2px 6px; border-radius:4px; background:#333; color:#ccc; margin-left:5px;"><?= strtoupper($log['source']) ?></span>
                    </div>
                    <small style="color:#666;">IP: <?= $log['ip_address'] ?></small>
                </div>
                <small style="color:#888;"><?= date('H:i', strtotime($log['created_at'])) ?></small>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
