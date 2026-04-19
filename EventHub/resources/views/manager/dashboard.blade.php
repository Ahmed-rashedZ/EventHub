<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Analytics – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<style>
.an-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:14px;margin-bottom:24px}
.an-stat{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:20px 22px;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s}
.an-stat:hover{transform:translateY(-3px);box-shadow:0 12px 36px rgba(0,0,0,.3)}
.an-stat-stripe{position:absolute;left:0;top:0;bottom:0;width:4px;border-radius:4px 0 0 4px}
.an-stat-icon{width:42px;height:42px;border-radius:10px;display:grid;place-items:center;font-size:1.2rem;margin-bottom:12px}
.an-stat-label{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:4px}
.an-stat-value{font-size:1.85rem;font-weight:800;color:#fff;line-height:1;margin-bottom:4px}
.an-stat-sub{font-size:.72rem;color:var(--text-muted)}
.an-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px}
.an-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,.2)}
.an-card-title{font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:16px;display:flex;align-items:center;gap:8px}
.an-card-title span{font-size:1rem}
.an-chart-wrap{position:relative;height:240px}
.an-rate-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;gap:24px;flex-wrap:wrap}
.an-rate-circle{width:72px;height:72px;flex-shrink:0;position:relative}
.an-rate-circle svg{transform:rotate(-90deg)}
.an-rate-pct{position:absolute;inset:0;display:grid;place-items:center;font-size:1.05rem;font-weight:800;color:#fff}
.an-rate-info h4{margin:0;font-size:1rem;font-weight:700;color:#fff}
.an-rate-info p{margin:4px 0 0;font-size:.8rem;color:var(--text-muted)}
.an-rate-divider{width:1px;height:48px;background:var(--border);margin:0 8px}

/* Event performance cards */
.an-section-title{font-size:1rem;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.an-events-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;margin-bottom:24px}
.an-ev-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:20px;transition:all .25s;position:relative;overflow:hidden}
.an-ev-card:hover{border-color:rgba(110,64,242,.35);transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,0,0,.3)}
.an-ev-header{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:14px}
.an-ev-title{font-size:1rem;font-weight:700;color:#fff;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.an-ev-type{display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:20px;font-size:.68rem;font-weight:600;flex-shrink:0}
.an-ev-metrics{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
.an-ev-metric{background:rgba(255,255,255,.03);border-radius:10px;padding:10px 12px;text-align:center}
.an-ev-metric-label{font-size:.62rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:2px}
.an-ev-metric-val{font-size:1.15rem;font-weight:800;color:#fff}
.an-ev-bar-wrap{margin-bottom:6px}
.an-ev-bar-label{display:flex;justify-content:space-between;font-size:.72rem;margin-bottom:4px}
.an-ev-bar-label span:first-child{color:var(--text-muted)}
.an-ev-bar-label span:last-child{font-weight:700;color:#fff}
.an-ev-bar{background:rgba(255,255,255,.06);border-radius:6px;height:7px;overflow:hidden}
.an-ev-bar-fill{height:100%;border-radius:6px;transition:width .6s ease}
.an-ev-footer{display:flex;justify-content:space-between;align-items:center;font-size:.72rem;color:var(--text-muted);margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.05)}
</style>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item active" href="/manager/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Events</span>
      <a class="nav-item" href="/manager/events"><span class="nav-icon">📅</span> My Events</a>
      <a class="nav-item" href="/manager/assistants"><span class="nav-icon">👥</span> Assistants</a>
      <a class="nav-item" href="/manager/attendance"><span class="nav-icon">📍</span> Attendance</a>
      <a class="nav-item" href="/manager/sponsorship"><span class="nav-icon">💼</span> Sponsorship</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">My Analytics</h1><p class="page-subtitle">Performance overview for your events</p></div>
      <div class="topbar-actions"><a href="/manager/events" class="btn btn-primary">+ Create Event</a></div>
    </div>

    <!-- Stat Cards -->
    <div class="an-stats" id="stats-row">
      <div class="an-stat"><div class="an-stat-stripe" style="background:#6e40f2"></div><div class="an-stat-icon" style="background:rgba(110,64,242,.15)">📅</div><div class="an-stat-label">My Events</div><div class="an-stat-value" id="s-events">—</div><div class="an-stat-sub" id="s-events-sub"></div></div>
      <div class="an-stat"><div class="an-stat-stripe" style="background:#22d3ee"></div><div class="an-stat-icon" style="background:rgba(34,211,238,.15)">🎟️</div><div class="an-stat-label">Tickets Sold</div><div class="an-stat-value" id="s-tickets">—</div><div class="an-stat-sub" id="s-tickets-sub"></div></div>
      <div class="an-stat"><div class="an-stat-stripe" style="background:#22c55e"></div><div class="an-stat-icon" style="background:rgba(34,197,94,.15)">📊</div><div class="an-stat-label">Attendance Rate</div><div class="an-stat-value" id="s-rate">—</div><div class="an-stat-sub" id="s-rate-sub"></div></div>
      <div class="an-stat"><div class="an-stat-stripe" style="background:#f59e0b"></div><div class="an-stat-icon" style="background:rgba(245,158,11,.15)">📈</div><div class="an-stat-label">Capacity Usage</div><div class="an-stat-value" id="s-cap">—</div><div class="an-stat-sub" id="s-cap-sub"></div></div>
      <div class="an-stat"><div class="an-stat-stripe" style="background:#eab308"></div><div class="an-stat-icon" style="background:rgba(234,179,8,.15)">⭐</div><div class="an-stat-label">Avg Rating</div><div class="an-stat-value" id="s-rating">—</div><div class="an-stat-sub" id="s-rating-sub">Based on all events</div></div>
    </div>


    <!-- Charts Row -->
    <div class="an-row">
      <div class="an-card"><div class="an-card-title"><span>📊</span> Events by Status</div><div class="an-chart-wrap"><canvas id="statusChart"></canvas></div></div>
      <div class="an-card"><div class="an-card-title"><span>🏷️</span> Events by Type</div><div class="an-chart-wrap"><canvas id="typeChart"></canvas></div></div>
    </div>

    <!-- Event Performance -->
    <div class="an-section-title"><span>🏆</span> Event Performance</div>
    <div class="an-events-grid" id="events-grid">
      <div class="an-ev-card" style="display:grid;place-items:center;min-height:140px"><div class="spinner"></div></div>
    </div>
  </main>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
const user = requireRole('Event Manager');
if (user) { populateSidebar(user); setActiveNav(); loadAnalytics(); }

Chart.defaults.color = '#7d8590';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
Chart.defaults.font.family = 'Inter';

function animVal(el, end, suffix='') {
  let s=0; const st=performance.now();
  (function step(t){const p=Math.min((t-st)/800,1);el.textContent=Math.round(s+(end-s)*(1-Math.pow(1-p,3)))+suffix;if(p<1)requestAnimationFrame(step)})(st);
}

const COLORS = { approved:'#22c55e', pending:'#f59e0b', rejected:'#ef4444' };
const TYPE_COLORS = { 'مؤتمر':'#3b82f6', 'ندوة':'#8b5cf6', 'ورشة عمل':'#10b981', 'دورة تدريبية':'#06b6d4', 'ترفيه':'#ec4899', 'ملتقى علمي':'#f59e0b', 'رياضة':'#22c55e', 'تقنية':'#6366f1', 'اجتماعية':'#f97316' };
const TYPE_ICONS  = { 'مؤتمر':'🎙️', 'ندوة':'📖', 'ورشة عمل':'🔧', 'دورة تدريبية':'🎓', 'ترفيه':'🎭', 'ملتقى علمي':'🔬', 'رياضة':'⚽', 'تقنية':'💻', 'اجتماعية':'🤝' };


async function loadAnalytics() {
  const res = await api.get('/analytics/manager');
  if (!res.ok) return;
  const d = res.data;

  // Stats
  animVal(document.getElementById('s-events'), d.total_events);
  animVal(document.getElementById('s-tickets'), d.total_tickets);
  animVal(document.getElementById('s-rate'), d.attendance_rate, '%');
  animVal(document.getElementById('s-cap'), d.capacity_utilization, '%');
  document.getElementById('s-rating').textContent = Number(d.manager_average_rating || 0).toFixed(1);
  document.getElementById('s-events-sub').textContent = `${d.approved_events} approved · ${d.pending_events} pending`;
  document.getElementById('s-tickets-sub').textContent = `${d.used_tickets} checked in`;
  document.getElementById('s-rate-sub').textContent = `${d.used_tickets} of ${d.total_tickets} attended`;
  document.getElementById('s-cap-sub').textContent = `${d.total_tickets} of ${d.total_capacity} capacity`;


  // Status donut
  const sLabels = Object.keys(d.events_by_status);
  if (sLabels.length) new Chart(document.getElementById('statusChart'), {
    type:'doughnut', data:{ labels:sLabels.map(s=>s.charAt(0).toUpperCase()+s.slice(1)), datasets:[{data:Object.values(d.events_by_status),backgroundColor:sLabels.map(s=>COLORS[s]||'#6b7280'),borderWidth:0,hoverOffset:8}]},
    options:{cutout:'68%',plugins:{legend:{position:'bottom',labels:{padding:14,usePointStyle:true,pointStyleWidth:10}}},responsive:true,maintainAspectRatio:false}
  });

  // Type bar
  const tLabels = Object.keys(d.events_by_type);
  if (tLabels.length) new Chart(document.getElementById('typeChart'), {
    type:'bar', data:{ labels:tLabels, datasets:[{data:Object.values(d.events_by_type),backgroundColor:tLabels.map(t=>TYPE_COLORS[t]||'#6b7280'),borderRadius:6,maxBarThickness:36}]},
    options:{indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{grid:{color:'rgba(255,255,255,.04)'},ticks:{stepSize:1}},y:{grid:{display:false}}},responsive:true,maintainAspectRatio:false}
  });

  // Event cards
  const grid = document.getElementById('events-grid');
  if (!d.events || !d.events.length) {
    grid.innerHTML = '<div class="an-ev-card" style="grid-column:1/-1"><div class="empty-state" style="padding:30px"><div class="empty-icon">📅</div><p>No events yet. <a href="/manager/events">Create one!</a></p></div></div>';
    return;
  }
  grid.innerHTML = d.events.map(ev => {
    const tc = TYPE_COLORS[ev.event_type]||'#6b7280';
    const ti = TYPE_ICONS[ev.event_type]||'📌';
    const fr = ev.fill_rate||0;
    const ar = ev.attendance_rate||0;
    const frColor = fr>80?'#22c55e':fr>50?'#f59e0b':'#ef4444';
    const arColor = ar>80?'#22c55e':ar>50?'#f59e0b':'#3b82f6';
    const evRating = Number(ev.average_rating || 0).toFixed(1);
    return `<div class="an-ev-card">
      <div class="an-ev-header">
        <div class="an-ev-title">${ev.title}</div>
        <span class="an-ev-type" style="background:${tc}18;color:${tc}">${ti} ${ev.event_type}</span>
      </div>
      <div style="font-size: 0.85rem; color: #eab308; margin-bottom: 12px; font-weight: 600;">⭐ ${evRating} / 5.0</div>
      <div class="an-ev-metrics">
        <div class="an-ev-metric"><div class="an-ev-metric-label">Registered</div><div class="an-ev-metric-val">${ev.tickets_count}</div></div>
        <div class="an-ev-metric"><div class="an-ev-metric-label">Attended</div><div class="an-ev-metric-val">${ev.attended_count}</div></div>
      </div>
      <div class="an-ev-bar-wrap"><div class="an-ev-bar-label"><span>Fill Rate</span><span>${ev.tickets_count}/${ev.capacity} (${fr}%)</span></div><div class="an-ev-bar"><div class="an-ev-bar-fill" style="width:${fr}%;background:${frColor}"></div></div></div>
      <div class="an-ev-bar-wrap"><div class="an-ev-bar-label"><span>Attendance</span><span>${ev.attended_count}/${ev.tickets_count} (${ar}%)</span></div><div class="an-ev-bar"><div class="an-ev-bar-fill" style="width:${ar}%;background:${arColor}"></div></div></div>
      <div class="an-ev-footer"><span>${badge(ev.status)} ${ev.status==='approved'?timeBadge(ev.time_status):''}</span><span>${fmtDateShort(ev.start_time)}</span></div>
    </div>`;
  }).join('');
}
</script>
</body>
</html>
