<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Event Statistics – EventHub Admin</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<style>
/* ── Event Stats Page ──────────────────────────────── */
.es-back{display:inline-flex;align-items:center;gap:6px;color:var(--text-muted);font-size:.85rem;font-weight:600;cursor:pointer;transition:color .2s;text-decoration:none;margin-bottom:20px}
.es-back:hover{color:#fff}
.es-hero{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:28px 32px;margin-bottom:24px;position:relative;overflow:hidden}
.es-hero-stripe{position:absolute;left:0;top:0;bottom:0;width:5px;border-radius:5px 0 0 5px}
.es-hero-top{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:16px}
.es-hero-title{font-size:1.6rem;font-weight:800;color:#fff;margin:0;line-height:1.2}
.es-hero-type{display:inline-flex;align-items:center;gap:5px;padding:4px 14px;border-radius:20px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;flex-shrink:0}
.es-hero-meta{display:flex;gap:20px;flex-wrap:wrap;font-size:.82rem;color:var(--text-muted)}
.es-hero-meta span{display:flex;align-items:center;gap:5px}

.es-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px;margin-bottom:24px}
.es-stat{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s}
.es-stat:hover{transform:translateY(-3px);box-shadow:0 12px 36px rgba(0,0,0,.3)}
.es-stat-stripe{position:absolute;left:0;top:0;bottom:0;width:4px;border-radius:4px 0 0 4px}
.es-stat-icon{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;font-size:1.25rem;margin-bottom:12px}
.es-stat-label{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:4px}
.es-stat-value{font-size:2rem;font-weight:800;color:#fff;line-height:1;margin-bottom:4px}
.es-stat-sub{font-size:.72rem;color:var(--text-muted)}

.es-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px}
@media(max-width:768px){.es-row{grid-template-columns:1fr}}
.es-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,.2)}
.es-card-title{font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:16px;display:flex;align-items:center;gap:8px}
.es-card-title span{font-size:1rem}
.es-chart-wrap{position:relative;height:260px}

