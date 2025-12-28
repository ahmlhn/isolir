<?php
// FILE: isolir/api_leads.php
require 'config.php';
header("Content-Type: application/json");
error_reporting(0);
mysqli_query($conn, "SET SESSION sql_mode = ''");

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// GET DATA LEADS
if ($action === 'get_all') {
    $search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
    $limit  = mysqli_real_escape_string($conn, $_GET['limit'] ?? '50');

    // Logic Search
    $where = "WHERE 1=1";
    if (!empty($search)) {
        $where .= " AND (customer_name LIKE '%$search%' OR customer_phone LIKE '%$search%' OR customer_address LIKE '%$search%')";
    }

    // Logic Limit
    $limit_sql = "";
    if ($limit !== 'all') {
        $l = (int)$limit;
        if($l < 1) $l = 10;
        $limit_sql = "LIMIT $l";
    }

    // Ambil semua kolom (*) agar ip_address & location_info terbawa
    $q = mysqli_query($conn, "SELECT * FROM noci_customers $where ORDER BY last_seen DESC $limit_sql");
    $data = [];
    
    while($r = mysqli_fetch_assoc($q)) {
        // Sanitasi Output
        $r['customer_name']    = htmlspecialchars($r['customer_name'] ?? '');
        $r['customer_address'] = htmlspecialchars($r['customer_address'] ?? '');
        $r['customer_phone']   = htmlspecialchars($r['customer_phone'] ?? '');
        
        // Sanitasi Lokasi & IP (Kolom Baru)
        $r['ip_address']       = htmlspecialchars($r['ip_address'] ?? '-');
        $r['location_info']    = htmlspecialchars($r['location_info'] ?? '-');
        
        // Format Waktu Relatif
        $r['last_seen_fmt'] = date('d M H:i', strtotime($r['last_seen']));
        
        $data[] = $r;
    }
    echo json_encode($data);
    exit;
}

// UPDATE STATUS
if ($action === 'update_status') {
    $id = mysqli_real_escape_string($conn, $_POST['visit_id']);
    $st = mysqli_real_escape_string($conn, $_POST['status']);
    if($id && $st) {
        mysqli_query($conn, "UPDATE noci_customers SET status='$st' WHERE visit_id='$id'");
        echo json_encode(['status'=>'success']);
    }
    exit;
}
?>