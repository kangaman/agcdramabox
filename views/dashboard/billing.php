<?php
$db = (new Database())->getConnection();

// --- 1. LOGIC: REDEEM VOUCHER (User Menukar Kode) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_code'])) {
    $code = strtoupper(trim($_POST['voucher_code']));
    $uid = $_SESSION['user_id'];

    // Cek Kode
    $stmt = $db->prepare("SELECT * FROM vouchers WHERE code = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$code]);
    $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($voucher) {
        // Hitung Tanggal Baru
        $addDays = "+ " . intval($voucher['days']) . " days";
        
        // Cek user sekarang VIP atau Free
        $user = $db->query("SELECT role, active_until FROM users WHERE id = $uid")->fetch(PDO::FETCH_ASSOC);
        
        if ($user['role'] === 'vip' && strtotime($user['active_until']) > time()) {
            // Jika sudah VIP, perpanjang dari tanggal expired
            $newDate = date('Y-m-d H:i:s', strtotime($addDays, strtotime($user['active_until'])));
        } else {
            // Jika Free/Expired, mulai dari sekarang
            $newDate = date('Y-m-d H:i:s', strtotime($addDays));
        }

        // Update User & Voucher
        $db->beginTransaction();
        try {
            $db->prepare("UPDATE users SET role='vip', active_until=? WHERE id=?")->execute([$newDate, $uid]);
            $db->prepare("UPDATE vouchers SET status='used', used_by=?, used_at=NOW() WHERE id=?")->execute([$uid, $voucher['id']]);
            $db->commit();
            
            // Update Session
            $_SESSION['role'] = 'vip';
            $_SESSION['vip_until'] = $newDate;

            setFlash('success', 'Berhasil!', "Selamat! Akun Anda kini VIP hingga " . date('d M Y', strtotime($newDate)));
        } catch (Exception $e) {
            $db->rollBack();
            setFlash('error', 'Gagal', 'Terjadi kesalahan sistem.');
        }
    } else {
        setFlash('error', 'Gagal', 'Kode Voucher tidak valid atau sudah dipakai.');
    }
    
    // Refresh halaman agar flash message muncul
    echo "<script>window.location='/dashboard/plans';</script>";
    exit;
}

