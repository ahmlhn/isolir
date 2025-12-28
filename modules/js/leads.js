// FILE: isolir/modules/js/leads.js
// VERSI: COLOR CODED STATUS

let searchTimeout;

async function loadLeads() {
    const tbody = document.getElementById('leads-body');
    const searchInput = document.getElementById('leads-search');
    const limitSelect = document.getElementById('leads-limit');
    const badgeEl = document.getElementById('total-leads-badge');

    if(!tbody) return;
    
    const searchVal = searchInput ? searchInput.value : '';
    const limitVal = limitSelect ? limitSelect.value : '50';
    
    tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-slate-500 animate-pulse"><div class="flex flex-col items-center gap-2"><div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div><span class="text-xs">Sedang mencari data...</span></div></td></tr>`;
    
    try {
        const res = await fetch(`api_leads.php?action=get_all&search=${encodeURIComponent(searchVal)}&limit=${limitVal}`); 
        const data = await res.json();
        
        if(badgeEl) badgeEl.innerText = data.length;

        if(data.length === 0) { 
            tbody.innerHTML = `<tr><td colspan="6" class="p-12 text-center text-slate-500 flex flex-col items-center justify-center"><span class="text-2xl mb-2 opacity-30">📂</span><span class="text-xs">Data tidak ditemukan.</span></td></tr>`; 
            return; 
        }
        
        tbody.innerHTML = data.map(r => {
            let statusLabel = r.status || 'Baru';
            let statusColor = 'bg-slate-700 text-slate-400 border-slate-600'; // Default (Baru)

            // --- LOGIKA WARNA BARU ---
            if(statusLabel === 'Menunggu') {
                statusColor = 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20';
                statusLabel = '⏳ Menunggu'; // Icon jam pasir
            }
            else if(statusLabel === 'Proses') {
                statusColor = 'bg-blue-500/10 text-blue-400 border-blue-500/20';
                statusLabel = '⚡ Proses';
            }
            else if(statusLabel === 'Selesai') {
                statusColor = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                statusLabel = '✔ Selesai';
            }
            // ------------------------

            let waNum = r.customer_phone.replace(/\D/g, '');
            if(waNum.startsWith('0')) waNum = '62' + waNum.substring(1);
            const waLink = `https://wa.me/${waNum}`;
            const locInfo = r.location_info || '-';
            const ipAddr = r.ip_address || '-';

            return `
            <tr class="hover:bg-slate-800/40 border-b border-slate-800/50 transition group">
                <td class="px-6 py-4">
                    <div class="font-bold text-white text-sm">${r.customer_name}</div>
                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">ID: ${r.visit_id}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-mono text-slate-300 text-xs">${r.customer_phone}</div>
                    <div class="text-[10px] text-slate-600 mt-0.5">${r.last_seen_fmt}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-xs text-slate-400 max-w-[150px] truncate" title="${r.customer_address}">
                        ${r.customer_address || '-'}
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-1.5 mb-0.5">
                        <span class="bg-slate-700 text-slate-300 text-[10px] px-1.5 rounded font-mono">${ipAddr}</span>
                    </div>
                    <div class="text-[11px] text-slate-400 max-w-[140px] truncate" title="${locInfo}">
                        <span class="text-blue-500 mr-1">📍</span>${locInfo}
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="${statusColor} text-[10px] px-2.5 py-1 rounded-full font-bold border uppercase tracking-wider">
                        ${statusLabel}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="${waLink}" target="_blank" class="p-2 bg-green-600/10 text-green-500 rounded-lg hover:bg-green-600 hover:text-white transition border border-green-600/20" title="Chat WA">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.891-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></path></svg>
                        </a>
                        ${statusLabel.includes('Selesai') ? `
                        <button class="px-3 py-1.5 bg-slate-800 text-slate-600 text-[10px] font-bold rounded-lg border border-slate-700 cursor-not-allowed uppercase">
                            Done
                        </button>` : `
                        <button onclick="updateLeadStatus('${r.visit_id}', 'Selesai')" class="px-3 py-1.5 bg-blue-600/20 text-blue-400 text-[10px] font-bold rounded-lg hover:bg-blue-600 hover:text-white transition border border-blue-600/30 uppercase">
                            ✔ Set Selesai
                        </button>`}
                    </div>
                </td>
            </tr>`;
        }).join('');
    } catch(e) { 
        console.error(e);
        tbody.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-red-400 text-xs">Gagal mengambil data server.</td></tr>'; 
    }
}

function searchLeads() { clearTimeout(searchTimeout); searchTimeout = setTimeout(loadLeads, 500); }
async function updateLeadStatus(id, s) { 
    if(confirm('Tandai pelanggan ini selesai diproses?')) { 
        const fd = new FormData(); fd.append('visit_id', id); fd.append('status', s); fd.append('action', 'update_status');
        await fetch('api_leads.php', {method:'POST', body:fd}); loadLeads(); 
    } 
}
document.addEventListener('DOMContentLoaded', loadLeads);