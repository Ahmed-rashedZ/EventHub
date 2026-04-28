<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Verifications – EventHub Admin</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <style>
    .vf-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: border-color 0.2s;
    }
    .vf-card:hover { border-color: rgba(110,64,242,.3); }
    .vf-info { display: flex; gap: 16px; align-items: center; }
    .vf-icon { font-size: 2rem; width: 48px; height: 48px; display: grid; place-items: center; background: rgba(255,255,255,0.05); border-radius: 50%; }
    .vf-name { font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 4px; }
    .vf-email { font-size: 0.85rem; color: var(--text-muted); }
    .vf-role { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; background: rgba(110,64,242,.1); color: #a78bfa; margin-top: 6px; }
    .vf-actions { display: flex; gap: 8px; }
    .vf-date { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px; text-align: right; }

    /* ── Document Review Cards ── */
    .doc-review-card {
      background: rgba(255,255,255,.02);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 14px 16px;
      margin-bottom: 12px;
      transition: border-color .2s;
    }
    .doc-review-card.status-approved { border-color: rgba(16,185,129,.4); background: rgba(16,185,129,.04); }
    .doc-review-card.status-rejected { border-color: rgba(239,68,68,.4); background: rgba(239,68,68,.04); }
    .doc-review-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }
    .doc-review-title {
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
      color: #fff;
      font-size: 0.9rem;
    }
    .doc-review-title .doc-icon { font-size: 1.2rem; }
    .doc-review-actions {
      display: flex;
      gap: 6px;
    }
    .doc-review-actions .btn {
      padding: 5px 12px;
      font-size: 0.75rem;
      border-radius: 6px;
    }
    .doc-btn-approve { 
      background: rgba(16,185,129,.15); color: #10b981; border: 1px solid rgba(16,185,129,.3);
      padding: 6px 14px; font-size: 0.8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; line-height: 1.2;
    }
    .doc-btn-approve:hover, .doc-btn-approve.active { background: #10b981; color: #fff; }
    .doc-btn-reject { 
      background: rgba(239,68,68,.1); color: #ef4444; border: 1px solid rgba(239,68,68,.3);
      padding: 6px 14px; font-size: 0.8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; line-height: 1.2;
    }
    .doc-btn-reject:hover, .doc-btn-reject.active { background: #ef4444; color: #fff; }
    .doc-btn-download { background: rgba(110,64,242,.1); color: #a78bfa; border: 1px solid rgba(110,64,242,.2); }
    .doc-btn-download:hover { background: var(--primary); color: #fff; }
    .doc-reject-note {
      margin-top: 8px;
      display: none;
    }
    .doc-reject-note textarea {
      width: 100%;
      background: rgba(239,68,68,.05);
      border: 1px solid rgba(239,68,68,.2);
      border-radius: 6px;
      color: #fff;
      padding: 8px 10px;
      font-size: 0.8rem;
      resize: vertical;
      min-height: 50px;
    }
    .doc-reject-note textarea::placeholder { color: rgba(239,68,68,.5); }

    /* ── Previous status badges ── */
    .doc-prev-status {
      font-size: 0.65rem;
      padding: 2px 6px;
      border-radius: 4px;
      font-weight: 600;
      text-transform: uppercase;
    }
    .doc-prev-approved { background: rgba(16,185,129,.15); color: #10b981; }
    .doc-prev-rejected { background: rgba(239,68,68,.15); color: #ef4444; }
    .doc-prev-pending { background: rgba(245,158,11,.15); color: #f59e0b; }

    /* ── Direct Reject Area ── */
    .direct-reject-area {
      display: none;
      margin-top: 20px;
      padding: 18px;
      background: rgba(239, 68, 68, 0.04);
      border: 1px solid rgba(239, 68, 68, 0.2);
      border-radius: 12px;
      animation: slideDown 0.3s ease forwards;
    }
    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .direct-reject-area textarea {
      width: 100%;
      background: rgba(0, 0, 0, 0.2);
      border: 1px solid rgba(239,68,68,.3);
      border-radius: 8px;
      color: #fff;
      padding: 12px;
      font-size: 0.9rem;
      resize: vertical;
      min-height: 80px;
      margin-bottom: 16px;
      font-family: inherit;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .direct-reject-area textarea:focus {
      border-color: #ef4444;
      box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    }
    .direct-reject-area textarea::placeholder { color: rgba(239,68,68,.4); }
  </style>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item" href="/admin/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Management</span>
      <a class="nav-item" href="/admin/users"><span class="nav-icon">👥</span> Users</a>
      <a class="nav-item" href="/admin/events"><span class="nav-icon">📅</span> Events</a>
      <a class="nav-item" href="/admin/venues"><span class="nav-icon">🏛️</span> Venues</a>
      <a class="nav-item active" href="/admin/verifications"><span class="nav-icon">🛡️</span> Verifications</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title"><script>document.write(t('Verification Requests'))</script></h1><p class="page-subtitle"><script>document.write(t('Review and approve partner applications'))</script></p></div>
    </div>

    <div id="vf-list">
      <div style="text-align:center; padding: 40px;"><div class="spinner" style="margin:auto"></div></div>
    </div>
  </main>
</div>

<!-- ── Document Review Modal ── -->
<div class="modal-overlay" id="doc-modal">
  <div class="modal" style="max-width: 640px;">
    <div class="modal-header">
      <h3 class="modal-title" id="doc-title"><script>document.write(t('Document Review'))</script></h3>
      <button class="modal-close" onclick="closeDocModal()">✕</button>
    </div>

    <div style="padding: 20px;" id="doc-cards-container">
      <!-- Document cards will be injected here -->
    </div>

    <div class="modal-footer" style="display:flex; justify-content:space-between; align-items:center;">
      <div style="display:flex; gap: 8px;">
        <button class="btn btn-primary" id="btn-submit-review" onclick="submitReview()">📤 <script>document.write(t('Submit Review'))</script></button>
        <button class="btn btn-danger" id="btn-direct-reject" onclick="toggleDirectReject()"><script>document.write(t('🚫 Reject Entirely'))</script></button>
      </div>
      <button class="btn btn-ghost" onclick="closeDocModal()"><script>document.write(t('Close'))</script></button>
    </div>

    <!-- Direct Reject Area -->
    <div class="direct-reject-area" id="direct-reject-area">
      <label class="form-label" style="color: #ef4444; font-size: 0.85rem; font-weight: 700; display:flex; align-items:center; gap:6px; margin-bottom: 12px;">
        <span style="font-size:1.1rem">🚫</span> <script>document.write(t('Rejection Reason'))</script>
      </label>
      <textarea id="direct-reject-reason"></textarea>
      <div style="display:flex; gap: 8px; justify-content: flex-end;">
        <button class="btn btn-ghost" onclick="toggleDirectReject()"><script>document.write(t('Cancel'))</script></button>
        <button class="btn" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; border: none; font-weight: 600; box-shadow: 0 4px 12px rgba(239,68,68,0.2);" onclick="confirmDirectReject()"><script>document.write(t('Confirm Rejection'))</script></button>
      </div>
    </div>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('direct-reject-reason').placeholder = t('Reason for rejecting this entire application...');
  });
  let currentReq = null;
  const DOC_TYPES = [
    { key: 'doc_commercial_register',     icon: '📋', label: 'Commercial Register',     labelAr: 'السجل التجاري' },
    { key: 'doc_tax_number',              icon: '🔢', label: 'Tax Number Certificate',  labelAr: 'شهادة الرقم الضريبي' },
    { key: 'doc_articles_of_association', icon: '📝', label: 'Articles of Association',  labelAr: 'عقد التأسيس' },
    { key: 'doc_practice_license',        icon: '🏢', label: 'Practice License',         labelAr: 'إذن المزاولة' },
  ];

  // Track review decisions
  let docDecisions = {};

  const user = requireRole('Admin');
  if (user) { populateSidebar(user); setActiveNav(); loadRequests(); }

  async function loadRequests() {
    const res = await api.get('/verifications/pending');
    const container = document.getElementById('vf-list');
    
    if (!res.ok) {
      container.innerHTML = '<div class="empty-state"><p style="color:var(--danger)">Failed to load requests.</p></div>';
      return;
    }

    const data = res.data;
    if (data.length === 0) {
      container.innerHTML = `
        <div class="empty-state" style="padding: 60px;">
          <div class="empty-icon">🎉</div>
          <p>${t('No pending verification requests.')}</p>
        </div>
      `;
      return;
    }

    container.innerHTML = data.map(req => {
      const icon = req.role === 'Sponsor' ? '💼' : '🎭';
      const isResubmit = req.verification_status === 'changes_requested';
      const isDocUpdate = req.verification_status === 'verified';
      let badge = '';
      if (isDocUpdate) {
        badge = `<span style="font-size: 0.7rem; background:#3b82f6; color:#fff; padding:2px 6px; border-radius:4px; margin-left:8px;">📄 ${t('Document Update')}</span>`;
      } else if (isResubmit) {
        badge = `<span style="font-size: 0.7rem; background:#0ea5e9; color:#fff; padding:2px 6px; border-radius:4px; margin-left:8px;">${t('🔄 Resubmitted')}</span>`;
      } else {
        badge = `<span style="font-size: 0.7rem; background:#10b981; color:#fff; padding:2px 6px; border-radius:4px; margin-left:8px;">${t('✨ New Request')}</span>`;
      }
      return `
        <div class="vf-card">
          <div class="vf-info">
            <div class="vf-icon">${icon}</div>
            <div>
              <div class="vf-name">${req.name} ${badge}</div>
              <div class="vf-email">${req.email}</div>
              <div class="vf-role">${t(req.role)}</div>
            </div>
          </div>
          <div>
            <div class="vf-date">${fmtDateShort(req.created_at)}</div>
            <div class="vf-actions">
              <button class="btn btn-ghost btn-sm" onclick='openDocModal(${JSON.stringify(req)})'>${isDocUpdate ? t('Review Update') : t('Review Application')}</button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  function openDocModal(req) {
    currentReq = req;
    docDecisions = {};
    const isDocUpdate = req.verification_status === 'verified';
    document.getElementById('doc-title').textContent = (isDocUpdate ? t('Review Document Update') : t('Review Application')) + `: ${req.name}`;
    document.getElementById('direct-reject-area').style.display = 'none';
    document.getElementById('direct-reject-reason').value = '';

    // Hide direct reject button for document updates (user is already verified)
    document.getElementById('btn-direct-reject').style.display = isDocUpdate ? 'none' : 'inline-flex';

    const container = document.getElementById('doc-cards-container');
    
    // Filter docs based on role
    let applicableDocs = req.role === 'Sponsor' 
      ? DOC_TYPES.filter(d => ['doc_commercial_register', 'doc_tax_number'].includes(d.key))
      : DOC_TYPES;

    // For document updates, only show docs that have pending_update status
    if (isDocUpdate) {
      applicableDocs = applicableDocs.filter(d => req[d.key + '_status'] === 'pending_update');
    }

    container.innerHTML = applicableDocs.map(doc => {
      const hasFile = !!req[doc.key];
      const currentStatus = req[doc.key + '_status'] || 'pending';
      const currentNote = req[doc.key + '_note'] || '';
      
      // Pre-populate decisions for already-approved docs
      if (currentStatus === 'approved') {
        docDecisions[doc.key] = { status: 'approved' };
      }

      const statusBadgeClass = currentStatus === 'approved' ? 'doc-prev-approved' 
        : currentStatus === 'rejected' ? 'doc-prev-rejected' 
        : currentStatus === 'pending_update' ? 'doc-prev-pending'
        : 'doc-prev-pending';

      const statusLabel = currentStatus === 'pending_update' ? t('Pending Review') : t(currentStatus);

      return `
        <div class="doc-review-card" id="review-card-${doc.key}">
          <div class="doc-review-header">
            <div class="doc-review-title">
              <span class="doc-icon">${doc.icon}</span>
              <span>${t(doc.label)}</span>
              <span class="doc-prev-status ${statusBadgeClass}">${statusLabel}</span>
            </div>
            <div class="doc-review-actions">
              ${hasFile ? `<button class="btn doc-btn-download" onclick="downloadDoc('${req.id}', '${doc.key}')">${t('⬇️ Download')}</button>` : `<span style="color:var(--text-muted); font-size:0.75rem;">${t('No file')}</span>`}
            </div>
          </div>
          
          ${currentNote && currentStatus === 'rejected' ? `<div style="font-size:0.75rem; color:#ef4444; background:rgba(239,68,68,.06); padding:6px 10px; border-radius:6px; margin-bottom:8px;">${t('Previous note: ')}${currentNote}</div>` : ''}

          <div style="display:flex; gap:6px; margin-top:4px;">
            <button class="btn doc-btn-approve" id="btn-approve-${doc.key}" onclick="setDocDecision('${doc.key}', 'approved')">${t('✅ Accept')}</button>
            <button class="btn doc-btn-reject" id="btn-reject-${doc.key}" onclick="setDocDecision('${doc.key}', 'rejected')">${t('❌ Reject')}</button>
          </div>
          
          <div class="doc-reject-note" id="note-area-${doc.key}">
            <textarea id="note-${doc.key}" placeholder="${t('Reason for rejecting this document...')}">${currentNote}</textarea>
          </div>
        </div>
      `;
    }).join('');

    document.getElementById('doc-modal').classList.add('open');
  }

  function setDocDecision(docKey, status) {
    const approveBtn = document.getElementById('btn-approve-' + docKey);
    const rejectBtn = document.getElementById('btn-reject-' + docKey);
    const noteArea = document.getElementById('note-area-' + docKey);
    const card = document.getElementById('review-card-' + docKey);

    // Reset classes
    approveBtn.classList.remove('active');
    rejectBtn.classList.remove('active');
    card.classList.remove('status-approved', 'status-rejected');

    if (status === 'approved') {
      approveBtn.classList.add('active');
      card.classList.add('status-approved');
      noteArea.style.display = 'none';
      docDecisions[docKey] = { status: 'approved' };
    } else {
      rejectBtn.classList.add('active');
      card.classList.add('status-rejected');
      noteArea.style.display = 'block';
      docDecisions[docKey] = { status: 'rejected', note: '' };
    }
  }

  async function submitReview() {
    if (!currentReq) return;
    const isDocUpdate = currentReq.verification_status === 'verified';

    // Get applicable docs
    let applicableDocs = currentReq.role === 'Sponsor' 
      ? DOC_TYPES.filter(d => ['doc_commercial_register', 'doc_tax_number'].includes(d.key))
      : DOC_TYPES;

    // For document updates, only validate pending_update docs
    if (isDocUpdate) {
      applicableDocs = applicableDocs.filter(d => currentReq[d.key + '_status'] === 'pending_update');
    }

    for (const doc of applicableDocs) {
      if (!docDecisions[doc.key]) {
        showToast(t('Please review "') + t(doc.label) + t('" before submitting.'), 'error');
        return;
      }
      if (docDecisions[doc.key].status === 'rejected') {
        const note = document.getElementById('note-' + doc.key).value.trim();
        if (!note) {
          showToast(t('Please provide a rejection reason for "') + t(doc.label) + '".', 'error');
          return;
        }
        docDecisions[doc.key].note = note;
      }
    }

    const btn = document.getElementById('btn-submit-review');
    btn.textContent = '...'; btn.disabled = true;

    const res = await api.put(`/verifications/${currentReq.id}/review`, { documents: docDecisions });

    if (res.ok) {
      showToast(res.data.message || t('Review submitted!'), 'success');
      closeDocModal();
      loadRequests();
    } else {
      showToast(res.data?.message || t('Error submitting review'), 'error');
    }

    btn.textContent = t('📤 Submit Review'); btn.disabled = false;
  }

  function toggleDirectReject() {
    const area = document.getElementById('direct-reject-area');
    area.style.display = area.style.display === 'none' ? 'block' : 'none';
  }

  async function confirmDirectReject() {
    if (!currentReq) return;
    const notes = document.getElementById('direct-reject-reason').value.trim();
    if (!notes) {
      showToast(t('Please provide a rejection reason.'), 'error');
      return;
    }

    if (!confirm(t('Are you sure you want to reject this entire application?'))) return;

    const res = await api.put(`/verifications/${currentReq.id}/reject`, { notes });
    if (res.ok) {
      showToast(t('Application rejected.'), 'info');
      closeDocModal();
      loadRequests();
    } else {
      showToast(t('Error rejecting application.'), 'error');
    }
  }

  function closeDocModal() {
    currentReq = null;
    docDecisions = {};
    document.getElementById('doc-modal').classList.remove('open');
    document.getElementById('direct-reject-area').style.display = 'none';
  }

  async function downloadDoc(userId, docType) {
    showToast(t('Downloading document...'), 'info');
    try {
      const res = await fetch(`/api/verifications/${userId}/document/${docType}`, {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
      });
      if (!res.ok) throw new Error('File not found');
      const blob = await res.blob();
      const urlObj = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.style.display = 'none';
      a.href = urlObj;
      a.download = `${docType}_${userId}`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(urlObj);
      showToast(t('Download complete'), 'success');
    } catch(e) {
      showToast(t('Error downloading file'), 'error');
    }
  }
</script>
</body>
</html>
