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
      <div><h1 class="page-title">Live Attendance</h1><p class="page-subtitle">Real-time attendance tracking for your live events</p></div>
      <div class="topbar-actions">
        <button class="att-refresh-btn" onclick="loadLiveEvents()" style="display:inline-flex; align-items:center; gap:6px;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path></svg>
          Refresh
        </button>
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
      container.innerHTML = '<div class="att-empty"><div class="att-empty-icon" style="color:var(--danger); display:flex; justify-content:center; margin-bottom:12px;"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div><div class="att-empty-title">Failed to load events</div><div class="att-empty-text">Please try again</div></div>';
      return;
    }

    // Filter for live events only (approved + currently running) that have check-in activity
    const liveEvents = res.data.filter(e => e.status === 'approved' && e.time_status === 'live');

    if (!liveEvents.length) {
      container.innerHTML = `
        <div class="att-empty">
          <div class="att-empty-icon" style="color:var(--text-muted); display:flex; justify-content:center; margin-bottom:12px;">
            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.3 12.3a4.5 4.5 0 016.3 0M6.3 9.3a8.5 8.5 0 0111.3 0M3.3 6.3a12.5 12.5 0 0117.3 0M12 15h.01"></path></svg>
          </div>
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
          <div class="att-empty-icon" style="color:var(--text-muted); display:flex; justify-content:center; margin-bottom:12px;">
            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.3 12.3a4.5 4.5 0 016.3 0M6.3 9.3a8.5 8.5 0 0111.3 0M3.3 6.3a12.5 12.5 0 0117.3 0M12 15h.01"></path></svg>
          </div>
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
              <span style="display:flex; align-items:center; gap:4px;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5m-15 10.5V10.5M3 21h18M10.5 8.25h3"></path></svg> ${ev.venue?.name || '—'}</span>
              <span style="display:flex; align-items:center; gap:4px;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> ${fmtDate(ev.start_time)} — ${fmtDate(ev.end_time)}</span>
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
            View Participants (${participants.length})
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
            ${participants.map((t, i) => {
              const latestLog = (t.attendance_logs && t.attendance_logs.length) 
                ? t.attendance_logs.reduce((latest, current) => new Date(current.scanned_at) > new Date(latest.scanned_at) ? current : latest, t.attendance_logs[0])
                : t.attendance_log;
              const scanTime = latestLog ? fmtDate(latestLog.scanned_at) : '—';
              const scannerName = latestLog && latestLog.scanner ? `<div style="font-size:.7rem;color:var(--text-muted);margin-top:2px; display:inline-flex; align-items:center; gap:2px;"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path></svg> by ${latestLog.scanner.name}</div>` : '';
              const daysAttendedStr = t.total_days_attended ? `<div style="font-size:.7rem;color:var(--text-muted);margin-top:4px; display:inline-flex; align-items:center; gap:2px;"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path></svg> <bdi>${t.total_days_attended}</bdi> ${t('day(s)')}</div>` : '';
              
              return `
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
                  <td>
                    ${badge(t.scanned_today ? 'used' : 'unused')}
                    ${daysAttendedStr}
                  </td>
                  <td style="color:var(--text-muted)">
                    ${scanTime}
                    ${scannerName}
                  </td>
                </tr>
              `;
            }).join('')}
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






