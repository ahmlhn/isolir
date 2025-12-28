<?php
// FILE: modules/sidebar.php
// VERSI: THEME SWITCHER READY
$level = isset($user_level) ? $user_level : ($_SESSION['level'] ?? 'staff');
$page  = $_GET['page'] ?? 'home'; 

function navClass($menu_name, $current_page) {
    if ($menu_name == $current_page) {
        return 'bg-blue-600 text-white shadow-lg shadow-blue-500/30';
    }
    // Hover: bg-slate-100 (Light Mode) | bg-white/5 (Dark Mode)
    return 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white';
}
?>

<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-[#0b141a]/95 backdrop-blur-xl border-r border-slate-200 dark:border-white/5 flex flex-col transition-transform duration-300 transform -translate-x-full md:translate-x-0 md:static h-screen shadow-xl dark:shadow-none">
    
    <div class="h-24 flex items-center px-8 border-b border-slate-100 dark:border-white/5 shrink-0">
        <a href="dashboard.php" class="flex items-center gap-4 group w-full">
            <div class="relative">
                <div class="absolute inset-0 bg-blue-500 blur-lg opacity-20 group-hover:opacity-40 transition"></div>
                <img src="assets/favicon.svg" alt="Logo" class="relative w-10 h-10 drop-shadow-md group-hover:scale-110 transition-transform duration-300">
            </div>
            <div class="flex flex-col">
                <h1 class="text-xl font-black text-slate-800 dark:text-white tracking-tight leading-none">
                    DARAT<span class="text-blue-600 dark:text-blue-500">LAUT</span>
                </h1>
                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold tracking-widest mt-1.5 uppercase">Network System</span>
            </div>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 custom-scrollbar">
        
        <div class="px-4 mb-2 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Main Menu</div>
        
        <a href="javascript:void(0)" onclick="loadPage('home')" data-page="home" class="menu-link flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 <?php echo navClass('home', $page); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            <span class="font-medium text-sm">Dashboard</span>
        </a>
        
        <?php if($level == 'admin' || $level == 'cs'): ?>
        <a href="chat/index.php" target="_blank" class="flex items-center gap-3 px-4 py-3.5 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white rounded-xl transition-all duration-300 group">
            <div class="relative">
                <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                </span>
                <svg class="w-5 h-5 group-hover:text-green-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <span class="font-medium text-sm">Live Chat</span>
        </a>
        <?php endif; ?>

        <a href="javascript:void(0)" onclick="loadPage('leads')" data-page="leads" class="menu-link flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 <?php echo navClass('leads', $page); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span class="font-medium text-sm">Data Pelanggan</span>
        </a>

        <?php if($level == 'admin'): ?>
        <div class="px-4 mt-6 mb-2 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Administrator</div>
        
        <a href="javascript:void(0)" onclick="loadPage('team')" data-page="team" class="menu-link flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 <?php echo navClass('team', $page); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span class="font-medium text-sm">Manajemen Tim</span>
        </a>

        <a href="javascript:void(0)" onclick="loadPage('setting')" data-page="setting" class="menu-link flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 <?php echo navClass('setting', $page); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="font-medium text-sm">Pengaturan</span>
        </a>
        <?php endif; ?>

        <div class="px-4 mt-6 mb-2 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">System</div>
        <a href="login.php?action=logout" class="flex items-center gap-3 px-4 py-3.5 text-slate-500 dark:text-slate-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all hover:pl-6 group">
            <svg class="w-5 h-5 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3 3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            <span class="font-medium text-sm">Logout</span>
        </a>
    </nav>

    <div class="p-4 border-t border-slate-200 dark:border-white/5 bg-slate-50 dark:bg-[#0b141a]/50 flex flex-col gap-3">
        
        <button onclick="toggleTheme()" class="hidden md:flex items-center justify-between w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300 hover:border-blue-500 dark:hover:border-blue-500 transition-colors group">
            <span class="group-hover:text-blue-600 dark:group-hover:text-blue-400">GANTI TEMA</span>
            <div class="relative w-5 h-5">
                 <svg class="w-5 h-5 absolute inset-0 transform transition-transform duration-500 rotate-0 dark:rotate-90 dark:opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                 <svg class="w-5 h-5 absolute inset-0 transform transition-transform duration-500 -rotate-90 opacity-0 dark:rotate-0 dark:opacity-100 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </div>
        </button>

        <div class="flex items-center gap-4 p-3 rounded-xl bg-white dark:bg-white/5 hover:bg-white border border-slate-200 dark:border-white/5 transition shadow-sm dark:shadow-none">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold text-base shadow-md">
                <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
            </div>
            <div class="min-w-0">
                <div class="text-sm font-bold text-slate-700 dark:text-white truncate"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></div>
                <div class="text-[11px] text-green-600 dark:text-green-400 flex items-center gap-1.5 font-medium uppercase tracking-wide">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    <?php echo htmlspecialchars($level); ?>
                </div>
            </div>
        </div>
    </div>
</aside>