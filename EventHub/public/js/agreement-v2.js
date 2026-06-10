/**
 * Agreement Negotiation Module
 * Shared logic for Manager & Sponsor agreement modals.
 * Requires: api.js, auth.js, i18n.js loaded first.
 */

function getAgreementStatusBadge(status) {
  const map = {
    draft:              { bg:'rgba(156,163,175,0.15)', color:'#9ca3af', border:'rgba(156,163,175,0.3)', label:'Draft', labelAr:'مسودة', icon:'<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"></path></svg>' },
    pending_review:     { bg:'rgba(234,179,8,0.15)',   color:'#eab308', border:'rgba(234,179,8,0.3)',   label:'Pending Review', labelAr:'بانتظار المراجعة', icon:'<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' },
    revision_requested: { bg:'rgba(249,115,22,0.15)',  color:'#f97316', border:'rgba(249,115,22,0.3)',  label:'Revision Requested', labelAr:'مطلوب تعديل', icon:'<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path></svg>' },
    accepted:           { bg:'rgba(16,185,129,0.15)',  color:'#10b981', border:'rgba(16,185,129,0.3)',  label:'Accepted', labelAr:'تم القبول', icon:'<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg>' },
    rejected:           { bg:'rgba(239,68,68,0.15)',   color:'#ef4444', border:'rgba(239,68,68,0.3)',   label:'Rejected', labelAr:'مرفوض', icon:'<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>' },
  };
  const s = map[status] || map.draft;
  return `<span style="background:${s.bg};color:${s.color};padding:4px 12px;border-radius:12px;border:1px solid ${s.border};font-size:11px;font-weight:600;display:inline-flex;align-items:center;gap:4px">${s.icon} ${t(s.label)}</span>`;
}

function getActionLabel(action) {
  const map = {
    uploaded:           { en: 'Uploaded new version', ar: 'تم رفع نسخة جديدة' },
    accepted:           { en: 'Accepted', ar: 'تم القبول' },
    rejected:           { en: 'Rejected', ar: 'تم الرفض' },
    revision_requested: { en: 'Revision requested', ar: 'طلب تعديل' },
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
          <svg width="64" height="64" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          <p style="color:var(--text-muted); margin-bottom:20px;">${t('No contract generated yet. Generate the initial agreement to start negotiation.')}</p>
          <button class="btn btn-primary" onclick="generateAgreement(${id}, '${type}')" style="padding:10px 30px; font-weight:600; display:inline-flex; align-items:center; justify-content:center; gap:6px; margin: 0 auto;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg> ${t('Generate Contract')}
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
    const iconMap = {
      accepted: `<span style="color:#10b981; display:inline-flex; align-items:center;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg></span>`,
      rejected: `<span style="color:#ef4444; display:inline-flex; align-items:center;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg></span>`,
      revision_requested: `<span style="color:#f97316; display:inline-flex; align-items:center;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path></svg></span>`,
      uploaded: `<span style="color:#22d3ee; display:inline-flex; align-items:center;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg></span>`
    };
    const icon = iconMap[v.action] || iconMap.uploaded;
    return `
    <div style="display:flex;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
      <div style="margin-top:2px;">${icon}</div>
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
    html += `<button onclick="downloadAgreement(${id}, '${type}')" style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;background:rgba(34,211,238,0.08);color:#22d3ee;border:1px solid rgba(34,211,238,0.2);border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;"><span style="display:inline-flex;align-items:center;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path></svg></span> ${t('Download Contract')}</button>`;
  }
  if (isFinalized) {
    html += `<button onclick="downloadFinalPdf(${id}, '${type}')" style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;background:rgba(16,185,129,0.08);color:#10b981;border:1px solid rgba(16,185,129,0.2);border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;"><span style="display:inline-flex;align-items:center;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg></span> ${t('Download Final Agreement')}</button>`;
  }
  html += `</div>`;

  // ── Waiting notice ──
  if (isMySubmission && neg.status === 'pending_review') {
    html += `
    <div style="background:rgba(234,179,8,0.05);border:1px solid rgba(234,179,8,0.12);border-radius:8px;padding:10px 12px;margin-bottom:14px;font-size:0.75rem;color:#eab308;display:flex;align-items:center;gap:6px;">
      <span style="display:inline-flex;align-items:center;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span> ${t('Waiting for the other party to review your submission...')}
    </div>`;
  }

  // ── Upload section — compact ──
  if (canUpload) {
    html += `
    <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:10px;padding:14px;margin-bottom:12px;">
      <div style="font-size:0.68rem;font-weight:700;color:#22d3ee;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;display:flex;align-items:center;gap:4px;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"></path></svg> ${t('Upload Modified Contract')}</div>
      <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px dashed rgba(255,255,255,0.12);border-radius:8px;cursor:pointer;margin-bottom:6px;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
        <span style="font-size:0.75rem;color:var(--text-muted);display:inline-flex;align-items:center;gap:4px;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"></path></svg> ${t('Choose file')}...</span>
        <input type="file" id="agreement-file-${id}" accept=".docx,.doc,.pdf" style="display:none;" onchange="this.parentElement.querySelector('span').innerHTML = '<svg width=\\\'14\\\' height=\\\'14\\\' fill=\\\'none\\\' stroke=\\\'currentColor\\\' stroke-width=\\\'2\\\' viewBox=\\\'0 0 24 24\\\' style=\\\'display:inline-flex;align-items:center;vertical-align:middle;margin-inline-end:4px;\\\'\>\<path stroke-linecap=\\\'round\\\' stroke-linejoin=\\\'round\\\' d=\\\'M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13\\\'\>\</path\>\</svg\>' + (this.files[0]?.name || t('Choose file') + '...')">
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
      <div style="font-size:0.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;display:flex;align-items:center;gap:4px;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"></path></svg> ${t('Respond to Contract')}</div>
      <textarea id="respond-msg-${id}" placeholder="${t('Add feedback or comments...')}" style="width:100%;min-height:40px;padding:8px 10px;border-radius:6px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);color:#fff;font-size:0.75rem;resize:vertical;margin-bottom:8px;"></textarea>
      <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <button onclick="respondAgreement(${id}, 'accepted', '${type}')" style="flex:1;padding:6px;font-size:0.72rem;font-weight:600;border-radius:6px;cursor:pointer;background:rgba(16,185,129,0.1);color:#10b981;border:1px solid rgba(16,185,129,0.25);display:inline-flex;align-items:center;justify-content:center;gap:4px;"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg> ${t('Accept Contract')}</button>
        <button onclick="respondAgreement(${id}, 'revision_requested', '${type}')" style="flex:1;padding:6px;font-size:0.72rem;font-weight:600;border-radius:6px;cursor:pointer;background:rgba(249,115,22,0.08);color:#f97316;border:1px solid rgba(249,115,22,0.2);display:inline-flex;align-items:center;justify-content:center;gap:4px;"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path></svg> ${t('Request Revision')}</button>
        <button onclick="respondAgreement(${id}, 'rejected', '${type}')" style="flex:1;padding:6px;font-size:0.72rem;font-weight:600;border-radius:6px;cursor:pointer;background:rgba(239,68,68,0.08);color:#ef4444;border:1px solid rgba(239,68,68,0.2);display:inline-flex;align-items:center;justify-content:center;gap:4px;"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg> ${t('Reject Contract')}</button>
      </div>
    </div>`;
  }

  // ── Timeline ──
  html += `
    <div>
      <div style="font-size:0.65rem;font-weight:700;color:rgba(255,255,255,0.3);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:6px;display:flex;align-items:center;gap:4px;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path></svg> ${t('Negotiation History')}</div>
      ${timelineHtml || `<p style="font-size:0.75rem;color:var(--text-muted);">${t('No history yet')}</p>`}
    </div>`;

  content.innerHTML = html;
}

