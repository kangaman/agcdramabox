<?php
require_once 'Database.php';

class Auth {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :u");
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['vip_until'] = $user['active_until'];
            
            // Cek Expired
            if ($user['role'] === 'vip' && strtotime($user['active_until']) < time()) {
                $_SESSION['role'] = 'free'; // Downgrade jika expired
            }
            return true;
        }
        return false;
    }

    public function register($username, $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, password) VALUES (:u, :p)");
        try {
            return $stmt->execute(['u' => $username, 'p' => $hash]);
        } catch (Exception $e) {
            return false; // Username duplikat
        }
    }

    public function isVip() {
        if (!isset($_SESSION['user_id'])) return false;
        if ($_SESSION['role'] === 'admin') return true;
        
        // Cek status real-time atau session (disini session biar cepat)
        if ($_SESSION['role'] === 'vip' && isset($_SESSION['vip_until'])) {
            return strtotime($_SESSION['vip_until']) > time();
        }
        return false;
    }

    public function logout() {
        session_destroy();
        header("Location: /");
    }
}