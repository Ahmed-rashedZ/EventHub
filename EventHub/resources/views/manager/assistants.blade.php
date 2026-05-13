<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Assistants – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <style>
    .assistant-card { background: var(--surface2); padding: 20px; border-radius: 12px; border: 1px solid var(--border); max-width: 600px; }
    .status-badge { padding: 4px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .status-active { background: rgba(16, 185, 129, 0.1); color: var(--success); }
    .status-inactive { background: rgba(239, 68, 68, 0.1); color: var(--error); }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); }
    .modal-content { background: var(--surface2); margin: 5% auto; padding: 24px; border-radius: 16px; border: 1px solid var(--border); width: 80%; max-width: 900px; max-height: 85vh; overflow-y: auto; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .stat-box { background: var(--surface1); padding: 16px; border-radius: 12px; border: 1px solid var(--border); }
    .history-item { display: flex; align-items: center; gap: 12px; padding: 12px; border-bottom: 1px solid var(--border); }
    .history-item img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
  </style>
<link rel="icon" href="/images/logo.jpg" type="image/jpeg">
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo" style="display:flex; justify-content:center; align-items:center; padding: 8px 0;"><img src="/images/logo.jpg" alt="EventHub Logo" style="width: 85px; height: 85px; object-fit: contain; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item" href="/manager/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Events</span>
      <a class="nav-item" href="/manager/events"><span class="nav-icon">📅</span> My Events</a>
      <a class="nav-item active" href="/manager/assistants"><span class="nav-icon">👥</span> Assistants</a>
      <a class="nav-item" href="/manager/attendance"><span class="nav-icon">📍</span> Attendance</a>
      <a class="nav-item" href="/manager/sponsorship"><span class="nav-icon">💼</span> Sponsorship</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav> 
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Assistants Management</h1><p class="page-subtitle">Manage your event assistants and track their check-in performance</p></div>
    </div>

    <div style="display: flex; gap: 20px; align-items: flex-start;">
      <div class="assistant-card" style="flex: 1;">
        <h3 id="form-title" style="margin-top:0; margin-bottom: 20px;">Generate Assistant Account</h3>
        <p id="form-desc" style="color:var(--text-muted); font-size: 0.9rem; margin-bottom: 24px;">
          Assistants use their accounts to log in on their mobile devices and scan QR codes for attendance tracking.
        </p>
        
        <form id="assistant-form">
          <input type="hidden" id="edit-id"/>
          <div class="form-group">
            <label class="form-label">Assistant Name</label>
            <input id="a-name" type="text" class="form-control" placeholder="e.g. Gate 1 Scanner" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Assistant Email (Login ID)</label>
            <input id="a-email" type="email" class="form-control" placeholder="gate1@myevent.com" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Password <span id="pass-hint" style="font-size:0.75rem; color:var(--text-muted)">(Leave blank to keep current)</span></label>
            <input id="a-pass" type="password" class="form-control" minlength="8"/>
          </div>
          <div class="form-group">
            <label class="form-label">Assign to Event</label>
            <select id="a-event" class="form-control" required>
              <option value="">Select an Event...</option>
            </select>
          </div>
          <div style="display:flex; justify-content: space-between; margin-top:20px;">
            <button type="button" class="btn btn-ghost" id="cancel-edit" style="display:none;" onclick="resetForm()">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-btn">Create Assistant</button>
          </div>
        </form>

        <div id="success-box" style="display:none; margin-top:30px; padding: 20px; background: rgba(16,185,129,0.1); border: 1px solid var(--success); border-radius: 8px;">
          <h4 style="color: var(--success); margin-top:0;">✅ Success!</h4>
          <p style="margin-bottom:0; font-size: 0.9rem; color: var(--text-secondary)">The assistant data has been updated. They can now log in and access the QR scanner.</p>
        </div>
      </div>

      <div class="assistant-card" style="flex: 1.5; max-width: none;">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 20px; flex-wrap:wrap; gap:16px;">
          <h3 style="margin:0;">Existing Assistants</h3>
          <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <div style="position:relative">
              <input type="text" id="filter-name" class="form-control" placeholder="Search name or email..." style="width: 220px; padding-left: 36px;" oninput="applyFilter()">
              <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:14px; opacity:0.5;">🔍</span>
            </div>
            <select id="filter-event" class="form-control" style="width: 200px;" onchange="applyFilter()">
              <option value="">All Events</option>
            </select>
          </div>
        </div>
        
        <!-- Leaderboard Widget -->
        <div id="leaderboard-container" style="display:none; margin-bottom: 24px;">
        </div>

        <div id="assistants-list">
          <p style="color:var(--text-muted); font-size: 0.9rem;">Loading assistants...</p>
        </div>
      </div>
    </div>
  </main>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script>
  const user = requireRole('Event Manager');
  if (user) { populateSidebar(user); setActiveNav(); loadEvents(); loadAssistants(); }

  let eventsMap = {};

  async function loadEvents() {
    const res = await api.get('/events/list/my');
    const select = document.getElementById('a-event');
    const filterSelect = document.getElementById('filter-event');
    if (res.ok) {
      res.data.forEach(ev => {
        eventsMap[ev.id] = ev.title;
        const opt = document.createElement('option');
        opt.value = ev.id;
        opt.textContent = ev.title;
        select.appendChild(opt);

        const filterOpt = document.createElement('option');
        filterOpt.value = ev.id;
        filterOpt.textContent = ev.title;
        filterSelect.appendChild(filterOpt);
      });
    }
  }

  let allAssistants = [];

  async function loadAssistants() {
    const res = await api.get('/assistants');
    if (res.ok) {
      allAssistants = res.data;
      applyFilter();
    } else {
      document.getElementById('assistants-list').innerHTML = '<div style="text-align:center; padding:40px; color:var(--danger);"><p>Failed to load assistants.</p></div>';
    }
  }

  function applyFilter() {
    const nameFilter = document.getElementById('filter-name').value.toLowerCase();
    const eventFilter = document.getElementById('filter-event').value;

    const filtered = allAssistants.filter(a => {
      const matchName = a.name.toLowerCase().includes(nameFilter) || a.email.toLowerCase().includes(nameFilter);
      
      let matchEvent = true;
      if (eventFilter !== "") {
        const isCurrentEvent = a.event && a.event.id.toString() === eventFilter;
        const isPastEvent = a.past_event_ids && a.past_event_ids.includes(parseInt(eventFilter));
        matchEvent = isCurrentEvent || isPastEvent;
      }

      return matchName && matchEvent;
    });

    renderLeaderboard(eventFilter, filtered);
    renderAssistantsList(filtered);
  }

  function renderLeaderboard(eventId, assistantsInEvent) {
    const container = document.getElementById('leaderboard-container');
    if (!eventId) {
      container.style.display = 'none';
      return;
    }

    // Filter out assistants who have 0 scans for this event
    const activeAssistants = assistantsInEvent.filter(a => a.event_scans && a.event_scans[eventId] > 0);
    
    if (activeAssistants.length === 0) {
      container.style.display = 'none';
      return;
    }

    // Sort by scans descending and take top 5
    activeAssistants.sort((a, b) => (b.event_scans[eventId] || 0) - (a.event_scans[eventId] || 0));
    const topAssistants = activeAssistants.slice(0, 5);
    const eventName = eventsMap[eventId] || 'Selected Event';

    let html = `
      <div style="background: rgba(110,64,242,0.06); border: 1px solid rgba(139,92,246,0.15); border-radius: 12px; padding: 20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
          <h4 style="margin:0; color:#c4b5fd; display:flex; align-items:center; gap:8px;">
            <span style="font-size:1.2rem;">🏆</span> Top 5 Scanners - ${eventName}
          </h4>
        </div>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
    `;

    topAssistants.forEach((a, index) => {
      const scans = a.event_scans[eventId];
      const medals = ['🥇', '🥈', '🥉'];
      const rankBadge = index < 3 ? `<span style="font-size:1.2rem;">${medals[index]}</span>` : `<div style="width:24px; height:24px; border-radius:50%; background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:bold; color:var(--text-muted);">#${index+1}</div>`;
      
      html += `
          <div style="background:rgba(15,18,25,0.5); border:1px solid rgba(255,255,255,0.05); border-radius:10px; padding:12px 16px; display:flex; align-items:center; gap:12px; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
            ${rankBadge}
            <div style="flex:1;">
              <div style="font-weight:700; font-size:0.9rem; color:#fff;">${a.name}</div>
              <div style="font-size:0.75rem; color:var(--text-muted);">${a.email.split('@')[0]}</div>
            </div>
            <div style="text-align:right;">
              <div style="font-weight:800; color:#a78bfa; font-size:1.2rem; line-height:1;">${scans}</div>
              <div style="font-size:0.65rem; color:var(--text-muted); text-transform:uppercase; margin-top:4px;">Scans</div>
            </div>
          </div>
      `;
    });

    html += `
        </div>
      </div>
    `;
    
    container.innerHTML = html;
    container.style.display = 'block';
  }

  function renderAssistantsList(data) {
    const listDiv = document.getElementById('assistants-list');
    if (data.length > 0) {
      listDiv.innerHTML = '<div class="table-wrap"><table style="width:100%; border-collapse: collapse;">' +
        '<thead><tr>' +
        '<th style="text-align:left; padding:12px 14px; font-size:0.7rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); border-bottom:1px solid var(--border);">Name</th>' +
        '<th style="text-align:left; padding:12px 14px; font-size:0.7rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); border-bottom:1px solid var(--border);">Event</th>' +
        '<th style="text-align:left; padding:12px 14px; font-size:0.7rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); border-bottom:1px solid var(--border);">Status</th>' +
        '<th style="text-align:right; padding:12px 14px; font-size:0.7rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); border-bottom:1px solid var(--border);">Actions</th>' +
        '</tr></thead><tbody>' +
        data.map(a => `
          <tr style="transition: background 0.2s; cursor: default;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
            <td style="padding:14px; border-bottom:1px solid rgba(255,255,255,0.04); vertical-align:middle;">
              <div style="font-weight:600; color:#fff;">${a.name}</div>
              <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">${a.email}</div>
            </td>
            <td style="padding:14px; border-bottom:1px solid rgba(255,255,255,0.04); vertical-align:middle; color:var(--text-muted);">
              ${a.event ? a.event.title : '—'}
            </td>
            <td style="padding:14px; border-bottom:1px solid rgba(255,255,255,0.04); vertical-align:middle;">
              <span style="display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:8px; font-size:0.75rem; font-weight:600; background:${a.is_active ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)'}; color:${a.is_active ? '#10b981' : '#ef4444'}; border:1px solid ${a.is_active ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.2)'};">
                 ${a.is_active ? 'Active' : 'Suspended'}
              </span>
            </td>
            <td style="padding:14px; border-bottom:1px solid rgba(255,255,255,0.04); vertical-align:middle; text-align:right;">
              <div style="display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap;">
                <button class="btn btn-sm" style="background:rgba(34,211,238,.12); color:#22d3ee; border:1px solid rgba(34,211,238,.25); display:inline-flex; align-items:center; gap:4px;" onclick="window.location.href='/manager/assistants/${a.id}/stats'">
                  📊 Stats
                </button>
                <button class="btn btn-sm" style="background:rgba(245,158,11,0.15); color:#f59e0b; border:1px solid rgba(245,158,11,0.3); display:inline-flex; align-items:center; gap:4px;" onclick="editAssistant(${JSON.stringify(a).replace(/"/g, '&quot;')})">
                  ✏️ Edit
                </button>
                <button class="btn btn-sm" style="background:${a.is_active ? 'rgba(239,68,68,.12)' : 'rgba(16,185,129,.12)'}; color:${a.is_active ? '#ef4444' : '#10b981'}; border:1px solid ${a.is_active ? 'rgba(239,68,68,.25)' : 'rgba(16,185,129,.25)'}; display:inline-flex; align-items:center; gap:4px;" onclick="toggleStatus(${a.id})">
                  ${a.is_active ? '🚫 Suspend' : '✅ Activate'}
                </button>
              </div>
            </td>
          </tr>`).join('') +
        '</tbody></table></div>';
    } else {
      listDiv.innerHTML = '<div style="text-align:center; padding:40px; color:var(--text-muted);"><div style="font-size:2rem; margin-bottom:12px;">👥</div><p>No assistants match the filter.</p></div>';
    }
  }

  async function toggleStatus(id) {
    const res = await api.patch(`/assistants/${id}/status`);
    if (res.ok) {
      showToast('Status updated!', 'success');
      loadAssistants();
    }
  }

  function editAssistant(a) {
    document.getElementById('form-title').textContent = 'Edit Assistant';
    document.getElementById('form-desc').textContent = 'Modify assistant details or reassign them to another event.';
    document.getElementById('edit-id').value = a.id;
    document.getElementById('a-name').value = a.name;
    document.getElementById('a-email').value = a.email;
    document.getElementById('a-event').value = a.event_id;
    document.getElementById('a-pass').required = false;
    document.getElementById('pass-hint').style.display = 'inline';
    document.getElementById('save-btn').textContent = 'Update Assistant';
    document.getElementById('cancel-edit').style.display = 'inline';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function resetForm() {
    document.getElementById('form-title').textContent = 'Generate Assistant Account';
    document.getElementById('form-desc').textContent = 'Assistants use their accounts to log in on their mobile devices and scan QR codes.';
    document.getElementById('edit-id').value = '';
    document.getElementById('assistant-form').reset();
    document.getElementById('a-pass').required = true;
    document.getElementById('pass-hint').style.display = 'none';
    document.getElementById('save-btn').textContent = 'Create Assistant';
    document.getElementById('cancel-edit').style.display = 'none';
  }

  document.getElementById('assistant-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('save-btn');
    const editId = document.getElementById('edit-id').value;
    btn.textContent = editId ? 'Updating...' : 'Creating...'; btn.disabled = true;

    const body = {
      name: document.getElementById('a-name').value,
      email: document.getElementById('a-email').value,
      event_id: document.getElementById('a-event').value,
    };
    if (document.getElementById('a-pass').value) {
      body.password = document.getElementById('a-pass').value;
    }

    let res;
    if (editId) {
      res = await api.put(`/assistants/${editId}`, body);
    } else {
      body.role = 'Assistant';
      res = await api.post('/users', body);
    }
    
    if (res.ok) {
      showToast(editId ? 'Assistant updated!' : 'Assistant created!', 'success');
      resetForm();
      document.getElementById('success-box').style.display = 'block';
      setTimeout(() => { document.getElementById('success-box').style.display = 'none'; }, 5000);
      loadAssistants();
    } else {
      const msg = res.data?.errors ? Object.values(res.data.errors).flat().join('. ') : res.data?.message || 'Error saving assistant';
      showToast(msg, 'error');
    }
    btn.textContent = editId ? 'Update Assistant' : 'Create Assistant'; btn.disabled = false;
  });
</script>
</body>





