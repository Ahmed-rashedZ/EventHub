<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Assistant Performance – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
<style>
/* ── Assistant Stats (Matching System Design) ──────────────── */

.es-back{display:inline-flex;align-items:center;gap:6px;color:var(--text-muted);font-size:.85rem;font-weight:600;cursor:pointer;transition:color .2s;text-decoration:none;margin-bottom:20px}
.es-back:hover{color:#fff}

.as-header{display:flex;align-items:center;gap:20px;margin-bottom:32px;padding:24px;background:var(--bg-card);border:1px solid var(--border);border-radius:20px}
.as-avatar{width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg, var(--accent), #8b5cf6);display:grid;place-items:center;font-size:1.8rem;font-weight:800;color:white}

.es-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px;margin-bottom:24px}
.es-stat{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:22px 24px;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s}
.es-stat:hover{transform:translateY(-3px);box-shadow:0 12px 36px rgba(0,0,0,.3)}
.es-stat-stripe{position:absolute;left:0;top:0;bottom:0;width:4px;border-radius:4px 0 0 4px}
.es-stat-icon{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;font-size:1.25rem;margin-bottom:12px}
.es-stat-label{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:4px}
.es-stat-value{font-size:2rem;font-weight:800;color:#fff;line-height:1;margin-bottom:4px}
.es-stat-sub{font-size:.72rem;color:var(--text-muted)}

.es-row{display:grid;grid-template-columns:1.5fr 1fr;gap:20px;margin-bottom:24px}
@media(max-width:768px){.es-row{grid-template-columns:1fr}}

.es-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,.2)}
.es-card-title{font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:20px;display:flex;align-items:center;gap:8px}

.as-event-bar{background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:12px;transition:all .2s}
.as-event-bar:hover{background:rgba(255,255,255,0.05);border-color:rgba(110,64,242,0.3)}

.es-participants-table{width:100%;border-collapse:collapse;font-size:.85rem}
.es-participants-table th{text-align:left;padding:12px 14px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);border-bottom:1px solid var(--border)}
.es-participants-table td{padding:14px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle}

