<?php
$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];

// --- LOGIC: PROSES REDEEM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['voucher_code']));
    
    if (empty($code)) {
        setFlash('error', 'Gagal', 'Masukkan kode voucher.');
    } else {
        // 1. Cek Ketersediaan Voucher
        $stmt = $db->prepare("SELECT * FROM vouchers WHERE code = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$code]);
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($voucher) {
            try {
                $db->beginTransaction();
                
                // 2. Ambil Data User Saat Ini
                $user = $db->query("SELECT active_until FROM users WHERE id = $uid")->fetch(PDO::FETCH_ASSOC);
                $currentExpiry = $user['active_until'];
                
                // 3. Hitung Tanggal Baru
                // Jika user masih VIP, tambah hari dari tanggal expired terakhir
                // Jika user FREE atau sudah expired, tambah hari dari SEKARANG
                if ($currentExpiry && strtotime($currentExpiry) > time()) {
                    $newExpiry = date('Y-m-d H:i:s', strtotime($currentExpiry . " + {$voucher['days']} days"));
                } else {
                    $newExpiry = date('Y-m-d H:i:s', strtotime("+ {$voucher['days']} days"));
                }
                
                // 4. Update User ke VIP
                $updateUser = $db->prepare("UPDATE users SET role = 'vip', active_until = ? WHERE id = ?");
                $updateUser->execute([$newExpiry, $uid]);
                
                // 5. Matikan Voucher (Set Used)
                $updateVoucher = $db->prepare("UPDATE vouchers SET status = 'used', used_by = ?, used_at = NOW() WHERE id = ?");
                $updateVoucher->execute([$uid, $voucher['id']]);
                
                $db->commit();
                
                // Update Session biar langsung terasa efeknya
                $_SESSION['role'] = 'vip';
                $_SESSION['vip_until'] = $newExpiry;
                
                setFlash('success', 'Berhasil!', "Selamat! Akun Anda aktif selama {$voucher['days']} hari.");
                header("Location: /dashboard"); // Redirect ke home dashboard
                exit;
                
            } catch (Exception $e) {
                $db->rollBack();
                setFlash('error', 'Error', 'Terjadi kesalahan sistem.');
            }
        } else {
            setFlash('error', 'Gagal', 'Kode Voucher Salah atau Sudah Dipakai.');
        }
    }
}
?>

<div class="header-flex">
    <div>
        <h2 class="page-title">🎁 Tukar Voucher</h2>
        <p style="color:var(--text-muted)">Masukkan kode voucher untuk mengaktifkan VIP.</p>
    </div>
</div>

<div class="card" style="max-width: 500px; margin: 0 auto; text-align:center; padding: 40px 20px;">
    
    <i class="ri-ticket-2-line" style="font-size: 4rem; color: var(--primary); margin-bottom: 20px; display:inline-block;"></i>
    
    <h3 style="margin-bottom: 10px;">Punya Kode Redeem?</h3>
    <p style="color: #888; margin-bottom: 30px;">
        Masukkan kode unik yang Anda dapatkan dari Admin untuk menikmati akses VIP Premium tanpa batas.
    </p>

    <form method="POST">
        <div style="margin-bottom: 20px;">
            <input type="text" name="voucher_code" placeholder="Masukan Kode (Contoh: VIP30-XXXX)" 
                   style="width: 100%; padding: 15px; background: #000; border: 2px dashed #444; border-radius: 8px; color: white; text-align: center; font-size: 1.2rem; font-weight: bold; text-transform: uppercase; letter-spacing: 2px;" required>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block" style="padding: 12px; font-size: 1.1rem;">
            Tukarkan Sekarang <i class="ri-arrow-right-line"></i>
        </button>
    </form>
    
    <div style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
        <small style="color: #666;">Belum punya kode?</small><br>
        <a href="https://t.me/jejakintel" target="_blank" style="color: var(--primary); font-weight: bold; text-decoration: none;">
            Beli Voucher via WhatsApp/Telegram
        </a>
    </div>
</div>
