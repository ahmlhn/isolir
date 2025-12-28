// FILE: isolir/modules/js/setting.js
// VERSI: LOG TABLE FIX (TARGET COLUMN ADDED)

const templateConfig = {
    'web_welcome': { title: '🤖 Greeting Web', vars: ['{name}', '{id}'], placeholder: 'Halo...' },
    'wa_login': { title: '🔔 Notifikasi Login', vars: ['{name}', '{phone}', '{address}'], placeholder: 'Login...' },
    'wa_chat': { title: '💬 Notifikasi Pesan', vars: ['{name}', '{message}'], placeholder: 'Pesan...' }
};

const getVal = (id) => { const el = document.getElementById(id); return el ? el.value : ''; };
const setElVal = (id, val) => { const el = document.getElementById(id); if(el) el.value = (val !== null && val !== undefined) ? val : ''; };

async function loadSettings() {
    const editor = document.getElementById('tpl_editor');
    if(editor) { editor.value = ''; editor.placeholder = 'Mengambil data...'; editor.disabled = true; }

    try {
        const res = await fetch('api_settings.php?action=get');
        if (!res.ok) throw new Error("API Error");
        const d = await res.json();

        setElVal('wa_url', d.wa_url);
        setElVal('wa_token', d.wa_token);
        setElVal('wa_sender', d.wa_sender);
        setElVal('wa_target', d.wa_target);
        
        setElVal('tg_bot_token', d.tg_bot_token);
        setElVal('tg_chat_id', d.tg_chat_id);

        const chk = document.getElementById('wa_active');
        if (chk) chk.checked = (d.wa_active == 1);

        setElVal('hidden_web_welcome', d.web_welcome);
        setElVal('hidden_wa_login', d.wa_login);
        setElVal('hidden_wa_chat', d.wa_chat);

        if(editor) editor.disabled = false;
        changeTemplate();
        loadLogs();
    } catch (e) { console.error(e); }
}

function changeTemplate() {
    const selector = document.getElementById('tpl_selector');
    const editor = document.getElementById('tpl_editor');
    const desc = document.getElementById('tpl_desc');
    const box = document.getElementById('var_container');
    if (!selector || !editor) return;
    const key = selector.value;
    const cfg = templateConfig[key];
    const hidden = document.getElementById('hidden_' + key);
    editor.value = hidden ? hidden.value : '';
    if(cfg) {
        if(desc) desc.innerText = cfg.title;
        editor.placeholder = editor.value ? '' : `Contoh: ${cfg.placeholder}`;
        if(box) box.innerHTML = cfg.vars.map(v => `<button type="button" onclick="insertVar('${v}')" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-600 rounded text-[10px] font-mono transition select-none">${v}</button>`).join('');
    }
}

function updateHiddenTemplate() {
    const sel = document.getElementById('tpl_selector');
    const edit = document.getElementById('tpl_editor');
    if(sel && edit) {
        const h = document.getElementById('hidden_' + sel.value);
        if(h) h.value = edit.value;
    }
}

window.insertVar = function(txt) {
    const ed = document.getElementById('tpl_editor');
    if(!ed) return;
    ed.setRangeText(txt, ed.selectionStart, ed.selectionEnd, 'end');
    updateHiddenTemplate();
    ed.focus();
};

async function saveSettings(type) {
    let btnId = '';
    let action = '';
    const fd = new FormData();

    if(type === 'wa') {
        btnId = 'btn-save-wa';
        action = 'save_wa';
        fd.append('wa_url', getVal('wa_url'));
        fd.append('wa_token', getVal('wa_token'));
        fd.append('wa_sender', getVal('wa_sender'));
        fd.append('wa_target', getVal('wa_target'));
        const chk = document.getElementById('wa_active');
        fd.append('wa_active', chk && chk.checked ? 1 : 0);
    } 
    else if (type === 'tg') {
        btnId = 'btn-save-tg';
        action = 'save_tg';
        fd.append('tg_bot_token', getVal('tg_bot_token'));
        fd.append('tg_chat_id', getVal('tg_chat_id'));
    } 
    else if (type === 'tpl') {
        btnId = 'btn-save-tpl';
        action = 'save_tpl';
        fd.append('web_welcome', getVal('hidden_web_welcome'));
        fd.append('wa_login', getVal('hidden_wa_login'));
        fd.append('wa_chat', getVal('hidden_wa_chat'));
    }

    fd.append('action', action);

    const btn = document.getElementById(btnId);
    const ori = btn ? btn.innerHTML : '';
    if(btn) { btn.innerHTML = 'Menyimpan...'; btn.disabled = true; }

    try {
        const res = await fetch('api_settings.php', { method: 'POST', body: fd });
        const d = await res.json();
        if (d.status === 'success') { showAlert('success', d.msg || 'Tersimpan!'); }
        else showAlert('error', d.msg || 'Gagal');
    } catch (e) { showAlert('error', 'Koneksi Gagal'); }
    finally { if(btn) { btn.innerHTML = ori; btn.disabled = false; } }
}

