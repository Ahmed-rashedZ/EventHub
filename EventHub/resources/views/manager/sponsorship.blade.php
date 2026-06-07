<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sponsorship – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
<link rel="icon" href="/images/logo.png" type="image/png">
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px;"><img src="/images/logo.png" alt="EventHub Logo" style="height: 60px; width: auto; object-fit: contain;"></div>
    <nav class="sidebar-nav" id="sidebar-links"></nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title"><script>document.write(t('Sponsorship Requests'))</script></h1><p class="page-subtitle"><script>document.write(t('Request sponsors for your events'))</script></p></div>
    </div>

    <!-- Discovery Section -->
    <div class="discovery-section">
        <h2 class="page-title" style="font-size: 1.1rem; font-weight: 600; margin-top:20px;"><script>document.write(t('Available Sponsors'))</script></h2>
        <div class="card" style="margin-bottom: 30px;">
          <div class="table-wrap">
            <table>
              <thead><tr><th><script>document.write(t('Sponsor'))</script></th><th><script>document.write(t('Sector'))</script></th><th><script>document.write(t('Contact'))</script></th><th><script>document.write(t('Actions'))</script></th></tr></thead>
              <tbody id="sponsors-body">
                <tr class="loading-row"><td colspan="4"><div class="spinner" style="margin:auto"></div></td></tr>
              </tbody>
            </table>
          </div>
        </div>
    </div>

    <h2 class="page-title" style="font-size: 1.2rem; font-weight: 600;"><script>document.write(t('Your Sponsorship Requests'))</script></h2>
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th><script>document.write(t('Event'))</script></th><th><script>document.write(t('Sponsor (Sector)'))</script></th><th><script>document.write(t('Status'))</script></th><th><script>document.write(t('Actions'))</script></th><th><script>document.write(t('Tier'))</script></th></tr></thead>
          <tbody id="req-body">
            <tr class="loading-row"><td colspan="7"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Profile Details Modal -->
<div class="modal-overlay" id="profile-details-modal">
  <div class="modal" style="max-width:500px; width:95%; padding:0; border-top:3.5px solid var(--accent2); max-height:85vh; display:flex; flex-direction:column; border-radius:16px;">
    <div style="padding:16px 20px 12px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid rgba(255,255,255,0.05);">
      <h3 class="modal-title" style="margin:0;font-size:1.1rem;display:flex;align-items:center;gap:8px;">👤 <script>document.write(t('Public Profile'))</script></h3>
      <button class="modal-close" onclick="closeProfileModal()">✕</button>
    </div>
    <div id="profile-details-content" style="padding:20px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:16px;">
      <div class="spinner" style="margin:40px auto"></div>
    </div>
  </div>
</div>

<!-- New Request Modal -->
<div class="modal-overlay" id="req-modal">
  <div class="modal" style="max-width: 450px;">
    <div class="modal-header">
      <h3 class="modal-title"><script>document.write(t('Request Sponsorship'))</script></h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form id="req-form">
      <div class="form-group">
        <label class="form-label"><script>document.write(t('Event'))</script></label>
        <select id="r-event" class="form-control i18n-skip" required>
          <option value="">Select your event…</option>
        </select>
      </div>
      <div class="form-group" id="target-sponsor-group" style="display:none; background: rgba(16,185,129,0.05); padding: 15px; border-radius: 12px; border: 1px solid rgba(16,185,129,0.1);">
        <label class="form-label"><script>document.write(t('Target Sponsor'))</script></label>
        <div id="r-sponsor-display" style="font-weight:700; color:var(--text); margin-bottom: 5px; font-size: 1.1rem;"></div>
        <input type="hidden" id="r-sponsor-id" value=""/>
      </div>
      <div class="form-group">
        <label class="form-label"><script>document.write(t('Message to Sponsors'))</script></label>
        <textarea id="r-message" class="form-control" placeholder="Describe sponsorship opportunity…" data-placeholder="Describe sponsorship opportunity…" style="min-height: 120px;"></textarea>
        <script>document.querySelector('#r-message').placeholder = t('Describe sponsorship opportunity…')</script>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()"><script>document.write(t('Cancel'))</script></button>
        <button type="submit" class="btn btn-primary"><script>document.write(t('Submit Request'))</script></button>
      </div>
    </form>
  </div>
