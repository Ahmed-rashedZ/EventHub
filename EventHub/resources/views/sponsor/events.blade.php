<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Browse Events – EventHub Sponsor</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <style>
      .req-btn {
          padding: 6px 12px;
          font-size: 13px;
      }
      .availability-warning {
          background: #fff3cd;
          color: #856404;
          padding: 15px;
          border-radius: 8px;
          margin-bottom: 20px;
          border: 1px solid #ffeeba;
          display: none;
      }
      .availability-load-error {
          background: #fee2e2;
          color: #991b1b;
          padding: 15px;
          border-radius: 8px;
          margin-bottom: 20px;
          border: 1px solid #fecaca;
          display: none;
      }
  </style>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item" href="/sponsor/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Opportunities</span>
      <a class="nav-item active" href="/sponsor/events"><span class="nav-icon">🌍</span> Browse Events</a>
      <a class="nav-item" href="/sponsor/requests"><span class="nav-icon">💼</span> Sponsorships</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar" id="sidebar-avatar">S</div>
        <div class="user-info"><div class="user-name" id="sidebar-username">Sponsor</div><div class="user-role" id="sidebar-role">Sponsor</div></div>
      </div>
      <button class="btn btn-logout" id="logout-btn">🚪 Sign Out</button>
    </div>
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Browse Events</h1><p class="page-subtitle">Discover opportunities to sponsor upcoming verified events</p></div>
    </div>
    
    <div class="availability-load-error" id="availability-load-error">
      Could not load your visibility status from the server. Refresh the page or try again later.
    </div>

    <div class="availability-warning" id="availability-warning">
      You are currently not visible to event managers.<br/>
      You will remain hidden until you turn this option ON again.
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
              <tr>
                  <th>Event Name</th>
                  <th>Location/Venue</th>
                  <th>Date</th>
                  <th>Capacity</th>
                  <th>Action</th>
              </tr>
          </thead>
          <tbody id="events-body">
            <tr class="loading-row"><td colspan="5"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Send Request Modal -->
<div class="modal-overlay" id="req-modal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Sponsor Event</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form id="req-form">
      <input type="hidden" id="r-event-id" value=""/>
      <div style="background:#f4f6f8; padding:15px; border-radius:6px; margin-bottom:15px;">
        <div style="font-size:12px; color:var(--text-muted)">Selected Event</div>
        <div id="r-event-title" style="font-weight:bold; font-size:16px;">--</div>
      </div>
      <div class="form-group">
        <label class="form-label">Message / Intro</label>
        <textarea id="r-message" class="form-control" placeholder="Introduce yourself and state what sponsorship options you are interested in..." rows="4"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="btn-submit">Send Request</button>
      </div>
    </form>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
  const user = requireRole('Sponsor');
  /** @type {boolean|null} null until loaded from GET /profile */
  let sponsorAvailability = null;

  if (user) {
      populateSidebar(user);
      setActiveNav();
      initSponsorEventsPage();
  }

  async function initSponsorEventsPage() {
      const res = await api.get('/profile');
      const warnEl = document.getElementById('availability-warning');
      const errEl = document.getElementById('availability-load-error');
      warnEl.style.display = 'none';
      errEl.style.display = 'none';

      let fresh = user;
      if (res.ok && res.data?.user) {
          fresh = res.data.user;
          localStorage.setItem('user', JSON.stringify(fresh));
          populateSidebar(fresh);
      }

      if (!res.ok || !fresh.profile) {
          sponsorAvailability = null;
          errEl.style.display = 'block';
          showToast('Could not load your visibility status.', 'error');
          loadEvents();
          return;
      }

      sponsorAvailability = availabilityFromDatabase(fresh.profile.is_available);
      if (sponsorAvailability === null) {
          errEl.style.display = 'block';
          showToast('Could not read visibility from the server.', 'error');
          loadEvents();
          return;
      }

      if (sponsorAvailability === false) {
          warnEl.style.display = 'block';
      }
      loadEvents();
  }

  async function loadEvents() {
    const res = await api.get('/events');
    const tbody = document.getElementById('events-body');
    if (!res.ok || !res.data.length) { 
        tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><p>No public events available right now.</p></div></td></tr>'; 
        return; 
    }
    
    tbody.innerHTML = res.data.map(e => `
      <tr>
        <td>
            <div style="font-weight:600">${e.title}</div>
            <div style="font-size:12px; color:var(--accent2); margin-top:2px; cursor:pointer; display:inline-block;" onclick="navigateToProfile(${e.creator?.id})">
                👤 ${e.creator?.name || 'Unknown'}
            </div>
        </td>
        <td style="color:var(--text-muted)">${e.location || e.venue?.name || 'TBA'}</td>
        <td style="color:var(--text-muted)">${fmtDateShort(e.start_time)}</td>
        <td style="color:var(--text-muted)">${e.capacity ? e.capacity.toLocaleString() : 'N/A'}</td>
        <td>
           <button class="btn btn-primary req-btn" onclick="openModal(${e.id}, '${e.title.replace(/'/g, "\\'")}')" ${sponsorAvailability !== true ? 'disabled' : ''} title="${sponsorAvailability === true ? 'Request to sponsor' : (sponsorAvailability === false ? 'Turn on Open to Sponsorship on your dashboard or profile' : 'Loading visibility…')}">Apply</button>
        </td>
      </tr>`).join('');
  }

  function openModal(eventId, eventTitle) { 
      if (sponsorAvailability !== true) {
          showToast(sponsorAvailability === false
            ? 'Turn on "Open to Sponsorship" on your dashboard or profile to send requests.'
            : 'Visibility status is still loading or unavailable.', 'warning');
          return;
      }
      document.getElementById('r-event-id').value = eventId;
      document.getElementById('r-event-title').innerText = eventTitle;
      document.getElementById('req-modal').classList.add('open'); 
  }
  
  function closeModal() { 
      document.getElementById('req-modal').classList.remove('open'); 
      document.getElementById('req-form').reset(); 
  }

  document.getElementById('req-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btn-submit');
    btn.textContent = 'Sending...';
    btn.disabled = true;

    const payload = { 
        event_id: +document.getElementById('r-event-id').value, 
        message: document.getElementById('r-message').value 
    };

    const res = await api.post('/sponsorship', payload);
    
    if (res.ok) { 
        showToast('Request sent directly to Event Manager!', 'success'); 
        closeModal(); 
    } else { 
        showToast(res.data?.message || 'Error sending request', 'error'); 
    }
    
    btn.textContent = 'Send Request';
    btn.disabled = false;
  });
</script>
</body>
</html>
