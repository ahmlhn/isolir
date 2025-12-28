<?php
// FILE: isolir/log.php
// VERSI: WHITELIST LOCATION (Custom IP Map + API Fallback)

require 'config.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if (!$conn) exit;

// 1. AMBIL DATA INPUT
$visit_id = $_POST['visit_id'] ?? '';
$action   = $_POST['action'] ?? 'view_halaman';

// 2. DATA TEKNIS DASAR
$ip  = $_SERVER['REMOTE_ADDR'];
// $ip = '103.173.138.183'; // Uncomment ini untuk tes Whitelist Tanjung Setia
$ua  = $_SERVER['HTTP_USER_AGENT'];
$now = date('Y-m-d H:i:s');

// -----------------------------------------------------------
// A. DETEKSI DEVICE, OS, BROWSER, BRAND
// -----------------------------------------------------------

// A1. DEVICE
$deviceType = 'Desktop';
if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $ua)) {
    $deviceType = 'Mobile';
}

// A2. PLATFORM (OS)
$platform = 'Unknown OS';
if (preg_match('/android/i', $ua)) {
    $platform = 'Android';
} elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
    $platform = 'iOS';
} elseif (preg_match('/windows|win32/i', $ua)) {
    $platform = 'Windows';
} elseif (preg_match('/macintosh|mac os x/i', $ua)) {
    $platform = 'Mac';
} elseif (preg_match('/linux/i', $ua)) {
    $platform = 'Linux'; 
}

// A3. BRAND (Merk HP)
$brand = 'Generic'; 
if (preg_match('/(iPhone|iPad)/i', $ua)) $brand = 'Apple';
elseif (preg_match('/(Samsung|SM-|GT-)/i', $ua)) $brand = 'Samsung';
elseif (preg_match('/(Oppo|CPH|R7|F1)/i', $ua)) $brand = 'Oppo';
elseif (preg_match('/(Vivo|V2)/i', $ua)) $brand = 'Vivo';
elseif (preg_match('/(Xiaomi|Redmi|Poco|Mi )/i', $ua)) $brand = 'Xiaomi';
elseif (preg_match('/(Realme|RMX)/i', $ua)) $brand = 'Realme';
elseif (preg_match('/(Infinix|X6)/i', $ua)) $brand = 'Infinix';
elseif (preg_match('/(Asus|Zenfone)/i', $ua)) $brand = 'Asus';

// A4. BROWSER
$browserName = 'Unknown';
if (strpos($ua, 'Firefox') !== false) $browserName = 'Firefox';
elseif (strpos($ua, 'Chrome') !== false) $browserName = 'Chrome';
elseif (strpos($ua, 'Safari') !== false) $browserName = 'Safari';
elseif (strpos($ua, 'Edge') !== false) $browserName = 'Edge';
elseif (strpos($ua, 'Opera') !== false || strpos($ua, 'OPR') !== false) $browserName = 'Opera';
elseif (strpos($ua, 'UCBrowser') !== false) $browserName = 'UC Browser';


// -----------------------------------------------------------
// B. DETEKSI LOKASI & ISP (WHITELIST + API)
// -----------------------------------------------------------
$city    = 'Unknown';
$country = 'ID';
$isp     = '-';

// 1. CEK WHITELIST (Data Manual)
// Masukkan IP dan Lokasi yang diinginkan di sini
$whitelist_map = [
    '103.173.138.183' => 'Tanjung Setia',
    '103.173.138.167' => 'Tanjung Setia',
    '103.173.138.153' => 'Bangkunat',
    '103.173.138.157' => 'Bangkunat'
];

if (array_key_exists($ip, $whitelist_map)) {
    // JIKA IP ADA DI DAFTAR
    $city    = $whitelist_map[$ip];
    $country = 'ID';
    $isp     = 'Local ISP'; // Bisa disesuaikan
} else {
    // 2. JIKA TIDAK ADA, CEK API
    function getGeoIP($ip_target) {
        if($ip_target == '127.0.0.1' || $ip_target == '::1') return null;
        $url = "http://ip-api.com/json/$ip_target?fields=status,message,countryCode,city,isp";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    $geo = getGeoIP($ip);
    if ($geo && isset($geo['status']) && $geo['status'] == 'success') {
        $city    = $geo['city'];
        $country = $geo['countryCode']; 
        $isp     = $geo['isp'];
    }
}

// -----------------------------------------------------------
// 3. EKSEKUSI INSERT DATABASE
// -----------------------------------------------------------
if ($visit_id) {
    $query = "INSERT INTO noci_logs 
              (visit_id, ip_address, device, platform, brand, city, country, isp, event_action, timestamp, Browser) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    
    mysqli_stmt_bind_param($stmt, "sssssssssss", 
        $visit_id,      
        $ip,            
        $deviceType,    
        $platform,      
        $brand,         
        $city,          // Akan terisi "Tanjung Setia" / "Bangkunat" jika IP cocok
        $country,       
        $isp,           
        $action,        
        $now,           
        $browserName    
    );
    
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>