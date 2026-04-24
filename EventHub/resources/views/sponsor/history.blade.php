<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Sponsorships History – EventHub</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <style>
    .history-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .history-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
    .hc-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
    .hc-title { font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: 4px; }
    .hc-meta { font-size: 0.8rem; color: var(--text-muted); display:flex; gap:12px; align-items:center; }
    
    .hc-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 15px; background: rgba(255,255,255,0.02); padding:15px; border-radius:10px; border:1px solid rgba(255,255,255,0.03); }
    .hc-stat { text-align: center; }
    .hc-stat-val { font-size: 1.4rem; font-weight: 800; color: #fff; line-height: 1.1; margin-bottom:4px; }
    .hc-stat-label { font-size: 0.65rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; }
    
    .hc-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.05); }
    .hc-manager { font-size: 0.8rem; color: var(--text-muted); }
    .hc-manager span { color: var(--accent2); font-weight: 600; cursor: pointer; }
    .hc-tier { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; padding: 4px 10px; border-radius: 20px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); }
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
      <a class="nav-item" href="/sponsor/events"><span class="nav-icon">🌍</span> Browse Events</a>
      <a class="nav-item" href="/sponsor/requests"><span class="nav-icon">💼</span> Sponsorships</a>
      <a class="nav-item active" href="/sponsor/history"><span class="nav-icon">📜</span> History</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div>
        <h1 class="page-title">Sponsorship History</h1>
        <p class="page-subtitle">Your portfolio of supported events and their performance</p>
      </div>
    </div>

    <!-- Stats summary header -->
    <div style="display:flex; gap:20px; margin-bottom:24px; flex-wrap:wrap;">
        <div class="card" style="flex:1; min-width:200px; display:flex; align-items:center; gap:16px;">
            <div style="font-size:2.5rem; background:rgba(34,211,238,0.1); width:64px; height:64px; border-radius:16px; display:grid; place-items:center;">🌍</div>
            <div>
                <div style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600; letter-spacing:0.05em; margin-bottom:4px;">Events Sponsored</div>
                <div style="font-size:1.8rem; font-weight:800; color:#fff; line-height:1;" id="summary-events">0</div>
            </div>
        </div>
        <div class="card" style="flex:1; min-width:200px; display:flex; align-items:center; gap:16px;">
            <div style="font-size:2.5rem; background:rgba(34,197,94,0.1); width:64px; height:64px; border-radius:16px; display:grid; place-items:center;">👥</div>
            <div>
                <div style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600; letter-spacing:0.05em; margin-bottom:4px;">Total Attendees</div>
                <div style="font-size:1.8rem; font-weight:800; color:#fff; line-height:1;" id="summary-attendees">0</div>
            </div>
        </div>
    </div>

    <div id="history-container" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)); gap:20px;">
        <div style="grid-column:1/-1"><div class="spinner" style="margin:60px auto"></div></div>
    </div>
  </main>
</div>
<div id="toast-container"></div>

