// FILE: isolir/modules/js/main.js
// VERSI: FIX LOAD DATA (Leads, Team, Setting)

document.addEventListener("DOMContentLoaded", () => {
    loadPage('home'); // Load default
    
    // Auto close sidebar mobile
    if (window.innerWidth < 768) {
        const sb = document.getElementById('sidebar');
        if(sb) sb.classList.add('-translate-x-full');
    }
});

function loadPage(page) {
    const container = document.getElementById('page-container');
    const sidebar = document.getElementById('sidebar');
    
    // 1. Tampilkan Loading Spinner
    container.innerHTML = `
        <div class="flex items-center justify-center h-full">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
        </div>`;

    // 2. Update Style Sidebar (Active State)
    document.querySelectorAll('.nav-item').forEach(el => {
        el.classList.remove('bg-slate-700/50', 'text-white', 'border-r-4', 'border-blue-500');
        el.classList.add('text-slate-300');
        if(el.getAttribute('onclick') && el.getAttribute('onclick').includes(`'${page}'`)) {
            el.classList.add('bg-slate-700/50', 'text-white', 'border-r-4', 'border-blue-500');
            el.classList.remove('text-slate-300');
        }
    });

    // 3. Ambil Halaman HTML
    fetch(`modules/view_${page}.php`)
    .then(response => {
        if (!response.ok) throw new Error("Halaman tidak ditemukan");
        return response.text();
    })
    .then(html => {
        container.innerHTML = html;

        // --- BAGIAN INI YANG MEMPERBAIKI MASALAH ANDA ---
        // Browser butuh waktu sedikit untuk merender HTML baru, 
        // jadi kita beri jeda 100ms sebelum memanggil fungsi datanya.
        
        setTimeout(() => {
            switch(page) {
                case 'home':
                    if(typeof initChart === 'function') initChart();
                    if(typeof loadDashboardData === 'function') loadDashboardData();
                    break;
                
                case 'leads': // Data Pelanggan
                    if(typeof loadLeads === 'function') loadLeads();
                    else console.error("Fungsi loadLeads tidak ditemukan! Cek dashboard.php");
                    break;

                case 'team': // Manajemen User
                    if(typeof loadTeam === 'function') loadTeam();
                    else console.error("Fungsi loadTeam tidak ditemukan! Cek dashboard.php");
                    break;

                case 'setting': // Pengaturan
                    if(typeof loadSettings === 'function') loadSettings();
                    else console.error("Fungsi loadSettings tidak ditemukan! Cek dashboard.php");
                    break;

                case 'chat': // Live Chat
                    // Chat biasanya punya inisialisasi sendiri di view_chat.php atau script terpisah
                    break;
            }
        }, 100); 

        // 4. Tutup Sidebar di HP
        if (window.innerWidth < 768 && sidebar) {
            sidebar.classList.add('-translate-x-full');
        }
    })
    .catch(err => {
        container.innerHTML = `<div class="p-10 text-center text-red-400">Error: ${err.message}</div>`;
    });
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if(sidebar) sidebar.classList.toggle('-translate-x-full');
}