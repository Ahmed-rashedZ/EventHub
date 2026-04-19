<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sponsorship – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css"/>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item" href="/manager/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Events</span>
      <a class="nav-item" href="/manager/events"><span class="nav-icon">📅</span> My Events</a>
      <a class="nav-item" href="/manager/assistants"><span class="nav-icon">👥</span> Assistants</a>
      <a class="nav-item" href="/manager/attendance"><span class="nav-icon">📍</span> Attendance</a>
      <a class="nav-item active" href="/manager/sponsorship"><span class="nav-icon">💼</span> Sponsorship</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Sponsorship Requests</h1><p class="page-subtitle">Request sponsors for your events</p></div>
    </div>

    <!-- Available Sponsors Section -->
    <h2 class="page-title" style="font-size: 1.2rem; margin-top: 20px; font-weight: 600;">Available Sponsors</h2>
    <div class="card" style="margin-bottom: 30px;">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Sponsor</th><th>Company</th><th>Contact</th><th>Action</th></tr></thead>
          <tbody id="sponsors-body">
            <tr class="loading-row"><td colspan="4"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <h2 class="page-title" style="font-size: 1.2rem; font-weight: 600;">Your Sponsorship Requests</h2>
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Event</th><th>Message</th><th>Sponsor</th><th>Status</th><th>Action</th><th>Tier</th></tr></thead>
          <tbody id="req-body">
            <tr class="loading-row"><td colspan="7"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- New Request Modal -->
<div class="modal-overlay" id="req-modal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Request Sponsorship</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form id="req-form">
      <div class="form-group">
        <label class="form-label">Event</label>
        <select id="r-event" class="form-control" required>
          <option value="">Select your event…</option>
        </select>
      </div>
      <div class="form-group" id="target-sponsor-group" style="display:none; background: #eef2f5; padding: 10px; border-radius: 6px;">
        <label class="form-label">Target Sponsor</label>
        <div id="r-sponsor-display" style="font-weight:600; margin-bottom: 5px;"></div>
        <input type="hidden" id="r-sponsor-id" value=""/>
      </div>
      <div class="form-group">
        <label class="form-label">Message to Sponsors</label>
        <textarea id="r-message" class="form-control" placeholder="Describe sponsorship opportunity…"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Submit Request</button>
      </div>
    </form>
  </div>
</div>

<!-- View Message Modal -->
<div class="modal-overlay" id="msg-modal">
  <div class="modal" style="max-width: 500px;">
    <div class="modal-header">
      <h3 class="modal-title">Sponsorship Message</h3>
      <button class="modal-close" onclick="closeMsgModal()">✕</button>
    </div>
    <div class="modal-body" id="msg-content" style="padding: 20px; font-size: 15px; line-height: 1.6; color: var(--text);"></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary" onclick="closeMsgModal()" style="width: 100%;">Close</button>
    </div>
  </div>
</div>

<!-- Accept Sponsorship Modal -->
<div class="modal-overlay" id="accept-modal">
  <div class="modal" style="max-width: 400px;">
    <div class="modal-header">
      <h3 class="modal-title">Accept Sponsorship</h3>
      <button class="modal-close" onclick="closeAcceptModal()">✕</button>
    </div>
    <form id="accept-form" onsubmit="submitAccept(event)">
      <input type="hidden" id="accept-req-id">
      <div class="form-group">
        <label class="form-label" style="font-size: 0.8rem;">Select Sponsor Tier / Rank</label>
        <select id="a-tier" class="form-control" style="cursor: pointer;">
          <option value="" selected>🏷️ بدون تصنيف (Unranked)</option>
          <option value="diamond">💎 Diamond Sponsor</option>
          <option value="gold">🥇 Gold Sponsor</option>
          <option value="silver">🥈 Silver Sponsor</option>
          <option value="bronze">🥉 Bronze Sponsor</option>
        </select>
        <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;">اختر التصنيف أو اتركه بدون تصنيف. لا يمكن لراعيين أن يحملا نفس التصنيف.</p>
      </div>
      <div class="modal-footer" style="margin-top: 20px;">
        <button type="button" class="btn btn-ghost" onclick="closeAcceptModal()">Cancel</button>
        <button type="submit" class="btn btn-success">✅ Confirm Acceptance</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Rank Modal -->
