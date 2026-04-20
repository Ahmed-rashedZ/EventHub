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
      <div><h1 class="page-title">Verification Requests</h1><p class="page-subtitle">Review and approve partner applications</p></div>
    </div>

    <div id="vf-list">
      <div style="text-align:center; padding: 40px;"><div class="spinner" style="margin:auto"></div></div>
    </div>
  </main>
</div>

<!-- Document Modal -->
<div class="modal-overlay" id="doc-modal">
  <div class="modal" style="max-width: 600px;">
    <div class="modal-header">
      <h3 class="modal-title" id="doc-title">Document Review</h3>
      <button class="modal-close" onclick="closeDocModal()">✕</button>
    </div>
    <div style="padding: 20px; text-align: center;">
      <p style="color:var(--text-muted); margin-bottom: 16px;">This user has uploaded a verification document. Click below to securely download and review it.</p>
      
      <div id="prev-feedback-box" style="display:none; text-align:left; background: rgba(14, 165, 233, 0.05); border: 1px solid rgba(14, 165, 233, 0.2); padding: 12px; border-radius: 8px; margin-bottom: 16px;">
          <h5 style="color: #0ea5e9; font-size: 0.8rem; margin:0 0 4px 0; text-transform: uppercase;">Previous Feedback Requested</h5>
          <p id="prev-feedback-text" style="color: #fff; font-size: 0.85rem; margin:0; line-height: 1.4;"></p>
      </div>

      <a href="#" id="doc-download-btn" target="_blank" class="btn btn-primary" style="display:inline-block; text-decoration:none; padding:10px 20px;">⬇️ Download Document</a>
    </div>
    <div class="modal-footer" style="display:flex; justify-content:space-between; align-items:center;">
      <div style="display:flex; gap: 8px;">
        <button class="btn btn-success" id="btn-approve">✓ Approve</button>
        <button class="btn" style="background:#f59e0b; color:#fff;" id="btn-request-changes">🔄 Request Changes</button>
        <button class="btn btn-danger" id="btn-reject">✕ Reject</button>
      </div>
      <button class="btn btn-ghost" onclick="closeDocModal()">Close</button>
    </div>
    
    <div id="feedback-area" style="display:none; padding: 20px; border-top: 1px solid var(--border); background: rgba(255, 255, 255, 0.02);">
        <label class="form-label" id="feedback-label" style="color: #fff;">Feedback Notes</label>
        <textarea id="feedback-reason" class="form-control" rows="3" placeholder="Explain what changes are needed or why it was rejected..."></textarea>
        <div style="display:flex; gap: 10px; margin-top:10px;">
          <button class="btn btn-danger" style="width:100%; display:none;" id="btn-confirm-reject">Confirm Rejection</button>
          <button class="btn" style="width:100%; display:none; background:#f59e0b; color:#fff;" id="btn-confirm-changes">Confirm Changes Request</button>
        </div>
    </div>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
  let currentReq = null;
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
          <p>No pending verification requests.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = data.map(req => {
      const icon = req.role === 'Sponsor' ? '💼' : '🎭';
      return `
        <div class="vf-card">
          <div class="vf-info">
            <div class="vf-icon">${icon}</div>
            <div>
              <div class="vf-name">${req.name} ${req.verification_notes ? '<span style="font-size: 0.7rem; background:#0ea5e9; color:#fff; padding:2px 6px; border-radius:4px; margin-left:8px;">🔄 Resubmitted</span>' : '<span style="font-size: 0.7rem; background:#10b981; color:#fff; padding:2px 6px; border-radius:4px; margin-left:8px;">✨ New Request</span>'}</div>
              <div class="vf-email">${req.email}</div>
              <div class="vf-role">${req.role}</div>
            </div>
          </div>
          <div>
            <div class="vf-date">${fmtDateShort(req.created_at)}</div>
            <div class="vf-actions">
              <button class="btn btn-ghost btn-sm" onclick='openDocModal(${JSON.stringify(req)})'>Review Application</button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  function openDocModal(req) {
    currentReq = req;
    const statusText = req.verification_status === 'changes_requested' ? ' (Waiting for Partner)' : '';
    document.getElementById('doc-title').textContent = `Review: ${req.name}${statusText}`;
    
    if (req.verification_notes) {
        document.getElementById('prev-feedback-box').style.display = 'block';
        document.getElementById('prev-feedback-text').textContent = req.verification_notes;
    } else {
        document.getElementById('prev-feedback-box').style.display = 'none';
    }

    const dlBtn = document.getElementById('doc-download-btn');
    
    const token = localStorage.getItem('token');
    // For protected file downloads, we can append token or handle it nicely. 
    // Usually browser downloads might not send Authorization header easily. A simple way for the demo is a query param if backend supports,
    // or fetch as blob. Let's use fetch API to download nicely with the bearer token.
    dlBtn.onclick = (e) => {
        e.preventDefault();
        downloadWithAuth(`/api/verifications/${req.id}/document`, `document_${req.id}`);
    };

    document.getElementById('feedback-area').style.display = 'none';
    document.getElementById('doc-modal').classList.add('open');
  }

  function closeDocModal() {
    currentReq = null;
    document.getElementById('doc-modal').classList.remove('open');
    document.getElementById('feedback-reason').value = '';
  }

  async function downloadWithAuth(url, filename) {
      showToast('Downloading document...', 'info');
      try {
          const res = await fetch(url, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
          if (!res.ok) throw new Error('File not found');
          const blob = await res.blob();
          const urlObj = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.style.display = 'none';
          a.href = urlObj;
          a.download = filename;
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(urlObj);
          showToast('Download complete', 'success');
      } catch(e) {
          showToast('Error downloading file', 'error');
      }
  }

  document.getElementById('btn-approve').onclick = async () => {
      if(!currentReq) return;
      if(!confirm('Are you sure you want to approve this application?')) return;
      
      const res = await api.put(`/verifications/${currentReq.id}/approve`);
      if(res.ok) {
          showToast('Partner Approved!', 'success');
          closeDocModal();
          loadRequests();
      } else {
          showToast('Error approving', 'error');
      }
  };

  document.getElementById('btn-reject').onclick = () => {
      document.getElementById('feedback-area').style.display = 'block';
      document.getElementById('feedback-area').style.background = 'rgba(239, 68, 68, 0.05)';
      document.getElementById('feedback-label').textContent = 'Rejection Reason';
      document.getElementById('feedback-label').style.color = '#ef4444';
      document.getElementById('btn-confirm-reject').style.display = 'block';
      document.getElementById('btn-confirm-changes').style.display = 'none';
  };

  document.getElementById('btn-request-changes').onclick = () => {
      document.getElementById('feedback-area').style.display = 'block';
      document.getElementById('feedback-area').style.background = 'rgba(245, 158, 11, 0.05)';
      document.getElementById('feedback-label').textContent = 'Changes Needed';
      document.getElementById('feedback-label').style.color = '#f59e0b';
      document.getElementById('btn-confirm-reject').style.display = 'none';
      document.getElementById('btn-confirm-changes').style.display = 'block';
  };

  document.getElementById('btn-confirm-reject').onclick = async () => {
      if(!currentReq) return;
      const notes = document.getElementById('feedback-reason').value;
      if(!notes.trim()) { showToast('Please provide a reason', 'error'); return; }
      
      const res = await api.put(`/verifications/${currentReq.id}/reject`, { notes });
      if(res.ok) {
          showToast('Partner Rejected', 'info');
          closeDocModal();
          loadRequests();
      } else {
          showToast('Error rejecting', 'error');
      }
  };

  document.getElementById('btn-confirm-changes').onclick = async () => {
      if(!currentReq) return;
      const notes = document.getElementById('feedback-reason').value;
      if(!notes.trim()) { showToast('Please describe what changes are needed', 'error'); return; }
      
      const res = await api.put(`/verifications/${currentReq.id}/request-changes`, { notes });
      if(res.ok) {
          showToast('Changes Requested', 'success');
          closeDocModal();
          loadRequests();
      } else {
          showToast('Error requesting changes', 'error');
      }
  };
</script>
</body>
</html>