</div>

<!-- View Message Modal -->
<div class="modal-overlay" id="msg-modal">
  <div class="modal" style="max-width: 500px;">
    <div class="modal-header">
      <h3 class="modal-title"><script>document.write(t('Sponsorship Message'))</script></h3>
      <button class="modal-close" onclick="closeMsgModal()">✕</button>
    </div>
    <div class="modal-body" id="msg-content" style="padding: 20px; font-size: 15px; line-height: 1.6; color: var(--text);"></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary" onclick="closeMsgModal()" style="width: 100%;"><script>document.write(t('Close'))</script></button>
    </div>
  </div>
</div>

<!-- Accept Sponsorship Modal -->
<div class="modal-overlay" id="accept-modal">
  <div class="modal" style="max-width: 400px;">
    <div class="modal-header">
      <h3 class="modal-title"><script>document.write(t('Accept Sponsorship'))</script></h3>
      <button class="modal-close" onclick="closeAcceptModal()">✕</button>
    </div>
    <form id="accept-form" onsubmit="submitAccept(event)">
      <input type="hidden" id="accept-req-id">
      <div class="form-group">
        <label class="form-label" style="font-size: 0.8rem;"><script>document.write(t('Select Sponsor Tier'))</script></label>
        <select id="a-tier" class="form-control" style="cursor: pointer;">
          <option value="">🏷️ <script>document.write(t('Unranked'))</script></option>
          <option value="diamond">💎 <script>document.write(t('Diamond Sponsor'))</script></option>
          <option value="gold">🥇 <script>document.write(t('Gold Sponsor'))</script></option>
          <option value="silver">🥈 <script>document.write(t('Silver Sponsor'))</script></option>
          <option value="bronze">🥉 <script>document.write(t('Bronze Sponsor'))</script></option>
        </select>
        <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;"><script>document.write(t('Choose a tier or leave unranked. Two sponsors cannot have the same tier.'))</script></p>
      </div>
      <div class="modal-footer" style="margin-top: 20px;">
        <button type="button" class="btn btn-ghost" onclick="closeAcceptModal()"><script>document.write(t('Cancel'))</script></button>
        <button type="submit" class="btn btn-success"><script>document.write(t('✅ Confirm Acceptance'))</script></button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Tier Modal -->
<div class="modal-overlay" id="edit-rank-modal">
  <div class="modal" style="max-width: 400px;">
    <div class="modal-header">
      <h3 class="modal-title"><script>document.write(t('Edit Sponsor Tier'))</script></h3>
      <button class="modal-close" onclick="closeEditRankModal()">✕</button>
    </div>
    <form id="edit-rank-form" onsubmit="submitEditRank(event)">
      <input type="hidden" id="edit-req-id">
      <div class="form-group">
        <label class="form-label" style="font-size: 0.8rem;"><script>document.write(t('Select New Sponsor Tier'))</script></label>
        <select id="e-tier" class="form-control" style="cursor: pointer;">
          <option value="">🏷️ <script>document.write(t('Unranked'))</script></option>
          <option value="diamond">💎 <script>document.write(t('Diamond Sponsor'))</script></option>
          <option value="gold">🥇 <script>document.write(t('Gold Sponsor'))</script></option>
          <option value="silver">🥈 <script>document.write(t('Silver Sponsor'))</script></option>
          <option value="bronze">🥉 <script>document.write(t('Bronze Sponsor'))</script></option>
        </select>
        <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;"><script>document.write(t('This tier will be updated immediately in all event details.'))</script></p>
      </div>
      <div class="modal-footer" style="margin-top: 20px;">
        <button type="button" class="btn btn-ghost" onclick="closeEditRankModal()"><script>document.write(t('Cancel'))</script></button>
        <button type="submit" class="btn btn-primary"><script>document.write(t('Save Changes'))</script></button>
      </div>
    </form>
  </div>
