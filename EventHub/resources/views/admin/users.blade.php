<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Users – EventHub Admin</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item" href="/admin/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Management</span>
      <a class="nav-item active" href="/admin/users"><span class="nav-icon">👥</span> Users</a>
      <a class="nav-item" href="/admin/events"><span class="nav-icon">📅</span> Events</a>
      <a class="nav-item" href="/admin/venues"><span class="nav-icon">🏛️</span> Venues</a>
      <a class="nav-item" href="/admin/verifications"><span class="nav-icon">🛡️</span> Verifications</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title"><script>document.write(t('Users'))</script></h1><p class="page-subtitle"><script>document.write(t('Manage all platform users'))</script></p></div>
      <div class="topbar-actions">
        <div class="search-bar"><span>🔍</span><input id="search-input" type="text" placeholder="Search users…" oninput="filterTable()"/></div>
      </div>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table id="users-table">
          <thead>
            <tr>
              <th>#</th>
              <th><script>document.write(t('Name'))</script></th>
              <th><script>document.write(t('Email'))</script></th>
              <th><script>document.write(t('Role'))</script></th>
              <th><script>document.write(t('Joined'))</script></th>
              <th><script>document.write(t('Actions'))</script></th>
            </tr>
          </thead>
          <tbody id="users-body">
            <tr class="loading-row"><td colspan="6"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Documents Review Modal -->
