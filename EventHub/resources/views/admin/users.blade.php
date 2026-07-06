<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Users – EventHub Admin</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js?v=3"></script>
<link rel="icon" href="/images/logo.png" type="image/png">
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px;"><img src="/images/logo.png?v=3" alt="EventHub Logo" style="height: 60px; width: auto; object-fit: contain; background: transparent !important;"></div>
    <nav class="sidebar-nav" id="sidebar-links"></nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title"><script>document.write(t('Users'))</script></h1><p class="page-subtitle"><script>document.write(t('Manage all platform users'))</script></p></div>
      <div class="topbar-actions">
        <div class="search-bar"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--text-muted); margin-inline-end: 8px;"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg><input id="search-input" type="text" placeholder="Search users…" oninput="filterTable()"/></div>
      </div>
    </div>

    <!-- Status Legend -->
    <div class="card" style="margin-bottom:16px; padding: 12px 18px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; background: rgba(255,255,255,0.01); border: 1px solid var(--border); border-radius: 8px;">
      <span style="font-size: 0.82rem; color: var(--text-secondary); font-weight: 600;"><script>document.write(t('Status Legend:'))</script></span>
      <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--text-muted);">
        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #10b981;"></span>
        <span><script>document.write(t('Active / Verified'))</script></span>
      </div>
      <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--text-muted);">
        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #f59e0b;"></span>
        <span><script>document.write(t('Pending Review'))</script></span>
      </div>
      <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--text-muted);">
        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #3b82f6;"></span>
        <span><script>document.write(t('Pending Update'))</script></span>
      </div>
      <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--text-muted);">
        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #ef4444;"></span>
        <span><script>document.write(t('Document Problem'))</script></span>
      </div>
      <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--text-muted);">
        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #6b7280;"></span>
        <span><script>document.write(t('Suspended'))</script></span>
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
              <th><script>document.write(t('Account Status'))</script></th>
            </tr>
          </thead>
          <tbody id="users-body">
            <tr class="loading-row"><td colspan="7"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Confirmation Modal -->
<div class="modal-overlay" id="confirm-modal">
  <div class="modal" style="max-width: 400px; text-align: center; padding: 30px 20px;">
    <div id="confirm-icon" style="margin-bottom: 16px; display: flex; justify-content: center;"></div>
    <h3 id="confirm-title" style="margin: 0 0 10px; color: #fff; font-size: 1.2rem;"></h3>
    <p id="confirm-message" style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 24px;"></p>
    <div style="display: flex; gap: 12px; justify-content: center;">
      <button class="btn btn-ghost btn-sm" onclick="closeConfirmModal()"><script>document.write(t('Cancel'))</script></button>
      <button class="btn btn-primary btn-sm" id="confirm-btn"><script>document.write(t('Confirm'))</script></button>
    </div>
  </div>
</div>

