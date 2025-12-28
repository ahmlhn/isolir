// FILE: isolir/modules/js/client_chat.js
// VERSI: CLIENT SIDE WITH "EDITED" LABEL

let visitId = localStorage.getItem('noci_visit_id');
let checkInterval = null;
let isChatOpen = false;
let lastMsgCount = 0;
let unreadCount = 0;
let lastDataJson = "";

// INJECT CSS
const style = document.createElement('style');
style.innerHTML = `
    .input-error { border-color: #ef4444 !important; background-color: #fef2f2 !important; animation: shake 0.3s; }
    .error-msg { color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block; font-weight: 500; }
    @keyframes shake { 0% { transform: translateX(0); } 25% { transform: translateX(-5px); } 50% { transform: translateX(5px); } 75% { transform: translateX(-5px); } 100% { transform: translateX(0); } }
`;
document.head.appendChild(style);

document.addEventListener('DOMContentLoaded', () => {
    if (!visitId) { 
        visitId = Math.floor(100000000 + Math.random() * 900000000).toString(); 
        localStorage.setItem('noci_visit_id', visitId); 
    }
    const dispId = document.getElementById('disp-id'); 
    if(dispId) dispId.innerText = visitId;
    
    const savedName = localStorage.getItem('noci_user_name');
    if (savedName) {
        const viewLogin = document.getElementById('view-login'); 
        const viewRoom = document.getElementById('view-room');
        if(viewLogin) viewLogin.style.display = 'none'; 
        if(viewRoom) viewRoom.style.display = 'flex';
        startPolling();
    }
    logVisit('view_halaman');
});

// LOGGING
function logVisit(action) {
    const fd = new FormData(); fd.append('action', action); fd.append('visit_id', visitId);
    const url = 'log.php'; 
    if (navigator.sendBeacon) navigator.sendBeacon(url, fd);
    else fetch(url, { method: 'POST', body: fd }).catch(() => {});
}

// UI CONTROL
function openLocalChat() {
    isChatOpen = true; 
    document.getElementById('chat-overlay').classList.add('active'); 
    logVisit('buka_form'); 
    const mainBtn = document.querySelector('.btn-chat-lokal'); 
    const badge = document.getElementById('chat-badge');
    if(mainBtn) mainBtn.classList.remove('btn-notify'); 
    unreadCount = 0; 
    if(badge) { badge.classList.remove('show'); badge.innerText = '0'; }
    scrollToBottom();
}

function closeLocalChat() { 
    isChatOpen = false; 
    document.getElementById('chat-overlay').classList.remove('active'); 
}

// FORM HANDLING
function showError(inputId, message) {
    const inputEl = document.getElementById(inputId);
    if (!inputEl) return;
    inputEl.classList.add('input-error');
    let errorEl = inputEl.nextElementSibling;
    if (!errorEl || !errorEl.classList.contains('error-msg')) {
        errorEl = document.createElement('span');
        errorEl.className = 'error-msg';
        inputEl.parentNode.insertBefore(errorEl, inputEl.nextSibling);
    }
    errorEl.innerText = message;
}

function clearErrors() {
    document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
    document.querySelectorAll('.error-msg').forEach(el => el.remove());
}

function startChatSession() {
    clearErrors();
    let nameInput = document.getElementById('cl-name');
    let phoneInput = document.getElementById('cl-phone');
    let addrInput = document.getElementById('cl-addr');

    let name = nameInput.value.trim();
    let phone = phoneInput.value.trim();
    let addr = addrInput ? addrInput.value.trim() : '';
    let isValid = true;

    if (!name || name.length < 3) { showError('cl-name', 'Nama wajib diisi (min 3 huruf).'); isValid = false; }
    if (!phone) { showError('cl-phone', 'Nomor HP wajib diisi.'); isValid = false; }

    if (!isValid) return;

    let phoneClean = phone.replace(/\D/g, ''); 
    if (phoneClean.startsWith('0')) phoneClean = '62' + phoneClean.substring(1); 
    else if (!phoneClean.startsWith('62')) phoneClean = '62' + phoneClean;

    localStorage.setItem('noci_user_name', name);
    const btnStart = document.getElementById('btn-start'); 
    if(btnStart) btnStart.innerText = "Menghubungkan..."; 
    logVisit('mulai_chat'); 

    const fd = new FormData(); 
    fd.append('action', 'start_session'); 
    fd.append('visit_id', visitId); 
    fd.append('name', name); 
    fd.append('phone', phoneClean); 
    fd.append('address', addr);
    
    fetch('chat/api.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        if(d.status === 'success') {
            document.getElementById('view-login').style.display = 'none'; 
            document.getElementById('view-room').style.display = 'flex';
            setTimeout(() => { playSound(); startPolling(); }, 1000);
        } else { 
            alert("Gagal memulai chat: " + (d.msg || 'Unknown Error')); 
            if(btnStart) btnStart.innerText = "MULAI CHAT"; 
        }
    }).catch(e => { 
        alert("Koneksi Error. Cek internet anda."); 
        if(btnStart) btnStart.innerText = "MULAI CHAT"; 
    });
}

