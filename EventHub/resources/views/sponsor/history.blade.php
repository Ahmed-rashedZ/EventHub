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

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Event</th>
                        <th>Manager</th>
                        <th>Date</th>
                        <th>Registered</th>
                        <th>Attended</th>
                        <th>Rating</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="history-body">
                    <tr>
                        <td colspan="8"><div class="spinner" style="margin:60px auto"></div></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
  </main>
</div>

<!-- Event Details Modal -->
<div class="modal-overlay" id="event-details-modal">
  <div class="modal ed-modal">
    <button class="ed-close-btn" onclick="closeEventDetailsModal()">✕</button>
    <div id="event-details-content" class="ed-content"></div>
  </div>
</div>

<div id="toast-container"></div>

<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script>
  const user = requireRole('Sponsor');
  if (user) { populateSidebar(user); setActiveNav(); loadHistory(); }

  async function loadHistory() {
      const tbody = document.getElementById('history-body');
      
      // 1. Get sponsorships
      const res = await api.get('/sponsorship');
      if (!res.ok) {
          tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--danger)">Failed to load data</td></tr>';
          return;
      }
      
      // Filter for accepted ones
      const acceptedReqs = res.data.filter(r => r.status === 'accepted');
      document.getElementById('summary-events').textContent = acceptedReqs.length;
      
      if (acceptedReqs.length === 0) {
          tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📜</div><p>You haven't sponsored any events yet.</p></div></td></tr>`;
          return;
      }

      // 2. Load stats for each accepted event
      let totalAttendees = 0;
      const historyRows = await Promise.all(acceptedReqs.map(async (r, i) => {
          const [statsRes, reviewsRes] = await Promise.all([
              api.get(`/analytics/event/${r.event_id}`),
              api.get(`/events/${r.event_id}/reviews`)
          ]);
          let stats = { registered_count: 0, attended_count: 0, attendance_rate: 0 };
          if (statsRes.ok) {
              stats = statsRes.data;
          }
          const avgRating = (reviewsRes.ok && reviewsRes.data?.average_rating)
              ? Number(reviewsRes.data.average_rating).toFixed(1)
              : null;
          
          totalAttendees += stats.attended_count || 0;
          
          return renderHistoryRow(r, i, stats, avgRating);
      }));
      
      // Update totals
      animVal(document.getElementById('summary-attendees'), totalAttendees);
      
      // Render rows
      tbody.innerHTML = historyRows.join('');
  }
  
  function animVal(el, end, suffix='') {
      let s=0; const st=performance.now();
      (function step(t){const p=Math.min((t-st)/800,1);el.textContent=Math.round(s+(end-s)*(1-Math.pow(1-p,3)))+suffix;if(p<1)requestAnimationFrame(step)})(st);
  }

  function renderHistoryRow(req, index, stats, avgRating = null) {
      const e = req.event;
      if (!e) return '';
      
      return `
        <tr>
            <td style="color:var(--text-muted)">${index + 1}</td>
            <td><div style="font-weight:600">${e.title}</div></td>
            <td style="color:var(--text-muted)">
                <div style="font-size:13px; color:var(--accent2); cursor:pointer; display:inline-block;" onclick="navigateToProfile(${e.creator?.id || req.manager?.id})">
                    👤 ${e.creator?.name || req.manager?.name || '—'}
                </div>
            </td>
            <td style="color:var(--text-muted);white-space:nowrap">${fmtDateShort(e.start_time)}</td>
            <td style="color:var(--text-muted)">${stats.registered_count}</td>
            <td style="color:var(--text-muted)">${stats.attended_count}</td>
            <td>${avgRating ? `<span style="color:#eab308;font-weight:700">⭐ ${avgRating}</span>` : '<span style="color:var(--text-muted)">—</span>'}</td>
            <td style="display:flex;gap:6px;padding:14px 16px;flex-wrap:wrap;align-items:center">
                <button class="btn btn-ghost btn-sm" onclick="showEventDetails(${e.id})">ℹ️ Details</button>
                <button class="btn btn-sm" style="background:rgba(34,211,238,.12);color:#22d3ee;border:1px solid rgba(34,211,238,.25)" onclick="window.location.href='/sponsor/event-stats/${e.id}'" title="View Statistics">📊 Stats</button>
                <a href="/storage/agreements/agreement_${req.id}.pdf" target="_blank" class="btn btn-sm" style="background:rgba(255,255,255,0.08);color:#fff;border:1px solid rgba(255,255,255,0.15)" title="${t('Agreement')}">📄 ${t('Agreement')}</a>
            </td>
        </tr>`;
  }

  const typeIcons = { 'مؤتمر': '🎙️', 'ندوة': '📖', 'ورشة عمل': '🔧', 'دورة تدريبية': '🎓', 'ترفيه': '🎭', 'ملتقى علمي': '🔬', 'رياضة': '⚽', 'تقنية': '💻', 'اجتماعية': '🤝' };
  const typeColors = { 'مؤتمر': '#3b82f6', 'ندوة': '#8b5cf6', 'ورشة عمل': '#10b981', 'دورة تدريبية': '#06b6d4', 'ترفيه': '#ec4899', 'ملتقى علمي': '#f59e0b', 'رياضة': '#22c55e', 'تقنية': '#6366f1', 'اجتماعية': '#f97316' };

  function showEventDetails(eventId) {
    const modal = document.getElementById('event-details-modal');
    const content = document.getElementById('event-details-content');
    modal.classList.add('open');
    content.innerHTML = '<div class="spinner" style="margin:auto"></div>';

    Promise.all([
      api.get(`/events/${eventId}`),
      api.get(`/events/${eventId}/reviews`)
    ]).then(([res, revRes]) => {
      if (!res.ok) {
        content.innerHTML = '<div class="empty-state"><div class="empty-icon">❌</div><p>Could not fetch event details</p></div>';
        return;
      }
      const ev = res.data;
      const reviewData = revRes.ok ? revRes.data : { average_rating: 0, reviews: [] };
      const eType = ev.event_type || 'Other';
      const tColor = typeColors[eType] || typeColors.Other;
      const tIcon = typeIcons[eType] || '📌';

      const bannerSection = ev.image
        ? `<div class="ed-banner" style="background-image:url('/storage/${ev.image}')"><div class="ed-banner-fade"></div></div>`
        : `<div class="ed-banner ed-banner-placeholder"><span class="ed-banner-emoji">${tIcon}</span><div class="ed-banner-fade"></div></div>`;

      let sponsorsHtml = '';
      if (ev.sponsors && ev.sponsors.length > 0) {
        const getTierBadge = (tier) => {
          switch (tier) {
            case 'diamond': return '<span style="background:rgba(6,182,212,0.15); color:#06b6d4; padding:3px 8px; border-radius:12px; border:1px solid rgba(6,182,212,0.3); font-size:10px;">💎 Diamond</span>';
            case 'gold': return '<span style="background:rgba(234,179,8,0.15); color:#eab308; padding:3px 8px; border-radius:12px; border:1px solid rgba(234,179,8,0.3); font-size:10px;">🥇 Gold</span>';
            case 'silver': return '<span style="background:rgba(156,163,175,0.15); color:#9ca3af; padding:3px 8px; border-radius:12px; border:1px solid rgba(156,163,175,0.3); font-size:10px;">🥈 Silver</span>';
            case 'bronze': return '<span style="background:rgba(217,119,6,0.15); color:#d97706; padding:3px 8px; border-radius:12px; border:1px solid rgba(217,119,6,0.3); font-size:10px;">🥉 Bronze</span>';
            default: return `<span style="background:rgba(255,255,255,0.1); color:#fff; padding:3px 8px; border-radius:12px; border:1px solid rgba(255,255,255,0.2); font-size:10px;">${tier || 'Sponsor'}</span>`;
          }
        };

        sponsorsHtml = `
          <div class="ed-section mt-4">
            <div class="ed-section-label">Current Sponsors</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
              ${ev.sponsors.map(sp => `
                 <div style="display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.04); padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,0.05); cursor:pointer;" onclick="navigateToProfile(${sp.id})">
                  <div class="avatar" style="width:36px; height:36px; font-size:14px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:50%; overflow:hidden;">
                      ${(() => {
            const src = sp.image || sp.avatar || sp.profile?.logo;
            if (src) {
              const fullSrc = (src.startsWith('http') || src.startsWith('/')) ? src : '/storage/' + src;
              return `<img src="${fullSrc}" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'; this.parentElement.innerText='${sp.name?.charAt(0).toUpperCase() || '?'}'">`;
            }
            return sp.name ? sp.name.charAt(0).toUpperCase() : '?';
          })()}
                  </div>
                    <div style="flex:1">
                        <div style="font-size:0.85rem; font-weight:600; color:#fff;">${sp.name}</div>
                        <div style="margin-top: 2px;">${getTierBadge(sp.pivot?.tier)}</div>
                    </div>
                 </div>
              `).join('')}
            </div>
          </div>
        `;
      }

      let reviewsHtml = '';
      if (reviewData.reviews.length > 0) {
        reviewsHtml = `
          <div class="ed-section" style="margin-top: 16px;">
            <div class="ed-section-label" style="display:flex;justify-content:space-between;align-items:center;">
               <span>👥 Attendee Reviews</span>
               <span style="color:#eab308;font-weight:700;font-size:0.8rem">⭐ ${Number(reviewData.average_rating).toFixed(1)}</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:12px; max-height:250px; overflow-y:auto; padding-right:4px;">
              ${reviewData.reviews.map(r => `
                <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); border-radius:10px; padding:12px;">
                  <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                      <div class="avatar" style="width:28px; height:28px; font-size:12px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:50%; overflow:hidden;">
                        ${(() => {
          const src = r.user?.image || r.user?.avatar || r.user?.profile?.logo;
          if (src) {
            const fullSrc = (src.startsWith('http') || src.startsWith('/')) ? src : '/storage/' + src;
            return `<img src="${fullSrc}" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'; this.parentElement.innerText='${r.user?.name?.charAt(0).toUpperCase() || '?'}'">`;
          }
          return r.user?.name ? r.user.name.charAt(0).toUpperCase() : '?';
        })()}
                      </div>
                      <span style="font-size:0.8rem; font-weight:600; color:#fff">${r.user?.name || 'Anonymous'}</span>
                    </div>
                    <div style="color:#eab308; font-size:0.8rem;">${'⭐'.repeat(r.rating)}</div>
                  </div>
                  ${r.review_text ? `<p style="font-size:0.85rem; color:rgba(255,255,255,0.7); margin:0;">"${r.review_text}"</p>` : '<p style="font-size:0.85rem; color:rgba(255,255,255,0.3); margin:0; font-style:italic">No written comment</p>'}
                </div>
              `).join('')}
            </div>
          </div>
        `;
      }

      content.innerHTML = `
        ${bannerSection}
        <div class="ed-body">
          <div class="ed-header">
            <div class="ed-title-row">
              <h2 class="ed-title">${ev.title}</h2>
              <span class="ed-type-pill" style="--tcolor:${tColor}">${tIcon} ${eType}</span>
            </div>
            <div class="ed-badges">
              ${timeBadge(ev.time_status)}
            </div>
          </div>
          <div class="ed-section">
            <div class="ed-section-label">About this Event</div>
            <p class="ed-description">${ev.description || 'No description provided.'}</p>
          </div>
          <div class="ed-info-grid">
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon">🏛️</div>
              <div><div class="ed-info-label">Venue</div><div class="ed-info-value">${ev.venue?.name || ev.external_venue_name || '—'}</div></div>
            </div>
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon">📍</div>
              <div><div class="ed-info-label">Location</div><div class="ed-info-value">
                ${ev.venue?.location ? `<a href="${ev.venue.location.startsWith('http') ? ev.venue.location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.venue.location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` 
                : (ev.external_venue_location ? `<a href="${ev.external_venue_location.startsWith('http') ? ev.external_venue_location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.external_venue_location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` : '—')}
              </div></div>
            </div>
            ${(() => {
              const schedule = ev.external_schedule && ev.external_schedule.length > 0 ? ev.external_schedule : 
                               (ev.internal_schedule && ev.internal_schedule.length > 0 ? ev.internal_schedule : null);
              if (schedule) {
                return `
                  <div style="grid-column: 1 / -1;">
                    <div style="font-size:0.72rem;font-weight:700;color:#a78bfa;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">📅 Event Schedule (${schedule.length} day${schedule.length > 1 ? 's' : ''})</div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                      ${schedule.map(slot => {
                        const d = new Date(slot.date + 'T00:00:00');
                        const dn = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                        const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                        return \`<div style="display:flex;align-items:center;gap:10px;background:rgba(139,92,246,0.06);border:1px solid rgba(139,92,246,0.15);border-radius:10px;padding:10px 14px;">
                          <div style="min-width:42px;text-align:center;background:rgba(139,92,246,0.12);border-radius:8px;padding:5px 4px;">
                            <div style="font-size:0.55rem;font-weight:700;color:#a78bfa;text-transform:uppercase;">\${dn[d.getDay()]}</div>
                            <div style="font-size:1.1rem;font-weight:800;color:#fff;line-height:1;">\${d.getDate()}</div>
                            <div style="font-size:0.5rem;color:#94a3b8;">\${mn[d.getMonth()]}</div>
                          </div>
                          <div style="flex:1;display:flex;align-items:center;gap:8px;">
                            \${slot.period ? \`<span style="background:rgba(16,185,129,0.1);color:#10b981;padding:3px 8px;border-radius:6px;font-size:0.78rem;font-weight:600;text-transform:capitalize;">\${slot.period.replace('_', ' ')}</span>\` : ''}
                            <span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.78rem;font-weight:600;">\${slot.start_time}</span>
                            <span style="color:#64748b;font-size:0.8rem;">→</span>
                            <span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.78rem;font-weight:600;">\${slot.end_time}</span>
                          </div>
                        </div>\`;
                      }).join('')}
                    </div>
                  </div>
                `;
              } else {
                return `
                  <div class="ed-info-card ed-info-accent">
                    <div class="ed-info-icon">🕐</div>
                    <div><div class="ed-info-label">Start</div><div class="ed-info-value">\${fmtDate(ev.start_time)}</div></div>
                  </div>
                  <div class="ed-info-card ed-info-accent">
                    <div class="ed-info-icon">🕔</div>
                    <div><div class="ed-info-label">End</div><div class="ed-info-value">\${fmtDate(ev.end_time)}</div></div>
                  </div>
                `;
              }
            })()}
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon">👥</div>
              <div><div class="ed-info-label">Capacity</div><div class="ed-info-value">${ev.capacity}</div></div>
            </div>
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon">🎟️</div>
              <div><div class="ed-info-label">Tickets Booked</div><div class="ed-info-value">${ev.tickets_count ?? '—'}</div></div>
            </div>
          </div>
          
          ${sponsorsHtml}
          ${reviewsHtml}
          
          ${(() => { const ag=ev.agenda; if(!ag||typeof ag!=='object') return ''; const isArr=Array.isArray(ag); if(isArr&&!ag.length) return ''; if(!isArr&&!Object.keys(ag).length) return ''; let h='<div style="margin-top:16px;"><div style="font-size:0.72rem;font-weight:700;color:#22d3ee;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;">📋 Event Agenda</div>'; const dn=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],mn=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; const renderItem=a=>`<div style="display:flex;align-items:center;gap:10px;background:rgba(34,211,238,0.04);border:1px solid rgba(34,211,238,0.12);border-radius:10px;padding:8px 14px;margin-left:8px;"><div style="display:flex;align-items:center;gap:6px;min-width:110px;"><span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.start_time}</span><span style="color:#64748b;font-size:0.7rem;">→</span><span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.end_time}</span></div><div style="flex:1;font-size:0.85rem;color:#e2e8f0;font-weight:500;">${a.title}</div></div>`; if(!isArr){Object.keys(ag).sort().forEach(ds=>{const items=ag[ds];if(!items||!items.length)return;const d=new Date(ds+'T00:00:00');h+=`<div style="margin-bottom:10px;"><div style="font-size:0.68rem;font-weight:600;color:#a78bfa;margin-bottom:6px;padding:4px 10px;background:rgba(139,92,246,0.08);border-radius:6px;display:inline-block;">📅 ${dn[d.getDay()]} ${d.getDate()} ${mn[d.getMonth()]} ${d.getFullYear()}</div><div style="display:flex;flex-direction:column;gap:4px;">${items.map(renderItem).join('')}</div></div>`;});}else{h+=`<div style="display:flex;flex-direction:column;gap:4px;">${ag.map(renderItem).join('')}</div>`;} return h+'</div>'; })()}

          <div class="ed-footer" style="margin-top: 8px;">
            <span class="ed-footer-label">Event Manager</span>
            <span class="ed-footer-name cursor-pointer" onclick="navigateToProfile(${ev.creator?.id})" style="color:var(--accent2);">${ev.creator?.name || '—'}</span>
          </div>
        </div>
      `;
    });
  }

  function closeEventDetailsModal() {
    document.getElementById('event-details-modal').classList.remove('open');
    document.getElementById('event-details-content').innerHTML = '';
  }
</script>

<style>
  /* ── Event Details Modal ───────────────────────────── */
  .ed-modal {
    max-width: 560px;
    width: 95%;
    padding: 0;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-top: 3.5px solid var(--accent2);
    box-shadow: 0 32px 80px rgba(0, 0, 0, 0.6);
    background: #13131f;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .ed-close-btn {
    position: absolute;
    top: 14px;
    right: 14px;
    z-index: 20;
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
  }

  .ed-close-btn:hover {
    background: rgba(255, 255, 255, 0.15);
  }

  .ed-content {
    position: relative;
    display: flex;
    flex-direction: column;
    max-height: 90vh;
  }

  .ed-banner {
    width: 100%;
    height: 200px;
    background-size: cover;
    background-position: center;
    position: relative;
    flex-shrink: 0;
  }

  .ed-banner-placeholder {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .ed-banner-emoji {
    font-size: 4.5rem;
    filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.5));
  }

  .ed-banner-fade {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 80px;
    background: linear-gradient(to bottom, transparent, #13131f);
  }

  .ed-body {
    padding: 20px 24px 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    overflow-y: auto;
  }

  .ed-header {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .ed-title-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    flex-wrap: wrap;
  }

  .ed-title {
    margin: 0;
    font-size: 1.55rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
    flex: 1;
    min-width: 0;
  }

  .ed-type-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: color-mix(in srgb, var(--tcolor) 18%, transparent);
    color: var(--tcolor);
    border: 1px solid color-mix(in srgb, var(--tcolor) 40%, transparent);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    white-space: nowrap;
    flex-shrink: 0;
  }

  .ed-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .ed-section-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(255, 255, 255, 0.35);
    margin-bottom: 6px;
  }

  .ed-description {
    margin: 0;
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.95rem;
    line-height: 1.7;
  }

  .ed-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }

  .ed-info-card {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: background 0.2s;
  }

  .ed-info-card:hover {
    background: rgba(255, 255, 255, 0.07);
  }

  .ed-info-icon {
    font-size: 1.3rem;
    flex-shrink: 0;
  }

  .ed-info-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 2px;
  }

  .ed-info-value {
    font-weight: 600;
    font-size: 0.88rem;
    color: #fff;
  }

  .ed-info-accent .ed-info-label {
    color: var(--accent);
  }

  .ed-info-accent2 .ed-info-label {
    color: var(--accent2);
  }

  .ed-info-warning .ed-info-label {
    color: var(--warning);
  }

  .ed-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-top: 4px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
  }

  .ed-footer-label {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.35);
  }

  .ed-footer-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: #fff;
  }

  .mt-4 {
    margin-top: 16px;
  }

  .cursor-pointer {
    cursor: pointer;
  }
</style>
</body>
</html>