<div class="modal-overlay" id="edit-rank-modal">
  <div class="modal" style="max-width: 400px;">
    <div class="modal-header">
      <h3 class="modal-title">Edit Sponsor Rank</h3>
      <button class="modal-close" onclick="closeEditRankModal()">✕</button>
    </div>
    <form id="edit-rank-form" onsubmit="submitEditRank(event)">
      <input type="hidden" id="edit-req-id">
      <div class="form-group">
        <label class="form-label" style="font-size: 0.8rem;">Select New Sponsor Tier / Rank</label>
        <select id="e-tier" class="form-control" style="cursor: pointer;">
          <option value="">🏷️ بدون تصنيف (Unranked)</option>
          <option value="diamond">💎 Diamond Sponsor</option>
          <option value="gold">🥇 Gold Sponsor</option>
          <option value="silver">🥈 Silver Sponsor</option>
          <option value="bronze">🥉 Bronze Sponsor</option>
        </select>
        <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;">هذا التصنيف سيتحدث فوراً في كل تفاصيل الحدث.</p>
      </div>
      <div class="modal-footer" style="margin-top: 20px;">
        <button type="button" class="btn btn-ghost" onclick="closeEditRankModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
  const user = requireRole('Event Manager');
  if (user) { populateSidebar(user); setActiveNav(); loadRequests(); loadMyEvents(); }

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
    if (!res.ok || !res.data.length) { tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">💼</div><p>No sponsorship requests yet</p></div></td></tr>'; return; }
    tbody.innerHTML = res.data.map((r, i) => {
      const canEdit = r.event?.start_time && new Date(r.event.start_time) > new Date();
      return `
      <tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td><div style="font-weight:600">${r.event?.title || '—'}</div></td>
        <td style="color:var(--text-muted)">
            ${r.message ? `<button class="btn btn-ghost btn-sm" onclick="showMsg('${r.message.replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, '\\n')}')" style="font-size:12px; padding: 4px 8px;">💬 Read Message</button>` : '—'}
        </td>
        <td style="color:var(--accent2); font-weight:500; cursor:pointer;" onclick="${r.sponsor_id ? `navigateToProfile(${r.sponsor_id})` : ''}">
            ${r.sponsor?.name || 'Open'}
        </td>
        <td>
          ${badge(r.status)}
          ${r.status === 'accepted' ? `<a href="/storage/agreements/agreement_${r.id}.pdf" target="_blank" style="margin-left:8px;font-size:12px;text-decoration:none">📄 PDF</a>` : ''}
          ${r.status === 'pending' && r.initiator === 'event_manager' ? `<div style="font-size: 11px; margin-top: 4px; color: var(--text-muted);">Awaiting Sponsor</div>` : ''}
        </td>
        <td>
          <div style="display: flex; gap: 8px;">
          ${r.status === 'pending' && r.initiator === 'sponsor' ? `
              <button class="btn btn-success" style="padding: 4px 12px; font-size: 12px; font-weight: 600;" onclick="respond(${r.id}, 'accepted')">✅ Accept</button>
              <button class="btn btn-danger" style="padding: 4px 12px; font-size: 12px; font-weight: 600;" onclick="respond(${r.id}, 'rejected')">❌ Reject</button>
          ` : (r.status === 'accepted' ? (canEdit ? `<button class="btn btn-ghost btn-sm" onclick="editRank(${r.id})" style="padding: 4px 12px; font-size: 11px;">✏️ Edit Rank</button>` : `<span style="font-size:11px; color:var(--text-muted)">Locked (Started)</span>`) : `<span style="font-size:12px; color:var(--text-muted)">No Action</span>`)}
          </div>
        </td>
        <td>
          ${r.status === 'accepted' ? getTierBadge(getSponsorTier(r)) : '<span style="font-size:12px; color:var(--text-muted)">—</span>'}
        </td>
      </tr>`;
    }).join('');
  }

  async function loadAvailableSponsors() {
    const res = await api.get('/sponsors/available');
    const tbody = document.getElementById('sponsors-body');
    if (!res.ok || !res.data.length) { 
        tbody.innerHTML = '<tr><td colspan="4"><div class="empty-state"><p>No available sponsors right now.</p></div></td></tr>'; 
        return; 
    }
    
    tbody.innerHTML = res.data.map(s => {
        let logo = '/images/default-avatar.png';
        if (s.image && s.image.trim() !== '') {
            logo = (s.image.startsWith('http') || s.image.startsWith('/')) ? s.image : '/storage/' + s.image;
        } else if (s.avatar && s.avatar.trim() !== '') {
            logo = (s.avatar.startsWith('http') || s.avatar.startsWith('/')) ? s.avatar : '/storage/' + s.avatar;
        } else if (s.profile && s.profile.logo) {
            logo = (s.profile.logo.startsWith('http') || s.profile.logo.startsWith('/')) ? s.profile.logo : '/' + s.profile.logo;
        }
        
        return `
      <tr>
        <td>
            <div style="display:flex; align-items:center; gap:10px; cursor:pointer;" onclick="navigateToProfile(${s.id})">
                <img src="${logo}" style="width:34px; height:34px; border-radius:6px; object-fit:cover; border: 1px solid var(--border);"/>
                <div>
                    <div style="font-weight:600;">${s.name}</div>
                    <div style="font-size:0.7rem; color:var(--text-muted);">Sponsor</div>
                </div>
            </div>
        </td>
        <td style="color:var(--text-muted)">${s.profile?.company_name || '—'}</td>
        <td style="color:var(--text-muted)">${s.email}</td>
        <td>
           <div style="display:flex; gap:8px;">
               <button class="btn btn-ghost btn-sm" onclick="navigateToProfile(${s.id})">View Profile</button>
               <button class="btn btn-primary btn-sm" onclick="openModal(${s.id}, '${s.profile?.company_name || s.name}')">Request</button>
           </div>
        </td>
      </tr>`;
    }).join('');
  }

  async function loadMyEvents() {
    const res = await api.get('/events/list/my');
    const sel = document.getElementById('r-event');
    if (!res.ok) return;
    sel.innerHTML = '<option value="">Select your event…</option>' + res.data.map(e => `<option value="${e.id}">${e.title}</option>`).join('');
  }

  function openModal(sponsorId = null, sponsorName = null) { 
      document.getElementById('req-modal').classList.add('open'); 
      if (sponsorId) {
          document.getElementById('target-sponsor-group').style.display = 'block';
          document.getElementById('r-sponsor-id').value = sponsorId;
          document.getElementById('r-sponsor-display').innerText = sponsorName;
      } else {
          document.getElementById('target-sponsor-group').style.display = 'none';
          document.getElementById('r-sponsor-id').value = '';
      }
  }

  function respond(id, status) {
    if (status === 'accepted') {
        document.getElementById('accept-req-id').value = id;
        document.getElementById('accept-modal').classList.add('open');
    } else {
        processResponse(id, status);
    }
  }

  async function processResponse(id, status, tier = null) {
    const payload = { status };
    if (status === 'accepted') {
        payload.tier = tier || null;
    }
    
    const res = await api.put(`/sponsorship/${id}`, payload);
    if (res.ok) { 
        showToast(status === 'accepted' ? 'Sponsorship accepted! 🎉' : 'Request rejected.', status === 'accepted' ? 'success' : 'info'); 
        loadRequests(); 
    }
    else showToast(res.data?.message || 'Error', 'error');
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
          showToast('Sponsor rank updated beautifully! ✨', 'success');
          closeEditRankModal();
          loadRequests();
      } else {
          showToast(res.data?.message || 'Error updating rank', 'error');
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
    const payload = { 
        event_id: +document.getElementById('r-event').value, 
        message: document.getElementById('r-message').value 
    };
    
    const sponsorId = document.getElementById('r-sponsor-id').value;
    if (sponsorId) {
        payload.sponsor_id = +sponsorId;
    }

    const res = await api.post('/sponsorship', payload);
    if (res.ok) { showToast('Request submitted!', 'success'); closeModal(); loadRequests(); }
    else showToast(res.data?.message || 'Error', 'error');
  });

  // Init
  if (user) { loadAvailableSponsors(); }
</script>
</body>
</html>
