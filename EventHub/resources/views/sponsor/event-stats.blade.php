<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Event Statistics – EventHub Sponsor</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
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

/* ── Reviews Section ─────────────────────────────── */
.es-reviews-summary{display:flex;align-items:center;gap:32px;flex-wrap:wrap;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid rgba(255,255,255,.06)}
.es-avg-big{font-size:3.5rem;font-weight:900;color:#fff;line-height:1}
.es-avg-label{font-size:.8rem;color:var(--text-muted);margin-top:4px}
.es-stars-bar-wrap{flex:1;min-width:200px}
.es-star-row{display:flex;align-items:center;gap:8px;margin-bottom:6px}
.es-star-row-label{font-size:.72rem;color:var(--text-muted);width:12px;text-align:right}
.es-star-bar{flex:1;background:rgba(255,255,255,.06);border-radius:6px;height:7px;overflow:hidden}
.es-star-bar-fill{height:100%;border-radius:6px;background:linear-gradient(90deg,#eab308,#f59e0b);transition:width .8s ease}
.es-star-bar-count{font-size:.72rem;color:var(--text-muted);width:20px}
.es-review-list{display:flex;flex-direction:column;gap:16px}
.es-review-item{background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:18px 20px;transition:border-color .2s}
.es-review-item:hover{border-color:rgba(110,64,242,.3)}
.es-review-header{display:flex;align-items:center;gap:12px;margin-bottom:10px}
.es-review-name{font-weight:700;color:#fff;font-size:.9rem}
.es-review-date{font-size:.72rem;color:var(--text-muted);margin-top:2px}
.es-review-stars{display:flex;gap:3px;margin-left:auto}
.es-review-star{font-size:1rem}
.es-review-text{font-size:.85rem;color:#b0bec5;line-height:1.6}
.es-review-empty{text-align:center;padding:48px 20px;color:var(--text-muted)}
.es-review-empty-icon{font-size:2.5rem;margin-bottom:12px}
</style>
<link rel="icon" href="/images/logo.png" type="image/png">
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px;"><img src="/images/logo.png?v=3" alt="EventHub Logo" style="height: 60px; width: auto; object-fit: contain; background: transparent !important;"></div>
    <nav class="sidebar-nav" id="sidebar-links" style="display:flex; flex-direction:column; gap:4px; padding-top:10px;">
      <!-- Filled by auth.js -->
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div id="page-loading" class="es-loading"><div class="spinner"></div></div>
    <div id="page-content" style="display:none">

      <!-- Back Link -->
      <a class="es-back" href="/sponsor/history" id="back-link">← Back to History</a>


      <!-- Stat Cards -->
      <div class="es-stats" id="stats-row">
        <div class="es-stat">
          <div class="es-stat-stripe" style="background:#6e40f2"></div>
          <div class="es-stat-icon" style="background:rgba(110,64,242,.15); display:inline-flex; align-items:center; justify-content:center;">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
          <div class="es-stat-label">Capacity</div>
          <div class="es-stat-value" id="s-capacity">—</div>
          <div class="es-stat-sub">Max attendees</div>
        </div>
        <div class="es-stat">
          <div class="es-stat-stripe" style="background:#22d3ee"></div>
          <div class="es-stat-icon" style="background:rgba(34,211,238,.15); display:inline-flex; align-items:center; justify-content:center;">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-12v.75m0 3v.75m0 3v.75m0 3V18M3 6.75A1.75 1.75 0 014.75 5h14.5A1.75 1.75 0 0121 6.75v10.5a1.75 1.75 0 01-1.75 1.75H4.75A1.75 1.75 0 013 17.25V6.75z" /></svg>
            </div>
          <div class="es-stat-label">Registered</div>
          <div class="es-stat-value" id="s-registered">—</div>
          <div class="es-stat-sub" id="s-registered-sub"></div>
        </div>
        <div class="es-stat">
          <div class="es-stat-stripe" style="background:#22c55e"></div>
          <div class="es-stat-icon" style="background:rgba(34,197,94,.15); display:inline-flex; align-items:center; justify-content:center;">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;color:#22c55e;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
          <div class="es-stat-label">Checked In</div>
          <div class="es-stat-value" id="s-attended">—</div>
          <div class="es-stat-sub" id="s-attended-sub"></div>
        </div>
        <div class="es-stat">
          <div class="es-stat-stripe" style="background:#f59e0b"></div>
          <div class="es-stat-icon" style="background:rgba(245,158,11,.15); display:inline-flex; align-items:center; justify-content:center;">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;color:#f59e0b;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
          <div class="es-stat-label">Remaining</div>
          <div class="es-stat-value" id="s-remaining">—</div>
          <div class="es-stat-sub">Not checked in yet</div>
        </div>
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
          <div class="es-card-title">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;color:var(--text-muted);display:inline-block;vertical-align:middle;margin-right:6px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg> Registration vs Attendance
          </div>
          <div class="es-chart-wrap"><canvas id="regAttChart"></canvas></div>
        </div>
        <div class="es-card">
          <div class="es-card-title">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg> Capacity Breakdown
            </div>
          <div class="es-chart-wrap"><canvas id="capChart"></canvas></div>
        </div>
      </div>

      <!-- Participants Table -->
      <div class="es-card" style="margin-bottom:24px">
        <div class="es-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;color:var(--text-muted);display:inline-block;vertical-align:middle;margin-right:6px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg> Participants (<span id="part-count">0</span>)
        </div>
        <div class="table-wrap">
          <table class="es-participants-table">
            <thead><tr><th>#</th><th>Attendee</th><th>Ticket Code</th><th>Status</th><th>Scanned At</th></tr></thead>
            <tbody id="participants-body">
              <tr><td colspan="5" style="text-align:center;padding:40px"><div class="spinner" style="margin:auto"></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Reviews & Ratings -->
      <div class="es-card" style="margin-bottom:24px" id="reviews-section">
        <div class="es-card-title">
          <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;color:var(--text-muted);display:inline-block;vertical-align:middle;margin-right:6px;" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg> Reviews & Ratings (<span id="review-count">0</span>)
        </div>
        <div id="reviews-loading" style="text-align:center;padding:32px"><div class="spinner" style="margin:auto"></div></div>
        <div id="reviews-content" style="display:none">
          <!-- Summary -->
          <div class="es-reviews-summary">
            <div style="text-align:center">
              <div class="es-avg-big" id="avg-rating-big">—</div>
              <div style="display:flex;justify-content:center;gap:3px;margin:6px 0" id="avg-stars-display"></div>
              <div class="es-avg-label" id="avg-label">No ratings yet</div>
            </div>
            <div class="es-stars-bar-wrap" id="stars-breakdown"></div>
          </div>
          <!-- Review List -->
          <div class="es-review-list" id="review-list"></div>
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
const user = requireRole('Sponsor', 'Company');
if (user) { 
  populateSidebar(user); 
  setActiveNav(); 
  
  if (user.role === 'Company') {
    const backBtn = document.getElementById('back-link');
    if (backBtn) {
       backBtn.href = '/company/exhibitions';
       backBtn.innerHTML = '← Back to Applications';
    }
  }
  
  loadEventStats(); 
}

Chart.defaults.color = '#7d8590';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
Chart.defaults.font.family = 'Inter';

const TYPE_COLORS = { 'مؤتمر':'#3b82f6', 'ندوة':'#8b5cf6', 'ورشة عمل':'#10b981', 'دورة تدريبية':'#06b6d4', 'ترفيه':'#ec4899', 'ملتقى علمي':'#f59e0b', 'رياضة':'#22c55e', 'تقنية':'#6366f1', 'اجتماعية':'#f97316', 'معرض':'#f43f5e', 'Exhibition':'#f43f5e' };

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
    window.location.href = '/sponsor/history';
    return;
  }

  const [statsRes, eventRes, partRes, reviewsRes] = await Promise.all([
    api.get(`/analytics/event/${eventId}`),
    api.get(`/events/${eventId}`),
    api.get(`/checkin/event/${eventId}`),
    api.get(`/events/${eventId}/reviews`)
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


  // Stats
  const hasCapacity = ev.capacity !== null && ev.capacity > 0;
  const remaining = s.registered_count - s.attended_count;
  const fillRate = hasCapacity ? Math.round((s.registered_count / ev.capacity) * 1000) / 10 : 0;

  if (hasCapacity) {
    animVal(document.getElementById('s-capacity'), ev.capacity);
  } else {
    document.getElementById('s-capacity').textContent = document.documentElement.lang === 'ar' ? 'مفتوح' : 'Unlimited';
    document.getElementById('s-capacity').style.fontSize = '1.4rem';
  }
  
  animVal(document.getElementById('s-registered'), s.registered_count);
  animVal(document.getElementById('s-attended'), s.attended_count);
  animVal(document.getElementById('s-remaining'), remaining);

  document.getElementById('s-registered-sub').textContent = `${fillRate}% ${t('of capacity')}`;
  document.getElementById('s-attended-sub').textContent = `${s.attendance_rate}% ${t('attendance')}`;

  // Rings
  setRing('fill-ring', fillRate);
  document.getElementById('fill-pct').textContent = hasCapacity ? fillRate + '%' : '∞';
  document.getElementById('fill-detail').textContent = hasCapacity 
    ? `${s.registered_count} ${t('registered out of')} ${ev.capacity} ${t('capacity')}`
    : `${s.registered_count} ${t('registered')} (${t('Unlimited Capacity')})`;

  setRing('att-ring', s.attendance_rate);
  document.getElementById('att-pct').textContent = s.attendance_rate + '%';
  document.getElementById('att-detail').textContent = `${s.attended_count} ${t('checked in out of')} ${s.registered_count} ${t('registrations')}`;

  // Charts
  // 1. Registration vs Attendance bar
  new Chart(document.getElementById('regAttChart'), {
    type: 'bar',
    data: {
      labels: [t('Registered'), t('Checked In'), t('Not Scanned')],
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

  // 2. Capacity donut
  const emptySlots = hasCapacity ? Math.max(0, ev.capacity - s.registered_count) : 0;
  const capLabels = hasCapacity 
    ? [t('Checked In'), t('Registered (Not Scanned)'), t('Available Slots')]
    : [t('Checked In'), t('Registered (Not Scanned)')];
  const capData = hasCapacity
    ? [s.attended_count, remaining, emptySlots]
    : [s.attended_count, remaining];
  const capColors = hasCapacity
    ? ['#22c55e', '#f59e0b', 'rgba(255,255,255,.08)']
    : ['#22c55e', '#f59e0b'];

  new Chart(document.getElementById('capChart'), {
    type: 'doughnut',
    data: {
      labels: capLabels,
      datasets: [{
        data: capData,
        backgroundColor: capColors,
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
    tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state" style="padding:30px"><div class="empty-icon" style="display:flex;justify-content:center;margin-bottom:15px;color:var(--text-muted);"><svg xmlns="http://www.w3.org/2000/svg" style="width:40px;height:40px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2-2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></div><p>${t('No registrations yet')}</p></div></td></tr>`;
  } else {
    tbody.innerHTML = participants.map((ticket, i) => {
      const latestLog = (ticket.attendance_logs && ticket.attendance_logs.length) 
        ? ticket.attendance_logs.reduce((latest, current) => new Date(current.scanned_at) > new Date(latest.scanned_at) ? current : latest, ticket.attendance_logs[0])
        : ticket.attendance_log;
      const scanTime = latestLog ? fmtDate(latestLog.scanned_at) : '—';
      const scannerName = latestLog && latestLog.scanner ? `<div style="font-size:.7rem;color:var(--text-muted);margin-top:2px">by ${latestLog.scanner.name}</div>` : '';
      const daysAttendedStr = ticket.total_days_attended ? `<div style="font-size:.7rem;color:var(--text-muted);margin-top:4px;display:flex;align-items:center;gap:4px;"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink: 0;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> <bdi>${ticket.total_days_attended}</bdi> ${t('day(s)')}</div>` : '';
      
      return `
        <tr>
          <td style="color:var(--text-muted)">${i+1}</td>
          <td>
            <div style="display:flex;align-items:center;gap:8px;cursor:pointer" onclick="navigateToProfile(${ticket.user?.id})">
              <div class="avatar" style="width:28px;height:28px;font-size:.72rem">${ticket.user?.name?.charAt(0) || '?'}</div>
              <div>
                <div style="font-weight:600;color:var(--accent2)">${ticket.user?.name || '—'}</div>
                <div style="font-size:.72rem;color:var(--text-muted)">${ticket.user?.email || ''}</div>
              </div>
            </div>
          </td>
          <td style="font-family:monospace;color:var(--accent2)">${ticket.qr_code}</td>
          <td>
            ${badge(ticket.scanned_today ? 'used' : 'unused')}
            ${daysAttendedStr}
          </td>
          <td style="color:var(--text-muted)">
            ${scanTime}
            ${scannerName}
          </td>
        </tr>
      `;
    }).join('');
  }

  // Reviews & Ratings
  loadReviews(reviewsRes);
}

function starsHtml(rating, size = '1rem') {
  const rounded = Math.round(rating); // whole numbers only
  return [1,2,3,4,5].map(i =>
    `<span class="es-review-star" style="font-size:${size};color:${i <= rounded ? '#eab308' : 'rgba(255,255,255,.15)'}">&#9733;</span>`
  ).join('');
}

function loadReviews(res) {
  const loadEl = document.getElementById('reviews-loading');
  const contEl = document.getElementById('reviews-content');
  loadEl.style.display = 'none';
  contEl.style.display = 'block';

  if (!res.ok) {
    document.getElementById('review-list').innerHTML = `<div class="es-review-empty"><div class="es-review-empty-icon" style="display:flex;justify-content:center;margin-bottom:15px;color:var(--danger);"><svg xmlns="http://www.w3.org/2000/svg" style="width:40px;height:40px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg></div><p>${t('Failed to load reviews')}</p></div>`;
    return;
  }

  const data = res.data;
  const reviews = data.reviews || [];
  const avgRating = parseFloat(data.average_rating) || 0;

  document.getElementById('review-count').textContent = reviews.length;

  // Summary
  document.getElementById('avg-rating-big').textContent = reviews.length ? Math.round(avgRating) : '—';
  document.getElementById('avg-stars-display').innerHTML = reviews.length ? starsHtml(avgRating, '1.3rem') : '';
  document.getElementById('avg-label').textContent = reviews.length
    ? `${t('Based on')} ${reviews.length} ${t('rating(s)')}`
    : t('No ratings yet');

  // Star breakdown (5 → 1)
  const breakdown = {1:0, 2:0, 3:0, 4:0, 5:0};
  reviews.forEach(r => { if (r.rating >= 1 && r.rating <= 5) breakdown[r.rating]++; });
  const bHtml = [5,4,3,2,1].map(star => {
    const cnt = breakdown[star];
    const pct = reviews.length ? Math.round((cnt / reviews.length) * 100) : 0;
    return `<div class="es-star-row">
      <span class="es-star-row-label" style="color:#eab308">${star}</span>
      <span style="font-size:.85rem;color:#eab308">&#9733;</span>
      <div class="es-star-bar"><div class="es-star-bar-fill" style="width:${pct}%"></div></div>
      <span class="es-star-bar-count">${cnt}</span>
    </div>`;
  }).join('');
  document.getElementById('stars-breakdown').innerHTML = bHtml;

  // Review list
  const listEl = document.getElementById('review-list');
  if (!reviews.length) {
    listEl.innerHTML = `<div class="es-review-empty"><div class="es-review-empty-icon" style="display:flex;justify-content:center;margin-bottom:15px;color:var(--text-muted);"><svg xmlns="http://www.w3.org/2000/svg" style="width:40px;height:40px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg></div><p>${t('No reviews yet for this event')}</p></div>`;
    return;
  }

  listEl.innerHTML = reviews.map(r => {
    const avatar = r.user?.avatar || r.user?.image
      ? `<img src="${r.user.avatar || r.user.image}" style="width:36px;height:36px;border-radius:50%;object-fit:cover">`
      : `<div class="avatar" style="width:36px;height:36px;font-size:.8rem">${(r.user?.name || '?').charAt(0)}</div>`;
    const dateStr = r.updated_at ? new Date(r.updated_at).toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'}) : '';
    return `<div class="es-review-item">
      <div class="es-review-header">
        ${avatar}
        <div>
          <div class="es-review-name">${r.user?.name || t('Anonymous')}</div>
          <div class="es-review-date">${dateStr}</div>
        </div>
        <div class="es-review-stars">${starsHtml(r.rating)}</div>
      </div>
      ${r.review_text ? `<div class="es-review-text">"${r.review_text}"</div>` : `<div class="es-review-text" style="color:rgba(255,255,255,.2);font-style:italic">${t('No written review')}</div>`}
    </div>`;
  }).join('');
}
</script>
</body>
</html>






