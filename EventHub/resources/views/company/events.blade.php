<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Browse Exhibitions – EventHub Company</title>
  <link rel="stylesheet" href="/css/style.css" />
  <script src="/js/i18n.js"></script>
  <style>
    .req-btn {
      padding: 6px 12px;
      font-size: 13px;
    }

    .availability-warning {
      background: #fff3cd;
      color: #856404;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid #ffeeba;
      display: none;
    }

    .availability-load-error {
      background: #fee2e2;
      color: #991b1b;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid #fecaca;
      display: none;
    }
  </style>
<link rel="icon" href="/images/logo.jpg" type="image/jpeg">
</head>

<body>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-logo">
        <div style="display:flex; justify-content:center; align-items:center; width: 100%;"><img src="/images/logo.jpg" alt="EventHub Logo" style="width: 85px; height: 85px; object-fit: contain; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></div>
      </div>
      <nav class="sidebar-nav" id="sidebar-links" style="display:flex; flex-direction:column; gap:4px; padding-top:10px;">
        <!-- Filled by auth.js -->
      </nav>
      @include('partials._sidebar-footer')
    </aside>

    <main class="main-content">
      <div class="topbar">
        <div>
          <h1 class="page-title">Browse Exhibitions</h1>
          <p class="page-subtitle">Discover opportunities to exhibit at upcoming verified events</p>
        </div>
        <div class="topbar-actions" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <div style="position:relative">
            <input id="search-input" type="text" class="form-control" placeholder="Search by name or manager..."
              style="width:220px;padding-left:36px" oninput="applyFilter()">
            <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted)"
              width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <div style="position:relative;display:flex;align-items:center">
            <svg
              style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none"
              width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 6h18M7 12h10M11 18h2" />
            </svg>
            <select id="sort-events" class="form-control" style="width:190px;padding-left:32px"
              onchange="applyFilter()">
              <option value="soonest">Soonest First</option>
              <option value="farthest">Farthest First</option>
              <option value="alpha">Alphabetical</option>
            </select>
          </div>
        </div>
      </div>

      <div class="availability-load-error" id="availability-load-error">
        Could not load your visibility status from the server. Refresh the page or try again later.
      </div>

      <div class="availability-warning" id="availability-warning">
        You are currently not visible to event managers.<br />
        You will remain hidden until you turn this option ON again.
      </div>

      <div class="card">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Event Name</th>
                <th>Manager</th>
                <th>Venue</th>
                <th>Date</th>
                <th>Capacity</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="events-body">
              <tr class="loading-row">
                <td colspan="5">
                  <div class="spinner" style="margin:auto"></div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <!-- Send Request Modal -->
  <div class="modal-overlay" id="req-modal">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title">Apply for Exhibition</h3>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <form id="req-form">
        <input type="hidden" id="r-event-id" value="" />
        <div style="margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.1);">
          <div style="font-size:12px; color:var(--text-muted)">Selected Event</div>
          <div id="r-event-title" style="font-weight:bold; font-size:18px; color:#ffffff;">--</div>
        </div>
        <div class="form-group">
          <label class="form-label">Message / Intro</label>
          <textarea id="r-message" class="form-control"
            placeholder="Introduce your company and specify what kind of booth you are looking for..."
            rows="4"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary" id="btn-submit">Apply for Booth</button>
        </div>
      </form>
    </div>
  </div>

  <div id="toast-container"></div>
  <script src="/js/api.js"></script>
  <script src="/js/notifications.js"></script>
  <script src="/js/auth.js"></script>
  <script>
    const user = requireRole('Company');
    /** @type {boolean|null} null until loaded from GET /profile */
    let sponsorAvailability = null;

    if (user) {
      populateSidebar(user);
      setActiveNav();
      initSponsorEventsPage();
    }

    async function initSponsorEventsPage() {
      try {
        const res = await api.get('/profile');
        const warnEl = document.getElementById('availability-warning');
        const errEl = document.getElementById('availability-load-error');
        warnEl.style.display = 'none';
        errEl.style.display = 'none';

        let fresh = user;
        if (res.ok && res.data?.user) {
          fresh = res.data.user;
          localStorage.setItem('user', JSON.stringify(fresh));
          populateSidebar(fresh);
        }

        if (res.ok && fresh.profile) {
          sponsorAvailability = availabilityFromDatabase(fresh.profile.is_available);
          if (sponsorAvailability === false) {
            warnEl.style.display = 'block';
          }
        } else {
          sponsorAvailability = null;
        }
      } catch (err) {
        console.error('Error loading profile:', err);
        sponsorAvailability = null;
      }
      // Always load events regardless of profile status
      loadEvents();
    }

    let allEvents = [];
    let myRequestEventIds = [];
    let resolvedRequestEventIds = [];

    async function loadEvents() {
      try {
        const [eventsRes, reqsRes] = await Promise.all([
          api.get('/events'),
          api.get('/company/exhibitions')
        ]);

        myRequestEventIds = [];
        resolvedRequestEventIds = [];
        if (reqsRes.ok && Array.isArray(reqsRes.data)) {
          reqsRes.data.forEach(r => {
             const evId = r.event?.id || r.event_id;
             if (r.status === 'accepted' || r.status === 'rejected') {
                 resolvedRequestEventIds.push(evId);
             } else {
                 myRequestEventIds.push(evId);
             }
          });
        }

        const tbody = document.getElementById('events-body');
        if (!eventsRes.ok || !Array.isArray(eventsRes.data) || !eventsRes.data.length) {
          tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><p>No public events available right now.</p></div></td></tr>';
          return;
        }

        // Filter events that are exhibitions
        allEvents = eventsRes.data.filter(e => e.is_exhibition);
        applyFilter();
      } catch (err) {
        console.error('Error loading events:', err);
        const tbody = document.getElementById('events-body');
        if (tbody) tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><p>Failed to load events</p></div></td></tr>';
      }
    }

    function applyFilter() {
      const q = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
      const s = document.getElementById('sort-events').value;
      const now = new Date();

      // 1. Show only upcoming events AND exclude resolved requests AND check registration open
      let filtered = allEvents.filter(e => 
        new Date(e.start_time) > now && 
        !resolvedRequestEventIds.includes(e.id) &&
        e.is_exhibitor_registration_open === true
      );

      // 2. Search
      if (q) {
        filtered = filtered.filter(e =>
          (e.title || '').toLowerCase().includes(q) ||
          (e.creator?.name || '').toLowerCase().includes(q)
        );
      }

      // 3. Sort
      if (s === 'soonest') {
        filtered.sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
      } else if (s === 'farthest') {
        filtered.sort((a, b) => new Date(b.start_time) - new Date(a.start_time));
      } else if (s === 'alpha') {
        filtered.sort((a, b) => (a.title || '').localeCompare(b.title || '', 'ar'));
      }

      renderEvents(filtered);
    }

    function renderEvents(events) {
      const tbody = document.getElementById('events-body');
      if (!events.length) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><p>No events match your selection.</p></div></td></tr>';
        return;
      }

      tbody.innerHTML = events.map(e => {
        const hasRequested = myRequestEventIds.includes(e.id);

        let reqBtnHtml = '';
        if (hasRequested) {
          reqBtnHtml = `<span style="font-size:11px; color:var(--text-muted); margin-right:8px; font-style:italic;">Already Applied</span>
                        <button class="btn btn-ghost btn-sm" style="opacity:0.4; cursor:not-allowed;" disabled>Apply</button>`;
        } else {
          reqBtnHtml = `<button class="btn btn-primary btn-sm req-btn" onclick="openModal(${e.id}, '${e.title.replace(/'/g, "\\'")}')" ${sponsorAvailability !== true ? 'disabled' : ''} title="${sponsorAvailability === true ? 'Apply for Exhibition' : (sponsorAvailability === false ? 'Turn on Open to Invitations on your dashboard' : 'Loading visibility…')}">Apply</button>`;
        }

        return `
      <tr>
        <td>
            <div style="font-weight:600">${e.title}</div>
        </td>
        <td>
            <div style="font-size:13px; color:var(--accent2); cursor:pointer; display:inline-block;" onclick="navigateToProfile(${e.creator?.id})">
                👤 ${e.creator?.name || 'Unknown'}
            </div>
        </td>
        <td style="color:var(--text-muted)">${e.venue?.name || 'TBA'}</td>
        <td style="color:var(--text-muted)">${fmtDateShort(e.start_time)} <div style="margin-top:4px;">${timeBadge(e.time_status)}</div></td>
        <td style="color:var(--text-muted)">${e.capacity ? e.capacity.toLocaleString() : 'N/A'}</td>
        <td style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
           <button class="btn btn-ghost btn-sm" onclick="showEventDetails(${e.id})" title="${t('View Details')}">ℹ️ ${t('Details')}</button>
           <div style="display:flex; align-items:center;">
             ${reqBtnHtml}
           </div>
        </td>
      </tr>`;
      }).join('');
    }

    function openModal(eventId, eventTitle) {
      if (sponsorAvailability !== true) {
        showToast(sponsorAvailability === false
          ? 'Turn on "Open to Invitations" on your dashboard or profile to apply.'
          : 'Visibility status is still loading or unavailable.', 'warning');
        return;
      }
      document.getElementById('r-event-id').value = eventId;
      document.getElementById('r-event-title').innerText = eventTitle;
      document.getElementById('req-modal').classList.add('open');
    }

    function closeModal() {
      document.getElementById('req-modal').classList.remove('open');
      document.getElementById('req-form').reset();
    }

    document.getElementById('req-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('btn-submit');
      btn.textContent = 'Sending...';
      btn.disabled = true;

      const payload = {
        event_id: +document.getElementById('r-event-id').value,
        message: document.getElementById('r-message').value,
        initiator: 'company'
      };

      const res = await api.post('/exhibition', payload);

      if (res.ok) {
        showToast('Application sent directly to Event Manager!', 'success');
        closeModal();
      } else {
        showToast(res.data?.message || 'Error sending application', 'error');
      }

      btn.textContent = 'Apply for Booth';
      btn.disabled = false;
    });

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
                ${ev.location_link || (ev.venue?.location ? `<a href="${ev.venue.location.startsWith('http') ? ev.venue.location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.venue.location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` 
                : (ev.external_venue_location ? `<a href="${ev.external_venue_location.startsWith('http') ? ev.external_venue_location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.external_venue_location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` : '—'))}
              </div></div>
            </div>
          </div>

          <!-- Document Links -->
          ${ev.booking_proof ? `
            <div class="ed-info-card" style="grid-column: 1 / -1; background:rgba(34,211,238,0.06); border:1px solid rgba(34,211,238,0.15);">
                <div class="ed-info-icon">📎</div>
                <div style="flex:1">
                    <div class="ed-info-label" style="color:#22d3ee">Booking Proof</div>
                    <a href="/storage/${ev.booking_proof}" target="_blank" class="ed-info-value" style="text-decoration:underline;">View Document ↗</a>
                </div>
            </div>
          ` : ''}

          ${ev.ministry_approval_doc ? `
            <div class="ed-info-card" style="grid-column: 1 / -1; background:rgba(139,92,246,0.06); border:1px solid rgba(139,92,246,0.15);">
                <div class="ed-info-icon">📄</div>
                <div style="flex:1">
                    <div class="ed-info-label" style="color:#a78bfa">Competent Authority Approval</div>
                    <a href="/storage/${ev.ministry_approval_doc}" target="_blank" class="ed-info-value" style="text-decoration:underline;">View Document ↗</a>
                </div>
            </div>
          ` : ''}

          <!-- Project Info -->
          ${ev.objective ? `
            <div class="ed-info-card" style="grid-column: 1 / -1; flex-direction:column; align-items:flex-start; gap:5px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:1.1rem;">🎯</span>
                    <div class="ed-info-label" style="margin-bottom:0">Event Objective</div>
                </div>
                <div class="ed-info-value" style="font-weight:400; font-size:0.85rem; line-height:1.4; color:rgba(255,255,255,0.7)">${ev.objective}</div>
            </div>
          ` : ''}

          ${ev.target_audience ? `
            <div class="ed-info-card" style="grid-column: 1 / -1; flex-direction:column; align-items:flex-start; gap:5px;">
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
                let scheduleHtml = '<div style="grid-column: 1 / -1;">';
                scheduleHtml += '<div style="font-size:0.72rem;font-weight:700;color:#a78bfa;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">📅 Event Schedule (' + schedule.length + ' day' + (schedule.length > 1 ? 's' : '') + ')</div>';
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
                  if (slot.period) {
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
                return '<div class="ed-info-card ed-info-accent"><div class="ed-info-icon">🕐</div><div><div class="ed-info-label">Start</div><div class="ed-info-value">' + fmtDate(ev.start_time) + '</div></div></div>' +
                       '<div class="ed-info-card ed-info-accent"><div class="ed-info-icon">🕔</div><div><div class="ed-info-label">End</div><div class="ed-info-value">' + fmtDate(ev.end_time) + '</div></div></div>';
              }
            })()}

            <div class="ed-info-grid">
                <div class="ed-info-card ed-info-warning">
                <div class="ed-info-icon">👥</div>
                <div><div class="ed-info-label">Capacity</div><div class="ed-info-value">${ev.capacity || 'N/A'}</div></div>
                </div>
                <div class="ed-info-card ed-info-warning">
                <div class="ed-info-icon">🎫</div>
                <div><div class="ed-info-label">Tickets Booked</div><div class="ed-info-value">${ev.tickets_count || 0}</div></div>
                </div>
            </div>

          ${exhibitorsHtml}

          <div class="ed-footer mt-2" style="justify-content:space-between">
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="ed-footer-label">Created by</span>
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
  </script>

  <!-- Event Details Modal -->
  <div class="modal-overlay" id="event-details-modal">
    <div class="modal ed-modal">
      <button class="ed-close-btn" onclick="closeEventDetailsModal()">✕</button>
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

    .mt-2 {
      margin-top: 8px;
    }

    .cursor-pointer {
      cursor: pointer;
    }
  </style>





