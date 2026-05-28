<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Exhibitions – EventHub</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
<link rel="icon" href="/images/logo.png" type="image/png">
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo" style="display:flex; justify-content:center; align-items:center; padding: 8px 0;"><img src="/images/logo.png" alt="EventHub Logo" style="width: 85px; height: 85px; object-fit: contain; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></div>
    <nav class="sidebar-nav" id="sidebar-links" style="display:flex; flex-direction:column; gap:4px; padding-top:10px;">
      <!-- Filled by auth.js -->
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div>
        <h1 class="page-title">My Exhibition Applications</h1>
        <p class="page-subtitle">Track your requests and participate in confirmed exhibitions</p>
      </div>
      <div class="topbar-actions">
        <a href="/company/events" class="btn btn-primary">Discover exhibitions</a>
      </div>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Status</th>
              <th>Exhibition Title</th>
              <th>Venue</th>
              <th>Date</th>
              <th>Booth Detail</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="ex-body">
            <tr class="loading-row"><td colspan="6"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Agreement Negotiation Modal -->
<div class="modal-overlay" id="agreement-modal">
  <div class="modal" style="max-width:520px; width:95%; padding:0; border-top:3px solid #22d3ee; max-height:85vh; display:flex; flex-direction:column; border-radius:16px;">
    <div style="padding:16px 20px 0; display:flex; justify-content:space-between; align-items:center;">
      <div><h3 class="modal-title" style="margin:0;font-size:1rem;">Contract Negotiation</h3><p style="font-size:0.7rem;color:var(--text-muted);margin:2px 0 0">Negotiate terms with the other party</p></div>
      <button class="modal-close" onclick="closeAgreementModal()">✕</button>
    </div>
    <div id="agreement-content" style="padding:12px 20px 20px; overflow-y:auto; flex:1;">
      <div class="spinner" style="margin:40px auto"></div>
    </div>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script src="/js/agreement-v2.js?v={{ time() }}"></script>
