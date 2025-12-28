// FILE: isolir/modules/js/team.js
// VERSI: FIX ERROR DASHBOARD (Safety Check Added)

document.addEventListener("DOMContentLoaded", () => {
    // PENGECEKAN PENTING:
    // Hanya jalankan loadTeam jika elemen tabel 'team-list' ditemukan di halaman ini.
    if (document.getElementById('team-list')) {
        loadTeam();
    }
});

function loadTeam() {
    const tbody = document.getElementById('team-list');
    
    // Safety check tambahan: Jika tbody null, hentikan fungsi agar tidak error
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-8 text-slate-500">Memuat data...</td></tr>';

    fetch('api_team.php?action=list')
    .then(r => r.json())
    .then(data => {
        // Cek lagi sebelum set innerHTML (berjaga-jaga jika user pindah halaman saat loading)
        const currentTbody = document.getElementById('team-list');
        if (!currentTbody) return;

        currentTbody.innerHTML = '';
        if(data.length === 0) {
            currentTbody.innerHTML = '<tr><td colspan="5" class="text-center py-8 text-slate-500">Belum ada user</td></tr>';
            return;
        }
        
        data.forEach(u => {
            // Badge Role
            let roleBadge = `<span class="px-2 py-1 rounded text-xs font-bold bg-slate-700 text-slate-300 uppercase">${u.role}</span>`;
            if(u.role === 'admin') roleBadge = `<span class="px-2 py-1 rounded text-xs font-bold bg-purple-900/30 text-purple-400 border border-purple-800 uppercase">ADMIN</span>`;
            if(u.role === 'cs') roleBadge = `<span class="px-2 py-1 rounded text-xs font-bold bg-blue-900/30 text-blue-400 border border-blue-800 uppercase">CS</span>`;

            // Badge Status
            let statusBadge = u.status === 'active' 
                ? `<span class="text-emerald-400 text-xs font-bold flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Aktif</span>`
                : `<span class="text-slate-500 text-xs font-bold flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Non-Aktif</span>`;

            currentTbody.innerHTML += `
                <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition">
                    <td class="py-4 px-6">
                        <div class="font-bold text-white text-sm">${u.username}</div>
                        <div class="text-xs text-slate-500">${u.name || '-'}</div>
                    </td>
                    <td class="py-4 px-6">${roleBadge}</td>
                    <td class="py-4 px-6">
                        <div class="text-xs text-slate-400">${u.email || '-'}</div>
                        <div class="text-xs text-slate-500">${u.phone || '-'}</div>
                    </td>
                    <td class="py-4 px-6">${statusBadge}</td>
                    <td class="py-4 px-6 text-right">
                        <button onclick='editTeam(${JSON.stringify(u)})' class="text-blue-400 hover:text-white mr-3 text-xs font-bold">EDIT</button>
                        <button onclick="deleteTeam(${u.id})" class="text-red-400 hover:text-white text-xs font-bold">HAPUS</button>
                    </td>
                </tr>
            `;
        });
    })
    .catch(err => console.error("Gagal load team:", err));
}

// BUKA MODAL
function openModalTeam() {
    const form = document.getElementById('form-team');
    if(!form) return; // Safety check

    form.reset();
    document.getElementById('inp-id').value = '';
    document.getElementById('modal-title').innerText = "Tambah User";
    
    // Enable Username input
    const inpUser = document.getElementById('inp-username');
    inpUser.readOnly = false;
    inpUser.classList.remove('opacity-50', 'cursor-not-allowed');
    
    document.getElementById('modal-team').classList.remove('hidden');
}

function closeModalTeam() {
    const modal = document.getElementById('modal-team');
    if(modal) modal.classList.add('hidden');
}

// EDIT
function editTeam(u) {
    document.getElementById('inp-id').value = u.id;
    document.getElementById('inp-username').value = u.username;
    document.getElementById('inp-name').value = u.name;
    document.getElementById('inp-email').value = u.email;
    document.getElementById('inp-phone').value = u.phone;
    document.getElementById('inp-role').value = u.role;
    document.getElementById('inp-status').value = u.status;
    document.getElementById('inp-password').value = ''; 

    const inpUser = document.getElementById('inp-username');
    inpUser.readOnly = true;
    inpUser.classList.add('opacity-50', 'cursor-not-allowed');

    document.getElementById('modal-title').innerText = "Edit User";
    document.getElementById('modal-team').classList.remove('hidden');
}

// SIMPAN
function saveTeam(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('action', 'save');

    fetch('api_team.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        if(res.status === 'success') {
            closeModalTeam();
            loadTeam();
        } else {
            alert('Gagal: ' + res.msg);
        }
    });
}

// HAPUS
function deleteTeam(id) {
    if(!confirm("Yakin hapus user ini?")) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);

    fetch('api_team.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        if(res.status === 'success') loadTeam();
        else alert(res.msg);
    });
}