async function generateAgreement(id, type = 'sponsor') {
  const content = document.getElementById('agreement-content');
  content.innerHTML = '<div class="spinner" style="margin:40px auto"></div>';
  const res = await api.post(`/agreements/${id}/generate?type=${type}`);
  if (res.ok) {
  showToast(t('Contract generated successfully!'), 'success');
    openAgreementModal(id, type);
    refreshParentPage();
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
  showToast(t('Download complete'), 'success');
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
  showToast(t('Download complete'), 'success');
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
    showToast(t('Contract uploaded and sent for review!'), 'success');
    openAgreementModal(id, type);
    refreshParentPage();
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
    const successMsg = action === 'accepted' ? t('Contract accepted!')
      : action === 'rejected' ? t('Contract rejected')
      : t('Revision requested');
    showToast(successMsg, action === 'accepted' ? 'success' : 'info');
    openAgreementModal(id, type);
    refreshParentPage();
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
          <h3 class="modal-title" style="color:#ef4444; display:flex; align-items:center; gap:6px;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg> ${t('Cancel Agreement')}</h3>
          <button class="modal-close" onclick="closeCancelModal()">✕</button>
        </div>
        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:16px;">
          ${t('This will cancel the preliminary agreement. Please provide a reason for cancellation.')}
        </p>
        <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.15); border-radius:10px; padding:12px; margin-bottom:16px; font-size:0.8rem; color:#fca5a5; display:flex; align-items:center; gap:6px;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> ${t('This action cannot be undone.')}
        </div>
        <textarea id="cancel-reason" placeholder="${t('Reason for cancellation (required)...')}" required
          style="width:100%;min-height:80px;padding:12px;border-radius:8px;background:rgba(255,255,255,0.05);border:1px solid rgba(239,68,68,0.2);color:#fff;font-size:0.85rem;resize:vertical;margin-bottom:16px;"></textarea>
        <div style="display:flex; gap:10px;">
          <button class="btn" onclick="closeCancelModal()" style="flex:1; padding:10px; font-size:0.85rem; background:rgba(255,255,255,0.06); color:var(--text-muted); border:1px solid var(--border);">
            ${t('Go Back')}
          </button>
          <button class="btn btn-danger" onclick="cancelAgreement()" style="flex:1; padding:10px; font-size:0.85rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; gap:6px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg> ${t('Confirm Cancellation')}
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
    showToast(t('Agreement cancelled successfully'), 'info');
    closeCancelModal();
    refreshParentPage();
  } else {
    showToast(res.data?.message || 'Error', 'error');
  }
}

function refreshParentPage() {
  if (typeof loadRequests === 'function') {
    loadRequests();
  }
  if (typeof loadData === 'function') {
    loadData();
  }
  if (typeof loadExhibitions === 'function') {
    loadExhibitions();
  }
}
