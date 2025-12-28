<?php
// FILE: isolir/chat/config.php
date_default_timezone_set('Asia/Jakarta'); // TIMEZONE PHP (PENTING)

$host = "localhost";
$user = "u429122506_isolir"; 
$pass = 'E7^+ag;$l~@y'; 
$db   = "u429122506_isolir";    

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) { die("Koneksi gagal: " . mysqli_connect_error()); }

// Set Timezone MySQL (Usaha sinkronisasi)
mysqli_query($conn, "SET time_zone = '+07:00'"); 

// FILE: isolir/chat/config.php

function kirimNotifWA($pesan) {
    global $conn;

    // 1. Ambil Config
    $q = mysqli_query($conn, "SELECT * FROM noci_settings WHERE id=1 LIMIT 1");
    if (!$q || mysqli_num_rows($q) == 0) {
        return ['status' => false, 'error' => 'Config DB Error'];
    }
    
    $conf = mysqli_fetch_assoc($q);

    // 2. Cek Syarat
    if ($conf['wa_active'] == 0) return ['status' => false, 'error' => 'Fitur Dimatikan Admin'];
    if (empty($conf['wa_token'])) return ['status' => false, 'error' => 'Token Kosong'];
    if (empty($conf['wa_target'])) return ['status' => false, 'error' => 'Nomor Target Kosong'];

    // 3. Susun URL & Data
    $params = [
        'api_key' => $conf['wa_token'],
        'sender'  => $conf['wa_sender'],
        'number'  => $conf['wa_target'],
        'message' => $pesan
    ];

    $url = $conf['wa_url'] . '?' . http_build_query($params);

    // 4. Kirim dengan cURL
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 4, // Tunggu maks 4 detik (cukup untuk dapat respon)
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => 'GET'
    ));

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);

    // 5. Analisis Hasil
    if ($err) {
        return ['status' => false, 'error' => "Curl Error: $err"];
    }

    // Cek HTTP Code (200 biasanya sukses)
    if ($http_code == 200) {
        // Opsional: Cek isi JSON dari MPWA jika perlu validasi lebih dalam
        return ['status' => true, 'msg' => $response];
    } else {
        return ['status' => false, 'error' => "HTTP $http_code: $response"];
    }
}
?>