// IMAGE HANDLING
function compressImage(file, quality = 0.75, maxWidth = 1280) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader(); reader.readAsDataURL(file);
        reader.onload = event => {
            const img = new Image(); img.src = event.target.result;
            img.onload = () => {
                let width = img.width; let height = img.height;
                if (width > maxWidth) { height = Math.round(height * (maxWidth / width)); width = maxWidth; }
                const canvas = document.createElement('canvas'); canvas.width = width; canvas.height = height;
                const ctx = canvas.getContext('2d'); ctx.drawImage(img, 0, 0, width, height);
                canvas.toBlob(blob => { if(blob) resolve(blob); else reject(new Error("Canvas blob failed")); }, 'image/jpeg', quality);
            }; img.onerror = error => reject(error);
        }; reader.onerror = error => reject(error);
    });
}

async function sendImageClient() {
    const fileInput = document.getElementById('img-input'); 
    if (fileInput.files.length === 0) return;
    
    const originalFile = fileInput.files[0];
    const tempId = Date.now();
    appendBubble('Mengirim gambar...', 'user', '...', 'text'); 
    scrollToBottom();
    
    let fileToSend = originalFile; 
    let fileName = originalFile.name;
    try { 
        const compressedBlob = await compressImage(originalFile); 
        fileToSend = compressedBlob; 
        fileName = 'image.jpg'; 
    } catch (err) { console.warn("Kompresi gagal, pakai file asli."); }
    
    const fd = new FormData(); 
    fd.append('action', 'send'); 
    fd.append('visit_id', visitId); 
    fd.append('sender', 'user'); 
    fd.append('image', fileToSend, fileName); 
    
    try {
        const res = await fetch('chat/api.php', { method: 'POST', body: fd });
        const text = await res.text(); 
        let json; try { json = JSON.parse(text); } catch(e) { throw new Error("Server Error"); }
        
        if (json.status === 'success') { 
            if (json.new_visit_id && json.new_visit_id !== visitId) {
                visitId = json.new_visit_id;
                localStorage.setItem('noci_visit_id', visitId);
            }
            fileInput.value = ''; 
            loadMessages(); 
        } else { 
            alert("Gagal kirim gambar."); 
        }
    } catch(e) { 
        alert("Error kirim gambar");
    }
}

function sendMsg() {
    const input = document.getElementById('chat-input'); 
    const msg = input.value.trim(); 
    if(!msg) return;
    
    appendBubble(msg, 'user', 'Baru saja', 'text'); 
    input.value = ''; 
    input.style.height = 'auto'; 
    scrollToBottom();
    
    const fd = new FormData(); 
    fd.append('action', 'send'); 
    fd.append('visit_id', visitId); 
    fd.append('message', msg); 
    fd.append('sender', 'user');
    
    fetch('chat/api.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(json => { 
        if(json.status === 'success') {
            if (json.new_visit_id && json.new_visit_id !== visitId) {
                visitId = json.new_visit_id;
                localStorage.setItem('noci_visit_id', visitId);
            }
            loadMessages(); 
        }
    }).catch(err => {});
}

function handleEnter(e) { if(e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); } }
function startPolling() { loadMessages(); if(checkInterval) clearInterval(checkInterval); checkInterval = setInterval(loadMessages, 3000); }

