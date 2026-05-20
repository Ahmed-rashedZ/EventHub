/**
 * Agreement Negotiation Module
 * Shared logic for Manager & Sponsor agreement modals.
 * Requires: api.js, auth.js, i18n.js loaded first.
 */

function getAgreementStatusBadge(status) {
  const map = {
    draft:              { bg:'rgba(156,163,175,0.15)', color:'#9ca3af', border:'rgba(156,163,175,0.3)', label:'Draft', labelAr:'مسودة', icon:'📝' },
    pending_review:     { bg:'rgba(234,179,8,0.15)',   color:'#eab308', border:'rgba(234,179,8,0.3)',   label:'Pending Review', labelAr:'بانتظار المراجعة', icon:'⏳' },
    revision_requested: { bg:'rgba(249,115,22,0.15)',  color:'#f97316', border:'rgba(249,115,22,0.3)',  label:'Revision Requested', labelAr:'مطلوب تعديل', icon:'🔄' },
    accepted:           { bg:'rgba(16,185,129,0.15)',  color:'#10b981', border:'rgba(16,185,129,0.3)',  label:'Accepted', labelAr:'تم القبول', icon:'✅' },
    rejected:           { bg:'rgba(239,68,68,0.15)',   color:'#ef4444', border:'rgba(239,68,68,0.3)',   label:'Rejected', labelAr:'مرفوض', icon:'❌' },
  };
  const s = map[status] || map.draft;
  return `<span style="background:${s.bg};color:${s.color};padding:4px 12px;border-radius:12px;border:1px solid ${s.border};font-size:11px;font-weight:600;display:inline-flex;align-items:center;gap:4px">${s.icon} ${t(s.label)}</span>`;
}

function getActionLabel(action) {
  const map = {
    uploaded:           { en: 'Uploaded new version', ar: 'تم رفع نسخة جديدة' },
    accepted:           { en: 'Accepted', ar: '✅ تم القبول' },
    rejected:           { en: 'Rejected', ar: '❌ تم الرفض' },
    revision_requested: { en: 'Revision requested', ar: '🔄 طلب تعديل' },
  };
  const a = map[action] || { en: action, ar: action };
  return localStorage.getItem('lang') === 'ar' ? a.ar : a.en;
}

function formatDateTime(dt) {
  if (!dt) return '—';
  const d = new Date(dt);
  return d.toLocaleDateString('en-GB', { year:'numeric', month:'short', day:'numeric' }) + ' ' + d.toLocaleTimeString('en-GB', { hour:'2-digit', minute:'2-digit' });
}

let currentNegotiationType = 'sponsor';

async function openAgreementModal(id, type = 'sponsor') {
  currentNegotiationType = type;
  const modal = document.getElementById('agreement-modal');
  const content = document.getElementById('agreement-content');
  modal.classList.add('open');
  content.innerHTML = '<div class="spinner" style="margin:40px auto"></div>';

  const res = await api.get(`/agreements/${id}?type=${type}`);

  if (!res.ok) {
    // No negotiation yet — offer to generate
    if (res.status === 404) {
      content.innerHTML = `
        <div style="text-align:center; padding:30px 0;">
          <div style="font-size:3rem; margin-bottom:16px;">📋</div>
          <p style="color:var(--text-muted); margin-bottom:20px;">${t('No contract generated yet. Generate the initial agreement to start negotiation.')}</p>
          <button class="btn btn-primary" onclick="generateAgreement(${id}, '${type}')" style="padding:10px 30px; font-weight:600;">
            📄 ${t('Generate Contract')}
          </button>
        </div>`;
    } else {
      content.innerHTML = `<div class="empty-state"><p>${t('Failed to load')}</p></div>`;
    }
    return;
  }

  renderAgreementContent(res.data, id, type);
}

