
<?php
date_default_timezone_set('Asia/Jakarta'); // SET WAKTU WIB

$host = "localhost";
$user = "u429122506_isolir"; 

// PERHATIKAN: Menggunakan tanda kutip SATU ('...') agar simbol $ dan ; terbaca aman
$pass = 'E7^+ag;$l~@y'; 

$db   = "u429122506_isolir";    // Sesuaikan nama database

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) { die("Koneksi Gagal: " . mysqli_connect_error()); }

// Sinkronkan waktu MySQL dengan PHP
mysqli_query($conn, "SET time_zone = '+07:00'"); 
// FUNGSI TAMBAHAN: Kirim Notif Telegram
function kirimNotifTelegram($token, $chat_id, $message) {
    if(empty($token) || empty($chat_id)) return ['status' => false, 'error' => 'Data Telegram kosong'];
    
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'Markdown' // Agar bisa bold/italic seperti WA
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) return ['status' => false, 'error' => $err];
    
    $json = json_decode($result, true);
    if($http_code == 200 && isset($json['ok']) && $json['ok']) {
        return ['status' => true];
    } else {
        return ['status' => false, 'error' => $json['description'] ?? 'Unknown Error'];
    }
}
?>