<?php
// FILE: isolir/dashboard.php
// VERSI: FULL HEIGHT LAYOUT (FIX SINGLE SCREEN)
session_start();
require 'config.php';

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$page = $_GET['page'] ?? 'home'; 
$user_level = $_SESSION['level'] ?? 'admin'; 

$allowed_pages = [
    'home'      => 'modules/view_home.php',
    'leads'     => 'modules/view_leads.php',
    'team'      => 'modules/view_team.php',
    'setting'   => 'modules/view_setting.php'
];

$view_file = $allowed_pages[$page] ?? 'modules/view_home.php';

// AJAX Handler
if (isset($_GET['ajax'])) {
    if (file_exists($view_file)) {
        include $view_file;
    } else {
        echo "<div class='p-10 text-red-500'>Error: Modul $page tidak ditemukan.</div>";
    }
    exit; 
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Admin</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class', 
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* TRANSISI HALUS */
        body, aside, div, span, h1, h2, h3, p, a, button, input {
            transition-property: background-color, border-color, color;
            transition-duration: 200ms;
        }

        /* CUSTOM SCROLLBAR */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .fade-in { animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* UTILITY */
        .glass-card {
            @apply bg-white dark:bg-[#1e293b]/70 backdrop-blur-xl border border-slate-200 dark:border-white/5 shadow-sm dark:shadow-none;
        }
    </style>
    
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>
<body class="bg-[#f8fafc] dark:bg-[#0f172a] text-slate-800 dark:text-slate-200 overflow-hidden h-screen w-full fixed selection:bg-blue-500 selection:text-white">

    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none hidden dark:block">
        <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] opacity-40"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-purple-600/20 rounded-full blur-[120px] opacity-40"></div>
    </div>

    <div class="flex h-full w-full relative overflow-hidden">
        
        <?php include 'modules/sidebar.php'; ?>
        
        <div class="md:hidden fixed top-4 left-4 right-4 z-50 flex justify-between items-center pointer-events-none">
            <button onclick="toggleSidebar()" class="pointer-events-auto text-slate-600 dark:text-white p-2.5 bg-white/80 dark:bg-slate-800/80 backdrop-blur-md rounded-xl border border-slate-200 dark:border-white/10 shadow-lg active:scale-95 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
            </button>
            <button onclick="toggleTheme()" class="pointer-events-auto text-slate-600 dark:text-yellow-400 p-2.5 bg-white/80 dark:bg-slate-800/80 backdrop-blur-md rounded-xl border border-slate-200 dark:border-white/10 shadow-lg active:scale-95 transition-all">
                 <svg class="w-6 h-6 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                 <svg class="w-6 h-6 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </button>
        </div>

        <main class="flex-1 h-full relative w-full flex flex-col overflow-hidden">
            <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden md:hidden transition-opacity"></div>

            <div id="page-container" class="flex-1 w-full h-full overflow-y-auto custom-scrollbar flex flex-col scroll-smooth">
                <div class="w-full max-w-7xl mx-auto flex-1 flex flex-col p-4 md:p-6 mt-16 md:mt-0 fade-in">
                    <?php include $view_file; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            // Update chart theme jika ada
            if (typeof initChart === 'function') setTimeout(initChart, 100);
        }

        async function loadPage(page) {
            const container = document.querySelector('#page-container > div');
            
            // Loader UI
            container.innerHTML = '<div class="flex items-center justify-center h-full min-h-[400px]"><div class="relative"><div class="w-12 h-12 border-4 border-slate-200 dark:border-slate-700 rounded-full"></div><div class="w-12 h-12 border-4 border-blue-500 rounded-full border-t-transparent animate-spin absolute top-0 left-0"></div></div></div>';

            // Auto close sidebar mobile
            const sidebar = document.getElementById('sidebar');
            if (!sidebar.classList.contains('-translate-x-full') && window.innerWidth < 768) {
                toggleSidebar();
            }

            try {
                const response = await fetch(`dashboard.php?page=${page}&ajax=1`);
                if (!response.ok) throw new Error('Halaman tidak ditemukan');
                const html = await response.text();
                
                container.innerHTML = html;
                window.history.pushState({page: page}, '', `dashboard.php?page=${page}`);
                updateActiveMenu(page);
                reinitPageScripts(page);
            } catch (error) {
                container.innerHTML = `<div class="p-6 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl text-red-600 dark:text-red-400">Error: ${error.message}</div>`;
            }
        }

        function reinitPageScripts(page) {
            if (page === 'home') {
                if (typeof initChart === 'function') initChart();
                if (typeof loadDashboardData === 'function') loadDashboardData();
            } else if (page === 'leads') {
                if (typeof loadLeads === 'function') loadLeads();
            } else if (page === 'team') {
                if (typeof loadTeam === 'function') loadTeam();
            } else if (page === 'setting') {
                if (typeof loadSettings === 'function') loadSettings();
            }
        }

        function updateActiveMenu(page) {
            const allMenus = document.querySelectorAll('.menu-link');
            allMenus.forEach(link => {
                const menuPage = link.getAttribute('data-page');
                if (menuPage === page) {
                    link.className = 'menu-link flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 bg-blue-600 text-white shadow-lg shadow-blue-500/30';
                } else {
                    link.className = 'menu-link flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white';
                }
            });
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full'); 
            overlay.classList.toggle('hidden');
        }

        window.onpopstate = (e) => {
            if(e.state && e.state.page) loadPage(e.state.page);
        };
    </script>

    <script src="modules/js/dashboard.js?v=<?php echo time(); ?>"></script>
    <script src="modules/js/leads.js?v=<?php echo time(); ?>"></script>
    <script src="modules/js/team.js?v=<?php echo time(); ?>"></script>
    <script src="modules/js/setting.js?v=<?php echo time(); ?>"></script>
</body>
</html>