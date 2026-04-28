<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pending Verification – EventHub</title>
  <link rel="stylesheet" href="/css/style.css" />
  <script src="/js/i18n.js"></script>
  <style>
    .doc-status-wrapper {
      margin-bottom: 12px;
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid var(--border);
      background: rgba(255,255,255,.02);
    }
    .doc-status-wrapper.doc-approved {
      border-color: rgba(16,185,129,.3);
    }
    .doc-status-wrapper.doc-rejected {
      border-color: rgba(239,68,68,.3);
    }
    .doc-status-wrapper.doc-pending {
      border-color: rgba(245,158,11,.3);
    }
    .doc-status-card {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 14px;
      background: transparent;
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
    
    .doc-rejected-actions {
      padding: 12px 16px;
      background: rgba(239,68,68,.03);
      border-top: 1px dashed rgba(239,68,68,.2);
    }
    .doc-reject-reason {
      font-size: 0.85rem;
      color: #ef4444;
      background: rgba(239, 68, 68, 0.08);
      border-left: 3px solid #ef4444;
      padding: 10px 12px;
      border-radius: 4px;
      line-height: 1.5;
      margin-bottom: 12px;
    }
    
    .custom-file-upload {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 10px;
      background: rgba(110,64,242,.05);
      border: 1px dashed rgba(110,64,242,.4);
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.2s ease;
      width: 100%;
    }
    .custom-file-upload:hover {
      background: rgba(110,64,242,.1);
      border-color: rgba(110,64,242,.6);
    }
    .custom-file-upload input[type="file"] {
      display: none;
    }
    .upload-icon {
      font-size: 1.1rem;
    }
    .upload-text {
      font-size: 0.85rem;
      color: #a78bfa;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="auth-page">
    <div class="auth-bg-glow"></div>
    <div class="auth-card" style="text-align: center; max-width: 520px;">
      <div style="font-size: 3rem; margin-bottom: 16px;" id="main-icon">⏳</div>
      <h2 style="margin: 0 0 16px 0; color: #fff;" id="main-heading"><script>document.write(t('Application Under Review'))</script></h2>
      <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 24px;" id="main-desc">
        <script>document.write(t('Your partner application has been received successfully. Our administration team is currently verifying the documents you uploaded.'))</script>
      </p>
      
      <div style="padding: 16px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 8px; margin-bottom: 24px;" id="status-box">
        <h4 style="color: #f59e0b; margin: 0 0 4px 0; font-size: 0.85rem; text-transform: uppercase;"><script>document.write(t('Current Status'))</script></h4>
        <div style="font-size: 1.1rem; font-weight: 700; color: #fff;" id="status-display"><script>document.write(t('PENDING'))</script></div>
        <p style="color: #f59e0b; font-size: 0.8rem; margin: 8px 0 0 0;" id="notes-display"></p>
      </div>

      <!-- ── Document Status List ── -->
      <div id="docs-status-section" style="display:none; text-align:left; margin-bottom: 24px;">
        <h4 style="color:#fff; font-size:0.9rem; margin-bottom:12px;">📄 <script>document.write(t('Document Status'))</script></h4>
        <div id="docs-list"></div>

        <div id="resubmit-section" style="display:none; margin-top:16px;">
          <button onclick="resubmitDocuments()" id="btn-resubmit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
            📤 <script>document.write(t('Resubmit Documents'))</script>
          </button>
        </div>
      </div>

      <button onclick="logout()" class="btn btn-ghost" style="width: 100%;"><script>document.write(t('Sign Out'))</script></button>
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
          document.getElementById('main-heading').textContent = t('Application Rejected');
          document.getElementById('main-desc').textContent = t('Unfortunately, your partner application has been rejected.');
          const sd = document.getElementById('status-display');
          sd.textContent = t('REJECTED');
          sd.style.color = '#ef4444';
          const box = document.getElementById('status-box');
          box.style.background = 'rgba(239, 68, 68, 0.1)';
          box.style.borderColor = 'rgba(239, 68, 68, 0.4)';
          box.querySelector('h4').style.color = '#ef4444';
          if (u.verification_notes) {
            document.getElementById('notes-display').textContent = t('Reason: ') + u.verification_notes;
            document.getElementById('notes-display').style.color = '#ef4444';
          }
        }
        else if (u.verification_status === 'changes_requested') {
          document.getElementById('main-icon').textContent = '⚠️';
          document.getElementById('main-heading').textContent = t('Action Required');
          document.getElementById('main-desc').textContent = t('Some of your documents need to be revised. Please re-upload the rejected documents below.');
          const sd = document.getElementById('status-display');
          sd.textContent = t('CHANGES REQUESTED');
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

      // Filter docs based on role
      const applicableDocs = u.role === 'Sponsor' 
        ? DOC_TYPES.filter(d => ['doc_commercial_register', 'doc_tax_number'].includes(d.key))
        : DOC_TYPES;

      list.innerHTML = applicableDocs.map(doc => {
        const status = u[doc.key + '_status'] || 'pending';
        const note = u[doc.key + '_note'] || '';
        const isRejected = status === 'rejected';
        if (isRejected) hasRejected = true;

        const cardClass = status === 'approved' ? 'doc-approved' : status === 'rejected' ? 'doc-rejected' : 'doc-pending';
        const badgeClass = status === 'approved' ? 'badge-approved' : status === 'rejected' ? 'badge-rejected-doc' : 'badge-pending-doc';
        const statusIcon = status === 'approved' ? '✅' : status === 'rejected' ? '❌' : '⏳';

        return `
          <div class="doc-status-wrapper ${cardClass}">
            <div class="doc-status-card">
              <div class="doc-status-info">
                <span class="doc-status-icon">${doc.icon}</span>
                <span class="doc-status-label">${t(doc.label)}</span>
              </div>
              <span class="doc-status-badge ${badgeClass}">${statusIcon} ${t(status)}</span>
            </div>
            ${isRejected ? `
              <div class="doc-rejected-actions">
                ${note ? `<div class="doc-reject-reason"><strong>💬 ${t('Reason:')}</strong> ${note}</div>` : ''}
                <label class="custom-file-upload">
                  <input type="file" id="reupload-${doc.key}" accept=".pdf,.jpg,.jpeg,.png" onchange="updateFileName(this, '${doc.key}')" />
                  <span class="upload-icon">📁</span>
                  <span class="upload-text" id="fname-${doc.key}">${t('Click to select new file...')}</span>
                </label>
              </div>
            ` : ''}
          </div>
        `;
      }).join('');

      if (hasRejected) {
        document.getElementById('resubmit-section').style.display = 'block';
      }
    }

    function updateFileName(input, docKey) {
      const textSpan = document.getElementById('fname-' + docKey);
      if (input.files && input.files.length > 0) {
        textSpan.textContent = input.files[0].name;
        textSpan.style.color = '#fff';
      } else {
        textSpan.textContent = t('Click to select new file...');
        textSpan.style.color = '#a78bfa';
      }
    }

    async function resubmitDocuments() {
      const btn = document.getElementById('btn-resubmit');
      btn.textContent = t('Submitting...'); btn.disabled = true;

      const formData = new FormData();
      let hasFiles = false;
      const uStr = localStorage.getItem('user');
      const user = uStr ? JSON.parse(uStr) : {};

      const applicableDocs = user.role === 'Sponsor' 
        ? DOC_TYPES.filter(d => ['doc_commercial_register', 'doc_tax_number'].includes(d.key))
        : DOC_TYPES;

      for (const doc of applicableDocs) {
        const input = document.getElementById('reupload-' + doc.key);
        if (input && input.files.length > 0) {
          formData.append(doc.key, input.files[0]);
          hasFiles = true;
        }
      }

      if (!hasFiles) {
        showToast(t('Please select at least one document to re-upload.'), 'error');
        btn.textContent = t('📤 Resubmit Documents'); btn.disabled = false;
        return;
      }

      const res = await api.postForm('/verifications/reupload', formData);

      if (res.ok) {
        showToast(t('Documents submitted successfully!'), 'success');
        localStorage.setItem('user', JSON.stringify(res.data.user));
        setTimeout(() => window.location.reload(), 1000);
      } else {
        showToast(res.data?.message || t('Error re-uploading documents.'), 'error');
        btn.textContent = t('📤 Resubmit Documents'); btn.disabled = false;
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