.es-rate-row{display:flex;gap:20px;margin-bottom:24px;flex-wrap:wrap}
.es-rate-card{flex:1;min-width:240px;background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:24px;display:flex;align-items:center;gap:20px;transition:transform .2s}
.es-rate-card:hover{transform:translateY(-2px)}
.es-rate-circle{width:88px;height:88px;flex-shrink:0;position:relative}
.es-rate-circle svg{transform:rotate(-90deg)}
.es-rate-pct{position:absolute;inset:0;display:grid;place-items:center;font-size:1.15rem;font-weight:800;color:#fff}
.es-rate-info h4{margin:0;font-size:1.05rem;font-weight:700;color:#fff}
.es-rate-info p{margin:4px 0 0;font-size:.8rem;color:var(--text-muted)}

.es-full{grid-column:1/-1}
.es-participants-table{width:100%;border-collapse:collapse;font-size:.85rem}
.es-participants-table th{text-align:left;padding:12px 14px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);border-bottom:1px solid var(--border)}
.es-participants-table td{padding:14px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle}
.es-participants-table tr:last-child td{border:none}
.es-participants-table tr:hover{background:rgba(255,255,255,.02)}

.es-loading{display:flex;align-items:center;justify-content:center;min-height:400px}
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
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div id="page-loading" class="es-loading"><div class="spinner"></div></div>
    <div id="page-content" style="display:none">

      <!-- Back Link -->
      <a class="es-back" href="/admin/events">← Back to Events</a>

      <!-- Hero Section -->
      <div class="es-hero" id="es-hero">
        <div class="es-hero-stripe" id="hero-stripe" style="background:#6e40f2"></div>
        <div class="es-hero-top">
          <h1 class="es-hero-title" id="hero-title">—</h1>
          <span class="es-hero-type" id="hero-type"></span>
        </div>
        <div class="es-hero-meta" id="hero-meta"></div>
      </div>

      <!-- Stat Cards -->
      <div class="es-stats" id="stats-row">
        <div class="es-stat"><div class="es-stat-stripe" style="background:#6e40f2"></div><div class="es-stat-icon" style="background:rgba(110,64,242,.15)">👥</div><div class="es-stat-label">Capacity</div><div class="es-stat-value" id="s-capacity">—</div><div class="es-stat-sub">Max attendees</div></div>
        <div class="es-stat"><div class="es-stat-stripe" style="background:#22d3ee"></div><div class="es-stat-icon" style="background:rgba(34,211,238,.15)">🎟️</div><div class="es-stat-label">Registered</div><div class="es-stat-value" id="s-registered">—</div><div class="es-stat-sub" id="s-registered-sub"></div></div>
        <div class="es-stat"><div class="es-stat-stripe" style="background:#22c55e"></div><div class="es-stat-icon" style="background:rgba(34,197,94,.15)">✅</div><div class="es-stat-label">Checked In</div><div class="es-stat-value" id="s-attended">—</div><div class="es-stat-sub" id="s-attended-sub"></div></div>
        <div class="es-stat"><div class="es-stat-stripe" style="background:#f59e0b"></div><div class="es-stat-icon" style="background:rgba(245,158,11,.15)">⏳</div><div class="es-stat-label">Remaining</div><div class="es-stat-value" id="s-remaining">—</div><div class="es-stat-sub">Not checked in yet</div></div>
      </div>

      <!-- Rate Rings -->
      <div class="es-rate-row">
        <div class="es-rate-card">
          <div class="es-rate-circle">
            <svg width="88" height="88" viewBox="0 0 88 88"><circle cx="44" cy="44" r="38" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="7"/><circle id="fill-ring" cx="44" cy="44" r="38" fill="none" stroke="url(#fg1)" stroke-width="7" stroke-linecap="round" stroke-dasharray="238.76" stroke-dashoffset="238.76"/><defs><linearGradient id="fg1" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#6e40f2"/><stop offset="100%" stop-color="#22d3ee"/></linearGradient></defs></svg>
            <div class="es-rate-pct" id="fill-pct">0%</div>
          </div>
          <div class="es-rate-info"><h4>Fill Rate</h4><p id="fill-detail">—</p></div>
        </div>
        <div class="es-rate-card">
          <div class="es-rate-circle">
            <svg width="88" height="88" viewBox="0 0 88 88"><circle cx="44" cy="44" r="38" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="7"/><circle id="att-ring" cx="44" cy="44" r="38" fill="none" stroke="url(#fg2)" stroke-width="7" stroke-linecap="round" stroke-dasharray="238.76" stroke-dashoffset="238.76"/><defs><linearGradient id="fg2" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#22c55e"/><stop offset="100%" stop-color="#10b981"/></linearGradient></defs></svg>
            <div class="es-rate-pct" id="att-pct">0%</div>
          </div>
          <div class="es-rate-info"><h4>Attendance Rate</h4><p id="att-detail">—</p></div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="es-row">
        <div class="es-card">
          <div class="es-card-title"><span>📊</span> Registration vs Attendance</div>
          <div class="es-chart-wrap"><canvas id="regAttChart"></canvas></div>
        </div>
        <div class="es-card">
          <div class="es-card-title"><span>🎯</span> Capacity Breakdown</div>
          <div class="es-chart-wrap"><canvas id="capChart"></canvas></div>
        </div>
      </div>

      <!-- Participants Table -->
      <div class="es-card" style="margin-bottom:24px">
        <div class="es-card-title"><span>👥</span> Participants (<span id="part-count">0</span>)</div>
        <div class="table-wrap">
          <table class="es-participants-table">
            <thead><tr><th>#</th><th>Attendee</th><th>Ticket Code</th><th>Status</th><th>Scanned At</th></tr></thead>
            <tbody id="participants-body">
              <tr><td colspan="5" style="text-align:center;padding:40px"><div class="spinner" style="margin:auto"></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
const user = requireRole('Admin');
if (user) { populateSidebar(user); setActiveNav(); loadEventStats(); }

Chart.defaults.color = '#7d8590';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
Chart.defaults.font.family = 'Inter';

const TYPE_COLORS = { Conference:'#3b82f6', Workshop:'#10b981', Exhibition:'#f59e0b', Entertainment:'#ec4899', Seminar:'#8b5cf6', Festival:'#f97316', Other:'#6b7280' };
const TYPE_ICONS  = { Conference:'🎤', Workshop:'🔧', Exhibition:'🖼️', Entertainment:'🎭', Seminar:'📚', Festival:'🎉', Other:'📌' };

function animVal(el, end, suffix='') {
  let s=0; const st=performance.now();
  (function step(t){const p=Math.min((t-st)/800,1);el.textContent=Math.round(s+(end-s)*(1-Math.pow(1-p,3)))+suffix;if(p<1)requestAnimationFrame(step)})(st);
}

function setRing(id, pct) {
  const el = document.getElementById(id);
  el.style.transition = 'stroke-dashoffset 1.2s ease';
  el.style.strokeDashoffset = 238.76 - (238.76 * Math.min(pct,100) / 100);
}

function getEventIdFromUrl() {
  const parts = window.location.pathname.split('/');
  return parts[parts.length - 1];
}

async function loadEventStats() {
  const eventId = getEventIdFromUrl();
  if (!eventId || isNaN(eventId)) {
    window.location.href = '/admin/events';
    return;
  }

  const [statsRes, eventRes, partRes] = await Promise.all([
    api.get(`/analytics/event/${eventId}`),
    api.get(`/events/${eventId}`),
    api.get(`/checkin/event/${eventId}`)
  ]);

  document.getElementById('page-loading').style.display = 'none';
  document.getElementById('page-content').style.display = 'block';

  if (!statsRes.ok || !eventRes.ok) {
    showToast('Failed to load event statistics', 'error');
    return;
  }

  const s = statsRes.data;
  const ev = eventRes.data;
  const eType = ev.event_type || 'Other';
  const tColor = TYPE_COLORS[eType] || '#6b7280';
  const tIcon  = TYPE_ICONS[eType]  || '📌';

  // Hero
  document.getElementById('hero-title').textContent = ev.title;
  document.getElementById('hero-stripe').style.background = tColor;
  const heroType = document.getElementById('hero-type');
  heroType.innerHTML = `${tIcon} ${eType}`;
  heroType.style.background = tColor + '18';
  heroType.style.color = tColor;
  heroType.style.border = `1px solid ${tColor}40`;

  document.getElementById('hero-meta').innerHTML = `
    <span>🏛️ ${ev.venue?.name || '—'}</span>
    <span>📍 ${ev.venue?.location || '—'}</span>
    <span>🕐 ${fmtDate(ev.start_time)} — ${fmtDate(ev.end_time)}</span>
    <span>👤 ${ev.creator?.name || '—'}</span>
    <span>${badge(ev.status)} ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}</span>
  `;

  // Stats
  const remaining = s.registered_count - s.attended_count;
  const fillRate = ev.capacity > 0 ? Math.round((s.registered_count / ev.capacity) * 1000) / 10 : 0;

  animVal(document.getElementById('s-capacity'), ev.capacity);
  animVal(document.getElementById('s-registered'), s.registered_count);
  animVal(document.getElementById('s-attended'), s.attended_count);
  animVal(document.getElementById('s-remaining'), remaining);

  document.getElementById('s-registered-sub').textContent = `${fillRate}% of capacity`;
  document.getElementById('s-attended-sub').textContent = `${s.attendance_rate}% attendance`;

  // Rings
  setRing('fill-ring', fillRate);
  document.getElementById('fill-pct').textContent = fillRate + '%';
  document.getElementById('fill-detail').textContent = `${s.registered_count} registered out of ${ev.capacity} capacity`;

  setRing('att-ring', s.attendance_rate);
  document.getElementById('att-pct').textContent = s.attendance_rate + '%';
  document.getElementById('att-detail').textContent = `${s.attended_count} checked in out of ${s.registered_count} registrations`;

  // Charts
  new Chart(document.getElementById('regAttChart'), {
    type: 'bar',
    data: {
      labels: ['Registered', 'Checked In', 'Not Scanned'],
      datasets: [{
        data: [s.registered_count, s.attended_count, remaining],
        backgroundColor: ['#22d3ee', '#22c55e', '#f59e0b'],
        borderRadius: 8,
        maxBarThickness: 56
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: { grid: { color: 'rgba(255,255,255,.04)' }, beginAtZero: true, ticks: { stepSize: 1 } }
      },
      responsive: true, maintainAspectRatio: false
    }
  });

  const emptySlots = Math.max(0, ev.capacity - s.registered_count);
  new Chart(document.getElementById('capChart'), {
    type: 'doughnut',
    data: {
      labels: ['Checked In', 'Registered (Not Scanned)', 'Available Slots'],
      datasets: [{
        data: [s.attended_count, remaining, emptySlots],
        backgroundColor: ['#22c55e', '#f59e0b', 'rgba(255,255,255,.08)'],
        borderWidth: 0,
        hoverOffset: 8
      }]
    },
    options: {
      cutout: '68%',
      plugins: { legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true, pointStyleWidth: 10 } } },
      responsive: true, maintainAspectRatio: false
    }
  });

  // Participants
  const tbody = document.getElementById('participants-body');
  const participants = partRes.ok ? partRes.data : [];
  document.getElementById('part-count').textContent = participants.length;

  if (!participants.length) {
    tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state" style="padding:30px"><div class="empty-icon">🎟️</div><p>No registrations yet</p></div></td></tr>';
  } else {
    tbody.innerHTML = participants.map((t, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td>
          <div style="display:flex;align-items:center;gap:8px;cursor:pointer" onclick="navigateToProfile(${t.user?.id})">
            <div class="avatar" style="width:28px;height:28px;font-size:.72rem">${t.user?.name?.charAt(0) || '?'}</div>
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
    `).join('');
  }
}
</script>
</body>
</html>
