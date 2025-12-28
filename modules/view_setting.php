<div id="view-setting" class="h-full w-full overflow-y-auto bg-[#0b141a] custom-scrollbar">
    
    <div class="p-4 md:p-8 pt-16 md:pt-8 w-full max-w-6xl mx-auto pb-20">
        
        <div class="mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-white tracking-tight">System Setting</h2>
            <p class="text-slate-400 text-sm mt-1">Konfigurasi Gateway WhatsApp, Telegram Backup, dan Template Pesan.</p>
        </div>

        <form id="form-settings" autocomplete="off">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-5 space-y-6">
                    
                    <div class="bg-slate-800/40 rounded-2xl border border-slate-700/50 overflow-hidden flex flex-col backdrop-blur-sm">
                        <div class="p-5 border-b border-slate-700/50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-200 text-sm">WhatsApp</h4>
                                    <p class="text-slate-500 text-[10px] uppercase font-bold tracking-wider">Gateway Utama</p>
                                </div>
                            </div>
                            
                            <label class="cursor-pointer flex items-center gap-3 group">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Off</span>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" id="wa_active" name="wa_active" value="1" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 shadow-inner"></div>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-500/50 peer-checked:text-emerald-400 transition">On</span>
                            </label>
                        </div>

                        <div class="p-6 space-y-5">
                            <div class="relative group">
                                <input type="text" id="wa_url" autocomplete="off" placeholder="https://..." class="w-full bg-slate-950/50 border border-slate-700 rounded-lg pl-3 pr-4 py-3 text-white text-sm focus:border-emerald-500 focus:outline-none transition-all">
                                <label class="absolute -top-2 left-3 bg-[#131d26] px-1 text-[10px] font-bold text-slate-400 uppercase">API URL</label>
                            </div>
                            <div class="relative group">
                                <input type="text" id="wa_token" autocomplete="new-password" placeholder="Key/Token..." class="w-full bg-slate-950/50 border border-slate-700 rounded-lg pl-3 pr-4 py-3 text-white text-sm focus:border-emerald-500 focus:outline-none transition-all font-mono">
                                <label class="absolute -top-2 left-3 bg-[#131d26] px-1 text-[10px] font-bold text-slate-400 uppercase">API Token</label>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="relative group">
                                    <input type="text" id="wa_sender" placeholder="628..." class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-3 text-white text-sm focus:border-emerald-500 focus:outline-none transition-all">
                                    <label class="absolute -top-2 left-3 bg-[#131d26] px-1 text-[10px] font-bold text-slate-500 uppercase">Sender</label>
                                </div>
                                <div class="relative group">
                                    <input type="text" id="wa_target" placeholder="628..." class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-3 py-3 text-white text-sm focus:border-emerald-500 focus:outline-none transition-all">
                                    <label class="absolute -top-2 left-3 bg-[#131d26] px-1 text-[10px] font-bold text-slate-500 uppercase">CS Target</label>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <button type="button" onclick="testConnection('wa')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-lg transition border border-slate-700">Tes Koneksi</button>
                                <button type="button" onclick="saveSettings('wa')" class="py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg transition shadow-lg shadow-emerald-900/20">Simpan WA</button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-800/40 rounded-2xl border border-slate-700/50 overflow-hidden flex flex-col backdrop-blur-sm">
                        <div class="p-4 border-b border-slate-700/50 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center border border-sky-500/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-200 text-sm">Telegram Backup</h4>
                                <p class="text-slate-500 text-[10px] uppercase font-bold tracking-wider">Aktif jika WA Gagal</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="relative group">
                                <input type="text" id="tg_bot_token" autocomplete="new-password" placeholder="12345:Abc..." class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-white text-sm focus:border-sky-500 focus:outline-none transition-all font-mono">
                                <label class="absolute -top-2 left-3 bg-[#131d26] px-1 text-[10px] font-bold text-slate-500 uppercase">Bot Token</label>
                            </div>
                            <div class="relative group">
                                <input type="text" id="tg_chat_id" placeholder="-100..." class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-white text-sm focus:border-sky-500 focus:outline-none transition-all">
                                <label class="absolute -top-2 left-3 bg-[#131d26] px-1 text-[10px] font-bold text-slate-500 uppercase">Chat ID</label>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <button type="button" onclick="testConnection('tg')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-lg transition border border-slate-700">Tes Kirim</button>
                                <button type="button" onclick="saveSettings('tg')" class="py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-lg transition shadow-lg shadow-sky-900/20">Simpan TG</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-slate-800/40 rounded-2xl border border-slate-700/50 flex flex-col h-full relative overflow-hidden backdrop-blur-sm">
                        
                        <div class="p-5 border-b border-slate-700/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center border border-purple-500/20">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-white text-sm">Editor Template</h4>
                                    <p class="text-slate-400 text-xs">Atur format pesan otomatis.</p>
                                </div>
                            </div>
                            <div class="relative w-full md:w-64 group">
                                <select id="tpl_selector" onchange="changeTemplate()" class="appearance-none w-full bg-slate-900 text-white text-sm font-bold border border-slate-600 rounded-lg py-2.5 pl-4 pr-10 focus:outline-none focus:border-purple-500 cursor-pointer hover:bg-slate-800 transition">
                                    <option value="web_welcome">🤖 Auto Greeting (Web)</option>
                                    <option value="wa_login">🔔 Notifikasi Login (WA)</option>
                                    <option value="wa_chat">💬 Notifikasi Chat (WA)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col relative">
                            <span id="tpl_desc" class="text-xs font-bold text-purple-400 uppercase tracking-wide mb-3 block">...</span>

                            <textarea id="tpl_editor" class="flex-1 w-full bg-[#0d151c] border border-slate-700/80 rounded-xl p-5 text-sm font-mono text-slate-200 leading-relaxed focus:outline-none focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/50 resize-none transition shadow-inner mb-6 min-h-[200px]" placeholder="Memuat..."></textarea>
                            
                            <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700/50 mb-6">
                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-3 flex items-center gap-2">Variabel Tersedia (Klik untuk salin)</p>
                                <div id="var_container" class="flex flex-wrap gap-2"></div>
                            </div>

                            <button type="button" onclick="saveSettings('tpl')" class="w-full py-3.5 bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-500 hover:to-purple-400 text-white text-xs uppercase font-bold rounded-xl transition shadow-lg shadow-purple-500/20">Update Template</button>
                            
                            <input type="hidden" id="hidden_web_welcome">
                            <input type="hidden" id="hidden_wa_login">
                            <input type="hidden" id="hidden_wa_chat">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <div class="mt-8 pt-8 border-t border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-200 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Riwayat Pengiriman
                </h3>
                <button onclick="loadLogs()" class="text-xs flex items-center gap-1 text-blue-400 hover:text-white transition bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700">Refresh Log</button>
            </div>
            <div class="bg-slate-800/40 rounded-xl border border-slate-700/50 overflow-hidden">
                <div class="overflow-x-auto max-h-64 custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-800/80 backdrop-blur-sm text-slate-400 text-[10px] uppercase font-bold sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-3">Waktu</th>
                                <th class="px-6 py-3">Tipe</th>
                                <th class="px-6 py-3">Target</th>
                                <th class="px-6 py-3">Pesan</th>
                                <th class="px-6 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="wa-log-body" class="divide-y divide-slate-700/30 text-xs text-slate-300">
                            <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">Memuat log...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>