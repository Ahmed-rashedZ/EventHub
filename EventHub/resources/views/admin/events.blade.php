<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Events – EventHub Admin</title>
  <link rel="stylesheet" href="/css/style.css" />
  <script src="/js/i18n.js"></script>
<link rel="icon" href="/images/logo.png" type="image/png">
</head>

<body>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-logo" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px;"><img src="/images/logo.png?v=3" alt="EventHub Logo" style="height: 60px; width: auto; object-fit: contain; background: transparent !important;"></div>
      <nav class="sidebar-nav" id="sidebar-links"></nav>
      @include('partials._sidebar-footer')
    </aside>

    <main class="main-content">
      <div class="topbar">
        <div>
          <h1 class="page-title"><script>document.write(t('Events'))</script></h1>
          <p class="page-subtitle"><script>document.write(t('Approve, reject and monitor all events'))</script></p>
        </div>
        <div class="topbar-actions" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <div style="position:relative">
            <input id="search-input" type="text" class="form-control" placeholder="Search"
              data-placeholder="Search by event or manager name..."
              style="width:240px;padding-left:36px" oninput="applyFilter()">
            <script>document.getElementById('search-input').placeholder = t(document.getElementById('search-input').getAttribute('data-placeholder'))</script>
            <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted)"
              width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <select id="filter-status" class="form-control" style="width:145px" onchange="applyFilter()">
            <option value="" selected><script>document.write(t('All Status'))</script></option>
            <option value="pending"><script>document.write(t('Pending'))</script></option>
            <option value="approved"><script>document.write(t('Approved'))</script></option>
            <option value="rejected"><script>document.write(t('Rejected'))</script></option>
            <option value="cancellation_requested"><script>document.write(t('Cancellation Requests'))</script></option>
            <option value="cancelled"><script>document.write(t('Cancelled'))</script></option>
          </select>
          <div style="position:relative;display:flex;align-items:center">
            <svg
              style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none"
              width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 6h18M7 12h10M11 18h2" />
            </svg>
            <select id="sort-events" class="form-control" style="width:190px;padding-left:32px"
              onchange="applyFilter()">
              <option value="soonest"><script>document.write(t('Soonest First'))</script></option>
              <option value="farthest"><script>document.write(t('Farthest First'))</script></option>
              <option value="alpha"><script>document.write(t('Alphabetical'))</script></option>
              <option value="live"><script>document.write(t('Live Now'))</script></option>
              <option value="ended"><script>document.write(t('Ended'))</script></option>
            </select>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th><script>document.write(t('Title'))</script></th>
                <th><script>document.write(t('Venue'))</script></th>
                <th><script>document.write(t('Manager'))</script></th>
                <th><script>document.write(t('Start Date'))</script></th>
                <th><script>document.write(t('Capacity'))</script></th>
                <th><script>document.write(t('Status'))</script></th>
                <th><script>document.write(t('Actions'))</script></th>
              </tr>
            </thead>
            <tbody id="events-body">
              <tr class="loading-row">
                <td colspan="8">
                  <div class="spinner" style="margin:auto"></div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
  <div id="toast-container"></div>
  <script src="/js/api.js"></script>
  <script src="/js/notifications.js"></script>
  <script src="/js/auth.js"></script>
  <script>
    let allEvents = [];
    const user = requireRole('Admin');
    if (user) { populateSidebar(user); setActiveNav(); loadEvents(); }

    async function loadEvents() {
      try {
        const res = await api.get('/events/list/all');
        const tbody = document.getElementById('events-body');
        if (!res.ok) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--danger)">Failed to load</td></tr>'; return; }
        allEvents = res.data;
        applyFilter();
      } catch (err) {
        console.error('Error loading events:', err);
        const tbody = document.getElementById('events-body');
        if (tbody) tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--danger)">Failed to load events</td></tr>';
      }
    }

    function applyFilter() {
      const f = document.getElementById('filter-status').value;
      const q = (document.getElementById('search-input').value || '').toLowerCase().trim();
      const s = document.getElementById('sort-events').value;
      const now = new Date();

      // 1. Filter by status
      let filtered = f ? allEvents.filter(e => e.status === f) : [...allEvents];

      // 2. Filter by search
      if (q) {
        filtered = filtered.filter(e => {
          const title = (e.title || '').toLowerCase();
          const manager = (e.creator?.name || '').toLowerCase();
          return title.includes(q) || manager.includes(q);
        });
      }

      // 3. Filter by time when sort is live/ended
      if (s === 'live') {
        filtered = filtered.filter(e => {
          const start = new Date(e.start_time);
          const end = new Date(e.end_time);
          return start <= now && end >= now;
        });
      } else if (s === 'ended') {
        filtered = filtered.filter(e => new Date(e.end_time) < now);
      }

      // 4. Sort
      if (s === 'soonest') {
        filtered.sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
      } else if (s === 'farthest') {
        filtered.sort((a, b) => new Date(b.start_time) - new Date(a.start_time));
      } else if (s === 'alpha') {
        filtered.sort((a, b) => (a.title || '').localeCompare(b.title || '', 'ar'));
      } else if (s === 'live') {
        filtered.sort((a, b) => new Date(a.end_time) - new Date(b.end_time));
      } else if (s === 'ended') {
        filtered.sort((a, b) => new Date(b.end_time) - new Date(a.end_time));
      }

      renderEvents(filtered);
    }

    function renderEvents(events) {
      const tbody = document.getElementById('events-body');
      if (!events.length) { tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state"><div class="empty-icon" style="display:flex; justify-content:center; margin-bottom:12px; color:var(--text-muted);"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path></svg></div><p>${t('No events found')}</p></div></td></tr>`; return; }
      tbody.innerHTML = events.map((ev, i) => {
        const reviewBadge = ev.review_status === 'needs_review' 
          ? `<span style="display:inline-flex;align-items:center;gap:3px;background:rgba(245,158,11,0.12);color:#f59e0b;padding:2px 8px;border-radius:8px;font-size:0.68rem;font-weight:600;border:1px solid rgba(245,158,11,0.25);">${t('Awaiting Changes')}</span>`
          : ev.review_status === 'reviewed'
          ? `<span style="display:inline-flex;align-items:center;gap:3px;background:rgba(59,130,246,0.12);color:#3b82f6;padding:2px 8px;border-radius:8px;font-size:0.68rem;font-weight:600;border:1px solid rgba(59,130,246,0.25);">${t('Updated')}</span>`
          : '';

        return `
        <tr id="event-row-${ev.id}">
          <td style="color:var(--text-muted)">${i + 1}</td>
          <td><div style="font-weight:600; ${ev.status === 'cancelled' ? 'text-decoration:line-through; color:var(--danger)' : ''}" class="i18n-skip">${ev.title}</div></td>
          <td style="color:var(--text-muted)">${ev.venue_id ? (ev.venue?.name || '—') : (ev.external_venue_name ? ev.external_venue_name + ' ' + t('External') : '—')}</td>
          <td style="color:var(--text-muted)">${ev.creator?.name || '—'}</td>
          <td style="color:var(--text-muted);white-space:nowrap">${fmtDateShort(ev.start_time)}</td>
          <td style="color:var(--text-muted)">${ev.capacity || 'Unlimited (مفتوح)'}</td>
          <td><div style="display:inline-flex;flex-wrap:wrap;gap:6px;align-items:center;">${badge(ev.status)} ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''} ${reviewBadge}</div></td>
          <td style="display:flex;gap:6px;padding:14px 16px;flex-wrap:wrap">
             <button class="btn btn-sm" style="background:rgba(139,92,246,0.12);color:#a78bfa;border:1px solid rgba(139,92,246,0.25)" onclick="showEventDetails(${ev.id})" title="${t('View Details')}">${t('Details')}</button>
            <button class="btn btn-sm" style="background:rgba(34,211,238,.12);color:#22d3ee;border:1px solid rgba(34,211,238,.25)" onclick="window.location.href='/admin/event-stats/${ev.id}'" title="${t('View Statistics')}">${t('Stats')}</button>
            ${ev.status === 'pending' ? `<button class="btn btn-sm" style="background:rgba(245,158,11,.12);color:#f59e0b;border:1px solid rgba(245,158,11,.25)" onclick="openReviewModal(${ev.id})" title="${t('Send Review')}">${t('Review')}</button><button class="btn btn-success btn-sm" onclick="approve(${ev.id})">${t('Approve')}</button><button class="btn btn-danger btn-sm" onclick="reject(${ev.id})">${t('Reject')}</button>` : ''}
            ${ev.status === 'cancellation_requested' ? `<button class="btn btn-danger btn-sm" onclick="approveCancellation(${ev.id})">${t('Approve Cancellation')}</button><button class="btn btn-ghost btn-sm" onclick="openCancellationRejectionModal(${ev.id})">${t('Reject Cancellation')}</button>` : ''}
          </td>
        </tr>`;
      }).join('');

      // Check for eventId query param to scroll and highlight target event
      const urlParams = new URLSearchParams(window.location.search);
      const targetId = urlParams.get('eventId');
      if (targetId) {
        setTimeout(() => {
          const row = document.getElementById(`event-row-${targetId}`);
          if (row) {
            document.querySelectorAll('.highlight-row').forEach(r => r.classList.remove('highlight-row'));
            row.classList.add('highlight-row');
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        }, 100);
      }
    }

    async function approve(id) {
      const res = await api.put(`/events/${id}/approve`);
      if (res.ok) { showToast('Event approved!', 'success'); loadEvents(); }
      else showToast(res.data?.message || 'Error', 'error');
    }

    function reject(id) {
      document.getElementById('reject-event-id').value = id;
      document.getElementById('rejection-modal').classList.add('open');
    }

    async function submitRejection(e) {
      e.preventDefault();
      const id = document.getElementById('reject-event-id').value;
      const reason = document.getElementById('r-reason').value;

      const res = await api.put(`/events/${id}/reject`, { rejection_reason: reason });
      if (res.ok) {
        showToast('Event rejected.', 'info');
        closeRejectionModal();
        loadEvents();
      }
      else showToast(res.data?.message || 'Error', 'error');
    }

    function closeRejectionModal() {
      document.getElementById('rejection-modal').classList.remove('open');
      document.getElementById('rejection-form').reset();
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
      api.get(`/events/${eventId}`).then(res => {
        if (!res.ok) {
          content.innerHTML = '<div class="empty-state"><div class="empty-icon" style="display:flex; justify-content:center; margin-bottom:12px; color:var(--text-muted);"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div><p>Could not fetch event details</p></div>';
          return;
        }
        const ev = res.data;
        const eType = ev.event_type || 'Other';
        const tColor = typeColors[eType] || typeColors.Other || '#64748b';

        const bannerSection = ev.image
          ? `<div class="ed-banner" style="background-image:url('/storage/${ev.image}')"><div class="ed-banner-fade"></div></div>`
          : `<div class="ed-banner ed-banner-placeholder"><div class="ed-banner-fade"></div></div>`;

        const rejectionSection = (ev.status === 'rejected' && ev.rejection_reason)
          ? `<div class="ed-rejection"><span class="ed-rej-label">${t('Rejection Reason')}</span><p>${ev.rejection_reason}</p></div>`
          : '';

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
            <div class="ed-section mt-4" style="margin-top: 16px;">
              <div class="ed-section-label">${t('Current Sponsors')}</div>
              <div style="display:flex; flex-direction:column; gap:8px;">
                ${ev.sponsors.filter(sp => sp).map(sp => `
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
                          <div style="margin-top: 2px;">
                              ${getTierBadge(sp.pivot?.tier)}
                              ${sp.sponsorship_request_id ? `<button style="background:rgba(34,211,238,0.1);color:#22d3ee;border:1px solid rgba(34,211,238,0.25);border-radius:6px;padding:2px 6px;font-size:0.65rem;margin-left:6px;cursor:pointer;" onclick="event.stopPropagation(); downloadAdminContract(${sp.sponsorship_request_id})">${t('Agreement')}</button>` : ''}
                          </div>
                      </div>
                   </div>
                 `).join('')}
              </div>
            </div>
          `;
        }

        // Build Exhibitors section
        let exhibitorsHtml = '';
        if (ev.exhibitors && ev.exhibitors.length > 0) {
          const exItems = ev.exhibitors.filter(ex => ex).map(ex => {
            const user = ex.company || {};
            const profile = user.profile || {};
            const name = profile.company_name || user.name || '—';
            const letter = name.charAt(0).toUpperCase();
            const rawLogo = profile.logo;
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
                ${t('Participating Companies')} (${ev.exhibitors.length})
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
              ${badge(ev.status)}
              ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}
            </div>
          </div>

          ${ev.status === 'cancelled' ? `
            <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 12px; padding: 14px; margin-bottom: 20px;">
              <span style="display: block; font-size: 0.72rem; font-weight: 700; color: #ef4444; text-transform: uppercase; margin-bottom: 4px;">${t('Event Cancelled')}</span>
              <p style="margin: 0; color: #e2e8f0; font-size: 0.9rem; line-height: 1.5;">${ev.cancellation_reason || t('This event has been cancelled.')}</p>
            </div>
          ` : ''}

          ${ev.status === 'cancellation_requested' ? `
            <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 12px; padding: 14px; margin-bottom: 20px;">
              <span style="display: block; font-size: 0.72rem; font-weight: 700; color: #f59e0b; text-transform: uppercase; margin-bottom: 4px;">${t('Cancellation Pending')}</span>
              <p style="margin: 0; color: #e2e8f0; font-size: 0.9rem; line-height: 1.5;">${ev.cancellation_reason || t('The manager has requested to cancel this event.')}</p>
            </div>
          ` : ''}

          ${rejectionSection}

          ${ev.status === 'cancelled' && ev.cancellation_reason ? `
            <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;">
              <span style="display: block; font-size: 0.72rem; font-weight: 700; color: #ef4444; text-transform: uppercase; margin-bottom: 4px;">${t('Cancellation Reason')}</span>
              <p style="margin: 0; color: #e2e8f0; font-size: 0.9rem; line-height: 1.5;">${ev.cancellation_reason}</p>
            </div>
          ` : ''}

          <div class="ed-section">
            <div class="ed-section-label">${t('About this Event')}</div>
            <p class="ed-description i18n-skip">${ev.description || t('No description provided.')}</p>
          </div>

          <div class="ed-info-grid">
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></div>
              <div><div class="ed-info-label">${t('Venue')}</div><div class="ed-info-value">${ev.venue?.name || ev.external_venue_name || '—'}</div></div>
            </div>
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg></div>
              <div><div class="ed-info-label">${t('Location')}</div><div class="ed-info-value">
                ${ev.venue?.location ? `<a href="${ev.venue.location.startsWith('http') ? ev.venue.location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.venue.location)}" target="_blank" style="color:inherit;text-decoration:underline;">${t('Open in Maps')} ↗</a>` 
                : (ev.external_venue_location ? `<a href="${ev.external_venue_location.startsWith('http') ? ev.external_venue_location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.external_venue_location)}" target="_blank" style="color:inherit;text-decoration:underline;">${t('Open in Maps')} ↗</a>` : '—')}
              </div></div>
            </div>
            ${!ev.venue_id && ev.booking_proof_path ? `
            <div class="ed-info-card ed-info-accent2" style="grid-column: 1 / -1;">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l4.5-4.5m.718-2.262a9 9 0 019-9" /></svg></div>
              <div><div class="ed-info-label">${t('Booking Proof')}</div><div class="ed-info-value"><button onclick="downloadEventDoc(${ev.id}, 'booking_proof')" style="text-decoration:underline;background:none;border:none;padding:0;font:inherit;cursor:pointer;color:inherit;">${t('View Document ↗')}</button></div></div>
            </div>
            ` : ''}
            ${ev.ministry_document_path ? `
            <div class="ed-info-card ed-info-accent" style="grid-column: 1 / -1;">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></div>
              <div><div class="ed-info-label">${t('Competent Authority Approval')}</div><div class="ed-info-value"><button onclick="downloadEventDoc(${ev.id}, 'ministry_document')" style="text-decoration:underline;background:none;border:none;padding:0;font:inherit;cursor:pointer;color:inherit;">${t('View Document ↗')}</button></div></div>
            </div>
            ` : `
            <div class="ed-info-card ed-info-danger" style="grid-column: 1 / -1;">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg></div>
              <div><div class="ed-info-label">${t('Competent Authority Approval')}</div><div class="ed-info-value" style="color:inherit;">${t('Not uploaded')}</div></div>
            </div>
            `}
            ${ev.event_objective ? `
            <div class="ed-info-card ed-info-accent" style="grid-column: 1 / -1;">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
              <div><div class="ed-info-label">${t('Event Objective')}</div><div class="ed-info-value i18n-skip" style="font-size:0.9rem;">${ev.event_objective}</div></div>
            </div>
            ` : ''}
            ${ev.target_audience ? `
            <div class="ed-info-card ed-info-accent2" style="grid-column: 1 / -1;">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg></div>
              <div><div class="ed-info-label">${t('Target Audience')}</div><div class="ed-info-value i18n-skip" style="font-size:0.9rem;">${ev.target_audience}</div></div>
            </div>
            ` : ''}
            ${(() => {
              const schedule = (ev.external_schedule && ev.external_schedule.length > 0) ? ev.external_schedule : 
                               (ev.internal_schedule && ev.internal_schedule.length > 0 ? ev.internal_schedule : null);
              const pubDates = (ev.published_schedule && ev.published_schedule.length > 0) ? ev.published_schedule.map(p => p.date) : [];
              if (schedule) {
                const dn = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                let scheduleHtml = '<div style="grid-column: 1 / -1;">';
                scheduleHtml += '<div style="font-size:0.72rem;font-weight:700;color:#a78bfa;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">' + t('Full Event Schedule') + ' (' + schedule.length + ' day' + (schedule.length > 1 ? 's' : '') + ')</div>';
                scheduleHtml += '<div style="display:flex;flex-direction:column;gap:6px;">';
                schedule.forEach(function(slot) {
                  const d = new Date(slot.date + 'T00:00:00');
                  const isPub = pubDates.length === 0 || pubDates.includes(slot.date);
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
                  scheduleHtml += '</div>';
                  if (ev.published_schedule && ev.published_schedule.length > 0) {
                     scheduleHtml += '<span style="font-size:0.65rem;background:' + (isPub ? 'rgba(16,185,129,0.15)' : 'rgba(245,158,11,0.15)') + ';color:' + (isPub ? '#10b981' : '#f59e0b') + ';padding:3px 8px;border-radius:6px;font-weight:700;border:1px solid ' + (isPub ? 'rgba(16,185,129,0.3)' : 'rgba(245,158,11,0.3)') + ';display:inline-flex;align-items:center;gap:4px;"><span>' + (isPub ? t('Published') : t('Setup Day')) + '</span></span>';
                  }
                  scheduleHtml += '</div>';
                });
                scheduleHtml += '</div></div>';
                return scheduleHtml;
              } else {
                return '<div class="ed-info-card ed-info-accent"><div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div><div><div class="ed-info-label">Start</div><div class="ed-info-value">' + fmtDate(ev.start_time) + '</div></div></div>' +
                       '<div class="ed-info-card ed-info-accent"><div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div><div><div class="ed-info-label">End</div><div class="ed-info-value">' + fmtDate(ev.end_time) + '</div></div></div>';
              }
            })()}
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg></div>
              <div><div class="ed-info-label">${t('Capacity')}</div><div class="ed-info-value">${ev.capacity || 'Unlimited (مفتوح)'}</div></div>
            </div>
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-12v.75m0 3v.75m0 3v.75m0 3V18M3 6.75A1.75 1.75 0 014.75 5h14.5A1.75 1.75 0 0121 6.75v10.5a1.75 1.75 0 01-1.75 1.75H4.75A1.75 1.75 0 013 17.25V6.75z" /></svg></div>
              <div><div class="ed-info-label">${t('Tickets Booked')}</div><div class="ed-info-value">${ev.tickets_count ?? '—'}</div></div>
            </div>
          </div>
          
          ${sponsorsHtml}
          
          ${exhibitorsHtml}

          ${(() => { let ag=ev.agenda; if(!ag||typeof ag!=='object') return ''; const isArr=Array.isArray(ag); if(isArr&&!ag.length) return ''; if(!isArr&&!Object.keys(ag).length) return ''; const pubDates=(ev.published_schedule&&ev.published_schedule.length>0)?ev.published_schedule.map(p=>p.date):[]; let h='<div style="margin-top:16px;"><div style="font-size:0.72rem;font-weight:700;color:#22d3ee;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;">' + t('Full Event Agenda') + '</div>'; const dn=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],mn=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; const renderItem=a=>`<div style="display:flex;flex-direction:column;gap:4px;background:rgba(34,211,238,0.04);border:1px solid rgba(34,211,238,0.12);border-radius:10px;padding:8px 14px;margin:0 8px;"><div style="display:flex;align-items:center;gap:10px;"><div style="display:flex;align-items:center;gap:6px;min-width:110px;"><span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.start_time}</span><span style="color:#64748b;font-size:0.7rem;">→</span><span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.end_time}</span></div><div style="flex:1;font-size:0.85rem;color:#e2e8f0;font-weight:500;">${a.title}</div></div>${a.description ? `<div style="font-size:0.78rem;color:#94a3b8;margin-top:4px;padding-inline-start:12px;border-inline-start:2px solid rgba(34,211,238,0.2);text-align:start;line-height:1.4;">${a.description}</div>` : ''}</div>`; if(!isArr){Object.keys(ag).sort().forEach(ds=>{const items=ag[ds];if(!items||!items.length)return;const d=new Date(ds+'T00:00:00');const isPub=pubDates.length===0||pubDates.includes(ds); const badge=(pubDates.length>0)?(isPub?'<span style="font-size:0.6rem;background:rgba(16,185,129,0.15);color:#10b981;padding:2px 6px;border-radius:4px;margin:0 6px;border:1px solid rgba(16,185,129,0.3);display:inline-flex;align-items:center;gap:4px;"><span>' + t('Published') + '</span></span>':'<span style="font-size:0.6rem;background:rgba(245,158,11,0.15);color:#f59e0b;padding:2px 6px;border-radius:4px;margin:0 6px;border:1px solid rgba(245,158,11,0.3);display:inline-flex;align-items:center;gap:4px;"><span>' + t('Setup Day') + '</span></span>'):''; h+=`<div style="margin-bottom:10px;"><div style="font-size:0.68rem;font-weight:600;color:#a78bfa;margin-bottom:6px;padding:4px 10px;background:rgba(139,92,246,0.08);border-radius:6px;display:inline-flex;align-items:center;">${dn[d.getDay()]} ${d.getDate()} ${mn[d.getMonth()]} ${d.getFullYear()}${badge}</div><div style="display:flex;flex-direction:column;gap:4px;">${items.map(renderItem).join('')}</div></div>`;});}else{h+=`<div style="display:flex;flex-direction:column;gap:4px;">${ag.map(renderItem).join('')}</div>`;} return h+'</div>'; })()}

          <div class="ed-footer" style="margin-top: 8px;">
            <span class="ed-footer-label">${t('Created by')}</span>
            <span class="ed-footer-name">${ev.creator?.name || ev.manager?.name || '—'}</span>
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


  </script>

  <!-- Event Details Modal -->
  <div class="modal-overlay" id="event-details-modal">
    <div class="modal ed-modal">
      <button class="ed-close-btn" onclick="closeEventDetailsModal()">&times;</button>
      <div id="event-details-content" class="ed-content"></div>
    </div>
  </div>

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

    .ed-rejection {
      background: rgba(239, 68, 68, 0.09);
      border-left: 3px solid #ef4444;
      border-radius: 8px;
      padding: 12px 14px;
    }

    .ed-rej-label {
      display: block;
      font-size: 0.72rem;
      font-weight: 700;
      color: #ef4444;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 4px;
    }

    .ed-rejection p {
      margin: 0;
      color: #e2e8f0;
      font-size: 0.9rem;
      line-height: 1.5;
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

    /* ── Highlight Animation for Target Event ── */
    @keyframes highlightPulse {
      0% { background-color: rgba(34, 211, 238, 0.3); }
      100% { background-color: rgba(34, 211, 238, 0.05); }
    }
    .highlight-row td {
      animation: highlightPulse 2s ease-out forwards;
      border-top: 1px solid rgba(34, 211, 238, 0.5) !important;
      border-bottom: 1px solid rgba(34, 211, 238, 0.5) !important;
    }
    .highlight-row td:first-child {
      border-left: 3px solid #22d3ee !important;
    }
    .highlight-row td:last-child {
      border-right: 1px solid rgba(34, 211, 238, 0.5) !important;
    }
  </style>



  <!-- Rejection Modal -->
  <div class="modal-overlay" id="rejection-modal">
    <div class="modal" style="max-width:400px">
      <div class="modal-header">
        <h3 class="modal-title"><script>document.write(t('Reason for Rejection'))</script></h3>
        <button class="modal-close" onclick="closeRejectionModal()">&times;</button>
      </div>
      <form id="rejection-form" onsubmit="submitRejection(event)">
        <input type="hidden" id="reject-event-id">
        <div class="form-group">
          <label class="form-label" style="font-size:0.75rem;"><script>document.write(t('Explain why this event is being rejected'))</script></label>
          <textarea id="r-reason" class="form-control" rows="4"
            placeholder="e.g. Venue capacity mismatch, missing details..." 
            data-placeholder="e.g. Venue capacity mismatch, missing details..."
            required></textarea>
          <script>document.getElementById('r-reason').placeholder = t(document.getElementById('r-reason').getAttribute('data-placeholder'))</script>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" onclick="closeRejectionModal()"><script>document.write(t('Cancel'))</script></button>
          <button type="submit" class="btn btn-danger"><script>document.write(t('Confirm Rejection'))</script></button>
        </div>
      </form>
    </div>
  </div>

  <!-- Review Modal -->
  <div class="modal-overlay" id="review-modal">
    <div class="modal" style="max-width:480px">
      <div class="modal-header">
        <h3 class="modal-title"><script>document.write(t('Send Review'))</script></h3>
        <button class="modal-close" onclick="closeReviewModal()">&times;</button>
      </div>
      <form id="review-form" onsubmit="submitReview(event)">
        <input type="hidden" id="review-event-id">
        
        <div class="form-group">
          <label class="form-label" style="font-size:0.75rem;margin-bottom:10px;"><script>document.write(t('Select fields that need changes'))</script></label>
          <div id="review-fields-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:8px;cursor:pointer;transition:all 0.2s;font-size:0.82rem;color:#e2e8f0;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
              <input type="checkbox" name="review_fields" value="title" style="width:15px;height:15px;cursor:pointer;"> <script>document.write(t('Title'))</script>
            </label>
            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:8px;cursor:pointer;transition:all 0.2s;font-size:0.82rem;color:#e2e8f0;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
              <input type="checkbox" name="review_fields" value="description" style="width:15px;height:15px;cursor:pointer;"> <script>document.write(t('Description'))</script>
            </label>
            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:8px;cursor:pointer;transition:all 0.2s;font-size:0.82rem;color:#e2e8f0;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
              <input type="checkbox" name="review_fields" value="event_type" style="width:15px;height:15px;cursor:pointer;"> <script>document.write(t('Type'))</script>
            </label>
            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:8px;cursor:pointer;transition:all 0.2s;font-size:0.82rem;color:#e2e8f0;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
              <input type="checkbox" name="review_fields" value="capacity" style="width:15px;height:15px;cursor:pointer;"> <script>document.write(t('Capacity'))</script>
            </label>
            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:8px;cursor:pointer;transition:all 0.2s;font-size:0.82rem;color:#e2e8f0;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
              <input type="checkbox" name="review_fields" value="image" style="width:15px;height:15px;cursor:pointer;"> <script>document.write(t('Banner'))</script>
            </label>
            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:8px;cursor:pointer;transition:all 0.2s;font-size:0.82rem;color:#e2e8f0;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
              <input type="checkbox" name="review_fields" value="ministry_document" style="width:15px;height:15px;cursor:pointer;"> <script>document.write(t('Ministry Doc'))</script>
            </label>
            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:8px;cursor:pointer;transition:all 0.2s;font-size:0.82rem;color:#e2e8f0;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
              <input type="checkbox" name="review_fields" value="booking_proof" style="width:15px;height:15px;cursor:pointer;"> <script>document.write(t('Booking Proof'))</script>
            </label>
            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:8px;cursor:pointer;transition:all 0.2s;font-size:0.82rem;color:#e2e8f0;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
              <input type="checkbox" name="review_fields" value="event_objective" style="width:15px;height:15px;cursor:pointer;"> <script>document.write(t('Event Objective'))</script>
            </label>
            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:8px;cursor:pointer;transition:all 0.2s;font-size:0.82rem;color:#e2e8f0;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
              <input type="checkbox" name="review_fields" value="target_audience" style="width:15px;height:15px;cursor:pointer;"> <script>document.write(t('Target Audience'))</script>
            </label>
          </div>
        </div>
 
        <div class="form-group" style="margin-top:16px;">
          <label class="form-label" style="font-size:0.75rem;"><script>document.write(t('Review Message'))</script></label>
          <textarea id="review-message" class="form-control" rows="3"
            placeholder="e.g. Please upload a clearer competent authority document and increase the capacity..." 
            data-placeholder="e.g. Please upload a clearer competent authority document and increase the capacity..."
            required></textarea>
          <script>document.getElementById('review-message').placeholder = t(document.getElementById('review-message').getAttribute('data-placeholder'))</script>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" onclick="closeReviewModal()"><script>document.write(t('Cancel'))</script></button>
          <button type="submit" class="btn" style="background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3);"><script>document.write(t('Send Review'))</script></button>
        </div>
      </form>
    </div>
  </div>

  <!-- Cancellation Rejection Modal -->
  <div class="modal-overlay" id="cancellation-rejection-modal">
    <div class="modal" style="max-width:400px">
      <div class="modal-header">
        <h3 class="modal-title"><script>document.write(t('Reason for Rejecting Cancellation'))</script></h3>
        <button class="modal-close" onclick="closeCancellationRejectionModal()">&times;</button>
      </div>
      <form id="cancellation-rejection-form" onsubmit="submitCancellationRejection(event)">
        <input type="hidden" id="cancellation-reject-event-id">
        <div class="form-group">
          <label class="form-label" style="font-size:0.75rem;"><script>document.write(t('Explain why you are rejecting this cancellation request'))</script></label>
          <textarea id="cr-reason" class="form-control" rows="4"
            placeholder="e.g. The event is already sold out and cannot be cancelled at this stage..." 
            data-placeholder="e.g. The event is already sold out and cannot be cancelled at this stage..."
            required></textarea>
          <script>document.getElementById('cr-reason').placeholder = t(document.getElementById('cr-reason').getAttribute('data-placeholder'))</script>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" onclick="closeCancellationRejectionModal()"><script>document.write(t('Cancel'))</script></button>
          <button type="submit" class="btn btn-danger"><script>document.write(t('Confirm Rejection'))</script></button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openReviewModal(eventId) {
      document.getElementById('review-event-id').value = eventId;
      document.getElementById('review-modal').classList.add('open');
    }

    function closeReviewModal() {
      document.getElementById('review-modal').classList.remove('open');
      document.getElementById('review-form').reset();
    }

    async function submitReview(e) {
      e.preventDefault();
      const eventId = document.getElementById('review-event-id').value;
      const message = document.getElementById('review-message').value;
      const checkboxes = document.querySelectorAll('input[name="review_fields"]:checked');
      const fields = Array.from(checkboxes).map(cb => cb.value);

      if (fields.length === 0) {
        showToast('Please select at least one field to review', 'error');
        return;
      }

      const res = await api.put(`/events/${eventId}/review`, {
        review_message: message,
        review_fields: fields
      });

      if (res.ok) {
        showToast('Review sent to event manager!', 'success');
        closeReviewModal();
        loadEvents();
      } else {
        showToast(res.data?.message || 'Error sending review', 'error');
      }
    }

    // ── Cancellation Management ─────────────────────
    async function approveCancellation(id) {
        if (!confirm('Are you sure you want to APPROVE this cancellation request? This will permanently cancel the event.')) return;
        const res = await api.put(`/events/${id}/approve-cancellation`);
        if (res.ok) {
            showToast('Event cancelled successfully.', 'success');
            loadEvents();
        } else {
            showToast(res.data?.message || 'Error', 'error');
        }
    }

    function openCancellationRejectionModal(id) {
        document.getElementById('cancellation-reject-event-id').value = id;
        document.getElementById('cr-reason').value = '';
        document.getElementById('cancellation-rejection-modal').classList.add('open');
    }

    function closeCancellationRejectionModal() {
        document.getElementById('cancellation-rejection-modal').classList.remove('open');
    }

    async function submitCancellationRejection(e) {
        e.preventDefault();
        const id = document.getElementById('cancellation-reject-event-id').value;
        const reason = document.getElementById('cr-reason').value.trim();

        if (!reason) {
            showToast('Please enter a reason for rejection.', 'error');
            return;
        }

        const res = await api.put(`/events/${id}/reject-cancellation`, { rejection_reason: reason });
        if (res.ok) {
            showToast('Cancellation request rejected. Event status restored to approved.', 'info');
            closeCancellationRejectionModal();
            loadEvents();
        } else {
            showToast(res.data?.message || 'Error', 'error');
        }
    }

    function navigateToProfile(userId) {
      window.location.href = `/admin/users?highlight=${userId}`;
    }

    async function downloadAdminContract(sponsorshipId) {
      const token = sessionStorage.getItem('token');
      showToast('Downloading contract...', 'info');
      try {
        const res = await fetch(`/api/agreements/${sponsorshipId}/download-final`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        if (!res.ok) {
          showToast('Download failed. Contract might not be finalized.', 'error');
          return;
        }
        let filename = `agreement_${sponsorshipId}_final.pdf`;
        const disposition = res.headers.get('content-disposition');
        if (disposition && disposition.indexOf('filename=') !== -1) {
            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            const matches = filenameRegex.exec(disposition);
            if (matches != null && matches[1]) { 
                filename = matches[1].replace(/['"]/g, '');
            }
        }
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
        showToast('Download complete', 'success');
      } catch (err) {
        showToast('Error downloading contract', 'error');
        console.error(err);
      }
    }

    async function downloadEventDoc(eventId, type) {
      const token = sessionStorage.getItem('token');
      showToast('Downloading document...', 'info');
      try {
        const res = await fetch(`/api/events/${eventId}/download-document/${type}`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        if (!res.ok) {
          showToast('Download failed.', 'error');
          return;
        }
        
        let filename = `${type}_${eventId}.pdf`;
        const disposition = res.headers.get('content-disposition');
        if (disposition && disposition.indexOf('filename=') !== -1) {
            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            const matches = filenameRegex.exec(disposition);
            if (matches != null && matches[1]) { 
                filename = matches[1].replace(/['"]/g, '');
            }
        }
        
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
        showToast('Download complete', 'success');
      } catch (err) {
        showToast('Error downloading document', 'error');
      }
    }
  </script>
</body>

</html>