/* Fixing font issues for Arabic text */
[dir="rtl"] .es-stat-stripe { left: auto; right: 0; border-radius: 0 4px 4px 0; }
[dir="rtl"] .es-participants-table th { text-align: right; }
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
    <div id="page-loading" style="display:flex; align-items:center; justify-content:center; min-height:400px;"><div class="spinner"></div></div>
    <div id="page-content" style="display:none">

      <a class="es-back" href="/manager/assistants">← Back to Assistants</a>

      <div class="as-header">
        <div id="as-avatar" class="as-avatar">A</div>
        <div>
          <h1 id="as-name" style="margin:0; font-size:1.5rem; font-weight:800; color:#fff;">Assistant Name</h1>
          <p id="as-email" style="margin:4px 0 0; color:var(--text-muted); font-size:.85rem;">assistant@eventhub.com</p>
        </div>
      </div>

      <div class="es-stats">
        <div class="es-stat"><div class="es-stat-stripe" style="background:#6e40f2"></div><div class="es-stat-icon" style="background:rgba(110,64,242,.15)">📡</div><div class="es-stat-label">Total Scans</div><div class="es-stat-value" id="s-total">0</div><div class="es-stat-sub">Lifetime activity</div></div>
        <div class="es-stat"><div class="es-stat-stripe" style="background:#22d3ee"></div><div class="es-stat-icon" style="background:rgba(34,211,238,.15)">📅</div><div class="es-stat-label">Events Worked</div><div class="es-stat-value" id="s-events">0</div><div class="es-stat-sub">Across system</div></div>
        <div class="es-stat"><div class="es-stat-stripe" style="background:#22c55e"></div><div class="es-stat-icon" style="background:rgba(34,197,94,.15)">⚡</div><div class="es-stat-label">Avg. Velocity</div><div class="es-stat-value" id="s-avg">0</div><div class="es-stat-sub">Scans per event</div></div>
      </div>

      <div class="es-row">
        <!-- History Table -->
        <div class="es-card">
          <div class="es-card-title"><span>📜</span> Recent Check-in Logs</div>
          <div class="table-wrap">
            <table class="es-participants-table">
              <thead>
                <tr>
                  <th>Participant</th>
                  <th>Event</th>
                  <th>Status</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody id="history-body">
                <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted)">Loading history...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Distribution -->
        <div class="es-card">
          <div class="es-card-title"><span>📊</span> Event Breakdown</div>
          <div id="distribution-list">
             <p style="color:var(--text-muted)">Loading breakdown...</p>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script>
  const assistantId = window.location.pathname.split('/')[3];
  
  const user = requireRole('Event Manager');
  if (user) { populateSidebar(user); setActiveNav(); loadData(); }

  async function loadData() {
    const [statsRes, historyRes] = await Promise.all([
      api.get(`/assistants/${assistantId}/stats`),
      api.get(`/assistants/${assistantId}/history`)
    ]);

    document.getElementById('page-loading').style.display = 'none';
    document.getElementById('page-content').style.display = 'block';

    if (!statsRes.ok) {
        showToast('فشل تحميل بيانات المساعد', 'error');
        document.getElementById('as-name').textContent = 'غير متوفر';
        document.getElementById('as-email').textContent = '—';
    } else {
        const d = statsRes.data;
        document.getElementById('as-name').textContent = d.assistant.name;
        document.getElementById('as-email').textContent = d.assistant.email;
        document.getElementById('as-avatar').textContent = d.assistant.name.charAt(0).toUpperCase();
        
        document.getElementById('s-total').textContent = d.total_scans;
        document.getElementById('s-events').textContent = d.scans_by_event.length;
        document.getElementById('s-avg').textContent = d.scans_by_event.length > 0 ? (d.total_scans / d.scans_by_event.length).toFixed(1) : 0;

        // Distribution
        const distDiv = document.getElementById('distribution-list');
        distDiv.innerHTML = d.scans_by_event.map(s => `
          <div class="as-event-bar" id="ev-bar-${s.id}" onclick="filterByEvent(${s.id}, this)" style="cursor:pointer;" title="انقر لتصفية السجل">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:.85rem;">
              <span style="font-weight:600; color:#fff;">${s.title}</span>
              <span style="color:var(--accent); font-weight:700;">${s.total}</span>
            </div>
            <div style="width:100%; height:5px; background:rgba(255,255,255,0.05); border-radius:10px; overflow:hidden;">
              <div style="width:${(s.total / d.total_scans * 100)}%; height:100%; background:linear-gradient(90deg, var(--accent), #8b5cf6);"></div>
            </div>
          </div>
        `).join('') || '<p style="color:var(--text-muted)">لم يشارك في أي حدث حتى الآن.</p>';
    }

    if (historyRes.ok) {
      renderHistory(historyRes.data.data);
    }
    
    lucide.createIcons();
  }

  let currentFilterEventId = null;

  async function filterByEvent(eventId, elem) {
    const isSame = currentFilterEventId === eventId;
    
    // UI Updates
    document.querySelectorAll('.as-event-bar').forEach(el => {
      el.style.borderColor = 'var(--border)';
      el.style.background = 'rgba(255,255,255,0.03)';
    });

    if (isSame) {
      currentFilterEventId = null; // Toggle off
    } else {
      currentFilterEventId = eventId;
      elem.style.borderColor = 'var(--accent)';
      elem.style.background = 'rgba(110,64,242,0.1)';
    }

    // Fetch filtered data
    document.getElementById('history-body').innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px;"><div class="spinner" style="margin:auto;"></div></td></tr>';
    
    const url = currentFilterEventId 
      ? `/assistants/${assistantId}/history?event_id=${currentFilterEventId}`
      : `/assistants/${assistantId}/history`;
      
    const res = await api.get(url);
    if (res.ok) {
      renderHistory(res.data.data);
    } else {
      document.getElementById('history-body').innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px; color:var(--danger)">فشل تحميل السجل.</td></tr>';
    }
  }

  function renderHistory(history) {
    const body = document.getElementById('history-body');
    if (history.length > 0) {
      body.innerHTML = history.map(h => `
        <tr>
          <td>
            <div style="display:flex; align-items:center; gap:10px;">
              <div class="avatar" style="width:28px; height:28px; font-size:.7rem;">${(h.ticket?.user?.name || '?').charAt(0)}</div>
              <div style="font-weight:600; color:var(--accent2)">${h.ticket?.user?.name || 'Guest'}</div>
            </div>
          </td>
          <td><span style="color:var(--text-muted); font-size:.8rem;">${h.ticket?.event?.title || 'Unknown'}</span></td>
          <td><span style="color:var(--success); font-size:.75rem; font-weight:600;">Scanned</span></td>
          <td style="color:var(--text-muted); font-size:.75rem;">${fmtDate(h.scanned_at)}</td>
        </tr>
      `).join('');
    } else {
      body.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted)">لا يوجد سجل عمليات مسح.</td></tr>';
    }
  }
</script>
</body>
</html>
