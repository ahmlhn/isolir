<div class="space-y-6 h-full flex flex-col">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 shrink-0">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Realtime Overview</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Memantau aktivitas jaringan dan pelanggan.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <select id="filter-period" onchange="loadDashboardData()" class="appearance-none bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-lg py-2.5 pl-10 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500/50 cursor-pointer shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    <option value="today">Hari Ini</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="7days">7 Hari Terakhir</option>
                    <option value="30days">30 Hari Terakhir</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            <span class="px-3 py-1 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold text-slate-600 dark:text-slate-300 shadow-sm flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                LIVE
            </span>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 shrink-0">
        
        <div class="glass-card p-4 rounded-xl flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-3 opacity-10 group-hover:opacity-20 transition">
                <svg class="w-16 h-16 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41C17.92 5.77 20 8.65 20 12c0 2.08-.8 3.97-2.1 5.39z"/></svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sedang Online</p>
            <div class="flex items-baseline gap-1 mt-1">
                <h3 id="val-online" class="text-2xl font-black text-slate-800 dark:text-white">0</h3>
                <span class="text-[10px] text-green-500 font-bold">User</span>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-3 opacity-10 group-hover:opacity-20 transition">
                <svg class="w-16 h-16 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pengunjung Unik</p>
            <div class="flex items-baseline gap-1 mt-1">
                <h3 id="val-unique" class="text-2xl font-black text-slate-800 dark:text-white">0</h3>
                <span class="text-[10px] text-blue-500 font-bold">IP</span>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-3 opacity-10 group-hover:opacity-20 transition">
                <svg class="w-16 h-16 text-purple-500" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Leads Baru</p>
            <div class="flex items-baseline gap-1 mt-1">
                <h3 id="val-leads" class="text-2xl font-black text-slate-800 dark:text-white">0</h3>
                <span class="text-[10px] text-purple-500 font-bold">Data</span>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-3 opacity-10 group-hover:opacity-20 transition">
                <svg class="w-16 h-16 text-orange-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Hits</p>
            <div class="flex items-baseline gap-1 mt-1">
                <h3 id="val-hits" class="text-2xl font-black text-slate-800 dark:text-white">0</h3>
                <span class="text-[10px] text-orange-500 font-bold">View</span>
            </div>
        </div>
    </div>

    <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-3 gap-4 pb-4">
        
        <div class="lg:col-span-2 glass-card rounded-xl p-4 flex flex-col shadow-sm dark:shadow-none">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    Tren Aktivitas
                </h3>
                <button onclick="loadDashboardData()" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition" title="Refresh">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
            <div class="flex-1 w-full min-h-0 relative">
                <div id="chart-activity" class="absolute inset-0"></div>
            </div>
        </div>

        <div class="glass-card rounded-xl flex flex-col overflow-hidden shadow-sm dark:shadow-none">
            <div class="p-4 border-b border-slate-200 dark:border-white/5 bg-slate-50/50 dark:bg-white/5">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Log Aktivitas (Filter)
                </h3>
            </div>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar p-0">
                <table class="w-full text-left border-collapse">
                    <tbody id="log-list-body" class="divide-y divide-slate-100 dark:divide-white/5">
                        <tr><td class="p-4 text-center text-xs text-slate-400">Memuat log...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>