// FILE: isolir/chat/app.js
// VERSI: AUTO FOCUS ON CHAT SELECT + ALL FEATURES

let activeVisitId = null;
let lastMsgCount = 0;
let pollingInterval = null;
let usersData = [];
let isFetching = false;
let unreadState = {};
let noteTimeout = null;
let lastDataJson = ""; 
let editingMsgId = null; 
const audioNotif = document.getElementById('notif-sound');

document.addEventListener("DOMContentLoaded", () => {
    injectSmartModal();
    injectEditIndicator(); 
    loadContacts(); 
    setInterval(loadContacts, 5000); 

    const msgInput = document.getElementById('msg-input');
    if (msgInput) {
        msgInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });
        msgInput.addEventListener('input', function(e) {
            this.style.height = 'auto'; this.style.height = (this.scrollHeight) + 'px';
            const val = e.target.value;
            if (val.startsWith('/')) showTemplatePopup(val.substring(1));
            else document.getElementById('tpl-popup').classList.add('hidden');
        });
    }

    const noteInput = document.getElementById('customer-note');
    if (noteInput) {
        noteInput.addEventListener('input', function() {
            const statusEl = document.getElementById('note-status');
            if (statusEl) {
                statusEl.innerText = 'Saving...';
                statusEl.className = 'text-[9px] text-yellow-600 font-bold opacity-100';
            }
            clearTimeout(noteTimeout);
            noteTimeout = setTimeout(saveNote, 1000);
        });
    }
    loadTemplates();

    // --- GLOBAL SHORTCUTS ---
    document.addEventListener('keydown', (e) => {
        // 1. NAVIGASI MODAL
        const smartModal = document.getElementById('smart-modal');
        if (smartModal && !smartModal.classList.contains('hidden')) {
            const btnCancel = document.getElementById('smart-btn-cancel');
            const btnConfirm = document.getElementById('smart-btn-confirm'); 
            const btnOk = document.getElementById('smart-btn-ok');

            if (e.key === 'ArrowLeft' && btnCancel) { e.preventDefault(); btnCancel.focus(); }
            if (e.key === 'ArrowRight' && btnConfirm) { e.preventDefault(); btnConfirm.focus(); }
            
            if (e.key === 'Enter') {
                e.preventDefault();
                if (document.activeElement === btnCancel) btnCancel.click();
                else if (document.activeElement === btnConfirm) btnConfirm.click();
                else if (document.activeElement === btnOk) btnOk.click();
            }
            if (e.key === 'Escape') { e.preventDefault(); handleEscapeKey(); }
            return; 
        }

        // 2. Shortcut Aplikasi
        if (e.key === 'Escape') handleEscapeKey();
        
        if (e.shiftKey && (e.key === 'Delete' || e.key === 'Backspace')) {
            if (activeVisitId && document.activeElement.id !== 'search-user' && !editingMsgId) {
                deleteSession();
            }
        }
    });
});

// --- HANDLE ESCAPE ---
function handleEscapeKey() {
    const imgViewer = document.getElementById('img-viewer');
    if (imgViewer && !imgViewer.classList.contains('hidden')) { closeImageViewer(); return; }

    const tplPopup = document.getElementById('tpl-popup');
    if (tplPopup && !tplPopup.classList.contains('hidden')) { tplPopup.classList.add('hidden'); return; }

    if (editingMsgId) { cancelEditMode(); return; }

    const smartModal = document.getElementById('smart-modal');
    if (smartModal && !smartModal.classList.contains('hidden')) {
        const btnCancel = document.getElementById('smart-btn-cancel');
        const btnOk = document.getElementById('smart-btn-ok');
        if (btnCancel) btnCancel.click(); else if (btnOk) btnOk.click(); else closeSmartModal();
        return; 
    }

    const activeModals = ['modal-edit-user', 'modal-detail', 'modal-tpl-manager', 'modal-form-tpl', 'modal-admin-profile', 'chat-menu', 'sidebar-menu'];
    let modalClosed = false;
    activeModals.forEach(id => {
        const el = document.getElementById(id);
        if (el && !el.classList.contains('hidden')) { el.classList.add('hidden'); modalClosed = true; }
    });
    if (modalClosed) return;

    if (activeVisitId) closeActiveChat();
}

