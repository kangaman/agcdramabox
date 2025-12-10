<?php
require_once 'Config.php';

class ApiHandler {
    // Gunakan cache path yang dinamis
    private $cachePath;

    public function __construct() {
        $this->cachePath = __DIR__ . '/../cache/';
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    private function request($endpoint, $params = []) {
        // 1. Masukkan API Key ke parameter URL (Sama seperti config.php asli)
        $params['api_key'] = Config::API_KEY;
        
        $url = Config::API_URL . $endpoint . '?' . http_build_query($params);
        $cacheFile = $this->cachePath . md5($url) . '.json';

        // 2. Cek Cache (Agar hemat kuota API)
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 3600)) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        // 3. Request ke API (DISINKRONKAN DENGAN config.php ASLI)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // PENTING: Gunakan FALSE jika di localhost/hosting murah (Sama seperti asli)
        // Ubah jadi TRUE jika sudah live production dengan SSL valid
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Naikkan timeout jadi 30 detik

        // PENTING: Tambahkan Header agar tidak dianggap BOT (Sama seperti asli)
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . Config::API_KEY,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch); // Cek error CURL
        curl_close($ch);

        // Debugging Darurat (Jika masih kosong, uncomment baris bawah ini lihat errornya)
        // if($error) die("CURL Error: $error");

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            
            // Simpan cache hanya jika status TRUE
            if (isset($data['status']) && $data['status'] === true) {
                file_put_contents($cacheFile, $response);
            }
            return $data;
        }

        // Return error graceful
        return ['status' => false, 'error' => 'API Error or Timeout'];
    }

    // --- ENDPOINT FUNCTIONS ---

    public function getHome($page = 1) {
        // PERBAIKAN PENTING: Ganti 'id' menjadi 'in' sesuai file asli
        return $this->request('index.php', ['page' => $page, 'lang' => 'in']);
    }

    public function getDetail($bookId) {
        // PERBAIKAN PENTING: Ganti 'id' menjadi 'in'
        return $this->request('drama.php', ['bookId' => $bookId, 'language' => 'in']);
    }

    public function search($query, $page = 1) {
        // PERBAIKAN PENTING: Ganti 'id' menjadi 'in'
        // Parameter di file asli adalah 'p', bukan 'page' untuk search
        return $this->request('cari.php', ['q' => $query, 'lang' => 'in', 'p' => $page]);
    }
}