<div class="modal-overlay" id="docs-modal">
  <div class="modal" style="max-width: 560px; max-height: 90vh; overflow-y: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
      <h3 style="margin: 0; color: #fff;" id="docs-modal-title"></h3>
      <button class="btn btn-ghost btn-sm" onclick="closeDocsModal()">✕</button>
    </div>
    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 18px;" id="docs-modal-subtitle"></p>
    <div id="docs-modal-list" style="display: flex; flex-direction: column; gap: 12px;"></div>
    <div id="docs-submit-row" style="display: none; justify-content: flex-end; gap: 10px; margin-top: 16px;">
      <button class="btn btn-primary" onclick="submitDocReview()">📤 <script>document.write(t('Submit Review'))</script></button>
    </div>
    <div style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center;">
      <span style="font-size: 0.8rem; color: var(--text-muted);" id="docs-modal-info"></span>
      <button class="btn btn-ghost btn-sm" onclick="closeDocsModal()"><script>document.write(t('Close'))</script></button>
    </div>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script>
  let allUsers = [];
  let currentDocUser = null;
  let docDecisions = {};

  const user = requireRole('Admin');
  if (user) { populateSidebar(user); setActiveNav(); loadUsers(); }

  async function loadUsers() {
    const res = await api.get('/analytics/users');
    const tbody = document.getElementById('users-body');
    if (!res.ok) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--danger)">Failed to load users</td></tr>'; return; }
    allUsers = res.data;
    renderUsers(allUsers);
  }

  function renderUsers(users) {
    const tbody = document.getElementById('users-body');
    if (!users.length) {
      tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><div class="empty-icon">👥</div><p>${t('No users found')}</p></div></td></tr>`;
      return;
    }
    tbody.innerHTML = users.map((u, i) => {
      const isPartner = u.role === 'Event Manager' || u.role === 'Sponsor';
      const nameCell = isPartner
        ? `<a href="/user-profile?id=${u.id}" style="display:flex;align-items:center;gap:8px;text-decoration:none;color:var(--accent);transition:opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">${u.name}</a>`
        : `<span style="font-weight:500;">${u.name}</span>`;

      const docBtn = isPartner && (u.doc_commercial_register || u.doc_tax_number)
        ? `<button class="btn btn-ghost btn-sm" style="background:rgba(139,92,246,0.1);color:#a78bfa;"
             onclick="openDocsModal(${u.id}, '${u.role}', '${u.name.replace(/'/g,"\\'")}', '${u.verification_status}')">
             📄 ${t('Documents')}</button>`
        : '';

      return `<tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td>${nameCell}</td>
        <td style="color:var(--text-muted)">${u.email}</td>
        <td>${roleBadge(u.role)}</td>
        <td style="color:var(--text-muted)">${fmtDateShort(u.created_at)}</td>
        <td>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            ${u.role !== 'Admin' ? (u.is_active
              ? `<button class="btn btn-warning btn-sm" onclick="toggleUserStatus(${u.id},'${u.name}',false)">${t('⏸ Suspend')}</button>`
              : `<button class="btn btn-success btn-sm" onclick="toggleUserStatus(${u.id},'${u.name}',true)">${t('▶ Activate')}</button>`)
            : ''}
            ${u.role !== 'Admin' ? `<button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id},'${u.name}')">${t('🗑 Delete')}</button>` : ''}
            ${docBtn}
          </div>
        </td>
      </tr>`;
    }).join('');
  }

  function filterTable() {
    const q = document.getElementById('search-input').value.toLowerCase();
    renderUsers(allUsers.filter(u =>
      u.name.toLowerCase().includes(q) ||
      u.email.toLowerCase().includes(q) ||
      u.role.toLowerCase().includes(q)
    ));
  }

  async function toggleUserStatus(id, name, activate) {
    const msg = activate ? t('Are you sure you want to activate user "') : t('Are you sure you want to suspend user "');
    if (!confirm(msg + name + '"?')) return;
    const res = await api.patch(`/analytics/users/${id}/status`);
    if (res.ok) { showToast(activate ? t('User activated successfully.') : t('User suspended successfully.'), 'success'); loadUsers(); }
    else showToast(res.data?.message || t('Error updating status'), 'error');
  }

  async function deleteUser(id, name) {
    if (!confirm(t('Delete user "') + name + t('"? This cannot be undone.'))) return;
    const res = await api.delete(`/analytics/users/${id}`);
    if (res.ok) { showToast(t('User deleted'), 'success'); loadUsers(); }
    else showToast(res.data?.message || t('Error deleting user'), 'error');
  }

  // ── Documents Review Modal ──────────────────────────────────────────────
  const ALL_DOC_TYPES = [
    { key: 'doc_commercial_register',     label: 'Commercial Register',     icon: '📋' },
    { key: 'doc_tax_number',              label: 'Tax Number Certificate',  icon: '🔢' },
    { key: 'doc_articles_of_association', label: 'Articles of Association', icon: '📝' },
    { key: 'doc_practice_license',        label: 'Practice License',        icon: '🏢' },
  ];

  async function openDocsModal(id, role, name, verStatus) {
    currentDocUser = { id, role, name, verStatus };
    docDecisions = {};

    document.getElementById('docs-modal-title').textContent = '📄 ' + name;
    document.getElementById('docs-modal-subtitle').textContent =
      t('Review verification documents. Accept or reject individual documents — the partner will be notified instantly.');

    const vsMap = {
      verified:          { color: '#10b981', label: t('Verified') },
      pending:           { color: '#f59e0b', label: t('Pending') },
      changes_requested: { color: '#f59e0b', label: t('Changes Requested') },
      rejected:          { color: '#ef4444', label: t('Rejected') },
    };
    const vs = vsMap[verStatus] || { color: '#6b7280', label: verStatus };
    document.getElementById('docs-modal-info').innerHTML =
      `${t('Account Status')}: <strong style="color:${vs.color}">${vs.label}</strong>`;

    document.getElementById('docs-submit-row').style.display = 'none';
    document.getElementById('docs-modal-list').innerHTML =
      `<div style="text-align:center;padding:30px;"><div class="spinner" style="margin:auto;"></div></div>`;
    document.getElementById('docs-modal').classList.add('open');

    // Fetch fresh user data
    const res = await api.get('/analytics/users');
    const userData = res.ok ? res.data.find(u => u.id === id) : null;

    const docTypes = role === 'Sponsor'
      ? ALL_DOC_TYPES.filter(d => ['doc_commercial_register','doc_tax_number'].includes(d.key))
      : ALL_DOC_TYPES;

    const statusStyles = {
      approved:       { color: '#10b981', bg: 'rgba(16,185,129,0.08)', border: 'rgba(16,185,129,0.3)', icon: '✅' },
      rejected:       { color: '#ef4444', bg: 'rgba(239,68,68,0.08)',  border: 'rgba(239,68,68,0.3)',  icon: '❌' },
      pending_update: { color: '#3b82f6', bg: 'rgba(59,130,246,0.08)', border: 'rgba(59,130,246,0.3)', icon: '🔄' },
      pending:        { color: '#f59e0b', bg: 'rgba(245,158,11,0.08)', border: 'rgba(245,158,11,0.3)', icon: '⏳' },
    };

    document.getElementById('docs-modal-list').innerHTML = docTypes.map(doc => {
      const status  = userData?.[doc.key + '_status'] || 'pending';
      const note    = (userData?.[doc.key + '_note'] || '').replace(/"/g, '&quot;');
      const hasFile = !!(userData?.[doc.key]);
      const ss      = statusStyles[status] || statusStyles.pending;
      const statusLabel = status === 'pending_update' ? t('Pending Review') : t(status);

      return `
        <div id="doc-card-${doc.key}" style="border:1px solid ${ss.border};border-radius:10px;overflow:hidden;">
          <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:${ss.bg};">
            <div style="display:flex;align-items:center;gap:10px;">
              <span style="font-size:1.3rem;">${doc.icon}</span>
              <div>
                <div style="color:#fff;font-weight:600;font-size:0.9rem;">${t(doc.label)}</div>
                <span style="font-size:0.7rem;font-weight:700;color:${ss.color};text-transform:uppercase;">${ss.icon} ${statusLabel}</span>
              </div>
            </div>
            ${hasFile
              ? `<button class="btn btn-ghost btn-sm" onclick="downloadSingleDoc(${id},'${doc.key}')" style="padding:5px 10px;font-size:0.75rem;">⬇️ ${t('Download')}</button>`
              : `<span style="color:var(--text-muted);font-size:0.75rem;">${t('No file')}</span>`}
          </div>
          ${note ? `<div style="padding:8px 14px;background:rgba(239,68,68,0.05);border-top:1px solid rgba(239,68,68,0.15);font-size:0.8rem;color:#ef4444;">💬 ${userData?.[doc.key + '_note'] || ''}</div>` : ''}
          <div style="padding:12px 14px;background:rgba(0,0,0,0.1);display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <button id="btn-acc-${doc.key}" onclick="setDocAction('${doc.key}','approved')"
              style="padding:5px 14px;font-size:0.8rem;border-radius:6px;cursor:pointer;display:${status === 'approved' ? 'none' : 'inline-flex'};align-items:center;gap:5px;
                     background:rgba(16,185,129,0.1); color:#10b981; border:1px solid rgba(16,185,129,0.4);">
              ✅ ${t('Accept')}
            </button>
            <button id="btn-rej-${doc.key}" onclick="setDocAction('${doc.key}','rejected')"
              style="padding:5px 14px;font-size:0.8rem;border-radius:6px;cursor:pointer;display:${status === 'rejected' ? 'none' : 'inline-flex'};align-items:center;gap:5px;
                     background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.3);">
              ❌ ${t('Reject')}
            </button>
            <div id="note-area-${doc.key}" style="flex-basis:100%;display:${status==='rejected'?'block':'none'};margin-top:6px;">
              <input type="text" id="note-input-${doc.key}" value="${note}"
                placeholder="${t('Reason for rejection...')}"
                style="width:100%;background:rgba(239,68,68,0.05);border:1px solid rgba(239,68,68,0.3);border-radius:6px;color:#fff;padding:7px 10px;font-size:0.8rem;box-sizing:border-box;">
            </div>
          </div>
        </div>`;
    }).join('');
  }

  function setDocAction(docKey, status) {
    if (!docDecisions[docKey]) docDecisions[docKey] = {};
    docDecisions[docKey].status = status;

    // Highlight buttons
    const accBtn = document.getElementById('btn-acc-' + docKey);
    const rejBtn = document.getElementById('btn-rej-' + docKey);
    if (accBtn && rejBtn) {
      // Once an action is clicked, ensure both buttons are visible so they can change their mind
      accBtn.style.display = 'inline-flex';
      rejBtn.style.display = 'inline-flex';

      if (status === 'approved') {
        accBtn.style.background = '#10b981'; accBtn.style.color = '#fff';
        rejBtn.style.background = 'rgba(239,68,68,0.1)'; rejBtn.style.color = '#ef4444';
      } else {
        rejBtn.style.background = '#ef4444'; rejBtn.style.color = '#fff';
        accBtn.style.background = 'rgba(16,185,129,0.1)'; accBtn.style.color = '#10b981';
      }
    }

    // Show/hide note input
    const noteArea = document.getElementById('note-area-' + docKey);
    if (noteArea) noteArea.style.display = status === 'rejected' ? 'block' : 'none';

    // Show submit button
    document.getElementById('docs-submit-row').style.display = 'flex';
  }

  async function submitDocReview() {
    if (!currentDocUser || Object.keys(docDecisions).length === 0) {
      showToast(t('Please make a decision on at least one document.'), 'error');
      return;
    }

    // Validate rejection notes
    for (const [key, dec] of Object.entries(docDecisions)) {
      if (dec.status === 'rejected') {
        const note = document.getElementById('note-input-' + key)?.value?.trim();
        if (!note) {
          showToast(t('Please provide a rejection reason for the document.'), 'error');
          return;
        }
        dec.note = note;
      }
    }

    const btn = document.querySelector('#docs-submit-row button');
    const origTxt = btn.innerHTML;
    btn.innerHTML = `<span class="spinner" style="width:14px;height:14px;border-width:2px;border-color:#fff;border-top-color:transparent;display:inline-block;"></span>`;
    btn.disabled = true;

    const res = await api.put(`/verifications/${currentDocUser.id}/review`, { documents: docDecisions });

    if (res.ok) {
      showToast(res.data?.message || t('Review submitted!'), 'success');
      docDecisions = {};
      closeDocsModal();
      loadUsers();
    } else {
      showToast(res.data?.message || t('Error submitting review'), 'error');
      btn.innerHTML = origTxt;
      btn.disabled = false;
    }
  }

  function closeDocsModal() {
    document.getElementById('docs-modal').classList.remove('open');
    docDecisions = {};
    currentDocUser = null;
  }

  async function downloadSingleDoc(id, docKey) {
    showToast(t('Downloading document...'), 'info');
    try {
      const res = await fetch(`/api/verifications/${id}/document/${docKey}`, {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
      });
      if (!res.ok) { showToast(t('Error downloading file'), 'error'); return; }
      const blob = await res.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.style.display = 'none'; a.href = url; a.download = `${docKey}_${id}`;
      document.body.appendChild(a); a.click();
      window.URL.revokeObjectURL(url);
      showToast(t('Download complete'), 'success');
    } catch(e) { showToast(t('Error downloading file'), 'error'); }
  }
</script>
</body>
</html>
