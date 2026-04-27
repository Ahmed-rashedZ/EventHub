<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pending Verification – EventHub</title>
  <link rel="stylesheet" href="/css/style.css" />
  <script src="/js/i18n.js"></script>
  <style>
    .doc-status-card {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 14px;
      border-radius: 8px;
      margin-bottom: 10px;
      border: 1px solid var(--border);
      background: rgba(255,255,255,.02);
    }
    .doc-status-card.doc-approved {
      border-color: rgba(16,185,129,.3);
      background: rgba(16,185,129,.04);
    }
    .doc-status-card.doc-rejected {
      border-color: rgba(239,68,68,.3);
      background: rgba(239,68,68,.04);
    }
    .doc-status-card.doc-pending {
      border-color: rgba(245,158,11,.3);
      background: rgba(245,158,11,.04);
    }
    .doc-status-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .doc-status-icon { font-size: 1.3rem; }
    .doc-status-label { font-weight: 600; color: #fff; font-size: 0.85rem; }
    .doc-status-badge {
      font-size: 0.65rem;
      padding: 2px 8px;
      border-radius: 4px;
      font-weight: 700;
      text-transform: uppercase;
    }
    .badge-approved { background: rgba(16,185,129,.15); color: #10b981; }
    .badge-rejected-doc { background: rgba(239,68,68,.15); color: #ef4444; }
    .badge-pending-doc { background: rgba(245,158,11,.15); color: #f59e0b; }
    .doc-reject-reason {
      font-size: 0.75rem;
      color: #ef4444;
      margin-top: 4px;
      padding-left: 32px;
    }
    .doc-reupload {
      margin-top: 6px;
      padding-left: 32px;
    }
    .doc-reupload input[type="file"] {
      font-size: 0.8rem;
      color: var(--text-muted);
    }
  </style>
</head>
<body>
  <div class="auth-page">
    <div class="auth-bg-glow"></div>
    <div class="auth-card" style="text-align: center; max-width: 520px;">
      <div style="font-size: 3rem; margin-bottom: 16px;" id="main-icon">⏳</div>
      <h2 style="margin: 0 0 16px 0; color: #fff;" id="main-heading">Application Under Review</h2>
      <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 24px;" id="main-desc">
        Your partner application has been received successfully. Our administration team is currently verifying the documents you uploaded.
      </p>
      
      <div style="padding: 16px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 8px; margin-bottom: 24px;" id="status-box">
        <h4 style="color: #f59e0b; margin: 0 0 4px 0; font-size: 0.85rem; text-transform: uppercase;">Current Status</h4>
        <div style="font-size: 1.1rem; font-weight: 700; color: #fff;" id="status-display">PENDING</div>
        <p style="color: #f59e0b; font-size: 0.8rem; margin: 8px 0 0 0;" id="notes-display"></p>
      </div>

      <!-- ── Document Status List ── -->
      <div id="docs-status-section" style="display:none; text-align:left; margin-bottom: 24px;">
        <h4 style="color:#fff; font-size:0.9rem; margin-bottom:12px;">📄 Document Status</h4>
        <div id="docs-list"></div>

        <div id="resubmit-section" style="display:none; margin-top:16px;">
          <button onclick="resubmitDocuments()" id="btn-resubmit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
            📤 Resubmit Documents
          </button>
        </div>
      </div>

      <button onclick="logout()" class="btn btn-ghost" style="width: 100%;">Sign Out</button>
    </div>
  </div>

  <div id="toast-container"></div>
  <script src="/js/api.js"></script>
  <script>
    const DOC_TYPES = [
      { key: 'doc_commercial_register',     icon: '📋', label: 'Commercial Register',     labelAr: 'السجل التجاري' },
      { key: 'doc_tax_number',              icon: '🔢', label: 'Tax Number Certificate',  labelAr: 'شهادة الرقم الضريبي' },
      { key: 'doc_articles_of_association', icon: '📝', label: 'Articles of Association',  labelAr: 'عقد التأسيس' },
      { key: 'doc_practice_license',        icon: '🏢', label: 'Practice License',         labelAr: 'إذن المزاولة' },
    ];

    const uStr = localStorage.getItem('user');
    if (!uStr) window.location.href = '/login';
    else {
      try {
        const u = JSON.parse(uStr);
        if (u.verification_status === 'verified') {
          window.location.href = u.role === 'Event Manager' ? '/manager/dashboard' : '/sponsor/dashboard';
        }
        else if (u.verification_status === 'rejected') {
          document.getElementById('main-icon').textContent = '🚫';
          document.getElementById('main-heading').textContent = 'Application Rejected';
          document.getElementById('main-desc').textContent = 'Unfortunately, your partner application has been rejected.';
          const sd = document.getElementById('status-display');
          sd.textContent = 'REJECTED';
          sd.style.color = '#ef4444';
          const box = document.getElementById('status-box');
          box.style.background = 'rgba(239, 68, 68, 0.1)';
          box.style.borderColor = 'rgba(239, 68, 68, 0.4)';
          box.querySelector('h4').style.color = '#ef4444';
          if (u.verification_notes) {
            document.getElementById('notes-display').textContent = 'Reason: ' + u.verification_notes;
            document.getElementById('notes-display').style.color = '#ef4444';
          }
        }
        else if (u.verification_status === 'changes_requested') {
          document.getElementById('main-icon').textContent = '⚠️';
          document.getElementById('main-heading').textContent = 'Action Required';
          document.getElementById('main-desc').textContent = 'Some of your documents need to be revised. Please re-upload the rejected documents below.';
          const sd = document.getElementById('status-display');
          sd.textContent = 'CHANGES REQUESTED';
          sd.style.color = '#f59e0b';

          // Show document status
          renderDocumentStatuses(u);
        }
        else if (u.verification_status === 'pending') {
          // Show all documents as pending
          renderDocumentStatuses(u);
        }
      } catch(e) { console.error(e); }
    }

    function renderDocumentStatuses(u) {
      const section = document.getElementById('docs-status-section');
      section.style.display = 'block';
      const list = document.getElementById('docs-list');
      let hasRejected = false;

      list.innerHTML = DOC_TYPES.map(doc => {
        const status = u[doc.key + '_status'] || 'pending';
        const note = u[doc.key + '_note'] || '';
        const isRejected = status === 'rejected';
        if (isRejected) hasRejected = true;

        const cardClass = status === 'approved' ? 'doc-approved' : status === 'rejected' ? 'doc-rejected' : 'doc-pending';
        const badgeClass = status === 'approved' ? 'badge-approved' : status === 'rejected' ? 'badge-rejected-doc' : 'badge-pending-doc';
        const statusIcon = status === 'approved' ? '✅' : status === 'rejected' ? '❌' : '⏳';

        return `
          <div class="doc-status-card ${cardClass}">
            <div class="doc-status-info">
              <span class="doc-status-icon">${doc.icon}</span>
              <span class="doc-status-label">${doc.label}</span>
            </div>
            <span class="doc-status-badge ${badgeClass}">${statusIcon} ${status}</span>
          </div>
          ${isRejected && note ? `<div class="doc-reject-reason">💬 "${note}"</div>` : ''}
          ${isRejected ? `<div class="doc-reupload"><input type="file" id="reupload-${doc.key}" accept=".pdf,.jpg,.jpeg,.png" /></div>` : ''}
        `;
      }).join('');

      if (hasRejected) {
        document.getElementById('resubmit-section').style.display = 'block';
      }
    }

    async function resubmitDocuments() {
      const btn = document.getElementById('btn-resubmit');
      btn.textContent = 'Submitting...'; btn.disabled = true;

      const formData = new FormData();
      let hasFiles = false;

      for (const doc of DOC_TYPES) {
        const input = document.getElementById('reupload-' + doc.key);
        if (input && input.files.length > 0) {
          formData.append(doc.key, input.files[0]);
          hasFiles = true;
        }
      }

      if (!hasFiles) {
        showToast('Please select at least one document to re-upload.', 'error');
        btn.textContent = '📤 Resubmit Documents'; btn.disabled = false;
        return;
      }

      const res = await api.postForm('/verifications/reupload', formData);

      if (res.ok) {
        showToast('Documents submitted successfully!', 'success');
        localStorage.setItem('user', JSON.stringify(res.data.user));
        setTimeout(() => window.location.reload(), 1000);
      } else {
        showToast(res.data?.message || 'Error re-uploading documents.', 'error');
        btn.textContent = '📤 Resubmit Documents'; btn.disabled = false;
      }
    }

    async function logout() {
      try {
        await fetch('/api/logout', { method: 'POST', headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token'), 'Accept': 'application/json' } });
      } catch(e) {}
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
      window.location.href = '/login';
    }
  </script>
</body>
</html>
