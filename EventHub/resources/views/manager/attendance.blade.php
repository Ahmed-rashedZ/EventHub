<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
<style>
/* ── Live Attendance Styles ──────────────────────────── */
.att-header-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.att-live-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#ef4444;padding:5px 14px;border-radius:20px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em}
.att-live-dot{width:8px;height:8px;border-radius:50%;background:#ef4444;animation:livePulse 1.5s infinite}
@keyframes livePulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.3)}}

.att-events-grid{display:grid;grid-template-columns:1fr;gap:20px}
.att-event-card{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;overflow:hidden;transition:all .3s}
.att-event-card:hover{border-color:rgba(110,64,242,.3);box-shadow:0 8px 32px rgba(0,0,0,.3)}

.att-ev-top{padding:24px 28px 0;display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
.att-ev-info{flex:1;min-width:0}
.att-ev-title{font-size:1.2rem;font-weight:800;color:#fff;margin:0 0 6px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.att-ev-meta{display:flex;gap:16px;font-size:.78rem;color:var(--text-muted);flex-wrap:wrap}
.att-ev-meta span{display:flex;align-items:center;gap:4px}
.att-ev-badges{display:flex;gap:6px;flex-shrink:0;flex-wrap:wrap}

.att-ev-stats{padding:20px 28px;display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
@media(max-width:640px){.att-ev-stats{grid-template-columns:repeat(2,1fr)}}
.att-mini-stat{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:14px 16px;text-align:center;transition:background .2s}
.att-mini-stat:hover{background:rgba(255,255,255,.06)}
.att-mini-stat-label{font-size:.62rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:4px}
.att-mini-stat-value{font-size:1.4rem;font-weight:800;color:#fff;line-height:1}
.att-mini-stat-sub{font-size:.68rem;color:var(--text-muted);margin-top:2px}

.att-ev-progress{padding:0 28px 8px}
.att-progress-header{display:flex;justify-content:space-between;font-size:.75rem;margin-bottom:6px}
.att-progress-header span:first-child{color:var(--text-muted)}
.att-progress-header span:last-child{font-weight:700;color:#fff}
.att-progress-track{background:rgba(255,255,255,.06);border-radius:8px;height:10px;overflow:hidden}
.att-progress-fill{height:100%;border-radius:8px;transition:width .8s ease;background:linear-gradient(90deg,#6e40f2,#22d3ee)}

.att-ev-participants{padding:0 28px 24px}
.att-toggle-btn{display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);color:var(--text-muted);padding:8px 16px;border-radius:10px;font-size:.78rem;font-weight:600;cursor:pointer;transition:all .2s;width:100%;justify-content:center}
.att-toggle-btn:hover{background:rgba(255,255,255,.08);color:#fff}
.att-toggle-btn.open{background:rgba(110,64,242,.1);border-color:rgba(110,64,242,.3);color:var(--accent)}

.att-table-wrap{margin-top:14px;max-height:400px;overflow-y:auto;border-radius:12px;border:1px solid rgba(255,255,255,.06)}
.att-table{width:100%;border-collapse:collapse;font-size:.82rem}
.att-table th{text-align:left;padding:10px 14px;font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);border-bottom:1px solid var(--border);background:rgba(255,255,255,.02);position:sticky;top:0;z-index:1}
.att-table td{padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.03);vertical-align:middle}
.att-table tr:last-child td{border:none}
.att-table tr:hover{background:rgba(255,255,255,.02)}

.att-empty{text-align:center;padding:60px 20px}
.att-empty-icon{font-size:3.5rem;margin-bottom:16px}
.att-empty-title{font-size:1.15rem;font-weight:700;color:#fff;margin-bottom:6px}
.att-empty-text{font-size:.88rem;color:var(--text-muted)}

.att-refresh-btn{display:inline-flex;align-items:center;gap:6px;background:rgba(110,64,242,.12);border:1px solid rgba(110,64,242,.3);color:var(--accent);padding:6px 16px;border-radius:10px;font-size:.82rem;font-weight:600;cursor:pointer;transition:all .2s}
.att-refresh-btn:hover{background:rgba(110,64,242,.2)}
</style>
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
      <a class="nav-item active" href="/manager/attendance"><span class="nav-icon">📍</span> Attendance</a>
      <a class="nav-item" href="/manager/sponsorship"><span class="nav-icon">💼</span> Sponsorship</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Live Attendance</h1><p class="page-subtitle">Real-time attendance tracking for your live events</p></div>
      <div class="topbar-actions">
        <button class="att-refresh-btn" onclick="loadLiveEvents()">🔄 Refresh</button>
      </div>
    </div>

    <div id="events-container">
      <div style="display:flex;align-items:center;justify-content:center;min-height:300px"><div class="spinner"></div></div>
    </div>
  </main>
</div>
<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script>
  const user = requireRole('Event Manager');
  if (user) { populateSidebar(user); setActiveNav(); loadLiveEvents(); }

  // Store participants data per event
  const participantsCache = {};
  const expandedEvents = new Set();

  async function loadLiveEvents() {
    const container = document.getElementById('events-container');
    container.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;min-height:300px"><div class="spinner"></div></div>';

    // Fetch my events
    const res = await api.get('/events/list/my');
    if (!res.ok) {
      container.innerHTML = '<div class="att-empty"><div class="att-empty-icon">❌</div><div class="att-empty-title">Failed to load events</div><div class="att-empty-text">Please try again</div></div>';
      return;
    }

    // Filter for live events only (approved + currently running) that have check-in activity
    const liveEvents = res.data.filter(e => e.status === 'approved' && e.time_status === 'live');

    if (!liveEvents.length) {
      container.innerHTML = `
        <div class="att-empty">
          <div class="att-empty-icon">📡</div>
          <div class="att-empty-title">No Live Events</div>
          <div class="att-empty-text">You don't have any events that are currently live.<br>Events will appear here during their scheduled time.</div>
        </div>
      `;
      return;
    }

    // Load stats for each live event
    const statsPromises = liveEvents.map(ev =>
      Promise.all([
        api.get(`/analytics/event/${ev.id}`),
        api.get(`/checkin/event/${ev.id}`)
      ]).then(([statsRes, partRes]) => ({
        event: ev,
        stats: statsRes.ok ? statsRes.data : null,
        participants: partRes.ok ? partRes.data : []
      }))
    );

    const results = await Promise.all(statsPromises);

    // Show all live events with their stats
    const activeEvents = results.filter(r => r.stats);

    if (!activeEvents.length) {
      container.innerHTML = `
        <div class="att-empty">
          <div class="att-empty-icon">📡</div>
          <div class="att-empty-title">No Live Events</div>
          <div class="att-empty-text">You don't have any events that are currently live.<br>Events will appear here during their scheduled time.</div>
        </div>
      `;
      return;
    }

    // Render
    container.innerHTML = `
      <div class="att-header-row">
        <div class="att-live-badge"><div class="att-live-dot"></div> ${activeEvents.length} Live Event${activeEvents.length > 1 ? 's' : ''}</div>
      </div>
      <div class="att-events-grid">
        ${activeEvents.map(r => renderEventCard(r)).join('')}
      </div>
    `;

    // Cache participants
    activeEvents.forEach(r => {
      participantsCache[r.event.id] = r.participants;
    });
  }

  function renderEventCard({event: ev, stats: s, participants}) {
    const remaining = s.registered_count - s.attended_count;
    const fillRate = ev.capacity > 0 ? Math.round((s.registered_count / ev.capacity) * 1000) / 10 : 0;
    const attRate = s.attendance_rate || 0;
    const progressColor = attRate > 80 ? '#22c55e' : attRate > 50 ? '#f59e0b' : '#6e40f2';

    return `
      <div class="att-event-card" id="ev-card-${ev.id}">
        <div class="att-ev-top">
          <div class="att-ev-info">
            <h3 class="att-ev-title">${ev.title}</h3>
            <div class="att-ev-meta">
              <span>🏛️ ${ev.venue?.name || '—'}</span>
              <span>🕐 ${fmtDate(ev.start_time)} — ${fmtDate(ev.end_time)}</span>
            </div>
          </div>
          <div class="att-ev-badges">
            ${badge('approved')}
            ${timeBadge('live')}
          </div>
        </div>

        <div class="att-ev-stats">
          <div class="att-mini-stat">
            <div class="att-mini-stat-label">Capacity</div>
            <div class="att-mini-stat-value">${ev.capacity}</div>
          </div>
          <div class="att-mini-stat">
            <div class="att-mini-stat-label">Registered</div>
            <div class="att-mini-stat-value" style="color:#22d3ee">${s.registered_count}</div>
            <div class="att-mini-stat-sub">${fillRate}% full</div>
          </div>
          <div class="att-mini-stat">
            <div class="att-mini-stat-label">Checked In</div>
            <div class="att-mini-stat-value" style="color:#22c55e">${s.attended_count}</div>
            <div class="att-mini-stat-sub">${attRate}%</div>
          </div>
          <div class="att-mini-stat">
            <div class="att-mini-stat-label">Remaining</div>
            <div class="att-mini-stat-value" style="color:#f59e0b">${remaining}</div>
            <div class="att-mini-stat-sub">not scanned</div>
          </div>
        </div>

        <div class="att-ev-progress">
          <div class="att-progress-header">
            <span>Attendance Progress</span>
            <span>${s.attended_count} / ${s.registered_count} (${attRate}%)</span>
          </div>
          <div class="att-progress-track">
            <div class="att-progress-fill" style="width:${attRate}%"></div>
          </div>
        </div>

        <div class="att-ev-participants">
          <button class="att-toggle-btn" id="toggle-${ev.id}" onclick="toggleParticipants(${ev.id})">
            👥 View Participants (${participants.length})
            <span id="arrow-${ev.id}">▼</span>
          </button>
          <div id="parts-${ev.id}" style="display:none">
            ${renderParticipantsTable(participants)}
          </div>
        </div>
      </div>
    `;
  }

  function renderParticipantsTable(participants) {
    if (!participants.length) {
      return '<div style="text-align:center;padding:20px;color:var(--text-muted)">No registrations</div>';
    }
    return `
      <div class="att-table-wrap">
        <table class="att-table">
          <thead><tr><th>#</th><th>Attendee</th><th>Ticket Code</th><th>Status</th><th>Scanned At</th></tr></thead>
          <tbody>
            ${participants.map((t, i) => `
              <tr>
                <td style="color:var(--text-muted)">${i+1}</td>
                <td>
                  <div style="display:flex;align-items:center;gap:8px;cursor:pointer" onclick="navigateToProfile(${t.user?.id})">
                    <div class="avatar" style="width:26px;height:26px;font-size:.7rem">${t.user?.name?.charAt(0) || '?'}</div>
                    <div>
                      <div style="font-weight:600;color:var(--accent2)">${t.user?.name || '—'}</div>
                      <div style="font-size:.72rem;color:var(--text-muted)">${t.user?.email || ''}</div>
                    </div>
                  </div>
                </td>
                <td style="font-family:monospace;color:var(--accent2)">${t.qr_code}</td>
                <td>${badge(t.status)}</td>
                <td style="color:var(--text-muted)">${t.attendance_log ? fmtDate(t.attendance_log.scanned_at) : '—'}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    `;
  }

  function toggleParticipants(eventId) {
    const el = document.getElementById(`parts-${eventId}`);
    const btn = document.getElementById(`toggle-${eventId}`);
    const arrow = document.getElementById(`arrow-${eventId}`);
    const isOpen = el.style.display !== 'none';
    el.style.display = isOpen ? 'none' : 'block';
    btn.classList.toggle('open', !isOpen);
    arrow.textContent = isOpen ? '▼' : '▲';
  }
</script>
</body>
</html>
