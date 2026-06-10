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
    <div class="sidebar-logo" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px;"><img src="/images/logo.png?v=3" alt="EventHub Logo" style="height: 60px; width: auto; object-fit: contain; background: transparent !important;"></div>
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
              <th>Date</th>
              <th>Booth Detail</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="ex-body">
            <tr class="loading-row"><td colspan="5"><div class="spinner" style="margin:auto"></div></td></tr>
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
      <button class="modal-close" onclick="closeAgreementModal()">&times;</button>
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
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--danger)">Failed to load data</td></tr>';
      return;
    }

    const apps = res.data;
    if (!apps.length) {
      tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><div class="empty-icon" style="display:flex; justify-content:center; margin-bottom:12px; color:var(--text-muted);"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg></div><p>You haven\'t applied to any exhibitions yet.</p></div></td></tr>';
      return;
    }

    tbody.innerHTML = apps.map(app => `
      <tr>
        <td>${badge(app.status)}</td>
        <td>
            <div style="font-weight:600" class="i18n-skip">${app.event?.title || '—'}</div>
        </td>
        <td style="color:var(--text-muted)">${fmtDateShort(app.event?.start_time)}</td>
        <td>
            ${(app.booth_number || (app.booth && app.booth.booth_number)) ? `<strong>${t('Booth')} #${app.booth_number || app.booth.booth_number}</strong><br/><span style="font-size:0.75rem">${app.booth_size || (app.booth && app.booth.size) || ''}</span>` : `<span style="opacity:0.5">${t('N/A')}</span>`}
        </td>
        <td>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                ${app.status === 'pending' && app.initiator === 'event_manager' ? `
                    <button class="btn btn-success btn-sm" onclick="respondRequest(${app.id}, 'accepted')" style="display:inline-flex;align-items:center;gap:4px;"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> ${t('Accept')}</button>
                    <button class="btn btn-danger btn-sm" onclick="respondRequest(${app.id}, 'rejected')" style="display:inline-flex;align-items:center;gap:4px;"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg> ${t('Reject')}</button>
                ` : ''}
                ${app.status === 'negotiating' ? `<button class="btn btn-sm btn-primary" onclick="openAgreementModal(${app.id}, 'exhibition')">${t('Review Offer')}</button>` : ''}
                ${app.status === 'accepted' ? `<button class="btn btn-sm btn-ghost" onclick="openAgreementModal(${app.id}, 'exhibition')" style="display:inline-flex;align-items:center;gap:4px;"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg> ${t('Agreement')}</button>` : ''}
                <button class="btn btn-sm" onclick="showEventDetails(${app.event_id})" style="display:inline-flex;align-items:center;gap:4px;background:rgba(139,92,246,0.12);color:#a78bfa;border:1px solid rgba(139,92,246,0.25)"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.063.852l-.708 2.836a.75.75 0 001.063.852l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg> ${t('Details')}</button>
            </div>
        </td>
      </tr>
    `).join('');
  }

  async function respondRequest(id, status) {
    const res = await api.put(`/exhibition/${id}`, { status });
    if (res.ok) {
      showToast(status === 'accepted' ? t('Preliminary acceptance successful! Contract negotiation started.') : t('Rejected.'), 'success');
      loadExhibitions();
    } else {
      showToast(res.data?.message || t('Error'), 'error');
    }
  }

  const typeColors = {
    'مؤتمر': '#3b82f6', 'Conference': '#3b82f6',
    'ندوة': '#8b5cf6', 'Seminar': '#8b5cf6',
    'ورشة عمل': '#10b981', 'Workshop': '#10b981',
    'دورة تدريبية': '#06b6d4', 'Training Course': '#06b6d4',
    'ترفيه': '#ec4899', 'Entertainment': '#ec4899',
    'ملتقى علمي': '#f59e0b', 'Scientific Forum': '#f59e0b',
    'رياضة': '#22c55e', 'Sports': '#22c55e',
    'تقنية': '#6366f1', 'Technology': '#6366f1',
    'اجتماعية': '#f97316', 'Social': '#f97316',
    'معرض': '#f43f5e', 'Exhibition': '#f43f5e',
    'Other': '#64748b'
  };

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
        content.innerHTML = '<div class="empty-state"><div class="empty-icon" style="display:flex; justify-content:center; margin-bottom:12px; color:var(--text-muted);"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div><p>Could not fetch event details</p></div>';
        return;
      }
      const ev = res.data;
      const apps = appRes.ok ? appRes.data : [];
      const myApp = apps.find(a => a.event_id == eventId);

      const eType = ev.event_type || 'Other';
      const tColor = typeColors[eType] || typeColors.Other || '#64748b';

      const bannerSection = ev.image
        ? `<div class="ed-banner" style="background-image:url('/storage/${ev.image}')"><div class="ed-banner-fade"></div></div>`
        : `<div class="ed-banner ed-banner-placeholder"><div class="ed-banner-fade"></div></div>`;


      // Build Exhibitors section
      let exhibitorsHtml = '';
      if (ev.exhibitors && ev.exhibitors.length > 0) {
        const exItems = ev.exhibitors.filter(ex => ex).map(ex => {
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
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;color:var(--accent2);"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H15m-1.5 3H15m-1.5 3H15" /></svg> ${t('Participating Companies')} (${ev.exhibitors.length})
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
            <span class="ed-type-pill" style="--tcolor:${tColor}">${eType}</span>
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
            <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.33m-15 10.67V10.33" /></svg></div>
            <div><div class="ed-info-label">Venue</div><div class="ed-info-value">${ev.venue?.name || ev.external_venue_name || '—'}</div></div>
          </div>
          <div class="ed-info-card ed-info-accent2">
            <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1115 0z" /></svg></div>
            <div><div class="ed-info-label">Location</div><div class="ed-info-value">
              ${ev.location_link ? `<a href="${ev.location_link.startsWith('http') ? ev.location_link : 'https://' + ev.location_link}" target="_blank" style="color:inherit;text-decoration:underline;">${ev.location_link} ↗</a>`
              : (ev.venue?.location ? `<a href="${ev.venue.location.startsWith('http') ? ev.venue.location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.venue.location)}" target="_blank" style="color:inherit;text-decoration:underline;">${t('Open in Maps ↗')}</a>` 
              : (ev.external_venue_location ? `<a href="${ev.external_venue_location.startsWith('http') ? ev.external_venue_location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.external_venue_location)}" target="_blank" style="color:inherit;text-decoration:underline;">${t('Open in Maps ↗')}</a>` : '—'))}
            </div></div>
          </div>
        </div>

        ${ev.booking_proof ? `
          <div class="ed-info-card ed-info-accent2" style="grid-column: 1 / -1;">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg></div>
              <div>
                  <div class="ed-info-label">${t('Booking Proof')}</div>
                  <a href="/storage/${ev.booking_proof}" target="_blank" class="ed-info-value" style="text-decoration:underline;">${t('View Document ↗')}</a>
              </div>
          </div>
        ` : ''}

        ${ev.ministry_approval_doc ? `
          <div class="ed-info-card ed-info-accent" style="grid-column: 1 / -1;">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></div>
              <div>
                  <div class="ed-info-label">${t('Competent Authority Approval')}</div>
                  <a href="/storage/${ev.ministry_approval_doc}" target="_blank" class="ed-info-value" style="text-decoration:underline;">${t('View Document ↗')}</a>
              </div>
          </div>
        ` : ''}

        ${ev.objective ? `
          <div class="ed-info-card ed-info-accent" style="grid-column: 1 / -1;">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2" /></svg></div>
              <div>
                  <div class="ed-info-label">Event Objective</div>
                  <div class="ed-info-value" style="font-weight:400; font-size:0.85rem; line-height:1.4; color:rgba(255,255,255,0.7)">${ev.objective}</div>
              </div>
          </div>
        ` : ''}

        ${ev.target_audience ? `
          <div class="ed-info-card ed-info-accent2" style="grid-column: 1 / -1;">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg></div>
              <div>
                  <div class="ed-info-label">Target Audience</div>
                  <div class="ed-info-value" style="font-weight:400; font-size:0.85rem; line-height:1.4; color:rgba(255,255,255,0.7)">${ev.target_audience}</div>
              </div>
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
              scheduleHtml += '<div style="font-size:0.72rem;font-weight:700;color:#a78bfa;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;display:flex;align-items:center;gap:6px;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg> ' + t('Event Schedule') + ' (' + schedule.length + ' ' + (schedule.length > 1 ? t('days') : t('day')) + ')</div>';
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
              return '<div class="ed-info-card ed-info-accent" style="margin-top:10px;"><div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div><div><div class="ed-info-label">' + t('Start') + '</div><div class="ed-info-value">' + fmtDate(ev.start_time) + '</div></div></div>' +
                     '<div class="ed-info-card ed-info-accent" style="margin-top:10px;"><div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div><div><div class="ed-info-label">' + t('End') + '</div><div class="ed-info-value">' + fmtDate(ev.end_time) + '</div></div></div>';
            }
          })()}

          <div class="ed-info-grid" style="margin-top:15px;">
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg></div>
              <div><div class="ed-info-label">${t('Capacity')}</div><div class="ed-info-value">${ev.capacity ? ev.capacity : t('Unlimited')}</div></div>
            </div>
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-12v.75m0 3v.75m0 3v.75m0 3V18M3 6.75A1.75 1.75 0 014.75 5h14.5A1.75 1.75 0 0121 6.75v10.5a1.75 1.75 0 01-1.75 1.75H4.75A1.75 1.75 0 013 17.25V6.75z" /></svg></div>
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
    }).catch(err => {
      console.error('Error loading event details:', err);
      showToast(t('Error loading event details'), 'error');
      closeEventDetailsModal();
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
    <button class="ed-close-btn" onclick="closeEventDetailsModal()">&times;</button>
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
.ed-info-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.7);
  font-size: 1.1rem;
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
.ed-info-accent .ed-info-icon {
  background: rgba(110, 64, 242, 0.15);
  color: #a78bfa;
}
.ed-info-accent2 .ed-info-label {
  color: var(--accent2);
}
.ed-info-accent2 .ed-info-icon {
  background: rgba(34, 211, 238, 0.15);
  color: #22d3ee;
}
.ed-info-warning .ed-info-label {
  color: var(--warning);
}
.ed-info-warning .ed-info-icon {
  background: rgba(245, 158, 11, 0.15);
  color: #f59e0b;
}
.ed-info-danger .ed-info-label {
  color: #ef4444;
}
.ed-info-danger .ed-info-icon {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}
.ed-footer { display: flex; align-items: center; gap: 8px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.06); }
.ed-footer-label { font-size: 0.75rem; color: rgba(255,255,255,0.3); }
.ed-footer-name { font-size: 0.82rem; font-weight: 600; color: #fff; }
.cursor-pointer { cursor: pointer; }
.mt-2 { margin-top: 8px; }
</style>
</body>
</html>
