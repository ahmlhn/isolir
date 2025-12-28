<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Data Pelanggan (Leads)</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Daftar pelanggan yang mengisi form di halaman isolir.</p>
        </div>
        
        <div class="flex gap-2">
            <button onclick="loadLeads()" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-4 py-2.5 rounded-xl font-medium text-sm transition shadow-lg shadow-blue-500/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh Data
            </button>
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden shadow-sm dark:shadow-none">
        <div class="p-4 border-b border-slate-200 dark:border-white/5 bg-slate-50 dark:bg-white/5 flex items-center gap-3">
             <svg class="w-5 h-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
             <input type="text" placeholder="Cari data pelanggan..." class="bg-transparent border-none outline-none text-sm text-slate-700 dark:text-white w-full placeholder-slate-400 dark:placeholder-slate-500" disabled>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-800/50 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-white/5">
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Nama Pelanggan</th>
                        <th class="px-6 py-4">Kontak</th>
                        <th class="px-6 py-4">Alamat</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="leads-body" class="divide-y divide-slate-200 dark:divide-white/5 text-sm text-slate-600 dark:text-slate-300">
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500 flex flex-col items-center">
                            <div class="w-8 h-8 border-2 border-slate-300 dark:border-slate-600 border-t-blue-500 rounded-full animate-spin mb-2"></div>
                            Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-slate-200 dark:border-white/5 flex justify-between items-center bg-slate-50 dark:bg-white/5">
            <span class="text-xs text-slate-500 dark:text-slate-500">Menampilkan 10 data terakhir</span>
            <div class="flex gap-1">
                <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-400 transition" disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                 <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-400 transition" disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>