<!-- Documents Review Modal -->
<div class="modal-overlay" id="docs-modal">
  <div class="modal" style="max-width: 560px; max-height: 90vh; overflow-y: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
      <h3 style="margin: 0; color: #fff;" id="docs-modal-title"></h3>
      <button class="btn btn-ghost btn-sm" onclick="closeDocsModal()">&times;</button>
    </div>
    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 18px;" id="docs-modal-subtitle"></p>
    <div id="docs-modal-list" style="display: flex; flex-direction: column; gap: 12px;"></div>
    <div id="docs-submit-row" style="display: none; justify-content: flex-end; gap: 10px; margin-top: 16px;">
      <button class="btn btn-primary" onclick="submitDocReview()"><script>document.write(t('Submit Review'))</script></button>
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
    if (!res.ok) { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--danger)">Failed to load users</td></tr>'; return; }
    allUsers = res.data;
    renderUsers(allUsers);
  }

  function renderUsers(users) {
    const tbody = document.getElementById('users-body');
    if (!users.length) {
      tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><div class="empty-icon" style="display:flex; justify-content:center; margin-bottom:12px; color:var(--text-muted);"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A11.386 11.386 0 0110.089 18M15 8.25a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.111c0-1.656 1.002-3.066 2.44-3.562 1.438-.495 3.017-.27 4.29.599m-5.83-.342A9.338 9.338 0 0110.25 15c2.25 0 4.29.599 5.83 1.342m-11.66 0C2.522 17.11 3 18.062 3 19.124v.111" /></svg></div><p>${t('No users found')}</p></div></td></tr>`;
      return;
    }
    tbody.innerHTML = users.map((u, i) => {
      const isPartner = u.role === 'Event Manager' || u.role === 'Sponsor' || u.role === 'Company';
      
      const nameCell = isPartner
        ? `<a href="/user-profile?id=${u.id}" style="display:flex;align-items:center;gap:8px;text-decoration:none;color:var(--accent);transition:opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">${u.name}</a>`
        : `<span style="font-weight:500;">${u.name}</span>`;

      const docBtn = isPartner && (u.doc_commercial_register || u.doc_tax_number)
        ? `<button class="btn btn-ghost btn-sm" style="background:rgba(139,92,246,0.1);color:#a78bfa;"
             onclick="openDocsModal(${u.id}, '${u.role}', '${u.name.replace(/'/g,"\\'")}', '${u.verification_status}')">
             ${t('Documents')}</button>`
        : '';

      let statusColor = '#10b981'; // Green
      let statusText = t('Active');

      if (!u.is_active) {
        statusColor = '#6b7280'; // Grey
        statusText = t('Suspended');
      } else if (isPartner) {
        const hasRejectedDoc = (
          u.doc_commercial_register_status === 'rejected' ||
          u.doc_tax_number_status === 'rejected' ||
          u.doc_articles_of_association_status === 'rejected' ||
          u.doc_practice_license_status === 'rejected'
        );

        const hasPendingUpdate = (
          u.doc_commercial_register_status === 'pending_update' ||
          u.doc_tax_number_status === 'pending_update' ||
          u.doc_articles_of_association_status === 'pending_update' ||
          u.doc_practice_license_status === 'pending_update'
        );

        if (u.verification_status === 'rejected') {
          statusColor = '#ef4444'; // Red
          statusText = t('Rejected');
        } else if (u.verification_status === 'changes_requested' || hasRejectedDoc) {
          statusColor = '#ef4444'; // Red
          statusText = t('Document Problem');
        } else if (hasPendingUpdate) {
          statusColor = '#3b82f6'; // Blue
          statusText = t('Pending Update');
        } else if (u.verification_status === 'pending') {
          statusColor = '#f59e0b'; // Yellow/Orange
          statusText = t('Pending Review');
        } else if (u.verification_status === 'verified') {
          statusColor = '#10b981'; // Green
          statusText = t('Verified');
        }
      }

      const verificationStatusDot = `
        <div style="display:flex;align-items:center;gap:8px;font-size:0.85rem;color:var(--text-secondary);">
          <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background-color:${statusColor};flex-shrink:0;"></span>
          <span style="white-space:nowrap;">${statusText}</span>
        </div>
      `;

      return `<tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td>${nameCell}</td>
        <td style="color:var(--text-muted)">${u.email}</td>
        <td>${roleBadge(u.role)}</td>
        <td style="color:var(--text-muted)">${fmtDateShort(u.created_at)}</td>
        <td>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            ${u.role !== 'Admin' ? (u.is_active
              ? `<button class="btn btn-warning btn-sm" onclick="toggleUserStatus(${u.id},'${u.name}',false)">${t('Suspend')}</button>`
              : `<button class="btn btn-success btn-sm" onclick="toggleUserStatus(${u.id},'${u.name}',true)">${t('Activate')}</button>`)
            : ''}
            ${u.role !== 'Admin' ? `<button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id},'${u.name}')">${t('Delete')}</button>` : ''}
            ${docBtn}
          </div>
        </td>
        <td>${verificationStatusDot}</td>
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

  function toggleUserStatus(id, name, activate) {
    const title = activate ? t('Activate User') : t('Suspend User');
    const msg = activate ? t('Are you sure you want to activate user "') : t('Are you sure you want to suspend user "');
    showConfirmModal(title, msg + name + '"?', !activate, async () => {
      const res = await api.patch(`/analytics/users/${id}/status`);
      if (res.ok) { showToast(activate ? t('User activated successfully.') : t('User suspended successfully.'), 'success'); loadUsers(); }
      else showToast(res.data?.message || t('Error updating status'), 'error');
    });
  }

  function deleteUser(id, name) {
    showConfirmModal(t('Delete User'), t('Delete user "') + name + t('"? This cannot be undone.'), true, async () => {
      const res = await api.delete(`/analytics/users/${id}`);
      if (res.ok) { showToast(t('User deleted'), 'success'); loadUsers(); }
      else showToast(res.data?.message || t('Error deleting user'), 'error');
    });
  }

  // ── Confirm Modal Logic ──────────────────────────────────────────────────
  let confirmCallback = null;
  function showConfirmModal(title, message, isDanger, onConfirm) {
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-message').textContent = message;
    
    const iconContainer = document.getElementById('confirm-icon');
    const btn = document.getElementById('confirm-btn');
    
    if (isDanger) {
      iconContainer.innerHTML = '<svg width="48" height="48" fill="none" stroke="#f59e0b" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
      btn.style.background = '#ef4444';
      btn.style.borderColor = '#ef4444';
      btn.style.color = '#fff';
    } else {
      iconContainer.innerHTML = '<svg width="48" height="48" fill="none" stroke="#10b981" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
      btn.style.background = '#10b981';
      btn.style.borderColor = '#10b981';
      btn.style.color = '#fff';
    }
    
    confirmCallback = onConfirm;
    document.getElementById('confirm-modal').classList.add('open');
  }

  function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.remove('open');
    confirmCallback = null;
  }

  document.getElementById('confirm-btn').addEventListener('click', () => {
    if (confirmCallback) confirmCallback();
    closeConfirmModal();
  });

  // ── Documents Review Modal ──────────────────────────────────────────────
  const ALL_DOC_TYPES = [
    { key: 'doc_commercial_register',     label: 'Commercial Register',     icon: '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>' },
    { key: 'doc_tax_number',              label: 'Tax Number Certificate',  icon: '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>' },
    { key: 'doc_articles_of_association', label: 'Articles of Association', icon: '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>' },
    { key: 'doc_practice_license',        label: 'Practice License',        icon: '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>' },
  ];

  async function openDocsModal(id, role, name, verStatus) {
    currentDocUser = { id, role, name, verStatus };
    docDecisions = {};

    document.getElementById('docs-modal-title').innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg> ' + name + '</span>';
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

    if (userData) {
      const hasRejectedDoc = (
        userData.doc_commercial_register_status === 'rejected' ||
        userData.doc_tax_number_status === 'rejected' ||
        userData.doc_articles_of_association_status === 'rejected' ||
        userData.doc_practice_license_status === 'rejected'
      );

      const hasPendingUpdate = (
        userData.doc_commercial_register_status === 'pending_update' ||
        userData.doc_tax_number_status === 'pending_update' ||
        userData.doc_articles_of_association_status === 'pending_update' ||
        userData.doc_practice_license_status === 'pending_update'
      );

      let modalStatusText = t('Verified');
      let modalStatusColor = '#10b981';

      if (!userData.is_active) {
        modalStatusText = t('Suspended');
        modalStatusColor = '#6b7280';
      } else if (userData.verification_status === 'rejected') {
        modalStatusText = t('Rejected');
        modalStatusColor = '#ef4444';
      } else if (userData.verification_status === 'changes_requested' || hasRejectedDoc) {
        modalStatusText = t('Document Problem');
        modalStatusColor = '#ef4444';
      } else if (hasPendingUpdate) {
        modalStatusText = t('Pending Update');
        modalStatusColor = '#3b82f6';
      } else if (userData.verification_status === 'pending') {
        modalStatusText = t('Pending Review');
        modalStatusColor = '#f59e0b';
      }

      document.getElementById('docs-modal-info').innerHTML =
        `${t('Account Status')}: <strong style="color:${modalStatusColor}">${modalStatusText}</strong>`;
    }

    const docTypes = (role === 'Sponsor' || role === 'Company')
      ? ALL_DOC_TYPES.filter(d => ['doc_commercial_register','doc_tax_number'].includes(d.key))
      : ALL_DOC_TYPES;

    const statusStyles = {
      approved:       { color: '#10b981', bg: 'rgba(16,185,129,0.08)', border: 'rgba(16,185,129,0.3)', icon: '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>' },
      rejected:       { color: '#ef4444', bg: 'rgba(239,68,68,0.08)',  border: 'rgba(239,68,68,0.3)',  icon: '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>' },
      pending_update: { color: '#3b82f6', bg: 'rgba(59,130,246,0.08)', border: 'rgba(59,130,246,0.3)', icon: '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89" /></svg>' },
      pending:        { color: '#f59e0b', bg: 'rgba(245,158,11,0.08)', border: 'rgba(245,158,11,0.3)', icon: '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' },
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
              <span style="display:inline-flex;align-items:center;color:#fff;">${doc.icon}</span>
              <div>
                <div style="color:#fff;font-weight:600;font-size:0.9rem;">${t(doc.label)}</div>
                <span style="font-size:0.7rem;font-weight:700;color:${ss.color};text-transform:uppercase;display:inline-flex;align-items:center;">${ss.icon} ${statusLabel}</span>
              </div>
            </div>
            ${hasFile
              ? `<button class="btn btn-ghost btn-sm" onclick="downloadSingleDoc(${id},'${doc.key}')" style="padding:5px 10px;font-size:0.75rem;">${t('Download')}</button>`
              : `<span style="color:var(--text-muted);font-size:0.75rem;">${t('No file')}</span>`}
          </div>
          ${note ? `<div style="padding:8px 14px;background:rgba(239,68,68,0.05);border-top:1px solid rgba(239,68,68,0.15);font-size:0.8rem;color:#ef4444;"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg> ${userData?.[doc.key + '_note'] || ''}</div>` : ''}
          <div style="padding:12px 14px;background:rgba(0,0,0,0.1);display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <button id="btn-acc-${doc.key}" onclick="setDocAction('${doc.key}','approved')"
              style="padding:5px 14px;font-size:0.8rem;border-radius:6px;cursor:pointer;display:${status === 'approved' ? 'none' : 'inline-flex'};align-items:center;gap:5px;
                     background:rgba(16,185,129,0.1); color:#10b981; border:1px solid rgba(16,185,129,0.4);">
              ${t('Accept')}
            </button>
            <button id="btn-rej-${doc.key}" onclick="setDocAction('${doc.key}','rejected')"
              style="padding:5px 14px;font-size:0.8rem;border-radius:6px;cursor:pointer;display:${status === 'rejected' ? 'none' : 'inline-flex'};align-items:center;gap:5px;
                     background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.3);">
              ${t('Reject')}
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
        headers: { 'Authorization': `Bearer ${sessionStorage.getItem('token')}` }
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






