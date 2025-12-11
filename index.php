<?php
// 1. SECURITY: SEMBUNYIKAN ERROR PHP (Mode Produksi)
error_reporting(0);
ini_set('display_errors', 0);

// 2. BUFFERING OUTPUT
ob_start();

// 3. SECURITY HEADERS & CACHE CONTROL
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// 4. LOAD CONFIG (Untuk Cek Maintenance)
require_once 'app/Config.php';

// --- FITUR MAINTENANCE MODE ---
// Pastikan 'const MAINTENANCE_MODE = true;' ada di app/Config.php
if (defined('Config::MAINTENANCE_MODE') && Config::MAINTENANCE_MODE) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    // Bypass: Admin boleh masuk
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(503);
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Maintenance - <?= Config::SITE_NAME ?></title>
            <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;800&display=swap" rel="stylesheet">
            <style>
                :root { --primary: #e50914; --bg: #0f1014; --card: #1b1e26; --text: #fff; }
                body {
                    margin: 0; padding: 0; background: var(--bg); color: var(--text);
                    font-family: 'Outfit', sans-serif; min-height: 100vh;
                    display: flex; flex-direction: column; align-items: center; justify-content: center;
                    overflow-x: hidden;
                }
                
                /* Layout */
                .split-screen {
                    display: flex; flex-wrap: wrap; width: 100%; max-width: 1200px;
                    gap: 40px; padding: 20px; align-items: center; justify-content: center;
                }
                .info-side { flex: 1; min-width: 300px; text-align: left; }
                .game-side { flex: 1; min-width: 300px; display: flex; justify-content: center; }

                /* Typography */
                h1 { font-size: 3rem; font-weight: 800; margin: 0 0 15px 0; line-height: 1.1; }
                h1 span { color: var(--primary); }
                p { color: #9ca3af; line-height: 1.6; margin-bottom: 25px; }

                /* Countdown */
                .countdown { display: flex; gap: 15px; margin-bottom: 30px; }
                .time-box { background: var(--card); padding: 10px 20px; border-radius: 8px; text-align: center; border: 1px solid rgba(255,255,255,0.1); }
                .time-box span { font-size: 1.5rem; font-weight: bold; color: var(--primary); display: block; }
                .time-box small { font-size: 0.7rem; color: #666; text-transform: uppercase; }

                /* Notify Form */
                .notify-box { display: flex; gap: 10px; margin-bottom: 30px; }
                .notify-box input {
                    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
                    padding: 12px 15px; border-radius: 8px; color: white; flex: 1; outline: none;
                }
                .notify-box button {
                    background: var(--primary); color: white; border: none; padding: 0 25px;
                    border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.2s;
                }
                .notify-box button:hover { opacity: 0.9; }

                /* GAME AREA */
                .game-container {
                    background: #000; border: 4px solid #333; border-radius: 12px;
                    position: relative; box-shadow: 0 0 30px rgba(229, 9, 20, 0.2);
                }
                canvas { display: block; background: #111; }
                .game-overlay {
                    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0,0,0,0.8); display: flex; flex-direction: column;
                    align-items: center; justify-content: center; text-align: center;
                }
                .start-btn {
                    background: var(--primary); color: white; padding: 10px 30px;
                    border: none; border-radius: 50px; font-size: 1.2rem; font-weight: bold;
                    cursor: pointer; margin-top: 15px; animation: pulse 1.5s infinite;
                }
                .score-board {
                    position: absolute; top: 10px; right: 15px; color: #fff; 
                    font-family: monospace; font-size: 1.2rem; font-weight: bold;
                }
                .mobile-controls { display: none; gap: 10px; margin-top: 15px; }
                .ctrl-btn { width: 50px; height: 50px; background: #333; border-radius: 50%; border: none; color: white; font-size: 1.2rem; }

                @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }

                @media (max-width: 768px) {
                    .split-screen { flex-direction: column-reverse; text-align: center; }
                    .info-side { text-align: center; }
                    .countdown { justify-content: center; }
                    .notify-box { max-width: 400px; margin: 0 auto 30px; }
                    .mobile-controls { display: flex; justify-content: center; }
                }
            </style>
        </head>
        <body>
            <div class="split-screen">
                <div class="info-side">
                    <h1>Kami Sedang <br><span>Upgrade Sistem</span></h1>
                    <p>
                        Server sedang menjalani perawatan rutin untuk meningkatkan performa dan fitur baru. 
                        Jangan khawatir, kami akan segera kembali!
                        <br>Sambil menunggu, yuk main game sebentar di sebelah! ðŸ‘‰
                    </p>

                    <div class="countdown">
                        <div class="time-box"><span id="h">01</span><small>Jam</small></div>
                        <div class="time-box"><span id="m">45</span><small>Menit</small></div>
                        <div class="time-box"><span id="s">30</span><small>Detik</small></div>
                    </div>

                    <div class="notify-box">
                        <input type="email" placeholder="Masukkan email Anda...">
                        <button onclick="alert('Terima kasih! Kami akan kabari saat website online.')">Kabari Saya</button>
                    </div>

                    <small style="color:#555;">&copy; <?= date('Y') ?> <?= Config::SITE_NAME ?> Engineering Team</small>
                </div>

                <div class="game-side">
                    <div>
                        <div class="game-container">
                            <div class="score-board">Score: <span id="score">0</span></div>
                            <canvas id="gameCanvas" width="300" height="300"></canvas>
                            
                            <div id="gameOverlay" class="game-overlay">
                                <h2 style="margin:0;">SNAKE GAME</h2>
                                <p style="margin:5px 0; font-size:0.9rem;">Gunakan Panah Keyboard</p>
                                <button class="start-btn" onclick="startGame()">MAIN SEKARANG</button>
                            </div>
                        </div>
                        
                        <div class="mobile-controls">
                            <button class="ctrl-btn" onclick="changeDir('LEFT')"><i class="ri-arrow-left-line"></i></button>
                            <button class="ctrl-btn" onclick="changeDir('UP')"><i class="ri-arrow-up-line"></i></button>
                            <button class="ctrl-btn" onclick="changeDir('DOWN')"><i class="ri-arrow-down-line"></i></button>
                            <button class="ctrl-btn" onclick="changeDir('RIGHT')"><i class="ri-arrow-right-line"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // 1. COUNTDOWN TIMER LOGIC
                // Set waktu selesai maintenance (Contoh: 2 jam dari sekarang)
                // Dalam real case, Anda bisa set timestamp spesifik
                let endTime = new Date().getTime() + (24 * 60 * 60 * 1000); 

                setInterval(function() {
                    let now = new Date().getTime();
                    let dist = endTime - now;

                    if (dist < 0) {
                        // Reset waktu jika habis (Simulation)
                        endTime = new Date().getTime() + (24 * 60 * 60 * 1000);
                        dist = endTime - now;
                    }

                    let h = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    let m = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
                    let s = Math.floor((dist % (1000 * 60)) / 1000);

                    document.getElementById("h").innerText = h < 10 ? "0" + h : h;
                    document.getElementById("m").innerText = m < 10 ? "0" + m : m;
                    document.getElementById("s").innerText = s < 10 ? "0" + s : s;
                }, 1000);

                // 2. SNAKE GAME LOGIC
                const canvas = document.getElementById('gameCanvas');
                const ctx = canvas.getContext('2d');
                const overlay = document.getElementById('gameOverlay');
                const scoreEl = document.getElementById('score');
                
                const box = 15; // Ukuran kotak
                let snake = [];
                let food = {};
                let score = 0;
                let d;
                let game;

                function initGame() {
                    snake = [];
                    snake[0] = { x: 10 * box, y: 10 * box };
                    score = 0;
                    d = "";
                    scoreEl.innerText = score;
                    createFood();
                }

                function createFood() {
                    food = {
                        x: Math.floor(Math.random() * (canvas.width/box)) * box,
                        y: Math.floor(Math.random() * (canvas.height/box)) * box
                    };
                }

                document.addEventListener('keydown', direction);

                function direction(event) {
                    let key = event.keyCode;
                    if(key == 37 && d != "RIGHT") d = "LEFT";
                    else if(key == 38 && d != "DOWN") d = "UP";
                    else if(key == 39 && d != "LEFT") d = "RIGHT";
                    else if(key == 40 && d != "UP") d = "DOWN";
                }
                
                // Fungsi untuk tombol HP
                function changeDir(dir) {
                    if(dir == "LEFT" && d != "RIGHT") d = "LEFT";
                    else if(dir == "UP" && d != "DOWN") d = "UP";
                    else if(dir == "RIGHT" && d != "LEFT") d = "RIGHT";
                    else if(dir == "DOWN" && d != "UP") d = "DOWN";
                }

                function draw() {
                    ctx.fillStyle = "#111"; // Background board
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    for(let i = 0; i < snake.length; i++) {
                        ctx.fillStyle = (i == 0) ? "#e50914" : "#fff"; // Kepala Merah, Badan Putih
                        ctx.fillRect(snake[i].x, snake[i].y, box, box);
                        ctx.strokeStyle = "#000";
                        ctx.strokeRect(snake[i].x, snake[i].y, box, box);
                    }

                    ctx.fillStyle = "#4ade80"; // Makanan Hijau
                    ctx.fillRect(food.x, food.y, box, box);

                    let snakeX = snake[0].x;
                    let snakeY = snake[0].y;

                    if(d == "LEFT") snakeX -= box;
                    if(d == "UP") snakeY -= box;
                    if(d == "RIGHT") snakeX += box;
                    if(d == "DOWN") snakeY += box;

                    // Makan
                    if(snakeX == food.x && snakeY == food.y) {
                        score++;
                        scoreEl.innerText = score;
                        createFood();
                    } else {
                        snake.pop();
                    }

                    // Game Over Logic
                    let newHead = { x: snakeX, y: snakeY };

                    if(snakeX < 0 || snakeX >= canvas.width || snakeY < 0 || snakeY >= canvas.height || collision(newHead, snake)) {
                        clearInterval(game);
                        overlay.style.display = "flex";
                        document.querySelector('#gameOverlay h2').innerText = "GAME OVER";
                        document.querySelector('.start-btn').innerText = "MAIN LAGI";
                        return;
                    }

                    snake.unshift(newHead);
                }

                function collision(head, array) {
                    for(let i = 0; i < array.length; i++) {
                        if(head.x == array[i].x && head.y == array[i].y) return true;
                    }
                    return false;
                }

                function startGame() {
                    overlay.style.display = "none";
                    initGame();
                    // Set default gerak ke kanan biar gak diem
                    d = "RIGHT"; 
                    if(game) clearInterval(game);
                    game = setInterval(draw, 190); // Kecepatan game (makin kecil makin cepat)
                }
            </script>
        </body>
        </html>
        <?php
        exit; // Hentikan script
    }
}




// 5. SESSION SECURITY & RATE LIMITING
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); 

