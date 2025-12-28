<?php 
// FILE: isolir/chat/index.php
// VERSI: TEMA LIGHT MODE (PUTIH CERAH)

session_start();

// 1. Cek Session Login
$is_admin = (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) || 
            (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true);

// 2. Jika belum login, redirect
if (!$is_admin) { 
    header("Location: ../login.php?redirect=chat"); 
    exit; 
}

// 3. AMBIL DATA DARI SESSION
$admin_name = $_SESSION['admin_name'] ?? 'Admin'; 
$admin_role = $_SESSION['level'] ?? 'Staff'; 
$admin_first_name = explode(' ', trim($admin_name))[0];

require_once '../config.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chat Admin | Isolir</title>
    
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <style>
        /* Base Styles - LIGHT MODE */
        html, body {
            height: 100%; width: 100%; position: fixed; inset: 0;
            overflow: hidden; background-color: #f8fafc; /* Latar abu sangat muda */
            font-family: 'Inter', sans-serif;
            overscroll-behavior: none; touch-action: none;
            color: #334155; /* Teks abu gelap */
        }
        #main-app { display: flex; width: 100%; height: 100%; position: relative; overflow: hidden; }
        
        /* Scrollbar Halus */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .mobile-hidden { display: none !important; }
        @media (min-width: 768px) { .mobile-hidden { display: flex !important; } }
        
        @keyframes zoomIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .animate-zoomIn { animation: zoomIn 0.2s ease-out forwards; }
        
        /* Active State untuk List User (Light Mode) */
        .user-item-active { background-color: #eff6ff !important; border-left-color: #3b82f6 !important; }
    </style>
</head>
<body class="text-slate-600">

    <audio id="notif-sound" src="../assets/ding.mp3"></audio>

    <div id="main-app">

        <div id="panel-list" class="w-full md:w-80 flex-shrink-0 flex flex-col border-r border-slate-200 bg-white h-full z-10 relative shadow-sm">
            
            <div class="h-16 flex items-center justify-between px-4 border-b border-slate-200 bg-white shrink-0">
                <div class="flex items-center gap-3">
                    <img src="../assets/favicon.svg" alt="Logo" class="w-8 h-8 drop-shadow-sm">
                    <div class="flex flex-col">
                        <h1 class="text-lg font-black text-slate-800 tracking-wider leading-none font-sans">DARAT<span class="text-blue-600">LAUT</span></h1>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                            <span class="text-[9px] text-slate-500 font-bold tracking-[0.2em] uppercase">Live Support</span>
                        </div>
                    </div>
                </div>
                <div class="relative" id="dropdown-sidebar-container">
                    <button onclick="toggleSidebarMenu()" class="p-2 hover:bg-slate-100 rounded-lg text-slate-500 hover:text-blue-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </button>
                    <div id="sidebar-menu" class="hidden absolute right-0 top-full mt-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50 animate-zoomIn ring-1 ring-black/5">
                        <div class="px-4 py-4 border-b border-slate-100 bg-slate-50 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg shadow-sm border border-blue-200">
                                <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                            </div>
                            <div class="min-w-0">
                                <div class="text-slate-800 font-bold truncate text-sm"><?php echo htmlspecialchars($admin_name); ?></div>
                                <div class="text-[10px] text-blue-600 uppercase font-bold tracking-wider bg-blue-50 px-1.5 py-0.5 rounded inline-block mt-1 border border-blue-100">
                                    <?php echo htmlspecialchars($admin_role); ?>
                                </div>
                            </div>
                        </div>

                        <a href="#" onclick="openAdminProfile()" class="block px-4 py-3 text-sm hover:bg-slate-50 text-slate-700 flex items-center gap-3 group">
                            <span class="group-hover:text-blue-600 transition text-slate-400">⚙️</span> Pengaturan Profil
                        </a>
                        <a href="#" onclick="openTplManager()" class="block px-4 py-3 text-sm hover:bg-slate-50 text-slate-700 border-b border-slate-100 flex items-center gap-3 group">
                            <span class="group-hover:text-yellow-500 transition text-slate-400">⚡</span> Atur Balas Cepat
                        </a>
                        <a href="../dashboard.php" class="block px-4 py-3 text-sm hover:bg-slate-50 text-slate-700 flex items-center gap-3">
                            <span>🔙</span> Kembali ke Dashboard
                        </a>
                        
                        <div class="border-t border-slate-100 mt-1">
                            <a href="../login.php?action=logout" class="block px-4 py-3 text-sm hover:bg-red-50 text-red-600 flex items-center gap-3 font-bold transition">
                                <span>🚪</span> Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 border-b border-slate-200 bg-slate-50/50 shrink-0">
                <div class="relative group">
                    <input style="display:none" type="text" name="fakeusernameremembered"/>
                    <input style="display:none" type="password" name="fakepasswordremembered"/>

                    <input type="text" 
                           id="search-user" 
                           name="search_query_<?php echo time(); ?>" 
                           onkeyup="filterUsers()" 
                           placeholder="Cari pelanggan..." 
                           autocomplete="new-password" 
                           readonly 
                           onfocus="this.removeAttribute('readonly');"
                           class="w-full bg-white border border-slate-300 text-sm rounded-lg pl-9 pr-3 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition text-slate-700 placeholder-slate-400 shadow-sm">
                    
                    <svg class="w-4 h-4 absolute left-3 top-3.5 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div id="user-list" class="flex-1 overflow-y-auto custom-scrollbar bg-white space-y-0.5">
                <div class="flex flex-col items-center justify-center h-40 text-slate-400 text-sm animate-pulse">
                    Memuat data...
                </div>
            </div>
        </div>


        <div id="panel-chat" class="flex-1 flex flex-col h-full bg-[#f0f2f5] relative mobile-hidden w-full overflow-hidden">
            
            <div id="empty-state" class="absolute inset-0 flex flex-col items-center justify-center bg-[#f8fafc] z-0 overflow-hidden group select-none">
                <div class="absolute inset-0 bg-[url('../assets/favicon.svg')] bg-repeat opacity-[0.05] grayscale animate-[pulse_8s_infinite] pointer-events-none"></div>
                <div class="relative z-10 text-center p-10 animate-in fade-in zoom-in duration-700">
                    <div class="relative inline-block mb-8 group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-blue-200 blur-3xl opacity-40 animate-pulse rounded-full"></div>
                        <img src="../assets/favicon.svg" alt="Empty" class="w-24 h-24 opacity-90 animate-[bounce_3s_infinite] drop-shadow-xl relative z-10 filter drop-shadow-md">
                    </div>
                    <h3 class="text-3xl font-black text-slate-800 mb-3 tracking-tight">
                        Halo, <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-blue-800"><?php echo htmlspecialchars($admin_first_name); ?></span>! 👋
                    </h3>
                    <p class="text-slate-500 text-sm max-w-xs mx-auto leading-relaxed">
                        Siap melayani pelanggan? Pilih percakapan dari panel sebelah kiri untuk memulai.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-2 opacity-60 text-[10px] uppercase tracking-widest font-mono text-slate-400">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Sistem Ready
                    </div>
                </div>
            </div>

            <div id="chat-interface" class="hidden w-full h-full bg-[#f0f2f5] z-10 relative">
                
                <div class="flex-1 flex flex-col min-w-0 h-full relative border-r border-slate-200">
                    
                    <div class="flex-none h-16 border-b border-slate-200 bg-white flex items-center justify-between px-3 md:px-4 z-20 shadow-sm">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <button onclick="closeChatMobile()" class="md:hidden p-2 -ml-2 text-slate-500 hover:text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            
                            <div id="h-avatar" class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center font-bold text-white shadow-md shrink-0 text-sm border border-white">U</div>
                            <div class="min-w-0 flex flex-col justify-center">
                                <div class="flex items-center gap-2"><h3 id="h-name" class="info-name font-bold text-slate-800 text-sm truncate leading-none max-w-[150px] md:max-w-[200px]">User</h3></div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <div id="h-status" class="text-[11px] text-slate-500 truncate">Loading...</div>
                                    <span class="text-slate-300 text-[10px]">|</span>
                                    <span id="h-id" class="text-[11px] font-mono text-blue-600 bg-blue-50 px-1 rounded">#ID</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <a id="wa-link" href="#" target="_blank" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition" title="WhatsApp">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.891-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></path></svg>
                            </a>
                            
                            <div class="relative" id="dropdown-chat-container">
                                <button onclick="toggleMenu()" class="p-2 text-slate-500 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                </button>
                                <div id="chat-menu" class="hidden absolute right-0 top-full mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50 animate-zoomIn ring-1 ring-black/5">
                                    <a href="#" onclick="showDetail()" class="block px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 border-b border-slate-100">👤 Detail Pelanggan</a>
                                    <a href="#" onclick="openEditUser()" class="block px-4 py-2.5 text-sm hover:bg-slate-50 text-blue-600">✏️ Edit Data</a>
                                    <a href="#" onclick="openEndModal()" class="block px-4 py-2.5 text-sm hover:bg-green-50 text-green-600">✔ Selesaikan Sesi</a>
                                    <a href="#" onclick="deleteSession()" class="block px-4 py-2.5 text-sm hover:bg-red-50 text-red-600 border-t border-slate-100">🗑️ Hapus Chat</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="messages" class="flex-1 overflow-y-auto p-4 md:p-5 custom-scrollbar space-y-3 bg-[#efeae2] bg-opacity-30" style="background-image: radial-gradient(#d1d5db 1px, transparent 1px); background-size: 20px 20px;">
                    </div>

                    <div class="flex-none p-3 md:p-4 bg-[#f0f2f5] border-t border-slate-200 relative z-20 shrink-0 pb-safe">
                        
                        <div id="footer-active" class="flex items-end gap-2 w-full">
                            <div class="relative hidden md:block" id="dropdown-tpl-container">
                                <button onclick="toggleTpl()" class="p-3 bg-white text-yellow-600 rounded-xl hover:bg-slate-50 transition flex-shrink-0 border border-slate-200 shadow-sm" title="Template (/)">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </button>
                                <div id="tpl-popup" class="hidden absolute bottom-full left-0 mb-3 w-64 bg-white border border-slate-200 rounded-xl shadow-2xl overflow-hidden z-50 flex flex-col max-h-60 animate-zoomIn ring-1 ring-black/5">
                                    <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex justify-between items-center">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Balas Cepat (Ketik /)</span>
                                        <button onclick="openTplManager()" class="text-[10px] text-blue-600 hover:text-blue-700 font-bold">ATUR</button>
                                    </div>
                                    <div id="tpl-list" class="overflow-y-auto custom-scrollbar flex-1"></div>
                                </div>
                            </div>

                            <div class="relative flex-1 bg-white rounded-xl border border-slate-300 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 shadow-sm transition">
                                <input type="file" id="admin-img-input" accept="image/*" style="display: none;" onchange="sendImageAdmin()">
                                <button onclick="document.getElementById('admin-img-input').click()" class="absolute left-2 bottom-2 p-2 text-slate-400 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition z-10" title="Upload Gambar">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </button>
                                <textarea id="msg-input" class="w-full bg-transparent text-slate-800 text-base md:text-sm px-4 py-3.5 pl-12 focus:outline-none resize-none custom-scrollbar rounded-xl placeholder-slate-400" rows="1" placeholder="Ketik pesan balasan..." style="min-height:48px; max-height:150px;"></textarea>
                            </div>

                            <button onclick="sendMessage()" class="bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition shadow-md shadow-blue-200 flex-shrink-0 h-[48px] w-[48px] flex items-center justify-center group">
                                <svg class="w-5 h-5 rotate-90 translate-x-0.5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            </button>
                        </div>

                        <div id="footer-locked" class="hidden w-full items-center justify-between bg-slate-100 border border-slate-200 rounded-xl p-3 px-4 border-dashed">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Tiket Selesai</span>
                            </div>
                            <button onclick="reopenSession()" class="flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold rounded-lg transition border border-slate-300 shadow-sm">
                                Buka Kembali
                            </button>
                        </div>
                    </div>
                </div>

                <div class="w-80 bg-white border-l border-slate-200 hidden lg:flex flex-col flex-shrink-0 overflow-y-auto custom-scrollbar shadow-sm">
                    
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Detail Pelanggan</h4>
                            <button onclick="openEditUser()" class="text-blue-600 hover:text-blue-800 transition p-1.5 bg-blue-50 rounded-lg hover:bg-blue-100 text-xs" title="Edit Data">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                        </div>
                        
                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 space-y-5">
                            <div class="flex items-center gap-3">
                                <div class="info-name font-bold text-slate-800 text-sm leading-tight">-</div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase mb-1">WhatsApp</div>
                                    <div class="info-phone text-sm font-mono text-blue-600 select-all border-b border-dashed border-slate-200 pb-1 cursor-pointer hover:text-blue-700">-</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase mb-1">Lokasi & IP</div>
                                    <div class="info-location text-sm text-slate-600 leading-snug mb-1">-</div>
                                    <div class="info-ip text-[10px] font-mono text-slate-500 bg-white inline-block px-1.5 py-0.5 rounded border border-slate-200">-</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase mb-1">Alamat</div>
                                    <div class="info-address text-sm text-slate-600 leading-relaxed bg-white p-2 rounded border border-slate-200">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 p-5 flex flex-col min-h-[150px]">
                        <div class="flex flex-col h-full bg-slate-50 rounded-xl border border-slate-200 p-1 hover:border-slate-300 transition relative focus-within:border-blue-200 focus-within:bg-white focus-within:ring-1 focus-within:ring-blue-100">
                            <label class="px-3 pt-3 pb-1 text-[10px] uppercase font-bold text-slate-400 tracking-widest flex justify-between items-center">
                                Catatan Internal
                                <span id="note-status" class="text-[9px] font-normal text-slate-400 transition-colors opacity-0">Saved</span>
                            </label>
                            <textarea id="customer-note" class="flex-1 w-full bg-transparent border-0 text-sm text-slate-700 focus:ring-0 p-3 pt-1 resize-none custom-scrollbar placeholder-slate-400 leading-relaxed" placeholder="Tulis catatan teknis (admin only)..."></textarea>
                        </div>
                    </div>

                    <div class="p-5 border-t border-slate-100 mt-auto bg-white">
                        <h4 class="text-[10px] uppercase font-bold text-slate-400 mb-3 tracking-widest">Aksi Cepat</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <button id="btn-end-session" onclick="openEndModal()" class="py-3 px-3 bg-green-50 hover:bg-green-100 text-green-700 text-xs font-bold rounded-xl border border-green-200 hover:border-green-300 transition flex items-center justify-center gap-1.5 group">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Selesai
                            </button>
                            <button id="btn-reopen-session" onclick="reopenSession()" class="hidden py-3 px-3 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl border border-blue-200 hover:border-blue-300 transition flex items-center justify-center gap-1.5 group">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Proses
                            </button>
                            <button onclick="deleteSession()" class="py-3 px-3 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold rounded-xl border border-red-200 hover:border-red-300 transition flex items-center justify-center gap-1.5 group">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Hapus
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div id="modal-edit-user" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm z-10 transition-opacity" onclick="document.getElementById('modal-edit-user').classList.add('hidden')"></div>
        <div class="bg-white border border-slate-200 w-full max-w-sm rounded-2xl p-6 shadow-2xl relative z-20 animate-zoomIn">
            <h3 class="font-bold text-slate-800 mb-5 flex items-center gap-2 text-lg">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg> 
                Edit Pelanggan
            </h3>
            <div class="space-y-4">
                <input type="hidden" id="edit-visit-id">
                <div>
                    <label class="text-xs text-slate-500 uppercase font-bold mb-1 block">Nama Lengkap</label>
                    <input type="text" id="edit-name" autocomplete="off" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-slate-800 text-sm focus:border-blue-500 outline-none transition shadow-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-500 uppercase font-bold mb-1 block">Nomor HP</label>
                    <input type="text" id="edit-phone" autocomplete="off" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-slate-800 text-sm focus:border-blue-500 outline-none transition shadow-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-500 uppercase font-bold mb-1 block">Alamat</label>
                    <textarea id="edit-addr" rows="3" autocomplete="off" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-slate-800 text-sm focus:border-blue-500 outline-none resize-none transition shadow-sm"></textarea>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 pt-6">
                <button onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="py-2.5 bg-white text-slate-600 rounded-xl hover:bg-slate-50 text-sm font-bold border border-slate-300 transition">Batal</button>
                <button onclick="saveEditUser()" class="py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-bold shadow-md shadow-blue-200 transition">Simpan Perubahan</button>
            </div>
        </div>
    </div>

    <div id="modal-detail" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm z-10 transition-opacity" onclick="closeDetail()"></div>
        <div class="bg-white border border-slate-200 w-full max-w-sm rounded-2xl p-6 shadow-2xl relative z-20 animate-zoomIn">
            <button onclick="closeDetail()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">✕</button>
            <h3 class="font-bold text-lg text-slate-800 mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> 
                Info Pengunjung
            </h3>
            <div class="space-y-4 text-sm">
                <div>
                    <div class="text-xs text-slate-500 uppercase font-bold mb-1">Nama</div>
                    <div class="info-name text-slate-800 bg-slate-50 p-2.5 rounded-lg border border-slate-200">-</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500 uppercase font-bold mb-1">Nomor HP</div>
                    <div class="info-phone text-blue-600 bg-slate-50 p-2.5 rounded-lg border border-slate-200 font-mono select-all cursor-pointer">-</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500 uppercase font-bold mb-1">Alamat</div>
                    <div class="info-address text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-200 leading-relaxed">-</div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="text-xs text-slate-500 uppercase font-bold mb-1">Lokasi</div>
                        <div class="info-location text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-xs h-full">-</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase font-bold mb-1">IP Address</div>
                        <div class="info-ip text-slate-500 bg-slate-50 p-2.5 rounded-lg border border-slate-200 font-mono text-xs h-full">-</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-tpl-manager" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm z-10 transition-opacity" onclick="document.getElementById('modal-tpl-manager').classList.add('hidden')"></div>
        <div class="bg-white border border-slate-200 w-full max-w-md rounded-2xl p-0 shadow-2xl relative flex flex-col max-h-[85vh] z-20 animate-zoomIn">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center shrink-0">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <span class="text-yellow-500">⚡</span> Atur Balas Cepat
                </h3>
                <button onclick="document.getElementById('modal-tpl-manager').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <div id="manager-list" class="p-5 overflow-y-auto custom-scrollbar space-y-3 flex-1">
                </div>
            <div class="p-5 border-t border-slate-100 bg-slate-50 shrink-0">
                <button onclick="openFormTpl()" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition text-sm flex items-center justify-center gap-2 shadow-lg shadow-blue-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> 
                    Tambah Template Baru
                </button>
            </div>
        </div>
    </div>

    <div id="modal-form-tpl" class="hidden fixed inset-0 z-[70] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm z-10 transition-opacity" onclick="document.getElementById('modal-form-tpl').classList.add('hidden')"></div>
        <div class="bg-white border border-slate-200 w-full max-w-sm rounded-2xl p-6 shadow-2xl relative z-20 animate-zoomIn">
            <h3 class="font-bold text-slate-800 mb-4">Tambah Template</h3>
            <div class="space-y-4">
                <div>
                    <label class="text-xs text-slate-500 uppercase font-bold mb-1 block">Judul Singkat (/Slash)</label>
                    <input type="text" id="tpl-label" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 text-base md:text-sm focus:border-blue-500 outline-none" placeholder="Misal: salam">
                </div>
                <div>
                    <label class="text-xs text-slate-500 uppercase font-bold mb-1 block">Isi Pesan</label>
                    <textarea id="tpl-msg" rows="4" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 text-base md:text-sm focus:border-blue-500 outline-none resize-none" placeholder="Halo kak, ada yang bisa dibantu?"></textarea>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 pt-4">
                <button onclick="document.getElementById('modal-form-tpl').classList.add('hidden'); document.getElementById('modal-tpl-manager').classList.remove('hidden');" class="py-2.5 bg-white text-slate-600 rounded-xl hover:bg-slate-50 text-sm font-bold border border-slate-300 transition">Batal</button>
                <button onclick="saveTemplate()" class="py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 text-sm font-bold shadow-lg shadow-green-200">Simpan</button>
            </div>
        </div>
    </div>
    
    <div id="modal-admin-profile" class="hidden fixed inset-0 z-[80] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm z-10 transition-opacity" onclick="document.getElementById('modal-admin-profile').classList.add('hidden')"></div>
        <div class="bg-white border border-slate-200 w-full max-w-sm rounded-2xl p-6 shadow-2xl relative z-20 animate-zoomIn">
            <h3 class="font-bold text-slate-800 mb-5 flex items-center gap-2 text-lg">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg> 
                Pengaturan Profil
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="text-xs text-slate-500 uppercase font-bold mb-1 block">Nama Lengkap</label>
                    <input type="text" id="adm-name" autocomplete="off" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-slate-800 text-sm focus:border-blue-500 outline-none transition shadow-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-500 uppercase font-bold mb-1 block">Username</label>
                    <input type="text" id="adm-username" autocomplete="off" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-slate-800 text-sm focus:border-blue-500 outline-none transition shadow-sm">
                </div>
                <div class="pt-2 border-t border-slate-100 mt-2">
                    <label class="text-xs text-yellow-600 uppercase font-bold mb-1 block">Ganti Password (Opsional)</label>
                    <input type="password" id="adm-password" placeholder="Kosongkan jika tidak ingin ubah" autocomplete="off" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-slate-800 text-sm focus:border-blue-500 outline-none transition shadow-sm placeholder-slate-400">
                    <p class="text-[10px] text-slate-400 mt-1">Minimal 5 karakter jika ingin mengubah password.</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 pt-6">
                <button onclick="document.getElementById('modal-admin-profile').classList.add('hidden')" class="py-2.5 bg-white text-slate-600 rounded-xl hover:bg-slate-50 text-sm font-bold border border-slate-300 transition">Batal</button>
                <button onclick="saveAdminProfile()" class="py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-bold shadow-md shadow-blue-200 transition">Simpan</button>
            </div>
        </div>
    </div>

    <div id="img-viewer" class="fixed inset-0 z-[100] bg-slate-900/95 hidden flex flex-col justify-center items-center p-2 backdrop-blur-sm transition-opacity duration-300">
        <div class="absolute top-4 right-4 flex gap-4 z-20">
            <a id="img-download-btn" href="#" download class="text-slate-300 hover:text-white transition p-3 bg-white/10 rounded-full hover:bg-white/20" title="Download">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            </a>
            <button onclick="closeImageViewer()" class="text-slate-300 hover:text-red-500 transition p-3 bg-white/10 rounded-full hover:bg-white/20" title="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <img id="img-viewer-src" src="" class="max-w-full max-h-[90dvh] rounded-lg shadow-2xl object-contain animate-zoomIn border border-white/10">
        <div onclick="closeImageViewer()" class="absolute inset-0 -z-10 cursor-zoom-out"></div>
    </div>
    
    <script src="app.js?v=<?php echo time(); ?>"></script>
    <script>
        // Close dropdowns when clicking outside
        window.onclick = function(event) { 
            if (!event.target.closest('#dropdown-sidebar-container')) { 
                const s = document.getElementById('sidebar-menu'); 
                if (s && !s.classList.contains('hidden')) s.classList.add('hidden'); 
            } 
            if (!event.target.closest('#dropdown-chat-container')) { 
                const c = document.getElementById('chat-menu'); 
                if (c && !c.classList.contains('hidden')) c.classList.add('hidden'); 
            } 
            if (!event.target.closest('#dropdown-tpl-container')) { 
                const t = document.getElementById('tpl-popup'); 
                if (t && !t.classList.contains('hidden')) t.classList.add('hidden'); 
            } 
        }
    </script>
</body>
</html>