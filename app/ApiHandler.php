<?php
require_once 'Config.php';

class ApiHandler {
    private $cachePath;

    public function __construct() {
        $this->cachePath = __DIR__ . '/../cache/';
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    private function makeRequest($url, $headers = []) {
        $cacheFile = $this->cachePath . md5($url) . '.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 3600)) {
            $cachedData = json_decode(file_get_contents($cacheFile), true);
            if ($cachedData) return $cachedData;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $defaultHeaders = ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if ($data) {
                file_put_contents($cacheFile, $response);
                return $data;
            }
        }
        return ['status' => false, 'error' => "HTTP $httpCode"];
    }

    private function requestDramabox($endpoint, $params = []) {
        $params['api_key'] = Config::API_KEY;
        $url = Config::API_URL . $endpoint . '?' . http_build_query($params);
        $headers = ['X-API-Key: ' . Config::API_KEY];
        return $this->makeRequest($url, $headers);
    }
    private function requestMelolo($endpoint) {
        $url = Config::MELOLO_API_URL . $endpoint;
        return $this->makeRequest($url);
    }

    private function fixImage($url) {
        if (!$url) return 'https://via.placeholder.com/300x450?text=No+Cover';
        if (strpos($url, '.heic') !== false) {
            $cleanUrl = str_replace(['http://', 'https://'], '', $url);
            return 'https://wsrv.nl/?url=' . urlencode($cleanUrl) . '&output=jpg&q=80';
        }
        return str_replace('http://', 'https://', $url);
    }

    // --- PUBLIC METHODS ---

    // 1. GET HOME (Updated)
    public function getHome($page = 1, $source = 'dramabox') {
        if ($source === 'melolo') {
            $limit = 20; 
            $endpoint = 'latest';
            if ($page > 1) { $offset = ($page - 1) * $limit; $endpoint .= "?limit={$limit}&offset={$offset}"; }
            $rawData = $this->requestMelolo($endpoint);
            
            $items = [];
            if (isset($rawData['books'])) $items = $rawData['books'];
            elseif (isset($rawData['results'])) $items = $rawData['results'];
            elseif (isset($rawData['data']) && is_array($rawData['data'])) $items = $rawData['data'];
            elseif (is_array($rawData) && isset($rawData[0])) $items = $rawData;
            
            $mappedData = [];
            foreach ($items as $item) {
                $id = $item['book_id'] ?? $item['id'] ?? '';
                if (!$id) continue; 

                $rawCover = $item['thumb_url'] ?? $item['book_cover'] ?? $item['cover'] ?? $item['thumb'] ?? '';
                $title = $item['book_name'] ?? $item['title'] ?? $item['name'] ?? 'Tanpa Judul';
                
                // [BARU] Ambil Abstract/Sinopsis
                $desc = $item['abstract'] ?? $item['desc'] ?? $item['description'] ?? 'Sinopsis tidak tersedia.';

                $mappedData[] = [
                    'id' => $id,
                    'title' => $title,
                    'thumbnail' => $this->fixImage($rawCover), 
                    'desc' => $desc, // Simpan di sini
                    'episode' => 'Baru',
                    'source' => 'melolo'
                ];
            }
            return ['status' => true, 'data' => $mappedData];
        } else {
            return $this->requestDramabox('index.php', ['page' => $page, 'lang' => 'in']);
        }
    }

    // 2. SEARCH (Updated)
    public function search($query, $page = 1, $source = 'dramabox') {
        if ($source === 'melolo') {
            $limit = 20; $offset = ($page - 1) * $limit;
            $endpoint = 'search?query=' . rawurlencode($query) . "&limit={$limit}&offset={$offset}";
            $rawData = $this->requestMelolo($endpoint);
            
            $items = [];
            if (isset($rawData['data']['search_data'])) $items = $rawData['data']['search_data'];
            elseif (isset($rawData['books'])) $items = $rawData['books'];
            else $items = $this->findEpisodeListRecursive($rawData) ?? [];

            $mappedData = [];
            foreach ($items as $wrapper) {
                $item = (isset($wrapper['books'][0])) ? $wrapper['books'][0] : $wrapper;
                $id = $item['book_id'] ?? $item['id'] ?? '';
                if (!$id) continue;

                $rawCover = $item['thumb_url'] ?? $item['cover'] ?? $item['thumb'] ?? '';
                $title = $item['book_name'] ?? $item['title'] ?? $item['name'] ?? 'Tanpa Judul';
                
                // [BARU] Ambil Abstract/Sinopsis
                $desc = $item['abstract'] ?? $item['desc'] ?? $item['description'] ?? 'Sinopsis tidak tersedia.';

                $mappedData[] = [
                    'id' => $id,
                    'title' => $title,
                    'thumbnail' => $this->fixImage($rawCover),
                    'desc' => $desc, // Simpan di sini
                    'episode' => 'Cek Detail',
                    'source' => 'melolo'
                ];
            }
            return ['status' => true, 'data' => $mappedData];
        } else {
            return $this->requestDramabox('cari.php', ['q' => $query, 'lang' => 'in', 'p' => $page]);
        }
    }

    // 3. GET DETAIL
    public function getDetail($id, $source = 'dramabox') {
        if ($source === 'melolo') {
            $rawData = $this->requestMelolo('detail/' . $id);
            $foundChapters = $this->findEpisodeListRecursive($rawData);
            $rootInfo = $rawData['data']['video_data'] ?? $rawData['data'] ?? [];
            
            $rawCover = $rootInfo['thumb_url'] ?? $rootInfo['cover'] ?? $rootInfo['thumb'] ?? '';
            $synopsis = $rootInfo['abstract'] ?? $rootInfo['description'] ?? $rootInfo['intro'] ?? '';

            $finalData = [
                'source' => 'melolo',
                'dramaInfo' => [
                    'bookName' => $rootInfo['book_name'] ?? $rootInfo['title'] ?? 'Nonton Drama',
                    'cover' => $this->fixImage($rawCover),
                    'introduction' => $synopsis,
                    'score' => $rootInfo['score'] ?? '5.0',
                    'followCount' => $rootInfo['read_count'] ?? 0,
                    'tags' => $rootInfo['stat_infos'] ?? []
                ],
                'chapters' => []
            ];

            if ($foundChapters) {
                foreach ($foundChapters as $idx => $chap) {
                    $vidId = $chap['vid'] ?? $chap['video_id'] ?? $chap['chapter_id'] ?? $chap['id'] ?? '';
                    if ($vidId) {
                        $finalData['chapters'][] = [
                            'vid' => $vidId, 
                            'title' => $chap['title'] ?? ('Episode ' . ($idx + 1))
                        ];
                    }
                }
            }
            return ['status' => true, 'data' => $finalData];
        } else {
            return $this->requestDramabox('drama.php', ['bookId' => $id, 'language' => 'in']);
        }
    }

    private function findEpisodeListRecursive($data) {
        if (!is_array($data)) return null;
        if (isset($data[0]) && is_array($data[0])) {
            $first = $data[0];
            if (isset($first['vid']) || isset($first['video_id']) || isset($first['chapter_id'])) return $data;
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result = $this->findEpisodeListRecursive($value);
                if ($result) return $result;
            }
        }
        return null;
    }

    public function getMeloloStream($vidId) {
        return $this->requestMelolo('stream/' . $vidId);
    }
}
?>