function renderAgreementContent(data, id, type) {
  const content = document.getElementById('agreement-content');
  const neg = data.negotiation;
  const versions = neg.versions || [];
  const sreq = data.sponsorship_request || data.exhibition_application;
  const currentUser = JSON.parse(sessionStorage.getItem('user') || '{}');
  const isMySubmission = neg.last_submitted_by === currentUser.id;
  const canRespond = neg.status === 'pending_review' && !isMySubmission;
  const canUpload = neg.status !== 'accepted' && neg.status !== 'rejected';
  const isFinalized = neg.status === 'accepted';

  // Timeline HTML — compact version
  let timelineHtml = versions.map(v => {
    const iconMap = { accepted:'✅', rejected:'❌', revision_requested:'🔄', uploaded:'📄' };
    const icon = iconMap[v.action] || '📄';
    return `
    <div style="display:flex;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
      <div style="font-size:12px;margin-top:2px;">${icon}</div>
      <div style="flex:1;min-width:0;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:4px;">
          <span style="font-weight:600;font-size:0.78rem;" class="i18n-skip">${v.uploader?.name || '—'}</span>
          <span style="font-size:0.65rem;color:var(--text-muted);white-space:nowrap;">${formatDateTime(v.created_at)}</span>
        </div>
        <div style="font-size:0.72rem;color:var(--text-muted);margin-top:1px;">
          ${getActionLabel(v.action)}${v.action === 'uploaded' ? ` (v${v.version_number})` : ''}
        </div>
        ${v.message ? `<div style="font-size:0.72rem;color:#cbd5e1;margin-top:4px;padding:6px 10px;background:rgba(255,255,255,0.03);border-radius:6px;border:1px solid rgba(255,255,255,0.05);"><em>"${v.message}"</em></div>` : ''}
      </div>
    </div>`;
  }).join('');

  // Build sections
  let html = '';

  // ── Header: status + event name ──
  html += `
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:6px;">
      <div>${getAgreementStatusBadge(neg.status)}</div>
      <div style="font-size:0.7rem;color:var(--text-muted);" class="i18n-skip">${sreq?.event?.title || '—'}</div>
    </div>`;

  // ── Download buttons — compact inline ──
  html += `<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px;">`;
  if (!isFinalized) {
    html += `<button onclick="downloadAgreement(${id}, '${type}')" style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;background:rgba(34,211,238,0.08);color:#22d3ee;border:1px solid rgba(34,211,238,0.2);border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;">📥 ${t('Download Contract')}</button>`;
  }
  if (isFinalized) {
    html += `<button onclick="downloadFinalPdf(${id}, '${type}')" style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;background:rgba(16,185,129,0.08);color:#10b981;border:1px solid rgba(16,185,129,0.2);border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;">📄 ${t('Download Final Agreement')}</button>`;
  }
  html += `</div>`;

  // ── Waiting notice ──
  if (isMySubmission && neg.status === 'pending_review') {
    html += `
    <div style="background:rgba(234,179,8,0.05);border:1px solid rgba(234,179,8,0.12);border-radius:8px;padding:10px 12px;margin-bottom:14px;font-size:0.75rem;color:#eab308;display:flex;align-items:center;gap:6px;">
      ⏳ ${t('Waiting for the other party to review your submission...')}
    </div>`;
  }

  // ── Upload section — compact ──
  if (canUpload) {
    html += `
    <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:10px;padding:14px;margin-bottom:12px;">
      <div style="font-size:0.68rem;font-weight:700;color:#22d3ee;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">📤 ${t('Upload Modified Contract')}</div>
      <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px dashed rgba(255,255,255,0.12);border-radius:8px;cursor:pointer;margin-bottom:6px;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
        <span style="font-size:0.75rem;color:var(--text-muted);">📎 ${t('Choose file')}...</span>
        <input type="file" id="agreement-file-${id}" accept=".docx,.doc,.pdf" style="display:none;" onchange="this.parentElement.querySelector('span').textContent = this.files[0]?.name || '📎 ${t('Choose file')}...'">
      </label>
      <textarea id="agreement-msg-${id}" placeholder="${t('Add a message describing your changes...')}" style="width:100%;min-height:44px;padding:8px 10px;border-radius:6px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);color:#fff;font-size:0.75rem;resize:vertical;margin-bottom:6px;"></textarea>
      <button class="btn btn-primary" onclick="uploadAgreement(${id}, '${type}')" style="padding:6px 14px;font-size:0.72rem;font-weight:600;width:100%;border-radius:6px;">
        ${t('Upload & Send for Review')}
      </button>
    </div>`;
  }

  // ── Response section — only show to the party who DID NOT submit the last version ──
  if (canUpload && !isMySubmission) {
    html += `
    <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:10px;padding:14px;margin-bottom:12px;">
      <div style="font-size:0.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">⚡ ${t('Respond to Contract')}</div>
      <textarea id="respond-msg-${id}" placeholder="${t('Add feedback or comments...')}" style="width:100%;min-height:40px;padding:8px 10px;border-radius:6px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);color:#fff;font-size:0.75rem;resize:vertical;margin-bottom:8px;"></textarea>
      <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <button onclick="respondAgreement(${id}, 'accepted', '${type}')" style="flex:1;padding:6px;font-size:0.72rem;font-weight:600;border-radius:6px;cursor:pointer;background:rgba(16,185,129,0.1);color:#10b981;border:1px solid rgba(16,185,129,0.25);">✅ ${t('Accept Contract')}</button>
        <button onclick="respondAgreement(${id}, 'revision_requested', '${type}')" style="flex:1;padding:6px;font-size:0.72rem;font-weight:600;border-radius:6px;cursor:pointer;background:rgba(249,115,22,0.08);color:#f97316;border:1px solid rgba(249,115,22,0.2);">🔄 ${t('Request Revision')}</button>
        <button onclick="respondAgreement(${id}, 'rejected', '${type}')" style="flex:1;padding:6px;font-size:0.72rem;font-weight:600;border-radius:6px;cursor:pointer;background:rgba(239,68,68,0.08);color:#ef4444;border:1px solid rgba(239,68,68,0.2);">❌ ${t('Reject Contract')}</button>
      </div>
    </div>`;
  }

  // ── Timeline ──
  html += `
    <div>
      <div style="font-size:0.65rem;font-weight:700;color:rgba(255,255,255,0.3);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:6px;">📜 ${t('Negotiation History')}</div>
      ${timelineHtml || `<p style="font-size:0.75rem;color:var(--text-muted);">${t('No history yet')}</p>`}
    </div>`;

  content.innerHTML = html;
}