</div>

<!-- Agreement Negotiation Modal -->
<div class="modal-overlay" id="agreement-modal">
  <div class="modal" style="max-width:520px; width:95%; padding:0; border-top:3px solid #22d3ee; max-height:85vh; display:flex; flex-direction:column; border-radius:16px;">
    <div style="padding:16px 20px 0; display:flex; justify-content:space-between; align-items:center;">
      <div><h3 class="modal-title" style="margin:0;font-size:1rem;"><script>document.write(t('Contract Negotiation'))</script></h3><p style="font-size:0.7rem;color:var(--text-muted);margin:2px 0 0"><script>document.write(t('Negotiate terms with the sponsor'))</script></p></div>
      <button class="modal-close" onclick="closeAgreementModal()">✕</button>
    </div>
    <div id="agreement-content" style="padding:12px 20px 20px; overflow-y:auto; flex:1;">
      <div class="spinner" style="margin:40px auto"></div>
    </div>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script src="/js/agreement-v2.js?v={{ time() }}"></script>
<script>
  const user = requireRole('Event Manager');
  if (user) { populateSidebar(user); setActiveNav(); loadRequests(); loadMyEvents(); loadAvailableSponsors(); }

  // Extract tier from event.sponsors pivot for a given sponsorship request
  function getSponsorTier(r) {
    if (!r.event?.sponsors || !r.sponsor_id) return null;
    const match = r.event.sponsors.find(s => s.id === r.sponsor_id);
    return match?.pivot?.tier || null;
  }

  function getTierBadge(tier) {
    switch (tier) {
      case 'diamond': return '<span style="background:rgba(6,182,212,0.15); color:#06b6d4; padding:3px 10px; border-radius:12px; border:1px solid rgba(6,182,212,0.3); font-size:11px; font-weight:600;">💎 Diamond</span>';
      case 'gold':    return '<span style="background:rgba(234,179,8,0.15); color:#eab308; padding:3px 10px; border-radius:12px; border:1px solid rgba(234,179,8,0.3); font-size:11px; font-weight:600;">🥇 Gold</span>';
      case 'silver':  return '<span style="background:rgba(156,163,175,0.15); color:#9ca3af; padding:3px 10px; border-radius:12px; border:1px solid rgba(156,163,175,0.3); font-size:11px; font-weight:600;">🥈 Silver</span>';
      case 'bronze':  return '<span style="background:rgba(217,119,6,0.15); color:#d97706; padding:3px 10px; border-radius:12px; border:1px solid rgba(217,119,6,0.3); font-size:11px; font-weight:600;">🥉 Bronze</span>';
      default:        return '<span style="background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.5); padding:3px 10px; border-radius:12px; border:1px solid rgba(255,255,255,0.12); font-size:11px; font-weight:600;">🏷️ Unranked</span>';
    }
  }

  async function loadRequests() {
    const res = await api.get('/sponsorship');
    const tbody = document.getElementById('req-body');
    if (!res.ok || !res.data.length) { 
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">💼</div><p>' + t('No sponsorship requests yet') + '</p></div></td></tr>'; 
        return; 
    }

    // Sort by status (pending first) then latest
    const sorted = res.data.sort((a, b) => {
        if (a.status === 'pending' && b.status !== 'pending') return -1;
        if (a.status !== 'pending' && b.status === 'pending') return 1;
        return new Date(b.created_at) - new Date(a.created_at);
    });

    tbody.innerHTML = sorted.map((r, i) => {
      const canEdit = r.event?.start_time && new Date(r.event.start_time) > new Date();
      let actionHtml = '';
      if (r.status === 'pending') {
          if (r.initiator === 'sponsor') {
              actionHtml = `
                <button class="btn btn-success" style="padding: 4px 12px; font-size: 12px; font-weight: 600;" onclick="respondReq(${r.id}, 'accepted')">✅ ${t('Accept')}</button>
                <button class="btn btn-danger" style="padding: 4px 12px; font-size: 12px; font-weight: 600;" onclick="respondReq(${r.id}, 'rejected')">❌ ${t('Reject')}</button>
              `;
          } else {
              actionHtml = `<div style="font-size: 11px; color: var(--text-muted);">${t('Awaiting Response')}</div>`;
          }
      } else if (r.status === 'negotiating') {
          actionHtml = `
            <button class="btn btn-sm" onclick="openAgreementModal(${r.id}, 'sponsor')" style="padding:4px 12px; font-size:11px; background:rgba(34,211,238,0.12); color:#22d3ee; border:1px solid rgba(34,211,238,0.25); font-weight:600;">📋 ${t('Contract Negotiation')}</button>
          `;
      } else if (r.status === 'accepted') {
          actionHtml = `
            ${canEdit ? `<button class="btn btn-ghost btn-sm" onclick="editRank(${r.id})" style="padding: 4px 12px; font-size: 11px;">✏️ ${t('Edit Tier')}</button>` : `<span style="font-size:11px; color:var(--text-muted)">${t('Locked')}</span>`}
            <button class="btn btn-sm" onclick="openAgreementModal(${r.id}, 'sponsor')" style="padding:4px 12px; font-size:11px; background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.25); font-weight:600;">📄 ${t('Agreement')}</button>
          `;
      } else {
          actionHtml = `<span style="font-size:11px;color:var(--text-muted);">${t('Locked')}</span>`;
      }

      return `
      <tr>
        <td style="color:var(--text-muted)">${i + 1}</td>
        <td><div style="font-weight:600" class="i18n-skip">${r.event?.title || '—'}</div></td>
        <td style="color:var(--accent2); font-weight:500; cursor:pointer;" onclick="navigateToProfile(${r.sponsor_id})">
            <div class="i18n-skip">${r.sponsor?.name || 'Open'}</div>
            <div style="font-size:0.7rem; color:var(--text-muted); font-weight:400;">${r.sponsor?.profile?.company_type || t('General')}</div>
        </td>
        <td>${badge(r.status)}</td>
        <td><div style="display: flex; gap: 8px; flex-wrap:wrap;">${actionHtml}</div></td>
        <td>
          ${r.status === 'accepted' ? getTierBadge(getSponsorTier(r)) : '<span style="font-size:12px; color:var(--text-muted)">—</span>'}
        </td>
      </tr>`;
    }).join('');
  }

  async function respondReq(id, status) {
    if (status === 'accepted') {
        document.getElementById('accept-req-id').value = id;
        document.getElementById('accept-modal').classList.add('open');
    } else {
        await processResponse(id, status);
    }
  }

  async function loadAvailableSponsors() {
    const res = await api.get('/sponsors/available');
    const tbody = document.getElementById('sponsors-body');
    if (!res.ok || !res.data.length) { 
        tbody.innerHTML = '<tr><td colspan="4"><div class="empty-state"><p>' + t('No available sponsors right now.') + '</p></div></td></tr>'; 
        return; 
    }
    
    tbody.innerHTML = res.data.map(s => {
        let logo = getLogo(s);
        return `
      <tr>
        <td>
            <div style="display:flex; align-items:center; gap:10px; cursor:pointer;" onclick="navigateToProfile(${s.id})">
                <img src="${logo}" style="width:34px; height:34px; border-radius:6px; object-fit:cover; border: 1px solid var(--border);"/>
                <div>
                    <div style="font-weight:600;" class="i18n-skip">${s.name}</div>
                    <div style="font-size:0.7rem; color:var(--text-muted);">${t('Sponsor')}</div>
                </div>
            </div>
        </td>
        <td style="color:var(--text-muted)" class="i18n-skip">${s.profile?.company_type || t('Other')}</td>
        <td style="color:var(--text-muted)">${s.email}</td>
        <td>
           <div style="display:flex; gap:8px;">
               <button class="btn btn-ghost btn-sm" onclick="navigateToProfile(${s.id})">${t('View Profile')}</button>
               <button class="btn btn-primary btn-sm" onclick="openModal(${s.id}, '${s.name}')">${t('Request')}</button>
           </div>
        </td>
      </tr>`;
    }).join('');
  }

  function getLogo(u) {
    let logo = '/images/default-avatar.png';
    if (u.image && u.image.trim() !== '') {
        logo = (u.image.startsWith('http') || u.image.startsWith('/')) ? u.image : '/storage/' + u.image;
    } else if (u.avatar && u.avatar.trim() !== '') {
        logo = (u.avatar.startsWith('http') || u.avatar.startsWith('/')) ? u.avatar : '/storage/' + u.avatar;
    } else if (u.profile && u.profile.logo) {
        logo = (u.profile.logo.startsWith('http') || u.profile.logo.startsWith('/')) ? u.profile.logo : '/' + u.profile.logo;
    }
    return logo;
  }

  async function loadMyEvents() {
    const res = await api.get('/events/list/my');
    const sel = document.getElementById('r-event');
    if (!res.ok) return;
    sel.innerHTML = '<option value="">Select your event…</option>' + res.data.map(e => `<option value="${e.id}">${e.title}</option>`).join('');
  }

  function openModal(targetId = null, targetName = null) { 
      document.getElementById('req-modal').classList.add('open'); 
      if (targetId) {
          document.getElementById('target-sponsor-group').style.display = 'block';
          document.getElementById('r-sponsor-id').value = targetId;
          document.getElementById('r-sponsor-display').innerText = targetName;
      } else {
          document.getElementById('target-sponsor-group').style.display = 'none';
          document.getElementById('r-sponsor-id').value = '';
      }
  }

  async function processResponse(id, status, tier = null) {
    const payload = { status };
    if (status === 'accepted') {
        payload.tier = tier || null;
    }
    
    const res = await api.put(`/sponsorship/${id}`, payload);
    if (res.ok) { 
        showToast(status === 'accepted' ? t('Preliminary acceptance successful! Contract negotiation started. 📝') : t('Request rejected.'), status === 'accepted' ? 'success' : 'info'); 
        loadRequests(); 
    }
    else showToast(res.data?.message || t('Error'), 'error');
  }

  async function submitAccept(e) {
      e.preventDefault();
      const id = document.getElementById('accept-req-id').value;
      const tier = document.getElementById('a-tier').value || null;
      closeAcceptModal();
      await processResponse(id, 'accepted', tier);
  }

  function closeAcceptModal() {
      document.getElementById('accept-modal').classList.remove('open');
      document.getElementById('accept-form').reset();
  }

  function editRank(id) {
      document.getElementById('edit-req-id').value = id;
      document.getElementById('edit-rank-modal').classList.add('open');
  }

  async function submitEditRank(e) {
      e.preventDefault();
      const id = document.getElementById('edit-req-id').value;
      const tier = document.getElementById('e-tier').value || null;
      
      const res = await api.patch(`/sponsorship/${id}/tier`, { tier });
      if (res.ok) {
          showToast(t('Sponsor tier updated beautifully! ✨'), 'success');
          closeEditRankModal();
          loadRequests();
      } else {
          showToast(res.data?.message || t('Error updating tier'), 'error');
      }
  }

  function closeEditRankModal() {
      document.getElementById('edit-rank-modal').classList.remove('open');
      document.getElementById('edit-rank-form').reset();
  }

  function closeModal() { 
      document.getElementById('req-modal').classList.remove('open'); 
      document.getElementById('req-form').reset(); 
      document.getElementById('target-sponsor-group').style.display = 'none';
      document.getElementById('r-sponsor-id').value = '';
  }

  function showMsg(message) {
      document.getElementById('msg-content').innerText = message;
      document.getElementById('msg-modal').classList.add('open');
  }

  function closeMsgModal() {
      document.getElementById('msg-modal').classList.remove('open');
  }

  document.getElementById('req-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const targetId = document.getElementById('r-sponsor-id').value;
    const eventId = +document.getElementById('r-event').value;
    const message = document.getElementById('r-message').value;

    if (!eventId) return showToast(t('Please select an event'), 'error');

    const payload = { event_id: eventId, message: message };
    if (targetId) payload.sponsor_id = +targetId;

    const res = await api.post('/sponsorship', payload);
    if (res.ok) { 
        showToast(t('Sponsorship request submitted!'), 'success'); 
        closeModal(); 
        loadRequests(); 
    } else {
        showToast(res.data?.message || t('Error'), 'error');
    }
  });

  function navigateToProfile(id) { showProfileModal(id); }

  async function showProfileModal(userId) {
    const modal = document.getElementById('profile-details-modal');
    const content = document.getElementById('profile-details-content');
    modal.classList.add('open');
    content.innerHTML = '<div class="spinner" style="margin:40px auto"></div>';

    try {
      const res = await api.get('/profile/' + userId);
      if (!res.ok) {
        content.innerHTML = `<div style="text-align:center;color:var(--danger);padding:20px;">${t('Failed to load profile')}</div>`;
        return;
      }
      const u = res.data.user;
      const p = u.profile || {};

      let avatar = '/images/default-avatar.png';
      if (u.image && u.image.trim() !== '') {
        avatar = (u.image.startsWith('http') || u.image.startsWith('/')) ? u.image : '/storage/' + u.image;
      } else if (u.avatar && u.avatar.trim() !== '') {
        avatar = (u.avatar.startsWith('http') || u.avatar.startsWith('/')) ? u.avatar : '/storage/' + u.avatar;
      } else if (p.logo && p.logo.trim() !== '') {
        avatar = (p.logo.startsWith('http') || p.logo.startsWith('/')) ? p.logo : '/' + p.logo;
      }

      const roleStyles = {
        'Admin': 'background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.3)',
        'Event Manager': 'background:rgba(110,64,242,.15);color:#a78bfa;border:1px solid rgba(110,64,242,.3)',
        'Sponsor': 'background:rgba(234,179,8,.15);color:#eab308;border:1px solid rgba(234,179,8,.3)',
        'Company': 'background:rgba(59,130,246,.15);color:#60a5fa;border:1px solid rgba(59,130,246,.3)',
        'Attendee': 'background:rgba(34,211,238,.15);color:#22d3ee;border:1px solid rgba(34,211,238,.3)',
        'Assistant': 'background:rgba(34,197,94,.15);color:#22c55e;border:1px solid rgba(34,197,94,.3)',
      };

      const roleStyle = roleStyles[u.role] || 'background:rgba(255,255,255,.1);color:#fff';

      // Build contacts HTML
      let contactsHtml = '';
      if (u.contact_email) {
        contactsHtml += `
          <div style="display:flex;align-items:center;gap:12px;background:rgba(255,255,255,0.02);padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.05);">
            <span style="font-size:1.2rem;">📧</span>
            <div>
              <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;">${t('Contact Email')}</div>
              <div style="font-weight:500;font-size:0.85rem;">${u.contact_email}</div>
            </div>
          </div>
        `;
      }
      if (u.phone) {
        contactsHtml += `
          <div style="display:flex;align-items:center;gap:12px;background:rgba(255,255,255,0.02);padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.05);">
            <span style="font-size:1.2rem;">📱</span>
            <div>
              <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;">${t('Phone')}</div>
              <div style="font-weight:500;font-size:0.85rem;"><a href="tel:${u.phone}" style="color:inherit;text-decoration:none;">${u.phone}</a></div>
            </div>
          </div>
        `;
      }

      // Build social links HTML
      let socialHtml = '';
      if (u.social_links && Object.keys(u.social_links).length > 0) {
        const iconMap = {
          'twitter': '𝕏', 'x': '𝕏',
          'linkedin': '💼',
          'website': '🌐', 'portfolio': '🎨',
          'facebook': '👥', 'instagram': '📸',
          'whatsapp': '💬', 'telegram': '✈️',
          'github': '💻', 'youtube': '🎬',
          'tiktok': '🎵', 'discord': '👾'
        };
        let linksHtml = '';
        for (let [pKey, link] of Object.entries(u.social_links)) {
          if (link) {
            const platform = pKey.split('_')[0];
            const icon = iconMap[platform] || '🔗';
            linksHtml += `
              <a href="${link.startsWith('http') ? link : 'https://' + link}" target="_blank" style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);display:inline-flex;align-items:center;justify-content:center;color:var(--text-muted);text-decoration:none;font-size:1.1rem;transition:all 0.2s;" onmouseover="this.style.background='rgba(110,64,242,0.1)';this.style.borderColor='var(--accent)';" onmouseout="this.style.background='rgba(255,255,255,0.03)';this.style.borderColor='rgba(255,255,255,0.08)';">
                ${icon}
              </a>
            `;
          }
        }
        if (linksHtml) {
          socialHtml = `
            <div>
              <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px;">${t('Social Profiles & Links')}</div>
              <div style="display:flex;gap:8px;flex-wrap:wrap;">${linksHtml}</div>
            </div>
          `;
        }
      }

      const bioText = (u.role === 'Sponsor' || u.role === 'Company' ? p.company_description : p.bio) || u.bio || t('No bio provided yet.');

      content.innerHTML = `
        <!-- Header -->
        <div style="display:flex;align-items:center;gap:14px;">
          <img src="${avatar}" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid var(--border);" />
          <div>
            <h4 style="margin:0;font-size:1.25rem;font-weight:800;color:#fff;" class="i18n-skip">${u.name}</h4>
            <div style="display:flex;gap:6px;margin-top:6px;align-items:center;flex-wrap:wrap;">
              <span style="${roleStyle};padding:2px 8px;border-radius:12px;font-size:0.65rem;font-weight:700;text-transform:uppercase;">${t(u.role)}</span>
              ${p.company_type ? `<span style="background:rgba(110,64,242,0.1);color:var(--accent);border:1px solid rgba(110,64,242,0.2);padding:2px 8px;border-radius:12px;font-size:0.65rem;font-weight:700;">${p.company_type}</span>` : ''}
            </div>
          </div>
        </div>

        <!-- About -->
        <div style="margin-top:4px;">
          <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:6px;">${t('About')}</div>
          <p style="margin:0;color:rgba(255,255,255,0.75);font-size:0.88rem;line-height:1.6;" class="i18n-skip">${bioText}</p>
        </div>

        <!-- Contact Info -->
        ${contactsHtml ? `
          <div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px;">${t('Contact Information')}</div>
            <div style="display:flex;flex-direction:column;gap:8px;">${contactsHtml}</div>
          </div>
        ` : ''}

        <!-- Socials -->
        ${socialHtml}
      `;
    } catch (err) {
      console.error(err);
      content.innerHTML = `<div style="text-align:center;color:var(--danger);padding:20px;">${t('Failed to load profile')}</div>`;
    }
  }

  function closeProfileModal() {
    document.getElementById('profile-details-modal').classList.remove('open');
    document.getElementById('profile-details-content').innerHTML = '';
  }
</script>
</body>