<script>
  const user = requireRole('Company');
  if (user) {
    populateSidebar(user);
    setActiveNav();
    loadExhibitions();
  }

  async function loadExhibitions() {
    const res = await api.get('/company/exhibitions');
    const tbody = document.getElementById('ex-body');
    
    if (!res.ok) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:var(--danger)">Failed to load data</td></tr>';
      return;
    }

    const apps = res.data;
    if (!apps.length) {
      tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state">📦<p>You haven\'t applied to any exhibitions yet.</p></div></td></tr>';
      return;
    }

    tbody.innerHTML = apps.map(app => `
      <tr>
        <td>${badge(app.status)}</td>
        <td>
            <div style="font-weight:600" class="i18n-skip">${app.event?.title || '—'}</div>
            <div style="font-size:0.75rem; color:var(--text-muted)">Applied: ${new Date(app.created_at).toLocaleDateString()}</div>
        </td>
        <td style="color:var(--text-muted)">${app.event?.venue?.name || '—'}</td>
        <td style="color:var(--text-muted)">${fmtDateShort(app.event?.start_time)}</td>
        <td>
            ${app.booth ? `<strong>Booth #${app.booth.booth_number}</strong><br/><span style="font-size:0.75rem">${app.booth.booth_size}</span>` : '<span style="opacity:0.5">N/A</span>'}
        </td>
        <td>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                ${app.status === 'pending' && app.initiator === 'event_manager' ? `
                    <button class="btn btn-success btn-sm" onclick="respondRequest(${app.id}, 'accepted')">✅ ${t('Accept')}</button>
                    <button class="btn btn-danger btn-sm" onclick="respondRequest(${app.id}, 'rejected')">❌ ${t('Reject')}</button>
                ` : ''}
                ${app.status === 'negotiating' ? `<button class="btn btn-sm btn-primary" onclick="openAgreementModal(${app.id}, 'exhibition')">${t('Review Offer')}</button>` : ''}
                ${app.status === 'accepted' ? `<button class="btn btn-sm btn-ghost" onclick="openAgreementModal(${app.id}, 'exhibition')">📄 ${t('Agreement')}</button>` : ''}
                <button class="btn btn-sm btn-ghost" onclick="showEventDetails(${app.event_id})">ℹ️ ${t('Details')}</button>
            </div>
        </td>
      </tr>
    `).join('');
  }

  async function respondRequest(id, status) {
    const res = await api.put(`/exhibition/${id}`, { status });
    if (res.ok) {
      showToast(status === 'accepted' ? t('Preliminary acceptance successful! Contract negotiation started. 📝') : t('Rejected.'), 'success');
      loadExhibitions();
    } else {
      showToast(res.data?.message || t('Error'), 'error');
    }
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
      api.get(`/company/exhibitions`)
    ]).then(([res, appRes]) => {
      if (!res.ok) {
        content.innerHTML = '<div class="empty-state"><div class="empty-icon">❌</div><p>Could not fetch event details</p></div>';
        return;
      }
      const ev = res.data;
      const apps = appRes.ok ? appRes.data : [];
      const myApp = apps.find(a => a.event_id == eventId);

      const eType = ev.event_type || 'Exhibition';
      const tColor = typeColors[eType] || typeColors.Other || '#8b5cf6';
      const tIcon = typeIcons[eType] || '🏢';

      const bannerSection = ev.image
        ? `<div class="ed-banner" style="background-image:url('/storage/${ev.image}')"><div class="ed-banner-fade"></div></div>`
        : `<div class="ed-banner ed-banner-placeholder"><span class="ed-banner-emoji">${tIcon}</span><div class="ed-banner-fade"></div></div>`;


      // Build Exhibitors section
      let exhibitorsHtml = '';
      if (ev.exhibitors && ev.exhibitors.length > 0) {
        const exItems = ev.exhibitors.map(ex => {
          const user = ex.company || {};
          const profile = user.profile || {};
          const name = profile.company_name || user.name || '—';
          const letter = name.charAt(0).toUpperCase();
          const rawLogo = profile.logo || user.image || user.avatar;
          const logo = rawLogo ? ((rawLogo.startsWith('http') || rawLogo.startsWith('/')) ? rawLogo : '/storage/' + rawLogo) : null;
          const avatarHtml = logo ? `<img src="${logo}" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.parentElement.innerHTML='<span style=\'font-size:15px;\'>${letter}</span>';">` : `<span style="font-size:15px;">${letter}</span>`;
          return `
            <div style="display:flex;align-items:center;gap:12px;background:rgba(255,255,255,0.03);padding:10px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.06);cursor:pointer;transition:all 0.2s;" 
                 onmouseover="this.style.background='rgba(255,255,255,0.06)';this.style.borderColor='rgba(255,255,255,0.12)'" 
                 onmouseout="this.style.background='rgba(255,255,255,0.03)';this.style.borderColor='rgba(255,255,255,0.06)'" 
                 onclick="navigateToProfile(${ex.company_id})">
              <div style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;background:var(--accent-gradient);border-radius:50%;overflow:hidden;font-weight:700;color:#fff;flex-shrink:0;box-shadow:0 4px 10px rgba(0,0,0,0.2);">
                ${avatarHtml}
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:0.95rem;font-weight:600;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${name}</div>
              </div>
            </div>`;
        }).join('');
        exhibitorsHtml = `
          <div style="margin-top:20px;">
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--accent2);margin-bottom:12px;display:flex;align-items:center;gap:8px;">
              <span style="font-size:1.1rem;">🏢</span> ${t('Participating Companies')} (${ev.exhibitors.length})
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:10px;">
              ${exItems}
            </div>
          </div>`;
      }


      content.innerHTML = `
      ${bannerSection}
      <div class="ed-body">
        <div class="ed-header">
          <div class="ed-title-row">
            <h2 class="ed-title i18n-skip">${ev.title}</h2>
            <span class="ed-type-pill" style="--tcolor:${tColor}">${tIcon} ${eType}</span>
          </div>
          <div class="ed-badges">
            ${timeBadge(ev.time_status)}
          </div>
        </div>



        <div class="ed-section">
          <div class="ed-section-label">About this Event</div>
          <p class="ed-description i18n-skip">${ev.description || 'No description provided.'}</p>
        </div>

        <div class="ed-info-grid">
          <div class="ed-info-card ed-info-accent2">
            <div class="ed-info-icon">🏛️</div>
            <div><div class="ed-info-label">Venue</div><div class="ed-info-value">${ev.venue?.name || ev.external_venue_name || '—'}</div></div>
          </div>
          <div class="ed-info-card ed-info-accent2">
            <div class="ed-info-icon">📍</div>
            <div><div class="ed-info-label">Location</div><div class="ed-info-value">
              ${ev.location_link || (ev.venue?.location ? `<a href="${ev.venue.location.startsWith('http') ? ev.venue.location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.venue.location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` 
              : (ev.external_venue_location ? `<a href="${ev.external_venue_location.startsWith('http') ? ev.external_venue_location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.external_venue_location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` : '—'))}
            </div></div>
          </div>
        </div>

        ${ev.booking_proof ? `
          <div class="ed-info-card" style="grid-column: 1 / -1; background:rgba(34,211,238,0.06); border:1px solid rgba(34,211,238,0.15); margin-top:10px;">
              <div class="ed-info-icon">📎</div>
              <div style="flex:1">
                  <div class="ed-info-label" style="color:#22d3ee">${t('Booking Proof')}</div>
                  <a href="/storage/${ev.booking_proof}" target="_blank" class="ed-info-value" style="text-decoration:underline;">${t('View Document ↗')}</a>
              </div>
          </div>
        ` : ''}

        ${ev.ministry_approval_doc ? `
          <div class="ed-info-card" style="grid-column: 1 / -1; background:rgba(139,92,246,0.06); border:1px solid rgba(139,92,246,0.15); margin-top:10px;">
              <div class="ed-info-icon">📄</div>
              <div style="flex:1">
                  <div class="ed-info-label" style="color:#a78bfa">${t('Competent Authority Approval')}</div>
                  <a href="/storage/${ev.ministry_approval_doc}" target="_blank" class="ed-info-value" style="text-decoration:underline;">${t('View Document ↗')}</a>
              </div>
          </div>
        ` : ''}

        ${ev.objective ? `
          <div class="ed-info-card" style="grid-column: 1 / -1; flex-direction:column; align-items:flex-start; gap:5px; margin-top:10px;">
              <div style="display:flex; align-items:center; gap:8px;">
                  <span style="font-size:1.1rem;">🎯</span>
                  <div class="ed-info-label" style="margin-bottom:0">Event Objective</div>
              </div>
              <div class="ed-info-value" style="font-weight:400; font-size:0.85rem; line-height:1.4; color:rgba(255,255,255,0.7)">${ev.objective}</div>
          </div>
        ` : ''}

        ${ev.target_audience ? `
          <div class="ed-info-card" style="grid-column: 1 / -1; flex-direction:column; align-items:flex-start; gap:5px; margin-top:10px;">
              <div style="display:flex; align-items:center; gap:8px;">
                  <span style="font-size:1.1rem;">👥</span>
                  <div class="ed-info-label" style="margin-bottom:0">Target Audience</div>
              </div>
              <div class="ed-info-value" style="font-weight:400; font-size:0.85rem; line-height:1.4; color:rgba(255,255,255,0.7)">${ev.target_audience}</div>
          </div>
        ` : ''}

        ${(() => {
          const schedule = (ev.published_schedule && ev.published_schedule.length > 0) ? ev.published_schedule :
                           (ev.external_schedule && ev.external_schedule.length > 0 ? ev.external_schedule : 
                           (ev.internal_schedule && ev.internal_schedule.length > 0 ? ev.internal_schedule : null));
            if (schedule) {
              const dn = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
              const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
              let scheduleHtml = '<div style="grid-column: 1 / -1; margin-top:15px;">';
              scheduleHtml += '<div style="font-size:0.72rem;font-weight:700;color:#a78bfa;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">📅 ' + t('Event Schedule') + ' (' + schedule.length + ' ' + (schedule.length > 1 ? t('days') : t('day')) + ')</div>';
              scheduleHtml += '<div style="display:flex;flex-direction:column;gap:6px;">';
              schedule.forEach(function(slot) {
                const d = new Date(slot.date + 'T00:00:00');
                scheduleHtml += '<div style="display:flex;align-items:center;gap:10px;background:rgba(139,92,246,0.06);border:1px solid rgba(139,92,246,0.15);border-radius:10px;padding:10px 14px;">';
                scheduleHtml += '<div style="min-width:42px;text-align:center;background:rgba(139,92,246,0.12);border-radius:8px;padding:5px 4px;">';
                scheduleHtml += '<div style="font-size:0.55rem;font-weight:700;color:#a78bfa;text-transform:uppercase;">' + dn[d.getDay()] + '</div>';
                scheduleHtml += '<div style="font-size:1.1rem;font-weight:800;color:#fff;line-height:1;">' + d.getDate() + '</div>';
                scheduleHtml += '<div style="font-size:0.5rem;color:#94a3b8;">' + mn[d.getMonth()] + '</div>';
                scheduleHtml += '</div>';
                scheduleHtml += '<div style="flex:1;display:flex;align-items:center;gap:8px;">';
                if (slot.period && !slot.start_time) {
                  scheduleHtml += '<span style="background:rgba(16,185,129,0.1);color:#10b981;padding:3px 8px;border-radius:6px;font-size:0.78rem;font-weight:600;text-transform:capitalize;">' + slot.period.replace('_', ' ') + '</span>';
                }
                if (slot.start_time) {
                  scheduleHtml += '<span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.78rem;font-weight:600;">' + slot.start_time + '</span>';
                  scheduleHtml += '<span style="color:#64748b;font-size:0.8rem;">→</span>';
                }
                if (slot.end_time) {
                  scheduleHtml += '<span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.78rem;font-weight:600;">' + slot.end_time + '</span>';
                }
                scheduleHtml += '</div></div>';
              });
              scheduleHtml += '</div></div>';
              return scheduleHtml;
            } else {
              return '<div class="ed-info-card ed-info-accent" style="margin-top:10px;"><div class="ed-info-icon">🕐</div><div><div class="ed-info-label">' + t('Start') + '</div><div class="ed-info-value">' + fmtDate(ev.start_time) + '</div></div></div>' +
                     '<div class="ed-info-card ed-info-accent" style="margin-top:10px;"><div class="ed-info-icon">🕔</div><div><div class="ed-info-label">' + t('End') + '</div><div class="ed-info-value">' + fmtDate(ev.end_time) + '</div></div></div>';
            }
          })()}

          <div class="ed-info-grid" style="margin-top:15px;">
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon">👥</div>
              <div><div class="ed-info-label">${t('Capacity')}</div><div class="ed-info-value">${ev.capacity ? ev.capacity : t('Unlimited')}</div></div>
            </div>
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon">🎫</div>
              <div><div class="ed-info-label">Tickets Booked</div><div class="ed-info-value">${ev.tickets_count || 0}</div></div>
            </div>
          </div>


          ${exhibitorsHtml}

        <div class="ed-footer mt-2" style="justify-content:space-between; margin-top:20px;">

          <div style="display:flex; align-items:center; gap:8px;">
              <span class="ed-footer-label">${t('Created by')}</span>
              <span class="ed-footer-name cursor-pointer" onclick="navigateToProfile(${ev.creator?.id})" style="color:var(--accent2);">${ev.creator?.name || '—'}</span>
          </div>
          <div class="ed-footer-label">ID: #${ev.id}</div>
        </div>
      </div>
    `;
    });
  }

  function closeEventDetailsModal() {
    document.getElementById('event-details-modal').classList.remove('open');
    document.getElementById('event-details-content').innerHTML = '';
  }

  function downloadBoothAgreement(id) {
    showToast('Downloading agreement file...', 'info');
    // Implement actual download logic if endpoint exists
  }
</script>

<!-- Event Details Modal -->
<div class="modal-overlay" id="event-details-modal">
  <div class="modal ed-modal">
    <button class="ed-close-btn" onclick="closeEventDetailsModal()">✕</button>
    <div id="event-details-content" class="ed-content"></div>
  </div>
</div>

<style>
/* ── Event Details Modal (Unified) ────────────────── */
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
  position: relative;
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

.ed-close-btn:hover { background: rgba(255, 255, 255, 0.15); }

.ed-content {
  position: relative;
  display: flex;
  flex-direction: column;
  max-height: 90vh;
}

.ed-banner {
  width: 100%;
  height: 180px;
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

.ed-banner-emoji { font-size: 4rem; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.5)); }

.ed-banner-fade {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 60px;
  background: linear-gradient(to bottom, transparent, #13131f);
}

.ed-body {
  padding: 15px 24px 24px;
  display: flex;
  flex-direction: column;
  gap: 15px;
  overflow-y: auto;
}

.ed-title-row { display: flex; align-items: flex-start; gap: 12px; flex-wrap: wrap; }
.ed-title { margin: 0; font-size: 1.45rem; font-weight: 800; color: #fff; line-height: 1.2; flex: 1; }
.ed-type-pill {
    display: inline-flex; align-items: center; gap: 5px;
    background: color-mix(in srgb, var(--tcolor) 18%, transparent);
    color: var(--tcolor); border: 1px solid color-mix(in srgb, var(--tcolor) 40%, transparent);
    padding: 4px 12px; border-radius: 20px; font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;
}
.ed-badges { display: flex; gap: 8px; flex-wrap: wrap; }
.ed-section-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.35); margin-bottom: 4px; }
.ed-description { margin: 0; color: rgba(255,255,255,0.7); font-size: 0.9rem; line-height: 1.6; }
.ed-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.ed-info-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; padding: 12px; display: flex; align-items: center; gap: 12px; }
.ed-info-icon { font-size: 1.2rem; flex-shrink: 0; }
.ed-info-label { font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px; }
.ed-info-value { font-weight: 600; font-size: 0.85rem; color: #fff; }
.ed-info-accent2 .ed-info-label { color: var(--accent2); }
.ed-info-warning .ed-info-label { color: var(--warning); }
.ed-footer { display: flex; align-items: center; gap: 8px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.06); }
.ed-footer-label { font-size: 0.75rem; color: rgba(255,255,255,0.3); }
.ed-footer-name { font-size: 0.82rem; font-weight: 600; color: #fff; }
.cursor-pointer { cursor: pointer; }
.mt-2 { margin-top: 8px; }
</style>
</body>
</html>