function loadMessages() {
    const viewerMode = isChatOpen ? 'user' : 'passive'; 
    fetch(`chat/api.php?action=get_messages&visit_id=${visitId}&viewer=${viewerMode}`)
    .then(r => r.json())
    .then(msgs => {
        const currentDataJson = JSON.stringify(msgs);

        if (currentDataJson !== lastDataJson) {
            const container = document.getElementById('chat-messages');
            
            if (msgs.length > lastMsgCount) {
                let hasNewAdminMsg = false; 
                for (let i = lastMsgCount; i < msgs.length; i++) { if (msgs[i].sender === 'admin') hasNewAdminMsg = true; }
                if (hasNewAdminMsg && lastMsgCount > 0) { 
                    playSound(); 
                    if (!isChatOpen) { unreadCount++; showToast("Pesan baru dari Admin!"); updateFrontBadge(); } 
                }
            }

            container.innerHTML = '';
            // Pass parameter 'm.is_edited' ke fungsi render
            msgs.forEach(m => { 
                const isMe = m.sender === 'user'; 
                appendBubble(m.message, isMe ? 'user' : 'admin', m.time, m.type || 'text', m.is_edited); 
            });
            
            scrollToBottom(); 
            
            lastDataJson = currentDataJson;
            lastMsgCount = msgs.length;
        }
    }).catch(e => {});
}

function updateFrontBadge() { const mainBtn = document.querySelector('.btn-chat-lokal'); const badge = document.getElementById('chat-badge'); if (unreadCount > 0) { if(mainBtn) mainBtn.classList.add('btn-notify'); if(badge) { badge.innerText = unreadCount > 9 ? '9+' : unreadCount; badge.classList.add('show'); } } }
function linkify(text) { const urlRegex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])|(\bwww\.[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig; let res = text.replace(urlRegex, function(url) { let href = url; if (url.toLowerCase().startsWith('www.')) href = 'https://' + url; return `<a href="${href}" target="_blank" style="color:#60a5fa; text-decoration:underline; word-break:break-all;">${url}</a>`; }); return res.replace(/\n/g, '<br>'); }

// [UPDATE] RENDER BUBBLE (MENERIMA PARAMETER 'isEdited')
function appendBubble(content, sender, time, type, isEdited = 0) {
    const div = document.createElement('div'); div.className = `msg msg-${sender}`;
    div.style.flexShrink = '0';

    // Tambahkan teks (diedit) jika flag isEdited bernilai 1
    const editedHtml = (isEdited == 1) ? ' <span style="font-size:9px; font-style:italic; opacity:0.7;">(diedit)</span>' : '';

    if (type === 'image') {
        div.style.padding = '0'; 
        div.style.overflow = 'hidden'; 
        div.style.backgroundColor = 'transparent';
        div.style.border = 'none';
        
        div.innerHTML = `
            <div style="position: relative; display: inline-block;">
                <img src="chat/uploads/${content}" 
                     style="display: block; width: auto; height: auto; max-width: 100%; max-height: 250px; border-radius: 12px; cursor: pointer; object-fit: contain; background: rgba(0,0,0,0.1);" 
                     onclick="openImageViewer(this.src)"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM2NDc0OGIiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cmVjdCB4PSIzIiB5PSIzIiB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHJ4PSIyIiByeT0iMiI+PC9yZWN0PjxjaXJjbGUgY3g9IjguNSIgY3k9IjguNSIgcj0iMS41Ij48L2NpcmNsZT48cG9seWxpbmUgcG9pbnRzPSIyMSAxNSAxNiAxMCA1IDIxIj48L3BvbHlsaW5lPjwvc3ZnPg==';">
                <div style="position: absolute; bottom: 6px; right: 8px; background: rgba(0,0,0,0.5); color: #fff; padding: 2px 6px; border-radius: 10px; font-size: 10px; pointer-events: none; backdrop-filter: blur(2px);">
                    ${time}
                </div>
            </div>`;
    } else { 
        const safeText = linkify(content); 
        // Masukkan editedHtml di sebelah jam
        div.innerHTML = `${safeText}<span class="msg-time">${editedHtml} ${time}</span>`; 
    }
    document.getElementById('chat-messages').appendChild(div); 
    return div;
}

function openImageViewer(src) { const v = document.getElementById('img-viewer-client'); const img = document.getElementById('img-view-src'); const dl = document.getElementById('img-dl-btn'); img.src = src; dl.href = src; v.style.display = 'flex'; }
function closeImageViewer() { document.getElementById('img-viewer-client').style.display = 'none'; document.getElementById('img-view-src').src = ''; }
function scrollToBottom() { const body = document.getElementById('chat-messages'); if(body) body.scrollTop = body.scrollHeight; }
function playSound() { const audio = document.getElementById('sfx-in'); if(audio) { audio.currentTime = 0; audio.play().catch(e => {}); } }
function showToast(msg) { const t = document.getElementById('cust-toast'); if(!t) return; const span = t.querySelector('span'); if(span) span.innerText = msg; t.classList.add('show'); setTimeout(() => t.classList.remove('show'), 4000); }