if (session_status() === PHP_SESSION_NONE) session_start();

// Regenerasi ID Sesi
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

// Anti Brute Force
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
    $lockout_time = $_SESSION['lockout_time'] ?? 0;
    if (time() - $lockout_time < 900) { 
        die("<div style='text-align:center;padding:50px;font-family:sans-serif;'>
                <h1>â›” Akses Dibatasi</h1>
                <p>Terlalu banyak percobaan login gagal.</p>
                <p>Silakan coba lagi dalam 15 menit.</p>
             </div>");
    } else {
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_time']);
    }
}

// 6. LOAD CORE FILES
require_once 'app/ApiHandler.php';
require_once 'app/Auth.php';

$api = new ApiHandler();
$auth = new Auth();
$page = $_GET['page'] ?? 'home';

// --- GLOBAL SOURCE HANDLER ---
// Menangkap pilihan server user (Default: dramabox)
$source = isset($_GET['source']) ? $_GET['source'] : 'dramabox';

// Helper Notifikasi
function setFlash($icon, $title, $text) {
    $_SESSION['swal'] = ['icon' => $icon, 'title' => $title, 'text' => $text];
}

// =========================================================
// AREA LOGIC (AUTH)
// =========================================================

// Login Handler
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($auth->login($_POST['username'], $_POST['password'])) {
        unset($_SESSION['login_attempts']); 
        setFlash('success', 'Login Berhasil', 'Selamat datang kembali!');
        header("Location: /dashboard");
        exit;
    } else {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        if ($_SESSION['login_attempts'] >= 5) $_SESSION['lockout_time'] = time();
        $error = "Username atau Password Salah!";
    }
}

