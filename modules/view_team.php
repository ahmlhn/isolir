<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Manajemen User</h1>
            <p class="text-slate-400 text-sm">Kelola akses login admin dan staff.</p>
        </div>
        <button onclick="openModalTeam()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            TAMBAH USER
        </button>
    </div>

    <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-900 text-slate-400 text-xs uppercase font-bold">
                <tr>
                    <th class="py-4 px-6">User Info</th>
                    <th class="py-4 px-6">Role</th>
                    <th class="py-4 px-6">Kontak</th>
                    <th class="py-4 px-6">Status</th>
                    <th class="py-4 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="team-list" class="text-sm divide-y divide-slate-700">
                </tbody>
        </table>
    </div>
</div>

<div id="modal-team" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeModalTeam()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-slate-800 w-full max-w-md rounded-xl border border-slate-700 shadow-2xl p-6">
        <h3 class="text-xl font-bold text-white mb-4" id="modal-title">Tambah User</h3>
        <form id="form-team" onsubmit="saveTeam(event)">
            <input type="hidden" name="id" id="inp-id">
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Username (Login)</label>
                    <input type="text" name="username" id="inp-username" required class="w-full bg-slate-900 border border-slate-700 text-white px-3 py-2 rounded focus:border-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" id="inp-name" required class="w-full bg-slate-900 border border-slate-700 text-white px-3 py-2 rounded focus:border-blue-500 text-sm">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs text-slate-400 mb-1">Password</label>
                <input type="password" name="password" id="inp-password" class="w-full bg-slate-900 border border-slate-700 text-white px-3 py-2 rounded focus:border-blue-500 text-sm" placeholder="Kosongkan jika tidak ingin mengubah password">
                <p class="text-[10px] text-slate-500 mt-1">* Wajib diisi untuk user baru</p>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Role / Jabatan</label>
                    <select name="role" id="inp-role" class="w-full bg-slate-900 border border-slate-700 text-white px-3 py-2 rounded focus:border-blue-500 text-sm">
                        <option value="admin">Administrator</option>
                        <option value="cs">Customer Service</option>
                        <option value="teknisi">Teknisi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Status</label>
                    <select name="status" id="inp-status" class="w-full bg-slate-900 border border-slate-700 text-white px-3 py-2 rounded focus:border-blue-500 text-sm">
                        <option value="active">Aktif</option>
                        <option value="inactive">Non-Aktif</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Email</label>
                    <input type="email" name="email" id="inp-email" class="w-full bg-slate-900 border border-slate-700 text-white px-3 py-2 rounded focus:border-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">WhatsApp</label>
                    <input type="text" name="phone" id="inp-phone" class="w-full bg-slate-900 border border-slate-700 text-white px-3 py-2 rounded focus:border-blue-500 text-sm">
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModalTeam()" class="px-4 py-2 text-slate-400 hover:text-white text-sm font-bold">BATAL</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-lg text-sm font-bold shadow-lg">SIMPAN</button>
            </div>
        </form>
    </div>
</div>
<script src="modules/js/team.js"></script>