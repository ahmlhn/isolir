<?php
// FILE: isolir/chat/api.php
// VERSI: SUPPORT ISP WHITELIST (SMC)

ob_start(); 
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json; charset=utf-8');

function sendJson($data) { ob_clean(); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }

try {
    // LOAD CONFIG
    $paths = ['config.php', '../config.php', '../../config.php'];
    $config_loaded = false;
    foreach ($paths as $path) { if (file_exists($path)) { require_once $path; $config_loaded = true; break; } }
    if (!$config_loaded || !isset($conn) || !$conn) throw new Exception("DB Error");

    mysqli_set_charset($conn, "utf8mb4");
    date_default_timezone_set('Asia/Jakarta');

    function cleanInputSafe($data) { global $conn; return mysqli_real_escape_string($conn, htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8')); }
    function checkAuthSafe() {
        $is_admin = (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) || (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true);
        if (!$is_admin) sendJson(['status' => 'error', 'msg' => 'Unauthorized']);
    }

    // [MODIFIED] FUNGSI DETEKSI ISP (WHITELIST + API)
    function detect_isp($ip) {
        // 1. Cek Localhost
        if ($ip == '127.0.0.1' || $ip == '::1') return 'Localhost (Server)';

        // 2. Cek Whitelist (IP Khusus SMC)
        // Format: 'IP_ADDRESS' => 'NAMA ISP (LOKASI)'
        $whitelist = [
            '103.173.138.153' => 'SMC (Bangkunat)',
            '103.173.138.157' => 'SMC (Bangkunat)',
            '103.173.138.183' => 'SMC (Tanjung Setia)',
            '103.173.138.167' => 'SMC (Tanjung Setia)'
        ];

        if (isset($whitelist[$ip])) {
            return $whitelist[$ip]; // Langsung return jika ada di whitelist
        }

        // 3. Cek API Public (ip-api.com) jika tidak ada di whitelist
        $ctx = stream_context_create(['http'=> ['timeout' => 2]]);
        $json = @file_get_contents("http://ip-api.com/json/" . $ip . "?fields=status,isp,city,org", false, $ctx);
        
        if ($json) {
            $data = json_decode($json, true);
            if (isset($data['status']) && $data['status'] == 'success') {
                $isp = $data['isp'] ?? $data['org'] ?? 'Unknown ISP';
                $city = $data['city'] ?? '-';
                return "$isp ($city)";
            }
        }
        return "Unknown ($ip)";
    }

    function autoFixTable($conn) {
        $resCust = mysqli_query($conn, "SHOW COLUMNS FROM noci_customers LIKE 'notes'");
        if ($resCust && mysqli_num_rows($resCust) == 0) mysqli_query($conn, "ALTER TABLE noci_customers ADD COLUMN notes TEXT");
        
        $resChat = mysqli_query($conn, "SHOW COLUMNS FROM noci_chat LIKE 'is_edited'");
        if ($resChat && mysqli_num_rows($resChat) == 0) mysqli_query($conn, "ALTER TABLE noci_chat ADD COLUMN is_edited TINYINT DEFAULT 0");
        
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS noci_settings (id INT AUTO_INCREMENT PRIMARY KEY, wa_active TINYINT DEFAULT 0, wa_token VARCHAR(255), wa_target VARCHAR(50), wa_url VARCHAR(255), wa_sender VARCHAR(50), welcome_msg TEXT)");
    }

    function safeSendWA($pesan) {
        global $conn;
        $q = mysqli_query($conn, "SELECT wa_active, wa_token, wa_target, wa_url, wa_sender FROM noci_settings WHERE id=1 LIMIT 1");
        if (!$q || mysqli_num_rows($q) == 0) return false;
        $conf = mysqli_fetch_assoc($q);
        if (($conf['wa_active'] ?? 0) == 0) return false;
        
        $url = ($conf['wa_url'] ?? '') . '?' . http_build_query([
            'api_key' => $conf['wa_token'] ?? '',
            'sender'  => $conf['wa_sender'] ?? '',
            'number'  => $conf['wa_target'] ?? '',
            'message' => $pesan
        ]);
        
        $curl = curl_init();
        curl_setopt_array($curl, [CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 2, CURLOPT_SSL_VERIFYPEER => false]);
        curl_exec($curl); curl_close($curl);
        return true;
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    // --- ACTIONS ---

    if ($action == 'start_session') {
        $visit_id = cleanInputSafe($_POST['visit_id'] ?? '');
        $name = cleanInputSafe($_POST['name'] ?? 'User');
        $phone = cleanInputSafe($_POST['phone'] ?? '-');
        $addr = cleanInputSafe($_POST['address'] ?? '-');
        
        if(strlen($name) < 2) throw new Exception("Nama pendek");
        autoFixTable($conn);

        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS noci_customers (visit_id VARCHAR(50) PRIMARY KEY, customer_name VARCHAR(100), customer_phone VARCHAR(20), customer_address TEXT, ip_address VARCHAR(50), location_info VARCHAR(150), last_seen DATETIME, visit_count INT, status VARCHAR(20), notes TEXT)");
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS noci_chat (id INT AUTO_INCREMENT PRIMARY KEY, visit_id VARCHAR(50), sender VARCHAR(20), message TEXT, type VARCHAR(10) DEFAULT 'text', is_read TINYINT DEFAULT 0, is_edited TINYINT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");

        // DETEKSI ISP (Prioritas Whitelist)
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $location_info = detect_isp($ip); 
        $location_info_esc = mysqli_real_escape_string($conn, $location_info);

        $sql = "INSERT INTO noci_customers (visit_id, customer_name, customer_phone, customer_address, ip_address, location_info, last_seen, visit_count, status) 
                VALUES ('$visit_id', '$name', '$phone', '$addr', '$ip', '$location_info_esc', NOW(), 1, 'Baru') 
                ON DUPLICATE KEY UPDATE 
                customer_name='$name', customer_phone='$phone', customer_address='$addr', 
                ip_address='$ip', location_info='$location_info_esc', 
                last_seen=NOW(), visit_count=visit_count+1, status=IF(status='Selesai','Baru',status)";
        
        mysqli_query($conn, $sql);

        $cekChat = mysqli_query($conn, "SELECT id FROM noci_chat WHERE visit_id='$visit_id' LIMIT 1");
        if(mysqli_num_rows($cekChat) == 0) {
            $qSet = mysqli_query($conn, "SELECT welcome_msg FROM noci_settings WHERE id=1 LIMIT 1");
            $wel = mysqli_fetch_assoc($qSet)['welcome_msg'] ?? 'Halo kak, ada yang bisa kami bantu?';
            mysqli_query($conn, "INSERT INTO noci_chat (visit_id, sender, message, type, is_read, created_at) VALUES ('$visit_id', 'admin', '".mysqli_real_escape_string($conn, $wel)."', 'text', 1, NOW())");
        }
        
        safeSendWA("*CHAT BARU*\nUser: $name\nHP: $phone\nInfo: $location_info");
        sendJson(['status' => 'success']);

    } elseif ($action == 'get_contacts') {
        checkAuthSafe(); autoFixTable($conn);
        $sql = "SELECT c.visit_id, COALESCE(NULLIF(c.customer_name, ''), 'Tanpa Nama') as name, c.customer_phone as phone, c.customer_address as address, c.ip_address, c.location_info, c.status, c.last_seen, c.notes, chat.message as last_msg, chat.type as msg_type, chat.created_at as msg_time, (SELECT COUNT(*) FROM noci_chat WHERE visit_id=c.visit_id AND sender='user' AND is_read=0) as unread FROM noci_customers c JOIN (SELECT t1.* FROM noci_chat t1 JOIN (SELECT visit_id, MAX(id) as max_id FROM noci_chat GROUP BY visit_id) t2 ON t1.visit_id = t2.visit_id AND t1.id = t2.max_id) chat ON c.visit_id = chat.visit_id ORDER BY chat.created_at DESC";
        $q = mysqli_query($conn, $sql); $data = [];
        while($r = mysqli_fetch_assoc($q)) {
            $ts = strtotime($r['msg_time']);
            $r['display_time'] = (date('Y-m-d') == date('Y-m-d', $ts)) ? date('H:i', $ts) : date('d/m', $ts);
            $data[] = $r;
        }
        sendJson($data);

    } elseif ($action == 'get_messages') {
        $visit_id = cleanInputSafe($_GET['visit_id'] ?? '');
        $viewer = $_GET['viewer'] ?? 'user';
        if ($viewer === 'admin') { checkAuthSafe(); mysqli_query($conn, "UPDATE noci_chat SET is_read=1 WHERE visit_id='$visit_id' AND sender='user'"); } 
        else { mysqli_query($conn, "UPDATE noci_customers SET last_seen=NOW() WHERE visit_id='$visit_id'"); }
        
        $q = mysqli_query($conn, "SELECT * FROM noci_chat WHERE visit_id='$visit_id' ORDER BY id ASC");
        $data = [];
        if($q) { while($r = mysqli_fetch_assoc($q)) {
            $data[] = [
                'id' => $r['id'],
                'sender' => $r['sender'],
                'message' => htmlspecialchars_decode($r['message']),
                'type' => $r['type'] ?? 'text',
                'is_edited' => $r['is_edited'] ?? 0,
                'time' => date('H:i', strtotime($r['created_at']))
            ];
        }}
        sendJson($data);

    } elseif ($action == 'send') {
        $visit_id = cleanInputSafe($_POST['visit_id'] ?? '');
        $sender = cleanInputSafe($_POST['sender'] ?? 'user');
        if ($sender === 'admin') checkAuthSafe();

        $message = ''; $type = 'text'; $new_visit_id = null;
        if ($sender === 'user') {
            if (mysqli_num_rows(mysqli_query($conn, "SELECT visit_id FROM noci_customers WHERE visit_id='$visit_id'")) == 0) {
                $new_visit_id = time() . rand(100, 999); $visit_id = $new_visit_id;
                // Auto Detect ISP
                $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $loc = mysqli_real_escape_string($conn, detect_isp($ip));
                mysqli_query($conn, "INSERT INTO noci_customers (visit_id, customer_name, last_seen, status, ip_address, location_info) VALUES ('$visit_id', 'Pelanggan (Reset)', NOW(), 'Baru', '$ip', '$loc')");
            }
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $file = $_FILES['image']; $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) && $file['size'] <= 5000000) {
                if(!is_dir('uploads')) mkdir('uploads', 0755, true);
                $newName = time() . '_' . rand(1000,9999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], 'uploads/' . $newName)) { $message = $newName; $type = 'image'; }
            }
        } else { $message = cleanInputSafe($_POST['message'] ?? ''); }

        if ($message != '') {
            mysqli_query($conn, "INSERT INTO noci_chat (visit_id, sender, message, type, is_read, created_at) VALUES ('$visit_id', '$sender', '$message', '$type', 0, NOW())");
            if ($sender === 'admin') mysqli_query($conn, "UPDATE noci_customers SET status='Proses' WHERE visit_id='$visit_id'");
            else mysqli_query($conn, "UPDATE noci_customers SET status='Menunggu', last_seen=NOW() WHERE visit_id='$visit_id' AND status!='Proses'");
            $res = ['status' => 'success']; if ($new_visit_id) $res['new_visit_id'] = $new_visit_id; sendJson($res);
        } else { sendJson(['status' => 'error', 'msg' => 'Pesan kosong']); }

    } elseif ($action == 'delete_message') {
        checkAuthSafe(); $id = cleanInputSafe($_POST['id'] ?? '');
        if(!empty($id)) { mysqli_query($conn, "DELETE FROM noci_chat WHERE id='$id'"); sendJson(['status' => 'success']); }
        else sendJson(['status' => 'error']);

    } elseif ($action == 'edit_message') {
        checkAuthSafe(); autoFixTable($conn); 
        $id = cleanInputSafe($_POST['id'] ?? ''); $msg = cleanInputSafe($_POST['message'] ?? '');
        if(!empty($id) && !empty($msg)) {
            mysqli_query($conn, "UPDATE noci_chat SET message='$msg', is_edited=1 WHERE id='$id'");
            sendJson(['status' => 'success']);
        } else sendJson(['status' => 'error']);

    } elseif ($action == 'get_admin_profile') {
        checkAuthSafe(); $id = $_SESSION['admin_id'] ?? 0;
        $q = mysqli_query($conn, "SELECT id, username, name FROM noci_users WHERE id='$id'");
        if ($r = mysqli_fetch_assoc($q)) sendJson(['status' => 'success', 'data' => $r]);
        else sendJson(['status' => 'error', 'msg' => 'User not found']);

    } elseif ($action == 'update_admin_profile') {
        checkAuthSafe(); $id = $_SESSION['admin_id'] ?? 0;
        $name = cleanInputSafe($_POST['name'] ?? ''); $username = cleanInputSafe($_POST['username'] ?? ''); $password = $_POST['password'] ?? '';
        $sql = "UPDATE noci_users SET name='$name', username='$username'" . (!empty($password) ? ", password='".password_hash($password, PASSWORD_DEFAULT)."'" : "") . " WHERE id='$id'";
        if (mysqli_query($conn, $sql)) { $_SESSION['admin_name'] = $name; sendJson(['status' => 'success']); }
        else throw new Exception("Gagal update profil");

    } elseif (in_array($action, ['end_session', 'delete_session', 'update_customer', 'reopen_session', 'save_note'])) {
        checkAuthSafe(); $vid = cleanInputSafe($_POST['visit_id'] ?? '');
        if ($action == 'end_session') mysqli_query($conn, "UPDATE noci_customers SET status='Selesai' WHERE visit_id='$vid'");
        elseif ($action == 'delete_session') { mysqli_query($conn, "DELETE FROM noci_chat WHERE visit_id='$vid'"); mysqli_query($conn, "DELETE FROM noci_customers WHERE visit_id='$vid'"); }
        elseif ($action == 'reopen_session') mysqli_query($conn, "UPDATE noci_customers SET status='Proses' WHERE visit_id='$vid'");
        elseif ($action == 'save_note') { $note = cleanInputSafe($_POST['note'] ?? ''); mysqli_query($conn, "UPDATE noci_customers SET notes='$note' WHERE visit_id='$vid'"); }
        elseif ($action == 'update_customer') { $nm = cleanInputSafe($_POST['name'] ?? ''); $ph = cleanInputSafe($_POST['phone'] ?? ''); $ad = cleanInputSafe($_POST['address'] ?? ''); mysqli_query($conn, "UPDATE noci_customers SET customer_name='$nm', customer_phone='$ph', customer_address='$ad' WHERE visit_id='$vid'"); }
        sendJson(['status' => 'success']);
    } else { sendJson(['status' => 'error']); }

} catch (Throwable $e) { sendJson(['status' => 'error', 'msg' => $e->getMessage()]); }
?>