<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script>
  const user = requireRole('Sponsor');
  if (user) { populateSidebar(user); setActiveNav(); loadHistory(); }

  async function loadHistory() {
      const container = document.getElementById('history-container');
      
      // 1. Get sponsorships
      const res = await api.get('/sponsorship');
      if (!res.ok) {
          container.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--danger)">Failed to load data</div>';
          return;
      }
      
      // Filter for accepted ones
      const acceptedReqs = res.data.filter(r => r.status === 'accepted');
      document.getElementById('summary-events').textContent = acceptedReqs.length;
      
      if (acceptedReqs.length === 0) {
          container.innerHTML = `
              <div style="grid-column:1/-1">
                  <div class="empty-state">
                      <div class="empty-icon">📜</div>
                      <p>You haven't sponsored any events yet.</p>
                      <button class="btn btn-primary" style="margin-top:15px" onclick="window.location.href='/sponsor/events'">Browse Opportunities</button>
                  </div>
              </div>
          `;
          return;
      }

      // 2. Load stats for each accepted event
      let totalAttendees = 0;
      const historyCards = await Promise.all(acceptedReqs.map(async (r) => {
          const [statsRes, reviewsRes] = await Promise.all([
              api.get(`/analytics/event/${r.event_id}`),
              api.get(`/events/${r.event_id}/reviews`)
          ]);
          let stats = { registered_count: 0, attended_count: 0, attendance_rate: 0 };
          if (statsRes.ok) {
              stats = statsRes.data;
          }
          const avgRating = (reviewsRes.ok && reviewsRes.data?.average_rating)
              ? Math.round(parseFloat(reviewsRes.data.average_rating))
              : 0;
          const reviewCount = (reviewsRes.ok && reviewsRes.data?.reviews)
              ? reviewsRes.data.reviews.length
              : 0;
          
          totalAttendees += stats.attended_count || 0;
          
          return renderHistoryCard(r, stats, avgRating, reviewCount);
      }));
      
      // Update totals
      animVal(document.getElementById('summary-attendees'), totalAttendees);
      
      // Render cards
      container.innerHTML = historyCards.join('');
  }
  
  function animVal(el, end, suffix='') {
      let s=0; const st=performance.now();
      (function step(t){const p=Math.min((t-st)/800,1);el.textContent=Math.round(s+(end-s)*(1-Math.pow(1-p,3)))+suffix;if(p<1)requestAnimationFrame(step)})(st);
  }

  function starsHtml(rating) {
      const r = Math.round(rating);
      return [1,2,3,4,5].map(i =>
          `<span style="color:${i <= r ? '#eab308' : 'rgba(255,255,255,.15)'}">★</span>`
      ).join('');
  }

  function renderHistoryCard(req, stats, avgRating = 0, reviewCount = 0) {
      const e = req.event;
      if (!e) return '';
      
      return `
        <div class="history-card">
            <div class="hc-top">
                <div>
                    <div class="hc-title">${e.title}</div>
                    <div class="hc-meta">
                        <span>🗓️ ${fmtDateShort(e.start_time)}</span>
                        <span>📍 ${e.venue?.name || 'TBA'}</span>
                    </div>
                </div>
                ${timeBadge(e.time_status)}
            </div>
            
            <div class="hc-stats-grid">
                <div class="hc-stat">
                    <div class="hc-stat-val" style="color:#22d3ee">${e.capacity || 0}</div>
                    <div class="hc-stat-label">Capacity</div>
                </div>
                <div class="hc-stat">
                    <div class="hc-stat-val" style="color:#f59e0b">${stats.registered_count}</div>
                    <div class="hc-stat-label">Registered</div>
                </div>
                <div class="hc-stat">
                    <div class="hc-stat-val" style="color:#22c55e">${stats.attended_count}</div>
                    <div class="hc-stat-label">Attended</div>
                </div>
                <div class="hc-stat">
                    <div class="hc-stat-val" style="font-size:1.4rem;line-height:1.1;margin-bottom:4px;letter-spacing:2px">${avgRating > 0 ? starsHtml(avgRating) : '<span style="color:rgba(255,255,255,.2);font-size:1.4rem">★★★★★</span>'}</div>
                    <div class="hc-stat-label">Rating${avgRating > 0 ? ` (${reviewCount})` : ''}</div>
                </div>
            </div>
            
            <div class="hc-footer">
                <div class="hc-manager">Manager: <span onclick="navigateToProfile(${e.creator?.id})">${e.creator?.name || req.manager?.name || '—'}</span></div>
                <a href="/storage/agreements/agreement_${req.id}.pdf" target="_blank" style="font-size:12px;text-decoration:none;display:flex;align-items:center;gap:4px;color:var(--text-muted);border:1px solid rgba(255,255,255,0.1);padding:4px 10px;border-radius:20px;transition:all 0.2s;background:rgba(255,255,255,0.02)">
                    📄 Agreement
                </a>
            </div>
        </div>
      `;
  }
</script>
</body>
</html>
