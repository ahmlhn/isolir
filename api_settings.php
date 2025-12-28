<?php
// FILE: isolir/api_settings.php
// VERSI: DEBUG & DIRECT SAVE + TEST TG

header('Content-Type: application/json');
error_reporting(E_ALL); 
ini_set('display_errors', 0); 

require_once 'config.php'; 
mysqli_query($conn, "SET SESSION sql_mode = ''");

$action = $_REQUEST['action'] ?? '';

try {
    // 1. GET SETTINGS
    if ($action == 'get') {
        $response = [];

        // WA Config
        $q_wa = mysqli_query($conn, "SELECT * FROM noci_conf_wa WHERE id=1");
        if(!$q_wa) { echo json_encode(['error' => 'Tabel WA hilang. Jalankan SQL Manual!']); exit; }
        $d_wa = mysqli_fetch_assoc($q_wa);
        
        $response['wa_url']    = $d_wa['base_url'] ?? '';
        $response['wa_token']  = $d_wa['token'] ?? '';
        $response['wa_sender'] = $d_wa['sender_number'] ?? '';
        $response['wa_target'] = $d_wa['target_number'] ?? '';
        $response['wa_active'] = $d_wa['is_active'] ?? 0;

        // Telegram Config
        $q_tg = mysqli_query($conn, "SELECT * FROM noci_conf_tg WHERE id=1");
        $d_tg = mysqli_fetch_assoc($q_tg);
        $response['tg_bot_token'] = $d_tg['bot_token'] ?? '';
        $response['tg_chat_id']   = $d_tg['chat_id'] ?? '';
        
        // Templates
        $conn->query("CREATE TABLE IF NOT EXISTS noci_msg_templates (code VARCHAR(50) PRIMARY KEY, message TEXT)");
        $q_tpl = mysqli_query($conn, "SELECT code, message FROM noci_msg_templates");
        while($row = mysqli_fetch_assoc($q_tpl)) { $response[$row['code']] = $row['message']; }

        echo json_encode($response);
        exit;
    }

    // 2. SAVE WA
    if ($action == 'save_wa') {
        $url    = mysqli_real_escape_string($conn, $_POST['wa_url']);
        $token  = mysqli_real_escape_string($conn, $_POST['wa_token']);
        $sender = mysqli_real_escape_string($conn, $_POST['wa_sender']);
        $target = mysqli_real_escape_string($conn, $_POST['wa_target']);
        $active = isset($_POST['wa_active']) ? 1 : 0;

        $cek = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM noci_conf_wa WHERE id=1"));
        if ($cek == 0) {
            $sql = "INSERT INTO noci_conf_wa (id, base_url, token, sender_number, target_number, is_active) 
                    VALUES (1, '$url', '$token', '$sender', '$target', '$active')";
        } else {
            $sql = "UPDATE noci_conf_wa SET base_url='$url', token='$token', sender_number='$sender', target_number='$target', is_active='$active' WHERE id=1";
        }

        if(mysqli_query($conn, $sql)) echo json_encode(['status' => 'success', 'msg' => 'WA Tersimpan']);
        else echo json_encode(['status' => 'error', 'msg' => 'SQL Error: '.mysqli_error($conn)]);
        exit;
    }

    // 3. SAVE TELEGRAM
    if ($action == 'save_tg') {
        $token = mysqli_real_escape_string($conn, $_POST['tg_bot_token']);
        $chat  = mysqli_real_escape_string($conn, $_POST['tg_chat_id']);
        
        $cek = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM noci_conf_tg WHERE id=1"));
        if ($cek == 0) {
            $sql = "INSERT INTO noci_conf_tg (id, bot_token, chat_id, is_active) VALUES (1, '$token', '$chat', 1)";
        } else {
            $sql = "UPDATE noci_conf_tg SET bot_token='$token', chat_id='$chat' WHERE id=1";
        }
        
        if(mysqli_query($conn, $sql)) echo json_encode(['status' => 'success', 'msg' => 'Telegram Tersimpan']);
        else echo json_encode(['status' => 'error', 'msg' => 'SQL Error: '.mysqli_error($conn)]);
        exit;
    }

    // 4. SAVE TEMPLATE
    if ($action == 'save_tpl') {
        $tpls = ['web_welcome' => $_POST['web_welcome'], 'wa_login' => $_POST['wa_login'], 'wa_chat' => $_POST['wa_chat']];
        foreach($tpls as $code => $msg) {
            $safe = mysqli_real_escape_string($conn, $msg);
            mysqli_query($conn, "INSERT INTO noci_msg_templates (code, message) VALUES ('$code', '$safe') ON DUPLICATE KEY UPDATE message='$safe'");
        }
        echo json_encode(['status' => 'success', 'msg' => 'Template Tersimpan']);
        exit;
    }

    // 5. TEST CONNECTION WA
    if ($action == 'test') {
        $url=$_POST['wa_url']; $token=$_POST['wa_token']; $sender=$_POST['wa_sender']; $target=$_POST['wa_target'];
        $params = ['api_key'=>$token, 'sender'=>$sender, 'number'=>$target, 'message'=>"Tes Koneksi WA Sukses! 🚀", 'footer'=>'System Check'];
        $ch = curl_init(); 
        curl_setopt_array($ch, [CURLOPT_URL=>$url.'?'.http_build_query($params), CURLOPT_RETURNTRANSFER=>true, CURLOPT_SSL_VERIFYPEER=>false]);
        $res = curl_exec($ch); 
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch);
        if($http==200) echo json_encode(['status'=>'success']); else echo json_encode(['status'=>'error','msg'=>'HTTP: '.$http]);
        exit;
    }

    // [BARU] TEST TELEGRAM
    if ($action == 'test_tg') {
        $token = $_POST['tg_bot_token'] ?? '';
        $chat  = $_POST['tg_chat_id'] ?? '';
        
        if(empty($token) || empty($chat)) {
            echo json_encode(['status'=>'error', 'msg'=>'Token & Chat ID wajib diisi']); 
            exit;
        }

        $msg = "✅ *Tes Koneksi Telegram Berhasil!*\n\nSistem Isolir siap mengirim notifikasi backup ke sini.";
        $url = "https://api.telegram.org/bot$token/sendMessage";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => [
                'chat_id' => $chat, 
                'text' => $msg, 
                'parse_mode' => 'Markdown'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if($err) {
            echo json_encode(['status'=>'error', 'msg'=>'Curl Error: '.$err]);
        } else {
            $json = json_decode($res, true);
            if($json && $json['ok']) {
                echo json_encode(['status'=>'success']);
            } else {
                $tgErr = $json['description'] ?? 'Token/ChatID Salah';
                echo json_encode(['status'=>'error', 'msg'=>'Telegram Error: '.$tgErr]);
            }
        }
        exit;
    }

    // 6. GET LOGS (Update: Tambahkan Target Number)
    if ($action == 'get_logs') {
        $conn->query("CREATE TABLE IF NOT EXISTS noci_wa_log (id INT AUTO_INCREMENT PRIMARY KEY, visit_id VARCHAR(50), message TEXT, target_number VARCHAR(20), status VARCHAR(20), api_response TEXT, created_at DATETIME)");
        
        $q = mysqli_query($conn, "SELECT * FROM noci_wa_log ORDER BY id DESC LIMIT 50"); 
        
        $data = []; 
        while($row = mysqli_fetch_assoc($q)) {
            $data[] = [
                'id' => $row['id'], 
                'visit_id' => $row['visit_id'],
                // Pastikan target number terkirim ke JS
                'target' => $row['target_number'] ? $row['target_number'] : '-', 
                'message' => htmlspecialchars(substr($row['message'], 0, 40)), 
                'time' => date('d/m H:i', strtotime($row['created_at'])), 
                'status' => $row['status'], 
                'log' => htmlspecialchars($row['api_response'])
            ];
        }
        echo json_encode($data); exit;
    }

} catch (Exception $e) { echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]); }
?>