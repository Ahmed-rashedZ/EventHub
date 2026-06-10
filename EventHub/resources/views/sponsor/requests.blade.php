<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Browse Requests – EventHub Sponsor</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
<link rel="icon" href="/images/logo.png" type="image/png">
</head>
<body>
<div class="app-layout">  <aside class="sidebar">
    <div class="sidebar-logo" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px;"><img src="/images/logo.png?v=3" alt="EventHub Logo" style="height: 60px; width: auto; object-fit: contain; background: transparent !important;"></div>
    <nav class="sidebar-nav" id="sidebar-links" style="display:flex; flex-direction:column; gap:4px; padding-top:10px;">
      <!-- Filled by auth.js -->
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Sponsorship Requests</h1><p class="page-subtitle">Browse and respond to open sponsorship opportunities</p></div>
      <div class="topbar-actions" style="display:flex; gap:10px">
        <select id="filter-type" class="form-control" style="width:160px" onchange="applyFilter()">
          <option value="">All Types</option>
          <option value="received">Received</option>
          <option value="sent">Sent</option>
        </select>
        <select id="filter-status" class="form-control" style="width:160px" onchange="applyFilter()">
          <option value="pending">Open Requests</option>
          <option value="">All Statuses</option>
          <option value="negotiating">Negotiating</option>
          <option value="accepted">Accepted</option>
          <option value="rejected">Rejected</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Event Title</th>
              <th>Manager</th>
              <th>Venue</th>
              <th>Start Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="req-body">
            <tr class="loading-row">
              <td colspan="7">
                <div class="spinner" style="margin:auto"></div>
              </td>
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
    <button class="ed-close-btn" onclick="closeEventDetailsModal()">&times;</button>
    <div id="event-details-content" class="ed-content"></div>
  </div>
</div>