async function testConnection() {
    const btn = document.getElementById('btn-test-wa');
    const ori = btn.innerHTML;
    btn.innerHTML = '...'; btn.disabled = true;
    const fd = new FormData();
    fd.append('wa_url', getVal('wa_url'));
    fd.append('wa_token', getVal('wa_token'));
    fd.append('wa_sender', getVal('wa_sender'));
    fd.append('wa_target', getVal('wa_target'));
    fd.append('action', 'test');
    try {
        const res = await fetch('api_settings.php', { method: 'POST', body: fd });
        const d = await res.json();
        if (d.status === 'success') showAlert('success', 'WA Terhubung!');
        else showAlert('error', d.msg || 'Gagal');
    } catch (e) { showAlert('error', 'Network Error'); }
    btn.innerHTML = ori; btn.disabled = false;
}

window.testTelegram = async function() {
    const btn = document.getElementById('btn-test-tg');
    const ori = btn ? btn.innerHTML : '';
    
    const token = getVal('tg_bot_token');
    const chat = getVal('tg_chat_id');

    if(!token || !chat) {
        alert("Mohon isi Bot Token dan Chat ID terlebih dahulu.");
        return;
    }

    if(btn) { btn.innerHTML = '...'; btn.disabled = true; }

    const fd = new FormData();
    fd.append('tg_bot_token', token);
    fd.append('tg_chat_id', chat);
    fd.append('action', 'test_tg');

    try {
        const res = await fetch('api_settings.php', { method: 'POST', body: fd });
        const d = await res.json();
        
        if (d.status === 'success') {
            showAlert('success', 'Telegram Terkirim! Cek aplikasi TG Anda.');
        } else {
            showAlert('error', 'Gagal: ' + (d.msg || 'Cek Token/ID'));
        }
    } catch (e) { 
        showAlert('error', 'Network Error'); 
        console.error(e);
    }
    
    if(btn) { btn.innerHTML = ori; btn.disabled = false; }
};

async function loadLogs() {
    const tbody = document.getElementById('wa-log-body');
    if(!tbody) return;
    try {
        const res = await fetch('api_settings.php?action=get_logs');
        const data = await res.json();
        
        // Colspan diubah jadi 5 karena ada kolom Target
        if(!data || data.length === 0) { 
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">Belum ada data log.</td></tr>'; 
            return; 
        }

        tbody.innerHTML = data.map(r => {
            let cl = r.status === 'success' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : 'text-red-400 bg-red-500/10 border-red-500/20';
            
            return `
            <tr class="hover:bg-slate-700/20 transition">
                <td class="px-6 py-3 font-mono text-slate-400 whitespace-nowrap">${r.time}</td>
                
                <td class="px-6 py-3 font-mono text-sky-400 font-bold">${r.target}</td>
                
                <td class="px-6 py-3 text-white truncate max-w-[200px]" title="${r.message}">${r.message}</td>
                <td class="px-6 py-3 text-center">
                    <span class="${cl} border px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider inline-block">
                        ${r.status}
                    </span>
                </td>
                <td class="px-6 py-3 text-slate-500 font-mono truncate max-w-xs" title="${r.log}">${r.log}</td>
            </tr>`;
        }).join('');
    } catch(e) { console.error(e); }
}

function showAlert(type, msg) {
    const box = document.getElementById('settings-alert');
    if(box) {
        box.className = `mt-3 p-3 rounded-lg text-xs font-medium text-center border ${type==='success'?'text-green-400 border-green-500/20 bg-green-500/10':'text-red-400 border-red-500/20 bg-red-500/10'}`;
        box.innerHTML = (type==='success'?'✅ ':'❌ ') + msg;
        box.classList.remove('hidden');
        setTimeout(()=>box.classList.add('hidden'), 3000);
    } else alert(msg);
}

if(document.getElementById('view-setting')) {
    loadSettings();
    const s = document.getElementById('tpl_selector'); if(s) s.addEventListener('change', changeTemplate);
    const e = document.getElementById('tpl_editor'); if(e) e.addEventListener('input', updateHiddenTemplate);
}