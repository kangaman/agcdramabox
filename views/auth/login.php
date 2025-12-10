<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= Config::SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #e50914;
            --bg: #0f1014;
            --card: #1b1e26;
            --text: #fff;
            --text-muted: #9ca3af;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }

        body {
            background: var(--bg);
            color: var(--text);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('/assets/images/bg-login.jpg'); /* Opsional: Ganti URL gambar background */
            background-size: cover;
            background-position: center;
        }

        .auth-card {
            background: rgba(27, 30, 38, 0.95);
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            text-align: center;
        }

        .brand {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 10px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        h2 { margin-bottom: 25px; font-size: 1.2rem; color: var(--text-muted); font-weight: 400; }

        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.9rem; color: var(--text-muted); }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background: #0a0b0d;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: white;
            outline: none;
            transition: 0.3s;
        }
        .form-group input:focus { border-color: var(--primary); }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            font-size: 1rem;
        }
        .btn-login:hover { background: #b2070f; }

        .auth-footer { margin-top: 25px; font-size: 0.9rem; color: var(--text-muted); }
        .auth-footer a { color: white; text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { color: var(--primary); }

        .alert-error {
            background: rgba(229, 9, 20, 0.1);
            border: 1px solid rgba(229, 9, 20, 0.3);
            color: #ff4757;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        /* TOMBOL KEMBALI (YANG ANDA MINTA) */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
            padding: 8px 15px;
            border-radius: 50px;
            border: 1px solid transparent;
        }
        .btn-back:hover {
            color: white;
            border-color: rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.05);
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <a href="/" class="brand">
            <i class="ri-movie-2-fill"></i> <?= Config::SITE_NAME ?>
        </a>
        <h2>Masuk ke Akun Anda</h2>

        <?php if(isset($error)): ?>
            <div class="alert-error">
                <i class="ri-error-warning-fill"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn-login">Login Sekarang</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="/register">Daftar disini</a>
        </div>

        <a href="/" class="btn-back">
            <i class="ri-arrow-left-line"></i> Kembali ke Beranda
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if(isset($_SESSION['swal'])): ?>
            Swal.fire({
                icon: '<?= $_SESSION['swal']['icon'] ?>',
                title: '<?= $_SESSION['swal']['title'] ?>',
                text: '<?= $_SESSION['swal']['text'] ?>',
                background: '#1b1e26',
                color: '#fff',
                confirmButtonColor: '#e50914'
            });
            <?php unset($_SESSION['swal']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