async function generateAgreement(id, type = 'sponsor') {
  const content = document.getElementById('agreement-content');
  content.innerHTML = '<div class="spinner" style="margin:40px auto"></div>';
  const res = await api.post(`/agreements/${id}/generate?type=${type}`);
  if (res.ok) {
    showToast(t('Contract generated successfully!') + ' 📋', 'success');
    openAgreementModal(id, type);
  } else {
    showToast(res.data?.message || 'Error', 'error');
    openAgreementModal(id, type);
  }
}

async function downloadAgreement(id, type = 'sponsor') {
  const token = sessionStorage.getItem('token');
  const res = await fetch(`/api/agreements/${id}/download?type=${type}`, {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  if (!res.ok) { showToast(t('Download failed'), 'error'); return; }
  
  let filename = `agreement_${id}.docx`;
  const disposition = res.headers.get('content-disposition');
  if (disposition && disposition.indexOf('filename=') !== -1) {
      const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
      const matches = filenameRegex.exec(disposition);
      if (matches != null && matches[1]) { 
          filename = matches[1].replace(/['"]/g, '');
      }
  }

  const blob = await res.blob();
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
  showToast(t('Download complete') + ' ✅', 'success');
}

async function downloadFinalPdf(id, type = 'sponsor') {
  const token = sessionStorage.getItem('token');
  const res = await fetch(`/api/agreements/${id}/download-final?type=${type}`, {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  if (!res.ok) { showToast(t('Download failed'), 'error'); return; }
  
  let filename = `agreement_${id}_final.pdf`;
  const disposition = res.headers.get('content-disposition');
  if (disposition && disposition.indexOf('filename=') !== -1) {
      const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
      const matches = filenameRegex.exec(disposition);
      if (matches != null && matches[1]) { 
          filename = matches[1].replace(/['"]/g, '');
      }
  }

  const blob = await res.blob();
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
  showToast(t('Download complete') + ' ✅', 'success');
}

async function uploadAgreement(id, type = 'sponsor') {
  const fileInput = document.getElementById(`agreement-file-${id}`);
  const msgInput = document.getElementById(`agreement-msg-${id}`);
  if (!fileInput.files.length) { showToast(t('Please select a file'), 'error'); return; }

  const formData = new FormData();
  formData.append('file', fileInput.files[0]);
  if (msgInput.value.trim()) formData.append('message', msgInput.value.trim());

  const token = sessionStorage.getItem('token');
  const res = await fetch(`/api/agreements/${id}/upload?type=${type}`, {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
    body: formData,
  });

  const data = await res.json();
  if (res.ok) {
    showToast(t('Contract uploaded and sent for review!') + ' 📤', 'success');
    openAgreementModal(id, type);
  } else {
    showToast(data?.message || 'Error', 'error');
  }
}

async function respondAgreement(id, action, type = 'sponsor') {
  const msgInput = document.getElementById(`respond-msg-${id}`);
  const message = msgInput?.value?.trim() || '';

  if (action === 'revision_requested' && !message) {
    showToast(t('Please provide feedback for your revision request'), 'error');
    return;
  }

  const confirmMsg = action === 'accepted' ? t('Are you sure you want to accept this contract?')
    : action === 'rejected' ? t('Are you sure you want to reject this contract?')
    : t('Send revision request with your feedback?');

  if (!confirm(confirmMsg)) return;

  const res = await api.put(`/agreements/${id}/respond?type=${type}`, { action, message });
  if (res.ok) {
    const successMsg = action === 'accepted' ? t('Contract accepted!') + ' ✅'
      : action === 'rejected' ? t('Contract rejected') + ' ❌'
      : t('Revision requested') + ' 🔄';
    showToast(successMsg, action === 'accepted' ? 'success' : 'info');
    openAgreementModal(id, type);
    if (typeof loadRequests === 'function') loadRequests();
  } else {
    showToast(res.data?.message || 'Error', 'error');
  }
}

function closeAgreementModal() {
  document.getElementById('agreement-modal').classList.remove('open');
}

// ── Cancel Agreement ──
let cancelTargetId = null;

function openCancelModal(id, type = 'sponsor') {
  cancelTargetId = id;
  currentNegotiationType = type;
  // Create modal dynamically if not exists
  let modal = document.getElementById('cancel-agreement-modal');
  if (!modal) {
    modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.id = 'cancel-agreement-modal';
    modal.innerHTML = `
      <div class="modal" style="max-width:480px; width:90%; border-top:3.5px solid #ef4444;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
          <h3 class="modal-title" style="color:#ef4444;">🚫 ${t('Cancel Agreement')}</h3>
          <button class="modal-close" onclick="closeCancelModal()">✕</button>
        </div>
        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:16px;">
          ${t('This will cancel the preliminary agreement. Please provide a reason for cancellation.')}
        </p>
        <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.15); border-radius:10px; padding:12px; margin-bottom:16px; font-size:0.8rem; color:#fca5a5;">
          ⚠️ ${t('This action cannot be undone.')}
        </div>
        <textarea id="cancel-reason" placeholder="${t('Reason for cancellation (required)...')}" required
          style="width:100%;min-height:80px;padding:12px;border-radius:8px;background:rgba(255,255,255,0.05);border:1px solid rgba(239,68,68,0.2);color:#fff;font-size:0.85rem;resize:vertical;margin-bottom:16px;"></textarea>
        <div style="display:flex; gap:10px;">
          <button class="btn" onclick="closeCancelModal()" style="flex:1; padding:10px; font-size:0.85rem; background:rgba(255,255,255,0.06); color:var(--text-muted); border:1px solid var(--border);">
            ${t('Go Back')}
          </button>
          <button class="btn btn-danger" onclick="cancelAgreement()" style="flex:1; padding:10px; font-size:0.85rem; font-weight:700;">
            🚫 ${t('Confirm Cancellation')}
          </button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
  }
  document.getElementById('cancel-reason').value = '';
  modal.classList.add('open');
}

function closeCancelModal() {
  const modal = document.getElementById('cancel-agreement-modal');
  if (modal) modal.classList.remove('open');
  cancelTargetId = null;
}

async function cancelAgreement() {
  const reason = document.getElementById('cancel-reason').value.trim();
  if (!reason || reason.length < 5) {
    showToast(t('Please provide a cancellation reason (at least 5 characters)'), 'error');
    return;
  }

  const res = await api.put(`/agreements/${cancelTargetId}/cancel?type=${currentNegotiationType}`, { message: reason });
  if (res.ok) {
    showToast(t('Agreement cancelled successfully') + ' 🚫', 'info');
    closeCancelModal();
    if (typeof loadRequests === 'function') loadRequests();
  } else {
    showToast(res.data?.message || 'Error', 'error');
  }
}
