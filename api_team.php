<?php
// FILE: isolir/api_team.php
// VERSI: CRUD Users Table

require 'config.php';
header("Content-Type: application/json");

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// --- 1. LIST DATA ---
if ($action == 'list') {
    $search = $_GET['search'] ?? '';
    $where = "WHERE 1=1";
    if ($search) {
        $s = mysqli_real_escape_string($conn, $search);
        $where .= " AND (name LIKE '%$s%' OR username LIKE '%$s%' OR email LIKE '%$s%')";
    }
    
    // Ambil semua user kecuali super admin utama (opsional)
    $q = mysqli_query($conn, "SELECT id, username, name, email, phone, role, status FROM noci_users $where ORDER BY id DESC");
    $data = [];
    while($r = mysqli_fetch_assoc($q)) {
        $data[] = $r;
    }
    echo json_encode($data);
    exit;
}

// --- 2. SIMPAN DATA (ADD / EDIT) ---
if ($action == 'save') {
    $id       = $_POST['id'] ?? '';
    $username = mysqli_real_escape_string($conn, $_POST['username']); // Wajib Unik
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);
    $status   = mysqli_real_escape_string($conn, $_POST['status']);
    $raw_pass = $_POST['password'] ?? '';

    // Validasi Username Unik (Jika user baru)
    if (!$id) {
        $check = mysqli_query($conn, "SELECT id FROM noci_users WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Username sudah dipakai!']); exit;
        }
    }

    if ($id) {
        // UPDATE
        $sql = "UPDATE noci_users SET username='$username', name='$name', email='$email', phone='$phone', role='$role', status='$status'";
        
        // Update password HANYA jika diisi
        if (!empty($raw_pass)) {
            $hashed = password_hash($raw_pass, PASSWORD_DEFAULT);
            $sql .= ", password='$hashed'";
        }
        
        $sql .= " WHERE id='$id'";
    } else {
        // INSERT BARU (Password Wajib)
        if (empty($raw_pass)) { echo json_encode(['status' => 'error', 'msg' => 'Password wajib diisi untuk user baru']); exit; }
        
        $hashed = password_hash($raw_pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO noci_users (username, name, email, phone, role, status, password) VALUES ('$username', '$name', '$email', '$phone', '$role', '$status', '$hashed')";
    }

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => mysqli_error($conn)]);
    }
    exit;
}

// --- 3. HAPUS DATA ---
if ($action == 'delete') {
    $id = (int)$_POST['id'];
    if ($id == 1) { // Mencegah hapus Admin Utama (opsional)
        echo json_encode(['status' => 'error', 'msg' => 'Tidak bisa menghapus Super Admin']); exit;
    }
    mysqli_query($conn, "DELETE FROM noci_users WHERE id=$id");
    echo json_encode(['status' => 'success']);
    exit;
}
?>