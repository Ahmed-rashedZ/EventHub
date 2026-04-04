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
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar" id="sidebar-avatar">M</div>
        <div class="user-info"><div class="user-name" id="sidebar-username">Manager</div><div class="user-role" id="sidebar-role">Event Manager</div></div>
      </div>
      <button class="btn btn-logout" id="logout-btn">🚪 Sign Out</button>
    </div>
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Sponsorship Requests</h1><p class="page-subtitle">Request sponsors for your events</p></div>
      <div class="topbar-actions">
        <!-- New Request logic tied to sponsors now -->
        <button class="btn btn-primary" onclick="openModal()">+ General Request</button>
      </div>
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
          <thead><tr><th>#</th><th>Event</th><th>Message</th><th>Sponsor</th><th>Status</th><th>Date</th></tr></thead>
          <tbody id="req-body">
            <tr class="loading-row"><td colspan="6"><div class="spinner" style="margin:auto"></div></td></tr>
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

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
  const user = requireRole('Event Manager');
  if (user) { populateSidebar(user); setActiveNav(); loadRequests(); loadMyEvents(); }

  async function loadRequests() {
    const res = await api.get('/sponsorship');
    const tbody = document.getElementById('req-body');
    if (!res.ok || !res.data.length) { tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><div class="empty-icon">💼</div><p>No sponsorship requests yet</p></div></td></tr>'; return; }
    tbody.innerHTML = res.data.map((r, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td><div style="font-weight:600">${r.event?.title || '—'}</div></td>
        <td style="color:var(--text-muted)">${r.message ? r.message.substring(0,60)+'…' : '—'}</td>
        <td style="color:var(--accent2); font-weight:500; cursor:pointer;" onclick="${r.sponsor_id ? `navigateToProfile(${r.sponsor_id})` : ''}">
            ${r.sponsor?.name || 'Open'}
        </td>
        <td>
          ${badge(r.status)}
          ${r.status === 'accepted' ? `<a href="/storage/agreements/agreement_${r.id}.pdf" target="_blank" style="margin-left:8px;font-size:12px;text-decoration:none">📄 PDF</a>` : ''}
          ${r.status === 'pending' && r.initiator === 'sponsor' ? `
            <div style="margin-top: 8px; display: flex; gap: 5px;">
                <button class="btn btn-success" style="padding: 2px 6px; font-size: 11px;" onclick="respond(${r.id}, 'accepted')">Accept</button>
                <button class="btn btn-danger" style="padding: 2px 6px; font-size: 11px;" onclick="respond(${r.id}, 'rejected')">Reject</button>
            </div>
          ` : ''}
          ${r.status === 'pending' && r.initiator === 'event_manager' ? `<div style="font-size: 11px; margin-top: 4px; color: var(--text-muted);">Awaiting Sponsor</div>` : ''}
        </td>
        <td style="color:var(--text-muted)">${fmtDateShort(r.created_at)}</td>
      </tr>`).join('');
  }

  async function loadAvailableSponsors() {
    const res = await api.get('/sponsors/available');
    const tbody = document.getElementById('sponsors-body');
    if (!res.ok || !res.data.length) { 
        tbody.innerHTML = '<tr><td colspan="4"><div class="empty-state"><p>No available sponsors right now.</p></div></td></tr>'; 
        return; 
    }
    
    tbody.innerHTML = res.data.map(s => {
        let logo = s.profile?.logo ? s.profile.logo : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(s.name);
        if(!logo.startsWith('http') && !logo.startsWith('/')) logo = '/' + logo;
        
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

  async function respond(id, status) {
    const res = await api.put(`/sponsorship/${id}`, { status });
    if (res.ok) { 
        showToast(status === 'accepted' ? 'Sponsorship accepted! 🎉' : 'Request rejected.', status === 'accepted' ? 'success' : 'info'); 
        loadRequests(); 
    }
    else showToast(res.data?.message || 'Error', 'error');
  }
  
  function closeModal() { 
      document.getElementById('req-modal').classList.remove('open'); 
      document.getElementById('req-form').reset(); 
      document.getElementById('target-sponsor-group').style.display = 'none';
      document.getElementById('r-sponsor-id').value = '';
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