// --- 2. AMBIL DAFTAR PAKET ---
$plans = $db->query("SELECT * FROM plans WHERE is_active=1 ORDER BY price ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">💎 Langganan Premium</h2>
        <p style="color:#888; margin:0; font-size:0.9rem;">Buka akses tanpa batas ke ribuan konten.</p>
    </div>
</div>

<div class="redeem-section">
    <div class="card redeem-card">
        <div class="redeem-icon">
            <i class="ri-ticket-2-fill"></i>
        </div>
        <div class="redeem-info">
            <h3>Punya Kode Voucher?</h3>
            <p>Masukkan kode promo atau voucher hadiah Anda di sini.</p>
        </div>
        <form method="POST" class="redeem-form">
            <input type="text" name="voucher_code" placeholder="Masukkan Kode (ex: VIP-XXXX)" required autocomplete="off">
            <button type="submit" name="redeem_code">Tukar</button>
        </form>
    </div>
</div>

<div class="plans-grid">
    <?php foreach($plans as $p): ?>
    <div class="plan-card">
        <?php if($p['duration'] == 30): ?>
            <div class="plan-badge">POPULER</div>
        <?php endif; ?>

        <div class="plan-header">
            <h3 class="plan-name"><?= htmlspecialchars($p['name']) ?></h3>
            <div class="plan-price">
                <span class="currency">Rp</span>
                <span class="amount"><?= number_format($p['price']) ?></span>
            </div>
            <div class="plan-duration">Masa aktif <?= $p['duration'] ?> Hari</div>
        </div>

        <div class="plan-body">
            <ul class="feature-list">
                <li><i class="ri-checkbox-circle-fill"></i> Akses Semua Drama</li>
                <li><i class="ri-checkbox-circle-fill"></i> Tanpa Iklan Mengganggu</li>
                <li><i class="ri-checkbox-circle-fill"></i> Kualitas HD 1080p</li>
                <?php 
                // Fitur Dinamis dari Database
                $feats = explode(',', $p['features']);
                foreach($feats as $f): 
                    if(trim($f) == '') continue;
                ?>
                    <li><i class="ri-checkbox-circle-fill"></i> <?= trim($f) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="plan-footer">
            <?php 
            $wa_msg = "Halo Admin, saya mau langganan *" . $p['name'] . "* harga Rp " . number_format($p['price']) . " (User: " . $_SESSION['username'] . "). Minta nomor rekening/QRIS ya.";
            ?>
            <button onclick="orderTelegram('<?= $wa_msg ?>')" class="btn-buy">
                <i class="ri-telegram-fill"></i> Beli Paket Ini
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
/* 1. REDEEM SECTION */
.redeem-section { margin-bottom: 40px; }
.redeem-card {
    display: flex; align-items: center; gap: 20px; padding: 25px;
    background: linear-gradient(135deg, #1e2129 0%, #16181c 100%);
    border: 1px solid rgba(255,255,255,0.08); flex-wrap: wrap;
}
.redeem-icon {
    width: 60px; height: 60px; background: rgba(229, 9, 20, 0.1); color: #e50914;
    border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;
}
.redeem-info { flex: 1; min-width: 200px; }
.redeem-info h3 { margin: 0 0 5px 0; font-size: 1.1rem; color: white; }
.redeem-info p { margin: 0; font-size: 0.9rem; color: #888; }

.redeem-form { display: flex; gap: 10px; width: 100%; max-width: 400px; }
.redeem-form input {
    flex: 1; background: #0f1014; border: 1px solid #333; color: white;
    padding: 12px 15px; border-radius: 8px; font-weight: bold; text-transform: uppercase;
}
.redeem-form input:focus { border-color: #e50914; }
.redeem-form button {
    background: #e50914; color: white; border: none; padding: 0 25px;
    border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.2s;
}
.redeem-form button:hover { background: #b2070f; }

/* 2. PLANS GRID */
.plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
}

/* 3. PLAN CARD DESIGN */
.plan-card {
    background: #1e2129;
    border-radius: 16px;
    padding: 30px;
    position: relative;
    border: 1px solid rgba(255,255,255,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex; flex-direction: column;
}
.plan-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    border-color: rgba(229,9,20,0.3);
}

/* Badge Populer */
.plan-badge {
    position: absolute; top: -12px; right: 20px;
    background: #e50914; color: white;
    padding: 5px 12px; border-radius: 20px;
    font-size: 0.75rem; font-weight: 800; letter-spacing: 1px;
    box-shadow: 0 5px 15px rgba(229,9,20,0.4);
}

.plan-header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 20px; }
.plan-name { font-size: 1.2rem; color: #aaa; margin: 0 0 15px 0; text-transform: uppercase; letter-spacing: 1px; }
.plan-price { display: flex; align-items: flex-start; justify-content: center; color: white; line-height: 1; }
.plan-price .currency { font-size: 1.2rem; margin-top: 5px; font-weight: 500; }
.plan-price .amount { font-size: 3.5rem; font-weight: 800; letter-spacing: -2px; }
.plan-duration { background: rgba(255,255,255,0.05); display: inline-block; padding: 6px 15px; border-radius: 20px; margin-top: 15px; font-size: 0.85rem; color: #ccc; }

.plan-body { flex: 1; margin-bottom: 30px; }
.feature-list { list-style: none; padding: 0; margin: 0; }
.feature-list li {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 15px; color: #ddd; font-size: 0.95rem;
}
.feature-list li i { color: #4ade80; font-size: 1.2rem; }

.btn-buy {
    width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px;
    padding: 14px; border-radius: 10px; border: none;
    background: #0088cc; /* Warna Telegram */
    color: white; font-weight: 700; font-size: 1rem;
    cursor: pointer; transition: 0.2s;
}
.btn-buy:hover { background: #0077b5; box-shadow: 0 5px 20px rgba(0, 136, 204, 0.3); }

/* Responsive Mobile */
@media (max-width: 600px) {
    .redeem-form { flex-direction: column; max-width: 100%; }
    .plans-grid { grid-template-columns: 1fr; }
}
</style>

<script>
function orderTelegram(message) {
    // Copy ke clipboard
    navigator.clipboard.writeText(message).then(function() {
        // Tampilkan notifikasi
        let t = document.createElement("div");
        t.innerText = "✅ Pesan Disalin! Membuka Telegram...";
        t.style.cssText = "position:fixed; bottom:20px; left:50%; transform:translateX(-50%); background:#22c55e; color:#fff; padding:10px 20px; border-radius:20px; z-index:9999; font-weight:bold; box-shadow:0 5px 20px rgba(0,0,0,0.3);";
        document.body.appendChild(t);
        
        setTimeout(() => {
            t.remove();
            // Buka Telegram
            window.open('http://t.me/jejakintel', '_blank');
        }, 1500);
    });
}
</script>