// --- OPEN CHAT (WITH AUTO FOCUS) ---
function openChat(visitId) {
    document.getElementById('empty-state').classList.add('hidden');
    const chatInterface = document.getElementById('chat-interface');
    chatInterface.classList.remove('hidden'); 
    chatInterface.classList.add('flex');

    if (window.innerWidth < 768) { 
        document.getElementById('panel-list').classList.add('hidden'); 
        document.getElementById('panel-chat').classList.remove('mobile-hidden'); 
    }

    // [BARU] AUTO FOCUS KE TEXT AREA
    // Diberi delay sedikit (50ms) agar UI render dulu
    setTimeout(() => {
        const input = document.getElementById('msg-input');
        if (input) {
            input.focus();
            // Opsional: Scroll ke paling bawah untuk memastikan input terlihat
            // input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 50);

    if (pollingInterval) clearInterval(pollingInterval);
    isFetching = false;
    cancelEditMode(); 
    
    if (activeVisitId) {
        const prevItem = document.getElementById(`user-item-${activeVisitId}`);
        if (prevItem) {
            prevItem.classList.remove('user-item-active', 'bg-blue-50', 'border-blue-600');
            prevItem.classList.add('hover:bg-slate-50', 'border-transparent');
            const av = prevItem.querySelector('.user-avatar');
            if(av) { av.classList.remove('from-blue-600', 'to-blue-700', 'text-white'); av.classList.add('from-blue-100', 'to-blue-200', 'text-blue-600'); }
        }
    }
    const newItem = document.getElementById(`user-item-${visitId}`);
    if (newItem) {
        newItem.classList.add('user-item-active', 'bg-blue-50', 'border-blue-600');
        newItem.classList.remove('hover:bg-slate-50', 'border-transparent');
        const av = newItem.querySelector('.user-avatar');
        if(av) { av.classList.remove('from-blue-100', 'to-blue-200', 'text-blue-600'); av.classList.add('from-blue-600', 'to-blue-700', 'text-white'); }
        const badge = newItem.querySelector('.bg-red-500.text-white'); if(badge) badge.remove();
    }
    activeVisitId = visitId; lastMsgCount = 0; if(unreadState[visitId]) unreadState[visitId] = 0;

    try {
        const msgContainer = document.getElementById('messages');
        msgContainer.innerHTML = `<div class="flex flex-col items-center justify-center h-full space-y-3 animate-pulse"><div class="w-8 h-8 rounded-full border-2 border-slate-300 border-t-transparent animate-spin"></div><div class="text-xs text-slate-400">Memuat...</div></div>`;
        const user = usersData.find(u => String(u.visit_id) === String(visitId));
        if (user) {
            setText('.info-name', user.name); setText('.info-phone', user.phone || '-'); setText('.info-address', user.address || '-'); setText('.info-id', '#' + user.visit_id); setText('.info-location', user.location_info || '-'); setText('.info-ip', user.ip_address || '-');
            const noteInput = document.getElementById('customer-note'); if(noteInput) { noteInput.value = user.notes || ''; document.getElementById('note-status').innerText = 'Saved'; }
            const hName = document.getElementById('h-name'); if(hName) hName.innerText = user.name;
            const hAvatar = document.getElementById('h-avatar'); if(hAvatar) hAvatar.innerText = (user.name||'U').charAt(0).toUpperCase();
            const hStatus = document.getElementById('h-status'); if(hStatus) hStatus.innerHTML = getStatus(user.last_seen);
            const hId = document.getElementById('h-id'); if(hId) hId.innerText = '#' + user.visit_id;
            
            const waLink = document.getElementById('wa-link'); 
            if (user.phone && user.phone.length > 5 && !user.phone.includes('Unknown') && user.phone.replace(/\D/g, '').length > 5) { 
                let clean = user.phone.replace(/\D/g, ''); 
                if (clean.startsWith('0')) clean = '62' + clean.substring(1); 
                else if (!clean.startsWith('62')) clean = '62' + clean;
                waLink.href = `https://wa.me/${clean}`; waLink.classList.remove('hidden'); 
            } else { waLink.classList.add('hidden'); }
            
            const footerActive = document.getElementById('footer-active'); const footerLocked = document.getElementById('footer-locked'); const btnEnd = document.getElementById('btn-end-session'); const btnReopen = document.getElementById('btn-reopen-session');
            if (user.status === 'Selesai') {
                if(footerActive) footerActive.classList.add('hidden'); if(footerLocked) { footerLocked.classList.remove('hidden'); footerLocked.classList.add('flex'); }
                if(btnEnd) btnEnd.classList.add('hidden'); if(btnReopen) btnReopen.classList.remove('hidden');
            } else {
                if(footerActive) footerActive.classList.remove('hidden'); if(footerLocked) footerLocked.classList.add('hidden');
                if(btnEnd) btnEnd.classList.remove('hidden'); if(btnReopen) btnReopen.classList.add('hidden');
            }
        }
    } catch (e) {}
    loadMessages(true); pollingInterval = setInterval(() => loadMessages(false), 3000);
}

// --- EDIT LOGIC ---
function injectEditIndicator() {
    const footer = document.getElementById('footer-active');
    if(footer) {
        const div = document.createElement('div');
        div.id = 'edit-indicator';
        div.className = 'hidden w-full bg-blue-50 border-t border-x border-blue-200 rounded-t-xl px-4 py-2 flex justify-between items-center text-xs text-blue-700 absolute bottom-full left-0 mb-[-5px] z-0 shadow-sm';
        div.innerHTML = `
            <div class="flex items-center gap-2"><span class="font-bold">✏️ Edit Mode</span></div>
            <button onclick="cancelEditMode()" class="text-slate-400 hover:text-red-500 font-bold px-2 uppercase text-[10px]">Batal (ESC)</button>
        `;
        footer.parentElement.style.position = 'relative'; 
        footer.parentElement.insertBefore(div, footer.parentElement.firstChild);
    }
}

function editMessage(id) {
    const bubbleText = document.querySelector(`#msg-${id} .msg-text`);
    if (!bubbleText) { alert("Gagal mengambil teks pesan."); return; }
    const textContent = bubbleText.innerText;

    editingMsgId = id;
    const input = document.getElementById('msg-input');
    const indicator = document.getElementById('edit-indicator');
    
    if(input) {
        input.value = textContent;
        input.focus();
        input.style.height = 'auto'; 
        input.style.height = (input.scrollHeight) + 'px';
        input.parentElement.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');
    }
    if(indicator) indicator.classList.remove('hidden');
}

function cancelEditMode() {
    editingMsgId = null;
    const input = document.getElementById('msg-input');
    const indicator = document.getElementById('edit-indicator');
    
    if(input) {
        input.value = '';
        input.style.height = 'auto';
        input.parentElement.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');
        input.blur();
    }
    if(indicator) indicator.classList.add('hidden');
}

function sendMessage() {
    if (!activeVisitId) return;
    const input = document.getElementById('msg-input'); 
    const text = input.value.trim(); 
    if (!text) return;

    if (editingMsgId) {
        const tempId = editingMsgId;
        const tempText = text;
        const bubbleText = document.querySelector(`#msg-${tempId} .msg-text`);
        if (bubbleText) {
            bubbleText.innerHTML = linkify(tempText);
            const bubbleTime = document.querySelector(`#msg-${tempId} .msg-time`);
            if(bubbleTime && !bubbleTime.innerHTML.includes('(diedit)')) {
                const label = `<span class="text-[9px] italic opacity-60 mx-1">(diedit)</span>`;
                bubbleTime.insertAdjacentHTML('afterbegin', label); 
            }
        }
        cancelEditMode();
        const fd = new FormData(); fd.append('action', 'edit_message'); fd.append('id', tempId); fd.append('message', tempText);
        fetch('api.php', { method: 'POST', body: fd }).then(r=>r.json()).then(res => { if(res.status !== 'success') loadMessages(false); });
    } else {
        appendBubble(text, 'admin', '...', 'text'); 
        input.value = ''; input.style.height = 'auto'; input.focus(); scrollToBottom();
        const fd = new FormData(); fd.append('action', 'send'); fd.append('visit_id', activeVisitId); fd.append('sender', 'admin'); fd.append('message', text);
        fetch('api.php', { method: 'POST', body: fd }).then(r => r.json()).then(res => { loadMessages(false); });
    }
}

// --- STANDARD FUNCTIONS ---
function closeActiveChat() {
    if (pollingInterval) clearInterval(pollingInterval);
    activeVisitId = null;
    cancelEditMode(); 
    document.getElementById('chat-interface').classList.add('hidden');
    document.getElementById('chat-interface').classList.remove('flex');
    document.getElementById('empty-state').classList.remove('hidden');
    document.getElementById('panel-list').classList.remove('hidden');
    document.getElementById('panel-chat').classList.add('mobile-hidden');
    document.title = "Chat Admin | Isolir";
    document.querySelectorAll('.user-item-active').forEach(el => {
        el.classList.remove('user-item-active', 'bg-blue-50', 'border-blue-600');
        el.classList.add('hover:bg-slate-50', 'border-transparent');
        const avatar = el.querySelector('.user-avatar');
        if(avatar) { avatar.classList.remove('from-blue-600', 'to-blue-700', 'text-white'); avatar.classList.add('from-blue-100', 'to-blue-200', 'text-blue-600'); }
    });
}

function loadContacts() {
    fetch('api.php?action=get_contacts')
    .then(async response => { const text = await response.text(); try { return JSON.parse(text); } catch (e) { return []; } })
    .then(data => { 
        if (data && data.status === 'error') return;
        if (!Array.isArray(data)) return;
        const newJson = JSON.stringify(data);
        if (newJson !== lastDataJson) { usersData = data; lastDataJson = newJson; renderUserList(data); }
    }).catch(err => {});
}

function renderUserList(data) {
    const listContainer = document.getElementById('user-list'); 
    if (!listContainer) return;
    let totalUnreadGlobal = 0; 
    if (data.length === 0) { listContainer.innerHTML = '<div class="flex flex-col items-center justify-center h-40 text-slate-400 text-sm"><p>Belum ada percakapan.</p></div>'; updateTitleNotification(0); return; }
    const currentScroll = listContainer.scrollTop; 
    let html = '';
    data.forEach(user => {
        let uName = user.name || 'Tanpa Nama'; 
        let visitIdStr = String(user.visit_id); 
        const isActive = (String(activeVisitId) === visitIdStr);
        let bgClass = isActive ? 'user-item-active bg-blue-50 border-l-4 border-blue-600' : 'hover:bg-slate-50 border-l-4 border-transparent';
        let avatarColor = isActive ? 'from-blue-600 to-blue-700 text-white' : 'from-blue-100 to-blue-200 text-blue-600';
        const currentUnread = parseInt(user.unread) || 0; 
        totalUnreadGlobal += currentUnread; 
        let badge = ''; 
        if (currentUnread > 0) badge = `<span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center shadow-md shadow-red-200 animate-pulse">${currentUnread}</span>`; 
        let prevMsg = user.last_msg || '...'; 
        if (user.msg_type === 'image') prevMsg = '📷 Gambar';
        let statusIcon = user.status === 'Selesai' ? '<span class="text-[9px] text-green-600 font-bold ml-1">✔</span>' : '';
        const safeId = visitIdStr.replace(/'/g, "\\'");
        html += `<div id="user-item-${safeId}" onclick="openChat('${safeId}')" class="flex items-center gap-3 p-3 cursor-pointer transition border-b border-slate-50 ${bgClass} relative select-none"><div class="relative"><div class="user-avatar w-10 h-10 rounded-full bg-gradient-to-br ${avatarColor} flex items-center justify-center font-bold shrink-0 text-sm shadow-sm transition-all">${uName.charAt(0).toUpperCase()}</div></div><div class="flex-1 min-w-0"><div class="flex justify-between items-center mb-0.5"><h4 class="text-slate-800 font-bold text-sm truncate flex items-center gap-1">${uName} ${statusIcon}</h4><span class="text-[10px] text-slate-400 font-mono">${user.display_time || ''}</span></div><div class="flex items-center text-xs text-slate-500"><p class="truncate flex-1 opacity-80">${prevMsg.substring(0,30)}</p>${badge}</div></div></div>`;
    });
    listContainer.innerHTML = html; 
    listContainer.scrollTop = currentScroll;
    updateTitleNotification(totalUnreadGlobal);
}

function updateTitleNotification(totalUnread) { document.title = totalUnread > 0 ? `(${totalUnread}) Pesan Baru` : "Chat Admin"; }

function loadMessages(isFirstLoad = false) {
    if (!activeVisitId || isFetching) return;
    if (isFirstLoad) isFetching = true;
    fetch(`api.php?action=get_messages&visit_id=${activeVisitId}&viewer=admin`)
        .then(r => r.json()).then(data => {
            isFetching = false;
            if (!Array.isArray(data)) { if (data && data.status === 'error' && data.msg === 'Unauthorized') window.location.href = '../login.php'; return; }
            const newDataJson = JSON.stringify(data);
            if (newDataJson !== lastDataJson || isFirstLoad) {
                lastDataJson = newDataJson;
                renderMessages(data);
                if (!isFirstLoad && data.length > lastMsgCount) { const lastM = data[data.length - 1]; if (lastM.sender === 'user') playSound(); }
                lastMsgCount = data.length; scrollToBottom();
            }
        }).catch(e => { isFetching = false; });
}

function renderMessages(data) {
    const container = document.getElementById('messages');
    if (!data || data.length === 0) { container.innerHTML = '<div class="flex h-full items-center justify-center text-xs text-slate-400">Belum ada riwayat chat.</div>'; return; }
    let html = '';
    data.forEach(msg => {
        const isMe = (msg.sender === 'admin'); const isImg = (msg.type === 'image');
        const align = isMe ? 'justify-end' : 'justify-start';
        const bg = isMe ? 'bg-[#d9fdd3] text-gray-900 rounded-br-none shadow-sm' : 'bg-white text-gray-900 rounded-bl-none shadow-sm border border-gray-100'; 
        const padClass = isImg ? 'p-1' : 'px-3 py-2'; 
        let content = '';
        if (isImg) {
            content = `<div class="cursor-pointer group relative" onclick="openImageViewer('uploads/${msg.message}')"><img src="uploads/${msg.message}" class="block rounded-lg max-w-[250px] max-h-[300px] w-auto h-auto shadow-sm bg-slate-50 object-contain border border-slate-200" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'p-3 bg-red-50 text-[10px] text-red-500 rounded border border-red-100 italic\\'>Gagal muat gambar</div>';"><div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition rounded-lg"></div></div>`;
        } else { content = linkify(msg.message); }
        let statusIcon = ''; if(isMe) statusIcon = `<span class="text-blue-500 font-bold ml-1">✓</span>`;
        const editedLabel = (msg.is_edited == 1) ? `<span class="text-[9px] italic opacity-60 mx-1">(diedit)</span>` : '';
        let menuBtn = '';
        if (isMe) {
            const canEdit = !isImg ? `<button onclick="editMessage('${msg.id}')" class="block w-full text-left px-4 py-2 text-xs hover:bg-slate-50 text-slate-700 font-medium">✏️ Edit</button>` : '';
            menuBtn = `
            <div class="relative group/menu flex items-center self-center opacity-0 group-hover:opacity-100 transition-opacity mx-2">
                <button class="text-slate-400 hover:text-slate-600 text-xs font-bold px-1 tracking-widest">•••</button>
                <div class="hidden group-hover/menu:block absolute right-0 top-4 bg-white border border-slate-200 shadow-xl rounded-lg z-50 w-24 overflow-hidden ring-1 ring-black/5">
                    ${canEdit}
                    <button onclick="deleteMessage('${msg.id}')" class="block w-full text-left px-4 py-2 text-xs hover:bg-red-50 text-red-600 font-medium border-t border-slate-100">🗑️ Hapus</button>
                </div>
            </div>`;
        }
        const contentHtml = isMe 
            ? `${menuBtn}<div id="msg-${msg.id}" class="${bg} ${padClass} rounded-lg text-[13.5px] leading-relaxed relative"><span class="msg-text">${content}</span><div class="msg-time text-[9px] opacity-50 text-right mt-1 font-mono tracking-wide flex justify-end items-center gap-1 select-none">${editedLabel} ${msg.time} ${statusIcon}</div></div>`
            : `<div id="msg-${msg.id}" class="${bg} ${padClass} rounded-lg text-[13.5px] leading-relaxed relative"><span class="msg-text">${content}</span><div class="msg-time text-[9px] opacity-50 text-right mt-1 font-mono tracking-wide flex justify-end items-center gap-1 select-none">${editedLabel} ${msg.time} ${statusIcon}</div></div>`;
        html += `<div class="flex ${align} mb-2 shrink-0 group animate-in fade-in zoom-in duration-200"><div class="max-w-[85%] md:max-w-[75%] flex items-start">${contentHtml}</div></div>`;
    });
    container.innerHTML = html;
}

function sendImageAdmin() {
    const fileInput = document.getElementById('admin-img-input'); if (fileInput.files.length === 0 || !activeVisitId) return;
    const file = fileInput.files[0]; const fd = new FormData(); fd.append('action', 'send'); fd.append('visit_id', activeVisitId); fd.append('sender', 'admin'); fd.append('image', file);
    if(file.size > 5*1024*1024) { showSmartAlert("File Besar", "Maksimal 5MB.", "warning"); fileInput.value=''; return; }
    appendBubble('Mengirim gambar...', 'admin', '...', 'text'); scrollToBottom(); fileInput.value = '';
    fetch('api.php', { method: 'POST', body: fd }).then(r => r.json()).then(res => { loadMessages(false); });
}

function deleteMessage(id) {
    showSmartConfirm("Hapus Pesan?", "Hapus permanen?", "danger", "Hapus").then(yes => {
        if(yes) {
            const fd = new FormData(); fd.append('action', 'delete_message'); fd.append('id', id);
            fetch('api.php', { method: 'POST', body: fd }).then(r=>r.json()).then(res => { if(res.status === 'success') loadMessages(false); });
        }
    });
}

function openAdminProfile() { fetch('api.php?action=get_admin_profile').then(r => r.json()).then(res => { if (res.status === 'success') { const d = res.data; document.getElementById('adm-name').value = d.name || ''; document.getElementById('adm-username').value = d.username || ''; document.getElementById('adm-password').value = ''; document.getElementById('sidebar-menu').classList.add('hidden'); document.getElementById('modal-admin-profile').classList.remove('hidden'); } else { showSmartAlert("Gagal", res.msg || "Gagal.", "error"); } }); }
function saveAdminProfile() { const name = document.getElementById('adm-name').value.trim(); const username = document.getElementById('adm-username').value.trim(); const password = document.getElementById('adm-password').value; if (!name || !username) { showSmartAlert("Gagal", "Lengkapi data.", "warning"); return; } const fd = new FormData(); fd.append('action', 'update_admin_profile'); fd.append('name', name); fd.append('username', username); if (password) fd.append('password', password); fetch('api.php', { method: 'POST', body: fd }).then(r => r.json()).then(res => { if (res.status === 'success') { document.getElementById('modal-admin-profile').classList.add('hidden'); showSmartAlert("Berhasil", "Profil disimpan.", "success").then(() => location.reload()); } else { showSmartAlert("Gagal", res.msg || "Error.", "error"); } }); }
function setText(selector, text) { document.querySelectorAll(selector).forEach(el => el.innerText = text || '-'); }
function getStatus(lastSeen) { if (!lastSeen) return '<span class="text-slate-400 text-xs">Offline</span>'; const seenDate = new Date(lastSeen.replace(/-/g, "/")); const diffMins = Math.floor((new Date() - seenDate) / 60000); if (diffMins < 5) return `<span class="text-green-600 text-xs font-bold">Online</span>`; return `<span class="text-slate-500 text-xs">Seen ${seenDate.getHours()}:${seenDate.getMinutes().toString().padStart(2,'0')}</span>`; }
function linkify(text) { return text.replace(/(\b(https?):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig, '<a href="$1" target="_blank" class="text-blue-600 hover:underline">$1</a>'); }
function appendBubble(text, sender, time) { const container = document.getElementById('messages'); const bg = 'bg-[#d9fdd3] text-gray-900 rounded-br-none shadow-sm'; container.insertAdjacentHTML('beforeend', `<div class="flex justify-end mb-2 shrink-0 opacity-70"><div class="max-w-[85%]"><div class="${bg} px-3 py-2 rounded-lg text-[13.5px]">${text}</div></div></div>`); }
function scrollToBottom() { const el = document.getElementById('messages'); el.scrollTop = el.scrollHeight; }
function playSound() { if(audioNotif) audioNotif.play().catch(()=>{}); }
function openImageViewer(src) { document.getElementById('img-viewer-src').src = src; document.getElementById('img-download-btn').href = src; document.getElementById('img-viewer').classList.remove('hidden'); document.getElementById('img-viewer').classList.add('flex'); }
function closeImageViewer() { document.getElementById('img-viewer').classList.add('hidden'); document.getElementById('img-viewer').classList.remove('flex'); }
function filterUsers() { const q = document.getElementById('search-user').value.toLowerCase(); renderUserList(usersData.filter(u => (u.name||'').toLowerCase().includes(q)), true); }
function toggleSidebarMenu() { const s = document.getElementById('sidebar-menu'); if(s) s.classList.toggle('hidden'); }
function toggleMenu() { const c = document.getElementById('chat-menu'); if(c) c.classList.toggle('hidden'); }
function showDetail() { document.getElementById('modal-detail').classList.remove('hidden'); toggleMenu(); }
function closeDetail() { document.getElementById('modal-detail').classList.add('hidden'); }
function openEditUser() { if (!activeVisitId) return; const user = usersData.find(u => u.visit_id == activeVisitId); if (!user) return; document.getElementById('edit-visit-id').value = user.visit_id; document.getElementById('edit-name').value = user.name || ''; document.getElementById('edit-phone').value = user.phone || ''; document.getElementById('edit-addr').value = user.address || ''; document.getElementById('modal-edit-user').classList.remove('hidden'); toggleMenu(); }

// Template
window.chatTemplates = [{ label: 'salam', text: 'Halo kak, selamat datang. Ada yang bisa kami bantu?' }, { label: 'gangguan', text: 'Mohon maaf saat ini sedang ada gangguan jaringan. Teknisi kami sedang memperbaikinya.' }, { label: 'bayar', text: 'Pembayaran bisa transfer ke BCA 1234567890 a.n PT ISP.' }, { label: 'makasih', text: 'Terima kasih telah menghubungi kami.' }];
function loadTemplates() { renderTemplateManager(); }
function showTemplatePopup(k) { const p = document.getElementById('tpl-popup'); const l = document.getElementById('tpl-list'); const m = window.chatTemplates.filter(t => t.label.includes(k.toLowerCase())); if(m.length===0) { p.classList.add('hidden'); return; } l.innerHTML = m.map(t => `<div onclick="useTemplate('${t.text.replace(/'/g,"\\'")}')" class="px-3 py-2 hover:bg-slate-100 cursor-pointer border-b border-slate-100 text-xs"><div class="text-blue-600 font-bold mb-0.5">/${t.label}</div><div class="text-slate-500 truncate">${t.text}</div></div>`).join(''); p.classList.remove('hidden'); }
function useTemplate(t) { const i=document.getElementById('msg-input'); i.value=t; i.dispatchEvent(new Event('input')); document.getElementById('tpl-popup').classList.add('hidden'); i.focus(); }
function toggleTpl() { const p=document.getElementById('tpl-popup'); if(p.classList.contains('hidden')) showTemplatePopup(''); else p.classList.add('hidden'); }
function openTplManager() { document.getElementById('modal-tpl-manager').classList.remove('hidden'); renderTemplateManager(); }
function renderTemplateManager() { const c = document.getElementById('manager-list'); if(c) c.innerHTML = window.chatTemplates.map((t,i) => `<div class="bg-slate-50 p-3 rounded-xl border border-slate-200 flex justify-between gap-3 items-start"><div class="overflow-hidden"><div class="text-blue-600 text-xs font-bold mb-1">/${t.label}</div><div class="text-xs text-slate-500 line-clamp-2">${t.text}</div></div><button onclick="deleteTemplate(${i})" class="text-slate-400 hover:text-red-500 p-1">Hapus</button></div>`).join(''); }
function openFormTpl() { document.getElementById('modal-tpl-manager').classList.add('hidden'); document.getElementById('modal-form-tpl').classList.remove('hidden'); }
function saveTemplate() { const l = document.getElementById('tpl-label').value.trim(); const m = document.getElementById('tpl-msg').value.trim(); if(l&&m) { window.chatTemplates.push({label:l, text:m}); openTplManager(); document.getElementById('modal-form-tpl').classList.add('hidden'); } }
function deleteTemplate(i) { showSmartConfirm('Hapus Template', 'Yakin ingin menghapus template ini?', 'warning').then(ok=>{if(ok){window.chatTemplates.splice(i,1); renderTemplateManager();}});}

// Modals
function saveEditUser() { const id = document.getElementById('edit-visit-id').value; const name = document.getElementById('edit-name').value.trim(); const phone = document.getElementById('edit-phone').value.trim(); const addr = document.getElementById('edit-addr').value.trim(); if (!id || !name) { showSmartAlert("Validasi", "Nama wajib diisi.", "warning"); return; } document.getElementById('modal-edit-user').classList.add('hidden'); const fd = new FormData(); fd.append('action', 'update_customer'); fd.append('visit_id', id); fd.append('name', name); fd.append('phone', phone); fd.append('address', addr); fetch('api.php', { method: 'POST', body: fd }).then(r => r.json()).then(res => { if (res.status === 'success') { const userIndex = usersData.findIndex(u => u.visit_id == id); if (userIndex !== -1) { usersData[userIndex].name = name; usersData[userIndex].phone = phone; usersData[userIndex].address = addr; } openChat(id); showSmartAlert("Berhasil", "Data disimpan.", "success"); } else { showSmartAlert("Gagal", 'Error.', "error"); } }); }
function openEndModal() { if (!activeVisitId) return; toggleMenu(); showSmartConfirm("Selesaikan Sesi?", "Sesi akan ditutup.", "success", "Ya, Selesaikan").then((isConfirmed) => { if (isConfirmed) confirmEndSession(); }); }
function confirmEndSession() { if (!activeVisitId) return; const fd = new FormData(); fd.append('action', 'end_session'); fd.append('visit_id', activeVisitId); fetch('api.php', { method: 'POST', body: fd }).then(r => r.json()).then(res => { if (res.status === 'success') { const userIndex = usersData.findIndex(u => u.visit_id == activeVisitId); if (userIndex !== -1) { usersData[userIndex].status = 'Selesai'; } openChat(activeVisitId); loadContacts(); showSmartAlert("Berhasil", "Sesi selesai.", "success"); } else { showSmartAlert("Error", "Gagal.", "error"); } }); }
function reopenSession() { if (!activeVisitId) return; const fd = new FormData(); fd.append('action', 'reopen_session'); fd.append('visit_id', activeVisitId); fetch('api.php', { method: 'POST', body: fd }).then(r => r.json()).then(res => { if (res.status === 'success') { loadContacts(); const userIndex = usersData.findIndex(u => u.visit_id == activeVisitId); if(userIndex !== -1) usersData[userIndex].status = 'Proses'; openChat(activeVisitId); showSmartAlert("Berhasil", "Sesi dibuka.", "success"); } else { showSmartAlert("Error", "Gagal.", "error"); } }); }
function deleteSession() { if (!activeVisitId) return; showSmartConfirm("Hapus Chat?", "Data akan hilang permanen.", "danger", "Hapus").then((isConfirmed) => { if (isConfirmed) { const fd = new FormData(); fd.append('action', 'delete_session'); fd.append('visit_id', activeVisitId); fetch('api.php', { method: 'POST', body: fd }).then(r => r.json()).then(res => { if (res.status === 'success') { if (pollingInterval) clearInterval(pollingInterval); activeVisitId = null; document.getElementById('chat-interface').classList.add('hidden'); document.getElementById('empty-state').classList.remove('hidden'); loadContacts(); showSmartAlert("Terhapus", "Data chat berhasil dihapus.", "success"); } else { showSmartAlert("Gagal", "Error.", "error"); } }); } }); }

// UI Helpers (WITH VISIBLE FOCUS RINGS)
function injectSmartModal() { if (document.getElementById('smart-modal')) return; const modalHtml = `<div id="smart-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4"><div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="smart-modal-bg"></div><div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 transform scale-95 opacity-0 transition-all duration-300 border border-slate-200" id="smart-modal-panel"><div class="text-center"><h3 class="text-lg font-bold text-slate-800 tracking-wide" id="smart-title">Notification</h3><div class="mt-2"><p class="text-sm text-slate-500 leading-relaxed" id="smart-msg">Message goes here...</p></div></div><div class="mt-6 flex gap-3 justify-center" id="smart-actions"></div></div></div>`; document.body.insertAdjacentHTML('beforeend', modalHtml); }
function showSmartAlert(title, message, type = 'info') { return new Promise((resolve) => { setupModal(title, message, type); const btnArea = document.getElementById('smart-actions'); btnArea.innerHTML = `<button id="smart-btn-ok" class="w-full justify-center rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 text-sm font-bold shadow-lg shadow-blue-200 transition-all outline-none focus:ring-4 focus:ring-blue-300 focus:ring-offset-2">Oke</button>`; const btn = document.getElementById('smart-btn-ok'); btn.onclick = () => { closeSmartModal(); resolve(true); }; openSmartModal(); setTimeout(() => btn.focus(), 50); }); }
function showSmartConfirm(title, message, type = 'warning', confirmText = 'Ya') { return new Promise((resolve) => { setupModal(title, message, type); const btnArea = document.getElementById('smart-actions'); let confirmColor = 'bg-blue-600 hover:bg-blue-700 shadow-blue-200 focus:ring-blue-300'; if (type === 'danger') { confirmColor = 'bg-red-600 hover:bg-red-700 shadow-red-200 focus:ring-red-300'; } if (type === 'success') { confirmColor = 'bg-green-600 hover:bg-green-700 shadow-green-200 focus:ring-green-300'; } btnArea.innerHTML = `<button id="smart-btn-cancel" class="flex-1 justify-center rounded-xl bg-white hover:bg-slate-50 text-slate-600 px-5 py-2.5 text-sm font-bold transition-all border border-slate-200 outline-none focus:ring-4 focus:ring-slate-100 focus:ring-offset-2">Batal</button><button id="smart-btn-confirm" class="flex-1 justify-center rounded-xl ${confirmColor} text-white px-5 py-2.5 text-sm font-bold shadow-lg transition-all outline-none focus:ring-4 focus:ring-offset-2">${confirmText}</button>`; const btnCancel = document.getElementById('smart-btn-cancel'); const btnConfirm = document.getElementById('smart-btn-confirm'); btnConfirm.addEventListener('keydown', (e) => { if (e.key === 'ArrowLeft') { e.preventDefault(); btnCancel.focus(); } }); btnCancel.addEventListener('keydown', (e) => { if (e.key === 'ArrowRight') { e.preventDefault(); btnConfirm.focus(); } }); btnCancel.onclick = () => { closeSmartModal(); resolve(false); }; btnConfirm.onclick = () => { closeSmartModal(); resolve(true); }; openSmartModal(); setTimeout(() => btnConfirm.focus(), 50); }); }
function setupModal(title, message, type) { document.getElementById('smart-title').innerText = title; document.getElementById('smart-msg').innerHTML = message; }
function openSmartModal() { const modal = document.getElementById('smart-modal'); const bg = document.getElementById('smart-modal-bg'); const panel = document.getElementById('smart-modal-panel'); modal.classList.remove('hidden'); modal.classList.add('flex'); setTimeout(() => { bg.classList.remove('opacity-0'); panel.classList.remove('opacity-0', 'scale-95'); panel.classList.add('opacity-100', 'scale-100'); }, 10); }
function closeSmartModal() { const bg = document.getElementById('smart-modal-bg'); const panel = document.getElementById('smart-modal-panel'); bg.classList.add('opacity-0'); panel.classList.remove('opacity-100', 'scale-100'); panel.classList.add('opacity-0', 'scale-95'); setTimeout(() => { document.getElementById('smart-modal').classList.add('hidden'); document.getElementById('smart-modal').classList.remove('flex'); }, 300); }