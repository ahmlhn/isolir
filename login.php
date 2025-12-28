<?php
// FILE: isolir/login.php
// VERSI: FIX ROLE SESSION (Menyimpan Level User)

session_start();
require 'config.php';

// 1. LOGOUT
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit;
}

// 2. CEK LOGIN
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

// 3. PROSES LOGIN
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $redirect_target = $_POST['redirect_to'] ?? '';

    // Ambil data user beserta ROLE-nya
    // Pastikan kolom di database namanya 'role' atau sesuaikan (misal: 'level')
    $query = mysqli_query($conn, "SELECT * FROM noci_users WHERE username = '$username'");
    
    if (mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        
        // Cek Password (Hash atau Plain)
        $is_valid = false;
        if (password_verify($password, $row['password'])) { $is_valid = true; } 
        else if ($password == $row['password']) { $is_valid = true; }

        if ($is_valid) {
            // --- BAGIAN PENTING: SIMPAN SESSION ---
            $_SESSION['is_logged_in'] = true;
            $_SESSION['admin_id']     = $row['id'];
            $_SESSION['admin_name']   = $row['name'] ?? $row['username']; // Ambil Nama Asli jika ada
            
            // SIMPAN ROLE DARI DATABASE KE SESSION
            // Jika kolom di DB kosong, default ke 'staff'
            $_SESSION['level']        = $row['role'] ?? 'staff'; 
            
            // Backup untuk kompatibilitas kode lama
            $_SESSION['logged_in']    = true; 

            // REDIRECT
            if ($redirect_target === 'chat') {
                header("Location: chat/index.php"); 
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan.";
    }
}

$req_redirect = $_GET['redirect'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body{background:#0f172a; font-family:'Inter', sans-serif;}</style>
</head>
<body class="flex items-center justify-center h-screen px-4">
    <div class="bg-slate-800 p-8 rounded-2xl shadow-2xl w-full max-w-sm border border-slate-700 relative overflow-hidden">
        <div class="text-center mb-8 relative z-10">
            <h1 class="text-3xl font-black text-white mb-1 tracking-tight">STAR<span class="text-blue-500">CONNECT</span></h1>
            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">System Access</p>
        </div>
        <?php if($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl mb-6 text-sm text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="space-y-4 relative z-10">
            <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($req_redirect); ?>">
            <div>
                <label class="block text-xs text-slate-500 uppercase font-bold mb-1 ml-1">Username</label>
                <input type="text" name="username" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-blue-500 transition placeholder-slate-600">
            </div>
            <div>
                <label class="block text-xs text-slate-500 uppercase font-bold mb-1 ml-1">Password</label>
                <input type="password" name="password" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-blue-500 transition placeholder-slate-600">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-blue-600/20 mt-2">MASUK</button>
        </form>
        <div class="mt-8 text-center relative z-10">
            <p class="text-[10px] text-slate-600">© 2025 Star Connect System</p>
        </div>
    </div>
</body>
</html>