// Register Handler
if ($page === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($auth->register($_POST['username'], $_POST['password'])) {
        setFlash('success', 'Pendaftaran Berhasil', 'Silakan login.');
        header("Location: /login");
        exit;
    } else {
        $error = "Username sudah digunakan!";
    }
}

// Logout Handler
if ($page === 'logout') {
    $auth->logout();
    setFlash('success', 'Logout Berhasil', 'Sampai jumpa lagi!');
    header("Location: /login");
    exit;
}

// =========================================================
// AREA ROUTING
// =========================================================

// A. ROUTE DASHBOARD (Proteksi Login)
if (strpos($page, 'dashboard') === 0) {
    if (!isset($_SESSION['user_id'])) {
        setFlash('warning', 'Akses Ditolak', 'Silakan login terlebih dahulu.');
        header("Location: /login");
        exit;
    }
    
    $parts = explode('/', $page);
    $view = $parts[1] ?? 'overview';
    
    if (strpos($view, 'user_form') !== false) $view = 'user_form';
    
    include 'views/dashboard/layout.php';
    exit; 
}

// B. ROUTE PUBLIC PAGES

// Load Header (Kecuali login/register)
if ($page !== 'login' && $page !== 'register') {
    include 'views/header.php';
}

switch($page) {
    case 'home':
        $p = $_GET['p'] ?? 1;
        // Pass parameter $source ke fungsi getHome
        $data = $api->getHome($p, $source);
        include 'views/public/home.php';
        break;
        
    case 'search':
        $q = $_GET['q'] ?? '';
        $p = $_GET['p'] ?? 1; 
        // Pass parameter $source ke fungsi search
        $data = $api->search($q, $p, $source);
        include 'views/public/home.php';
        break;

    case 'watch':
        $id = $_GET['id'] ?? null;
        if($id) {
            // [UPDATE] Ambil detail berdasarkan source
            $data = $api->getDetail($id, $source);
            
            // [FIX VALIDASI] ApiHandler sekarang menormalisasi data Melolo 
            // menjadi struktur yang sama dengan Dramabox: ['data']['dramaInfo']
            $isValid = false;
            
            // Cek apakah dramaInfo ada (Berlaku untuk Dramabox & Melolo yang sudah dinormalisasi)
            if (isset($data['data']['dramaInfo']) || isset($data['dramaInfo'])) {
                $isValid = true;
            }
            // Fallback: Cek data raw jika normalisasi gagal
            else if (!empty($data) && (isset($data['book_id']) || isset($data['title']) || isset($data['data']['book_name']))) {
                $isValid = true;
            }

            if($isValid) {
                include 'views/public/watch.php';
            } else {
                // Debugging: Jika masih error, uncomment baris bawah untuk lihat isi data asli
                // echo "<pre style='color:white'>"; print_r($data); echo "</pre>";
                
                echo "<div class='container' style='padding:100px; text-align:center; color:white;'>
                        <h3 style='font-size: 2rem; margin-bottom: 20px;'>Drama tidak ditemukan :(</h3>
                        <p style='color:#ccc;'>Mungkin ID salah atau Server <b>" . ucfirst($source) . "</b> sedang sibuk.</p>
                        <br>
                        <a href='/?source=$source' style='color:#e50914; text-decoration:none; border:1px solid #e50914; padding:10px 20px; border-radius:5px;'>Kembali ke Beranda</a>
                      </div>";
            }
        } else { 
            header("Location: /"); 
        }
        break;

    // --- HALAMAN STATIS ---
    case 'terms':
        $pageTitle = "Syarat & Ketentuan";
        include 'views/public/terms.php';
        break;

    case 'privacy':
        $pageTitle = "Kebijakan Privasi";
        include 'views/public/privacy.php';
        break;

    // --- AUTH ---
    case 'login':
        include 'views/auth/login.php'; 
        break;

    case 'register':
        include 'views/auth/register.php';
        break;

    // --- API BACKEND (AJAX) ---
    
    // 1. Save History
    case 'api_save_history':
        ob_end_clean(); 
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => false, 'msg' => 'Unauthorized']); exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && !empty($input['id'])) {
            try {
                $db = (new Database())->getConnection();
                $sql = "INSERT INTO history (user_id, book_id, title, cover, episode, total_eps) 
                        VALUES (?, ?, ?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE episode = VALUES(episode), updated_at = NOW()";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $_SESSION['user_id'], $input['id'], $input['title'], 
                    $input['cover'], $input['episode'], $input['total']
                ]);
                echo json_encode(['status' => true]);
            } catch (Exception $e) {
                echo json_encode(['status' => false, 'msg' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => false, 'msg' => 'Invalid Data']);
        }
        exit; 
        break;
        
    // 2. [WAJIB ADA] API Proxy untuk Streaming Melolo
    case 'api_get_stream':
        ob_end_clean();
        header('Content-Type: application/json');
        
        $vidId = $_GET['id'] ?? null;
        $src   = $_GET['source'] ?? '';
        
        if ($src === 'melolo' && $vidId) {
            $streamData = $api->getMeloloStream($vidId);
            echo json_encode($streamData); 
        } else {
            echo json_encode(['status' => false, 'msg' => 'Invalid Request']);
        }
        exit;
        break;

    default:
        http_response_code(404);
        include 'views/404.php';
        break;
}

// Load Footer
if ($page !== 'login' && $page !== 'register') {
    include 'views/footer.php';
}

// =========================================================
// PROSES ENKRIPSI HTML (VIEW SOURCE PROTECTION)
// =========================================================

$html_content = ob_get_clean(); 

// Pengecualian Enkripsi
$is_exempt = in_array($page, ['login', 'register', 'api_save_history', 'api_get_stream']) || strpos($page, 'dashboard') === 0;

if ($is_exempt) {
    echo $html_content;
} else {
    $encrypted = '';
    $len = strlen($html_content);
    for ($i = 0; $i < $len; $i++) {
        $encrypted .= '%' . bin2hex($html_content[$i]);
    }
    echo '<script type="text/javascript">document.write(unescape("' . $encrypted . '"));</script>';
}
?>