<!-- Message Modal -->
<div class="modal-overlay" id="message-modal">
  <div class="modal card" style="max-width:400px; width:90%; padding:24px; border-top: 3.5px solid var(--accent2);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h3 id="message-sender" style="margin:0; font-size:1rem; color:var(--text-muted);">Request Message</h3>
        <button class="btn btn-ghost btn-sm" onclick="closeMessageModal()" style="padding:4px 8px;">&times;</button>
    </div>
    <div id="message-text" style="font-size:0.95rem; line-height:1.6; color:#fff; background:rgba(255,255,255,0.03); padding:16px; border-radius:12px; border:1px solid rgba(255,255,255,0.05); min-height:80px; white-space:pre-wrap;"></div>
    <div style="margin-top:20px; text-align:right;">
        <button class="btn btn-primary" onclick="closeMessageModal()" style="padding:6px 20px;"><script>document.write(t('Got it'))</script></button>
    </div>
  </div>
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
  let allRequests = [];
  const user = requireRole('Sponsor');
  if (user) {
    populateSidebar(user);
    setActiveNav();
    loadRequests();
  }

  async function loadRequests() {
    const res = await api.get('/sponsorship');
    const tbody = document.getElementById('req-body');
    if (!res.ok) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--danger)">Failed to load</td></tr>';
      return;
    }
    allRequests = res.data;
    applyFilter();
  }

  function applyFilter() {
    const statusF = document.getElementById('filter-status').value;
    const typeF = document.getElementById('filter-type').value;
    
    let filtered = allRequests;
    
    if (statusF) {
      filtered = filtered.filter(r => {
        let effectiveStatus = r.status;
        if (r.event?.status === 'cancelled') effectiveStatus = 'cancelled';
        else if (r.event?.status === 'cancellation_requested' && r.status === 'accepted') effectiveStatus = 'cancellation_requested';
        return effectiveStatus === statusF;
      });
    }
    
    if (typeF) {
      if (typeF === 'received') {
        filtered = filtered.filter(r => r.initiator === 'event_manager');
      } else if (typeF === 'sent') {
        filtered = filtered.filter(r => r.initiator === 'sponsor');
      }
    }
    
    renderTable(filtered);
  }

  function renderTable(requests) {
    const tbody = document.getElementById('req-body');
    if (!requests.length) {
      tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon" style="display:flex;justify-content:center;margin-bottom:15px;color:var(--text-muted);"><svg xmlns="http://www.w3.org/2000/svg" style="width:40px;height:40px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></div><p>No requests found</p></div></td></tr>';
      return;
    }
    tbody.innerHTML = requests.map((r, i) => {
      const e = r.event || {};
      
      // Override status if event is cancelled
      let effectiveStatus = r.status;
      if (e.status === 'cancelled') effectiveStatus = 'cancelled';
      else if (e.status === 'cancellation_requested' && r.status === 'accepted') effectiveStatus = 'cancellation_requested';

      const initiatorBadge = r.initiator === 'event_manager'
        ? '<span style="font-size:10px; padding:2px 6px; border-radius:10px; background:rgba(59,130,246,0.1); color:#3b82f6; border:1px solid rgba(59,130,246,0.2); margin-left:4px;">Received</span>'
        : '<span style="font-size:10px; padding:2px 6px; border-radius:10px; background:rgba(139,92,246,0.1); color:#8b5cf6; border:1px solid rgba(139,92,246,0.2); margin-left:4px;">Sent</span>';

      let actionHtml = '';
      if (r.status === 'pending') {
        if (r.initiator === 'event_manager') {
          actionHtml = `
            <button class="btn btn-success btn-sm" onclick="respond(${r.id}, 'accepted')">${t('Accept')}</button>
            <button class="btn btn-danger btn-sm" onclick="respond(${r.id}, 'rejected')">${t('Decline')}</button>
          `;
        } else {
          actionHtml = `<span style="font-size:11px; color:var(--text-muted); font-style:italic">${t('Awaiting Manager')}</span>`;
        }
      } else if (r.status === 'negotiating') {
        actionHtml = `
          <button class="btn btn-sm" onclick="openAgreementModal(${r.id})" style="padding:4px 12px; font-size:11px; background:rgba(34,211,238,0.12); color:#22d3ee; border:1px solid rgba(34,211,238,0.25); font-weight:600;">${t('Contract Negotiation')}</button>
          <button class="btn btn-sm" onclick="openCancelModal(${r.id})" style="padding:4px 12px; font-size:11px; background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2); font-weight:600;">${t('Cancel')}</button>
        `;
      } else if (r.status === 'accepted') {
        actionHtml = `
          <button class="btn btn-sm" onclick="openAgreementModal(${r.id})" style="padding:4px 12px; font-size:11px; background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.25); font-weight:600;">${t('Final Agreement')}</button>
        `;
      }

      return `
      <tr>
        <td style="color:var(--text-muted)">${i + 1}</td>
        <td>
          <div style="font-weight:600">${e.title || 'Untitled'}</div>
          ${initiatorBadge}
        </td>
        <td style="color:var(--text-muted)">
            <div class="i18n-skip" style="font-size:13px; color:var(--accent2); cursor:pointer; display:inline-block;" onclick="navigateToProfile(${e.creator?.id || r.manager?.id})">
                ${e.creator?.name || r.manager?.name || '—'}
            </div>
        </td>
        <td style="color:var(--text-muted)">${e.venue?.name || e.external_venue_name || '—'}</td>
        <td style="color:var(--text-muted);white-space:nowrap">${fmtDateShort(e.start_time)}</td>
        <td>${badge(effectiveStatus)}</td>
        <td style="display:flex;gap:6px;padding:14px 16px;flex-wrap:wrap;align-items:center">
          <button class="btn btn-sm" style="background:rgba(139,92,246,0.12);color:#a78bfa;border:1px solid rgba(139,92,246,0.25)" onclick="showEventDetails(${e.id})" title="${t('View Details')}">${t('Details')}</button>
          ${(r.negotiation?.final_notes || r.message) ? `<button class="btn btn-ghost btn-sm" onclick="viewMessage(${r.id})" title="${t('View Message')}">${t('Msg')}</button>` : ''}
          ${actionHtml}
        </td>
      </tr>`;
    }).join('');
  }

  function viewMessage(requestId) {
    const req = allRequests.find(r => r.id === requestId);
    if (!req) return;
    const modal = document.getElementById('message-modal');
    document.getElementById('message-text').innerText = req.negotiation?.final_notes || req.message || t('No message provided.');
    document.getElementById('message-sender').innerText = req.initiator === 'event_manager' ? t('From Event Manager') : t('From You');
    modal.classList.add('open');
  }

  function closeMessageModal() {
    document.getElementById('message-modal').classList.remove('open');
  }

  async function respond(id, status) {
    const res = await api.put(`/sponsorship/${id}`, { status });
    if (res.ok) {
      showToast(status === 'accepted' ? t('Sponsorship accepted!') : t('Request declined.'), status === 'accepted' ? 'success' : 'info');
      loadRequests();
    }
    else showToast(res.data?.message || t('Error'), 'error');
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
        api.get(`/events/${eventId}/reviews`)
      ]).then(([res, revRes]) => {
        if (!res.ok) {
          content.innerHTML = `<div class="empty-state"><div style="display:flex;justify-content:center;margin-bottom:15px;color:var(--danger);"><svg xmlns="http://www.w3.org/2000/svg" style="width:40px;height:40px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg></div><p>Could not fetch event details</p></div>`;
          return;
        }
        const ev = res.data;
        const reviewData = revRes.ok ? revRes.data : { average_rating: 0, reviews: [] };
        const eType = ev.event_type || 'Other';
        const tColor = typeColors[eType] || '#64748b';
        const tIcon = ''; // Placeholder as requested

        const bannerSection = ev.image
          ? `<div class="ed-banner" style="background-image:url('/storage/${ev.image}')"><div class="ed-banner-fade"></div></div>`
          : `<div class="ed-banner ed-banner-placeholder"><div style="display:flex; justify-content:center; color:rgba(255,255,255,0.15);"><svg xmlns="http://www.w3.org/2000/svg" style="width:64px;height:64px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></div><div class="ed-banner-fade"></div></div>`;

        let sponsorsHtml = '';
        if (ev.sponsors && ev.sponsors.length > 0) {
          const getTierBadge = (tier) => {
            switch (tier) {
              case 'diamond': return '<span style="background:rgba(6,182,212,0.15); color:#06b6d4; padding:3px 8px; border-radius:12px; border:1px solid rgba(6,182,212,0.3); font-size:10px; display:inline-flex; align-items:center; gap:4px;">&#128142; Diamond</span>';
              case 'gold': return '<span style="background:rgba(234,179,8,0.15); color:#eab308; padding:3px 8px; border-radius:12px; border:1px solid rgba(234,179,8,0.3); font-size:10px; display:inline-flex; align-items:center; gap:4px;">&#129351; Gold</span>';
              case 'silver': return '<span style="background:rgba(156,163,175,0.15); color:#9ca3af; padding:3px 8px; border-radius:12px; border:1px solid rgba(156,163,175,0.3); font-size:10px; display:inline-flex; align-items:center; gap:4px;">&#129352; Silver</span>';
              case 'bronze': return '<span style="background:rgba(217,119,6,0.15); color:#d97706; padding:3px 8px; border-radius:12px; border:1px solid rgba(217,119,6,0.3); font-size:10px; display:inline-flex; align-items:center; gap:4px;">&#129353; Bronze</span>';
              default: return `<span style="background:rgba(255,255,255,0.1); color:#fff; padding:3px 8px; border-radius:12px; border:1px solid rgba(255,255,255,0.2); font-size:10px; display:inline-flex; align-items:center; gap:4px;">&#9898; ${tier || 'Sponsor'}</span>`;
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

        const VenueIcon = `<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;color:var(--accent2);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>`;
        const PinIcon = `<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;color:var(--accent2);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>`;
        const ClockIcon = `<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;color:var(--accent);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
        const UsersIcon = `<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;color:var(--warning);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>`;
        const TicketIcon = `<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;color:var(--warning);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-12v.75m0 3v.75m0 3v.75m0 3V18M3 6.75A1.75 1.75 0 014.75 5h14.5A1.75 1.75 0 0121 6.75v10.5a1.75 1.75 0 01-1.75 1.75H4.75A1.75 1.75 0 013 17.25V6.75z" /></svg>`;

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

            ${ev.status === 'cancelled' ? `
              <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 12px; padding: 14px; margin-bottom: 20px; display:flex; gap:10px; align-items:flex-start;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;color:#ef4444;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                  <span style="display: block; font-size: 0.72rem; font-weight: 700; color: #ef4444; text-transform: uppercase; margin-bottom: 4px;">Event Cancelled</span>
                  <p style="margin: 0; color: #e2e8f0; font-size: 0.9rem; line-height: 1.5;">${ev.cancellation_reason || 'This event has been cancelled by the manager.'}</p>
                </div>
              </div>
            ` : ''}

            ${ev.status === 'cancellation_requested' ? `
              <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 12px; padding: 14px; margin-bottom: 20px; display:flex; gap:10px; align-items:flex-start;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;color:#f59e0b;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                  <span style="display: block; font-size: 0.72rem; font-weight: 700; color: #f59e0b; text-transform: uppercase; margin-bottom: 4px;">Cancellation Pending</span>
                  <p style="margin: 0; color: #e2e8f0; font-size: 0.9rem; line-height: 1.5;">The manager has requested to cancel this event. Awaiting administrator approval.</p>
                </div>
              </div>
            ` : ''}
            <div class="ed-section">
              <div class="ed-section-label">About this Event</div>
              <p class="ed-description">${ev.description || 'No description provided.'}</p>
            </div>
            <div class="ed-info-grid">
              <div class="ed-info-card ed-info-accent2">
                <div class="ed-info-icon">${VenueIcon}</div>
                <div><div class="ed-info-label">Venue</div><div class="ed-info-value">${ev.venue?.name || ev.external_venue_name || '—'}</div></div>
              </div>
              <div class="ed-info-card ed-info-accent2">
                <div class="ed-info-icon">${PinIcon}</div>
                <div><div class="ed-info-label">Location</div><div class="ed-info-value">
                  ${ev.venue?.location ? `<a href="${ev.venue.location.startsWith('http') ? ev.venue.location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.venue.location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` 
                  : (ev.external_venue_location ? `<a href="${ev.external_venue_location.startsWith('http') ? ev.external_venue_location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.external_venue_location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` : '—')}
                </div></div>
              </div>
              ${(() => {
                const schedule = (ev.published_schedule && ev.published_schedule.length > 0) ? ev.published_schedule :
                                 (ev.external_schedule && ev.external_schedule.length > 0 ? ev.external_schedule : 
                                 (ev.internal_schedule && ev.internal_schedule.length > 0 ? ev.internal_schedule : null));
                if (schedule) {
                  const dn = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                  const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                  let scheduleHtml = '<div style="grid-column: 1 / -1;">';
                  scheduleHtml += '<div style="font-size:0.72rem;font-weight:700;color:#a78bfa;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">Event Schedule (' + schedule.length + ' day' + (schedule.length > 1 ? 's' : '') + ')</div>';
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
                  return '<div class="ed-info-card ed-info-accent"><div class="ed-info-icon">' + ClockIcon + '</div><div><div class="ed-info-label">Start</div><div class="ed-info-value">' + fmtDate(ev.start_time) + '</div></div></div>' +
                         '<div class="ed-info-card ed-info-accent"><div class="ed-info-icon">' + ClockIcon + '</div><div><div class="ed-info-label">End</div><div class="ed-info-value">' + fmtDate(ev.end_time) + '</div></div></div>';
                }
              })()}
              <div class="ed-info-card ed-info-warning">
                <div class="ed-info-icon">${UsersIcon}</div>
                <div><div class="ed-info-label">Capacity</div><div class="ed-info-value">${ev.capacity}</div></div>
              </div>
              <div class="ed-info-card ed-info-warning">
                <div class="ed-info-icon">${TicketIcon}</div>
                <div><div class="ed-info-label">Tickets Booked</div><div class="ed-info-value">${ev.tickets_count ?? '—'}</div></div>
              </div>
            </div>
            
            ${sponsorsHtml}
            
            ${(() => { let ag=ev.agenda; if(!ag||typeof ag!=='object') return ''; const isArr=Array.isArray(ag); if(isArr&&!ag.length) return ''; if(!isArr&&!Object.keys(ag).length) return ''; const pubDates=ev.published_schedule&&ev.published_schedule.length>0?ev.published_schedule.map(p=>p.date):null; if(pubDates&&!isArr){const f={};Object.keys(ag).forEach(ds=>{if(pubDates.includes(ds))f[ds]=ag[ds];});ag=f;if(!Object.keys(ag).length)return '';} let h='<div style="margin-top:16px;"><div style="font-size:0.72rem;font-weight:700;color:#22d3ee;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;">Event Agenda</div>'; const dn=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],mn=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; const renderItem=a=>`<div style="display:flex;flex-direction:column;gap:4px;background:rgba(34,211,238,0.04);border:1px solid rgba(34,211,238,0.12);border-radius:10px;padding:8px 14px;margin:0 8px;"><div style="display:flex;align-items:center;gap:10px;"><div style="display:flex;align-items:center;gap:6px;min-width:110px;"><span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.start_time}</span><span style="color:#64748b;font-size:0.7rem;">→</span><span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.end_time}</span></div><div style="flex:1;font-size:0.85rem;color:#e2e8f0;font-weight:500;">${a.title}</div></div>${a.description ? `<div style="font-size:0.78rem;color:#94a3b8;margin-top:4px;padding-inline-start:12px;border-inline-start:2px solid rgba(34,211,238,0.2);text-align:start;line-height:1.4;">${a.description}</div>` : ''}</div>`; if(!isArr){Object.keys(ag).sort().forEach(ds=>{const items=ag[ds];if(!items||!items.length)return;const d=new Date(ds+'T00:00:00');h+=`<div style="margin-bottom:10px;"><div style="font-size:0.68rem;font-weight:600;color:#a78bfa;margin-bottom:6px;padding:4px 10px;background:rgba(139,92,246,0.08);border-radius:6px;display:inline-block;">${dn[d.getDay()]} ${d.getDate()} ${mn[d.getMonth()]} ${d.getFullYear()}</div><div style="display:flex;flex-direction:column;gap:4px;">${items.map(renderItem).join('')}</div></div>`;});}else{h+=`<div style="display:flex;flex-direction:column;gap:4px;">${ag.map(renderItem).join('')}</div>`;} return h+'</div>'; })()}
 
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






