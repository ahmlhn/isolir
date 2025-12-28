<?php
// FILE: isolir/api_dashboard.php
// VERSI: FIX APEXCHARTS DATA TYPE (INTEGER FORCE)
header('Content-Type: application/json');
error_reporting(0);
require 'config.php';

session_start();
if (!isset($_SESSION['is_logged_in'])) {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}

$response = [];

// --- 1. FILTER ---
$period = $_GET['period'] ?? 'today';
$where_log = "";
$where_cust = ""; 
$is_daily_chart = false; 

switch ($period) {
    case 'yesterday':
        $where_log = "DATE(`timestamp`) = CURDATE() - INTERVAL 1 DAY";
        $where_cust = "DATE(`last_seen`) = CURDATE() - INTERVAL 1 DAY";
        break;
    case '7days':
        $where_log = "`timestamp` >= DATE(NOW() - INTERVAL 7 DAY)";
        $where_cust = "`last_seen` >= DATE(NOW() - INTERVAL 7 DAY)";
        $is_daily_chart = true;
        break;
    case '30days':
        $where_log = "`timestamp` >= DATE(NOW() - INTERVAL 30 DAY)";
        $where_cust = "`last_seen` >= DATE(NOW() - INTERVAL 30 DAY)";
        $is_daily_chart = true;
        break;
    case 'today':
    default:
        $where_log = "DATE(`timestamp`) = CURDATE()";
        $where_cust = "DATE(`last_seen`) = CURDATE()";
        break;
}

// --- 2. STATISTIK ---
$q_online = mysqli_query($conn, "SELECT COUNT(DISTINCT visit_id) as total FROM noci_logs WHERE `timestamp` > (NOW() - INTERVAL 10 MINUTE)");
$response['online_users'] = ($q_online) ? (int)mysqli_fetch_assoc($q_online)['total'] : 0;

$q_unique = mysqli_query($conn, "SELECT COUNT(DISTINCT visit_id) as total FROM noci_logs WHERE $where_log");
$response['unique_visits'] = ($q_unique) ? (int)mysqli_fetch_assoc($q_unique)['total'] : 0;

$q_leads = mysqli_query($conn, "SELECT COUNT(*) as total FROM noci_customers WHERE $where_cust");
if (!$q_leads) $q_leads = mysqli_query($conn, "SELECT COUNT(*) as total FROM noci_customers");
$response['total_leads'] = ($q_leads) ? (int)mysqli_fetch_assoc($q_leads)['total'] : 0;

$q_visits = mysqli_query($conn, "SELECT COUNT(*) as total FROM noci_logs WHERE event_action = 'view_halaman' AND $where_log");
$response['total_hits'] = ($q_visits) ? (int)mysqli_fetch_assoc($q_visits)['total'] : 0;


// --- 3. GRAFIK (STRICT INTEGER) ---
$labels = [];
$template_data = [];
$map_index = [];

if ($is_daily_chart) {
    $days_count = ($period == '30days') ? 30 : 7;
    for ($i = $days_count - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d M', strtotime($date));
        $map_index[$date] = count($labels) - 1;
        $template_data[] = 0; // Integer 0
    }
    $query = "SELECT event_action, DATE(`timestamp`) as time_key, COUNT(*) as jumlah FROM noci_logs WHERE $where_log GROUP BY event_action, DATE(`timestamp`)";
} else {
    for ($i = 0; $i < 24; $i++) {
        $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ":00";
        $map_index[$i] = $i;
        $template_data[] = 0; // Integer 0
    }
    $query = "SELECT event_action, HOUR(`timestamp`) as time_key, COUNT(*) as jumlah FROM noci_logs WHERE $where_log GROUP BY event_action, HOUR(`timestamp`)";
}

$response['chart_labels'] = $labels;

$raw_series = [];
$q_chart = mysqli_query($conn, $query);

if ($q_chart) {
    while ($row = mysqli_fetch_assoc($q_chart)) {
        $event = $row['event_action'] ?: 'unknown';
        // Pastikan time_key integer jika mode jam
        $time_key = $is_daily_chart ? $row['time_key'] : (int)$row['time_key'];
        $count = (int)$row['jumlah']; // FORCE INTEGER

        if (!isset($raw_series[$event])) {
            $raw_series[$event] = $template_data;
        }

        if (isset($map_index[$time_key])) {
            $idx = $map_index[$time_key];
            $raw_series[$event][$idx] = $count;
        }
    }
}

$final_series = [];
if (empty($raw_series)) {
    // Jika kosong, kirim data dummy 0 agar grafik tetap muncul (flat line)
    $final_series[] = [
        'name' => 'Tidak Ada Data',
        'data' => $template_data
    ];
} else {
    foreach ($raw_series as $event_name => $data_values) {
        $final_series[] = [
            'name' => ucwords(str_replace('_', ' ', $event_name)),
            'data' => $data_values
        ];
    }
}

$response['chart_series'] = $final_series;


// --- 4. LOGS ---
$logs = [];
$q_logs = mysqli_query($conn, "SELECT * FROM noci_logs WHERE $where_log ORDER BY `timestamp` DESC LIMIT 5");

if ($q_logs) {
    while ($row = mysqli_fetch_assoc($q_logs)) {
        $time_obj = strtotime($row['timestamp']);
        $time = date('H:i', $time_obj);
        $date_label = ($is_daily_chart || $period == 'yesterday') ? date('d/m', $time_obj) : '';
        
        $raw_action = $row['event_action'] ?? 'unknown';
        $action_label = str_replace('_', ' ', strtoupper($raw_action));
        
        $badge_class = 'text-slate-500 bg-slate-100 dark:bg-slate-800';
        if ($raw_action == 'view_halaman') $badge_class = 'text-blue-600 bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400';
        if ($raw_action == 'buka_form') $badge_class = 'text-orange-600 bg-orange-100 dark:bg-orange-500/10 dark:text-orange-400';
        if ($raw_action == 'mulai_chat') $badge_class = 'text-green-600 bg-green-100 dark:bg-green-500/10 dark:text-green-400';
        if (strpos($raw_action, 'error') !== false) $badge_class = 'text-red-600 bg-red-100 dark:bg-red-500/10 dark:text-red-400';

        $logs[] = [
            'time' => $time,
            'date' => $date_label,
            'ip' => $row['ip_address'],
            'device' => ($row['device'] ?? '-') . ' ' . ($row['brand'] ?? ''),
            'status' => $action_label,
            'badge_class' => $badge_class
        ];
    }
}
$response['recent_logs'] = $logs;

echo json_encode($response);
?>