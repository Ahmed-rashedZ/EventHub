<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard – EventHub Admin</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<style>
/* ── Analytics Styles ──────────────────────────────────── */
.an-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:14px;margin-bottom:24px}
.an-stat{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:20px 22px;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s}
.an-stat:hover{transform:translateY(-3px);box-shadow:0 12px 36px rgba(0,0,0,.3)}
.an-stat-stripe{position:absolute;left:0;top:0;bottom:0;width:4px;border-radius:4px 0 0 4px}
.an-stat-icon{width:42px;height:42px;border-radius:10px;display:grid;place-items:center;font-size:1.2rem;margin-bottom:12px}
.an-stat-label{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:4px}
.an-stat-value{font-size:1.85rem;font-weight:800;color:#fff;line-height:1;margin-bottom:4px}
.an-stat-sub{font-size:.72rem;color:var(--text-muted)}
.an-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px}
.an-row.tri{grid-template-columns:1fr 1fr 1fr}
.an-full{grid-column:1/-1}
.an-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,.2)}
.an-card-title{font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:16px;display:flex;align-items:center;gap:8px}
.an-card-title span{font-size:1rem}
.an-chart-wrap{position:relative;height:260px}
.an-bar{background:rgba(255,255,255,.06);border-radius:6px;height:8px;overflow:hidden;flex:1}
.an-bar-fill{height:100%;border-radius:6px;transition:width .6s ease}
.an-rate-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;gap:24px}
.an-rate-circle{width:72px;height:72px;flex-shrink:0;position:relative}
.an-rate-circle svg{transform:rotate(-90deg)}
.an-rate-pct{position:absolute;inset:0;display:grid;place-items:center;font-size:1.05rem;font-weight:800;color:#fff}
.an-rate-info h4{margin:0;font-size:1rem;font-weight:700;color:#fff}
.an-rate-info p{margin:4px 0 0;font-size:.8rem;color:var(--text-muted)}
.an-top-table{width:100%;border-collapse:collapse;font-size:.85rem}
.an-top-table th{text-align:left;padding:10px 12px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);border-bottom:1px solid var(--border)}
.an-top-table td{padding:12px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle}
.an-top-table tr:last-child td{border:none}
.an-top-table tr:hover{background:rgba(255,255,255,.02)}
.an-ev-type{display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:20px;font-size:.7rem;font-weight:600;background:rgba(110,64,242,.12);color:#a78bfa}
.an-fill-row{display:flex;align-items:center;gap:10px}
.an-fill-pct{font-size:.8rem;font-weight:700;min-width:42px;text-align:right;color:#fff}
.an-pending-item{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border)}
.an-pending-item:last-child{border:none}
.an-pending-title{font-weight:600;font-size:.88rem}
.an-pending-sub{font-size:.72rem;color:var(--text-muted)}
</style>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item active" href="/admin/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Management</span>
      <a class="nav-item" href="/admin/users"><span class="nav-icon">👥</span> Users</a>
      <a class="nav-item" href="/admin/events"><span class="nav-icon">📅</span> Events</a>
      <a class="nav-item" href="/admin/venues"><span class="nav-icon">🏛️</span> Venues</a>
      <a class="nav-item" href="/admin/verifications"><span class="nav-icon">🛡️</span> Verifications</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Analytics Dashboard</h1><p class="page-subtitle">System-wide insights & performance metrics</p></div>
    </div>

    <!-- Stat Cards -->
    <div class="an-stats" id="stats-row">
      <div class="an-stat"><div class="an-stat-stripe" style="background:#6e40f2"></div><div class="an-stat-icon" style="background:rgba(110,64,242,.15)">📅</div><div class="an-stat-label">Total Events</div><div class="an-stat-value" id="s-events">—</div><div class="an-stat-sub" id="s-events-sub"></div></div>
      <div class="an-stat"><div class="an-stat-stripe" style="background:#22c55e"></div><div class="an-stat-icon" style="background:rgba(34,197,94,.15)">✅</div><div class="an-stat-label">Approved</div><div class="an-stat-value" id="s-approved">—</div><div class="an-stat-sub" id="s-approved-sub"></div></div>
      <div class="an-stat"><div class="an-stat-stripe" style="background:#f59e0b"></div><div class="an-stat-icon" style="background:rgba(245,158,11,.15)">⏳</div><div class="an-stat-label">Pending</div><div class="an-stat-value" id="s-pending">—</div><div class="an-stat-sub">Awaiting review</div></div>
      <div class="an-stat"><div class="an-stat-stripe" style="background:#22d3ee"></div><div class="an-stat-icon" style="background:rgba(34,211,238,.15)">🎟️</div><div class="an-stat-label">Total Tickets</div><div class="an-stat-value" id="s-tickets">—</div><div class="an-stat-sub" id="s-tickets-sub"></div></div>
      <div class="an-stat"><div class="an-stat-stripe" style="background:#ec4899"></div><div class="an-stat-icon" style="background:rgba(236,72,153,.15)">👥</div><div class="an-stat-label">Total Users</div><div class="an-stat-value" id="s-users">—</div><div class="an-stat-sub">All registered users</div></div>
    </div>

    <!-- Attendance Rate -->
    <div class="an-rate-card" id="rate-card">
      <div class="an-rate-circle">
        <svg width="72" height="72" viewBox="0 0 72 72"><circle cx="36" cy="36" r="30" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="6"/><circle id="rate-ring" cx="36" cy="36" r="30" fill="none" stroke="url(#rg)" stroke-width="6" stroke-linecap="round" stroke-dasharray="188.5" stroke-dashoffset="188.5"/><defs><linearGradient id="rg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#6e40f2"/><stop offset="100%" stop-color="#22d3ee"/></linearGradient></defs></svg>
        <div class="an-rate-pct" id="rate-pct">0%</div>
      </div>
      <div class="an-rate-info"><h4>Overall Attendance Rate</h4><p id="rate-detail">Loading...</p></div>
    </div>

    <!-- Charts Row: Status + Type -->
    <div class="an-row">
      <div class="an-card"><div class="an-card-title"><span>📊</span> الأحداث حسب الحالة</div><div class="an-chart-wrap"><canvas id="statusChart"></canvas></div></div>
      <div class="an-card"><div class="an-card-title"><span>🏷️</span> الأحداث حسب النوع</div><div class="an-chart-wrap"><canvas id="typeChart"></canvas></div></div>
    </div>

    <!-- Registration Trend -->
    <div class="an-card" style="margin-bottom:24px"><div class="an-card-title"><span>📈</span> Registration Trend (Last 6 Months)</div><div class="an-chart-wrap"><canvas id="trendChart"></canvas></div></div>

    <!-- Top Events -->
    <div class="an-card" style="margin-bottom:24px">
      <div class="an-card-title"><span>🏆</span> Top Events by Registrations</div>
      <table class="an-top-table"><thead><tr><th>#</th><th>Event</th><th>Type</th><th>Tickets</th><th>Fill Rate</th></tr></thead><tbody id="top-events-body"><tr><td colspan="5" style="text-align:center;padding:40px"><div class="spinner" style="margin:auto"></div></td></tr></tbody></table>
    </div>

    <!-- Bottom Row: Users by Role + Pending -->
    <div class="an-row">
      <div class="an-card"><div class="an-card-title"><span>👥</span> المستخدمون حسب الدور</div><div class="an-chart-wrap" style="height:220px"><canvas id="rolesChart"></canvas></div></div>
      <div class="an-card">
        <div class="an-card-title"><span>⚠️</span> Pending Approvals</div>
        <div id="pending-list"><div class="empty-state" style="padding:30px"><div class="empty-icon">🎉</div><p>No pending events</p></div></div>
      </div>
    </div>
  </main>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script>
const user = requireRole('Admin');
if (user) { populateSidebar(user); setActiveNav(); loadDashboard(); }

Chart.defaults.color = '#7d8590';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
Chart.defaults.font.family = 'Inter';

function animVal(el, end) {
  let s = 0; const st = performance.now();
  (function step(t) { const p = Math.min((t-st)/800,1); el.textContent = Math.round(s+(end-s)*(1-Math.pow(1-p,3))); if(p<1) requestAnimationFrame(step); })(st);
}

const COLORS = { approved:'#22c55e', pending:'#f59e0b', rejected:'#ef4444' };
const TYPE_COLORS = { 'مؤتمر':'#3b82f6', 'ندوة':'#8b5cf6', 'ورشة عمل':'#10b981', 'دورة تدريبية':'#06b6d4', 'ترفيه':'#ec4899', 'ملتقى علمي':'#f59e0b', 'رياضة':'#22c55e', 'تقنية':'#6366f1', 'اجتماعية':'#f97316' };
const ROLE_COLORS = { Admin:'#ef4444', 'Event Manager':'#8b5cf6', Sponsor:'#22d3ee', User:'#22c55e', Assistant:'#f59e0b', Attendee:'#3b82f6' };

async function loadDashboard() {
  const [sysRes, pendRes] = await Promise.all([api.get('/analytics/system'), api.get('/events/list/pending')]);
  if (!sysRes.ok) return;
  const d = sysRes.data;

  // Stats
  animVal(document.getElementById('s-events'), d.total_events);
  animVal(document.getElementById('s-approved'), d.approved_events);
  animVal(document.getElementById('s-pending'), d.pending_events);
  animVal(document.getElementById('s-tickets'), d.total_tickets);
  animVal(document.getElementById('s-users'), d.total_users);
  document.getElementById('s-events-sub').textContent = `${d.rejected_events || 0} rejected`;
  document.getElementById('s-approved-sub').textContent = d.total_events > 0 ? `${Math.round(d.approved_events/d.total_events*100)}% of all events` : '';
  document.getElementById('s-tickets-sub').textContent = `${d.used_tickets} checked in`;

  // Attendance ring
  const rate = d.attendance_rate || 0;
  const ring = document.getElementById('rate-ring');
  ring.style.transition = 'stroke-dashoffset 1.2s ease';
  ring.style.strokeDashoffset = 188.5 - (188.5 * rate / 100);
  document.getElementById('rate-pct').textContent = rate + '%';
  document.getElementById('rate-detail').textContent = `${d.used_tickets} checked in out of ${d.total_tickets} total tickets`;

  // Status donut
  const statusLabels = Object.keys(d.events_by_status);
  new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: { labels: statusLabels.map(s=>t(s.charAt(0).toUpperCase()+s.slice(1))), datasets: [{ data: Object.values(d.events_by_status), backgroundColor: statusLabels.map(s=>COLORS[s]||'#6b7280'), borderWidth: 0, hoverOffset: 8 }] },
    options: { cutout: '68%', plugins: { legend: { position:'bottom', labels:{ padding:14, usePointStyle:true, pointStyleWidth:10 } } }, responsive:true, maintainAspectRatio:false }
  });

  // Type bar
  const typeLabels = Object.keys(d.events_by_type);
  new Chart(document.getElementById('typeChart'), {
    type: 'bar',
    data: { labels: typeLabels.map(tv=>t(tv)), datasets: [{ data: Object.values(d.events_by_type), backgroundColor: typeLabels.map(t=>TYPE_COLORS[t]||'#6b7280'), borderRadius: 6, maxBarThickness: 36 }] },
    options: { indexAxis:'y', plugins:{ legend:{display:false} }, scales:{ x:{grid:{color:'rgba(255,255,255,.04)'},ticks:{stepSize:1}}, y:{grid:{display:false}} }, responsive:true, maintainAspectRatio:false }
  });

  // Trend line
  const mLabels = Object.keys(d.monthly_registrations);
  new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: { labels: mLabels.length ? mLabels : ['No data'], datasets: [{ data: mLabels.length ? Object.values(d.monthly_registrations) : [0], borderColor:'#6e40f2', backgroundColor:'rgba(110,64,242,.12)', fill:true, tension:.4, pointRadius:5, pointHoverRadius:7, pointBackgroundColor:'#6e40f2' }] },
    options: { plugins:{legend:{display:false}}, scales:{ x:{grid:{color:'rgba(255,255,255,.04)'}}, y:{grid:{color:'rgba(255,255,255,.04)'},beginAtZero:true,ticks:{stepSize:1}} }, responsive:true, maintainAspectRatio:false }
  });

  // Top events
  const tbody = document.getElementById('top-events-body');
  if (!d.top_events || !d.top_events.length) { tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state" style="padding:20px"><p>No approved events yet</p></div></td></tr>'; }
  else { tbody.innerHTML = d.top_events.map((ev,i) => {
    const c = TYPE_COLORS[ev.event_type]||'#6b7280'; const fr = ev.fill_rate||0;
    return `<tr><td style="color:var(--text-muted);font-weight:700">${i+1}</td><td><div style="font-weight:600">${ev.title}</div><div style="font-size:.72rem;color:var(--text-muted)">${fmtDateShort(ev.start_time)}</div></td><td><span class="an-ev-type" style="background:${c}18;color:${c}">${ev.event_type}</span></td><td style="font-weight:600">${ev.tickets_count}/${ev.capacity}</td><td><div class="an-fill-row"><div class="an-bar"><div class="an-bar-fill" style="width:${fr}%;background:${fr>80?'#22c55e':fr>50?'#f59e0b':'#ef4444'}"></div></div><span class="an-fill-pct">${fr}%</span></div></td></tr>`; }).join(''); }

  // Users by role
  const roleLabels = Object.keys(d.users_by_role);
  new Chart(document.getElementById('rolesChart'), {
    type: 'doughnut',
    data: { labels: roleLabels.map(rl=>t(rl)), datasets: [{ data: Object.values(d.users_by_role), backgroundColor: roleLabels.map(r=>ROLE_COLORS[r]||'#6b7280'), borderWidth: 0, hoverOffset: 8 }] },
    options: { cutout:'65%', plugins:{ legend:{ position:'bottom', labels:{padding:12,usePointStyle:true,pointStyleWidth:10} } }, responsive:true, maintainAspectRatio:false }
  });

  // Pending
  const pEl = document.getElementById('pending-list');
  if (pendRes.ok && pendRes.data.length) {
    pEl.innerHTML = pendRes.data.slice(0,5).map(ev=>`<div class="an-pending-item"><div><div class="an-pending-title">${ev.title}</div><div class="an-pending-sub">${ev.venue?.name||'—'} · ${fmtDateShort(ev.start_time)}</div></div><div style="display:flex;gap:6px"><button class="btn btn-success btn-sm" onclick="approve(${ev.id})">✓</button><button class="btn btn-danger btn-sm" onclick="reject(${ev.id})">✕</button></div></div>`).join('');
  }
}

async function approve(id) { const r = await api.put(`/events/${id}/approve`); if(r.ok){showToast('Approved!','success');loadDashboard();}else showToast(r.data?.message||'Error','error'); }
async function reject(id) { const r = await api.put(`/events/${id}/reject`); if(r.ok){showToast('Rejected.','info');loadDashboard();}else showToast(r.data?.message||'Error','error'); }
</script>
</body>
</html>
