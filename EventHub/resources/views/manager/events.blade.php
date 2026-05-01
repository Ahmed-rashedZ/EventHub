<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Events – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://npmcdn.com/flatpickr/dist/l10n/ar.js"></script>
  <script src="/js/i18n.js"></script>
  <style>
    .flatpickr-calendar { direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}; }
    .flatpickr-current-month { display: flex !important; justify-content: center !important; }
  </style>
</head>

<body>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-logo">
        <div class="logo-icon">🎯</div><span>EventHub</span>
      </div>
      <nav class="sidebar-nav">
        <span class="nav-section-label">Overview</span>
        <a class="nav-item" href="/manager/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
        <span class="nav-section-label">Events</span>
        <a class="nav-item active" href="/manager/events"><span class="nav-icon">📅</span> My Events</a>
        <a class="nav-item" href="/manager/assistants"><span class="nav-icon">👥</span> Assistants</a>
        <a class="nav-item" href="/manager/attendance"><span class="nav-icon">📍</span> Attendance</a>
        <a class="nav-item" href="/manager/sponsorship"><span class="nav-icon">💼</span> Sponsorship</a>
        <span class="nav-section-label">Settings</span>
        <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
      </nav>
      @include('partials._sidebar-footer')
    </aside>

    <main class="main-content">
      <div class="topbar">
        <div>
          <h1 class="page-title">My Events</h1>
          <p class="page-subtitle">Create and manage your events</p>
        </div>
        <div class="topbar-actions" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <div style="position:relative">
            <input id="search-input" type="text" class="form-control" placeholder="Search by event name..."
              style="width:220px;padding-left:36px" oninput="applyFilter()">
            <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted)"
              width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <select id="filter-status" class="form-control" style="width:145px" onchange="applyFilter()">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
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
              <option value="live">Live Now</option>
              <option value="ended">Ended</option>
            </select>
          </div>
          <button class="btn btn-primary" onclick="openModal()">+ Create Event</button>
        </div>
      </div>

      <div class="card">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Venue</th>
                <th>Start</th>
                <th>Capacity</th>
                <th>Sponsorships</th>
                <th>Status</th>
                <th>Actions</th>
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

  <!-- Create Event Modal -->
  <div class="modal-overlay" id="event-modal">
    <div class="modal" style="max-width:600px; max-height: 90vh; overflow-y: auto; margin: 20px 0; padding-top: 0;">
      <div class="modal-header"
        style="position: sticky; top: 0; background: rgba(30,33,45,0.95); backdrop-filter: blur(12px); z-index: 10; padding: 24px 0 16px; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
        <h3 class="modal-title">Create New Event</h3>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <form id="event-form">
        <!-- Section 1: Basic Information -->
        <div class="form-section">
          <div class="form-section-title"><span>📝</span> Basic Information</div>
          <div class="form-group">
            <label class="form-label">Event Title</label>
            <input id="e-title" type="text" class="form-control" placeholder="e.g. Tech Summit 2026" required />
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea id="e-desc" class="form-control" placeholder="Briefly describe your event…" required rows="3"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Event Type</label>
            <select id="e-type" class="form-control" required>
              <option value="مؤتمر"><script>document.write(t('Conference'))</script></option>
              <option value="ندوة"><script>document.write(t('Seminar'))</script></option>
              <option value="ورشة عمل"><script>document.write(t('Workshop'))</script></option>
              <option value="دورة تدريبية"><script>document.write(t('Training Course'))</script></option>
              <option value="ترفيه"><script>document.write(t('Entertainment'))</script></option>
              <option value="ملتقى علمي"><script>document.write(t('Scientific Forum'))</script></option>
              <option value="رياضة"><script>document.write(t('Sports'))</script></option>
              <option value="تقنية"><script>document.write(t('Technology'))</script></option>
              <option value="اجتماعية"><script>document.write(t('Social'))</script></option>
            </select>
          </div>

          <!-- Location Type moved to Section 2 -->
        </div>

        <!-- Section 2: Date & Location -->
        <div class="form-section">
          <div class="form-section-title"><span>📅</span> Date & Location Details</div>
          
          <input type="hidden" id="e-location-type" name="location_type" value="internal" />

          <!-- Modern Venue Selector -->
          <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label">Event Venue</label>
            
            <!-- Internal Mode -->
            <div id="venue-internal-wrap" style="display: flex; gap: 12px; align-items: center;">
              <div style="flex: 1; position: relative;">
                <select id="e-venue" class="form-control" onchange="updatePeriodTimes()" required style="padding-left: 42px; cursor: pointer; background-color: rgba(255,255,255,0.02);">
                  <option value="">Select a hall inside the exhibition...</option>
                </select>
                <div style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; opacity: 0.7;">
                  🏢
                </div>
              </div>
              <button type="button" class="btn" style="background: rgba(34,211,238,0.1); border: 1px solid rgba(34,211,238,0.25); color: #22d3ee; white-space: nowrap; height: 42px; padding: 0 20px; transition: all 0.2s;" onclick="setLocationMode('external')" onmouseover="this.style.background='rgba(34,211,238,0.15)'" onmouseout="this.style.background='rgba(34,211,238,0.1)'">
                ➕ External Hall
              </button>
            </div>

            <!-- External Mode -->
            <div id="venue-external-wrap" style="display: none; gap: 12px; align-items: center;">
              <div style="flex: 1; position: relative;">
                <input id="e-ext-name" type="text" class="form-control" placeholder="External Hall Name (e.g., Corinthia Hotel)" style="padding-left: 42px; background-color: rgba(255,255,255,0.02);" />
                <div style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; opacity: 0.7;">
                  🌍
                </div>
              </div>
              <button type="button" class="btn" style="background: rgba(110,64,242,0.1); border: 1px solid rgba(110,64,242,0.25); color: #a78bfa; white-space: nowrap; height: 42px; padding: 0 20px; transition: all 0.2s;" onclick="setLocationMode('internal')" onmouseover="this.style.background='rgba(110,64,242,0.15)'" onmouseout="this.style.background='rgba(110,64,242,0.1)'">
                🏢 Inside Exhibition
              </button>
            </div>
          </div>

          <div id="internal-fields">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Booking Date</label>
                <input id="e-booking-date" type="text" class="form-control" placeholder="YYYY-MM-DD" required />
                <div style="display: flex; gap: 16px; margin-top: 10px; font-size: 13px; color: var(--text-muted, #9ca3af);">
                  <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background-color: rgba(239, 68, 68, 0.5); border: 1px solid #ef4444; box-shadow: 0 0 8px rgba(239, 68, 68, 0.4);"></span>
                    <span><script>document.write(t('Fully Booked'))</script></span>
                  </div>
                  <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background-color: rgba(234, 179, 8, 0.5); border: 1px solid #eab308; box-shadow: 0 0 8px rgba(234, 179, 8, 0.4);"></span>
                    <span><script>document.write(t('Partially Booked'))</script></span>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Period</label>
                <select id="e-period" class="form-control" onchange="updatePeriodTimes()" required>
                  <option value="morning">Morning Period ☀</option>
                  <option value="evening">Evening Period 🌙</option>
                  <option value="full_day">Full Day 🗓️</option>
                </select>
                <small id="selected-period-time" style="color:var(--text-muted);font-size:12px;margin-top:6px;display:block">—</small>
              </div>
            </div>
          </div>

          <div id="external-fields" style="display: none;">
            <div class="form-group">
              <label class="form-label">Google Maps Link</label>
              <input id="e-ext-location" type="url" class="form-control" placeholder="https://maps.google.com/..." />
            </div>
            <div class="form-group">
              <label class="form-label">Proof of Booking (PDF/Image)</label>
              <input id="e-booking-proof" type="file" accept=".pdf,image/*" class="form-control" style="padding: 7px 10px;" />
              <small style="color:var(--text-muted);font-size:12px;margin-top:4px;display:block">Required. Upload official confirmation of the external booking.</small>
            </div>
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Start Date & Time</label>
                <input id="e-start" type="datetime-local" class="form-control" />
              </div>
              <div class="form-group">
                <label class="form-label">End Date & Time</label>
                <input id="e-end" type="datetime-local" class="form-control" />
              </div>
            </div>
          </div>
        </div>

        <!-- Section 3: Additional Settings -->
        <div class="form-section" style="margin-bottom: 0;">
          <div class="form-section-title"><span>⚙️</span> Additional Settings</div>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Capacity</label>
              <input id="e-capacity" type="number" class="form-control" placeholder="200" min="1" required />
            </div>
            <div class="form-group">
              <label class="form-label">Event Banner Image</label>
              <input id="e-image" type="file" accept="image/*" class="form-control" style="padding: 7px 10px;" />
            </div>
          </div>
        </div>
        <div class="modal-footer"
          style="margin-top: 24px; padding-top: 18px; border-top: 1px solid rgba(255,255,255,0.06);">
          <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit for Approval</button>
        </div>
      </form>
    </div>
  </div>

  <div id="toast-container"></div>
  <script src="/js/api.js"></script>
  <script src="/js/notifications.js"></script>
  <script src="/js/auth.js"></script>
  <script>
    let allEvents = [];
    let globalVenues = [];
    let currentVenueBookings = [];
    let fpInstance = null;

    const user = requireRole('Event Manager');
    if (user) { 
        populateSidebar(user); 
        setActiveNav(); 
        loadEvents(); 
        loadVenues(); 

        fpInstance = flatpickr("#e-booking-date", {
            dateFormat: "Y-m-d",
            locale: document.documentElement.lang === 'ar' ? 'ar' : 'en',
            disable: [
                function(date) {
                    if (!currentVenueBookings.length) return false;
                    const y = date.getFullYear();
                    const m = String(date.getMonth() + 1).padStart(2, '0');
                    const d = String(date.getDate()).padStart(2, '0');
                    const dateStrLocal = `${y}-${m}-${d}`;
                    
                    const bookings = currentVenueBookings.filter(b => b.booking_date === dateStrLocal);
                    if (bookings.length > 0) {
                        const periods = bookings.map(b => b.period);
                        return periods.includes('full_day') || (periods.includes('morning') && periods.includes('evening'));
                    }
                    return false;
                }
            ],
            onChange: function() {
                checkAvailability();
            },
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                dayElem.classList.remove('date-fully-booked', 'date-partially-booked');
                if (!currentVenueBookings.length) return;
                
                const y = dayElem.dateObj.getFullYear();
                const m = String(dayElem.dateObj.getMonth() + 1).padStart(2, '0');
                const d = String(dayElem.dateObj.getDate()).padStart(2, '0');
                const dateStrLocal = `${y}-${m}-${d}`;
                
                const bookings = currentVenueBookings.filter(b => b.booking_date === dateStrLocal);
                if (bookings.length > 0) {
                    const periods = bookings.map(b => b.period);
                    if (periods.includes('full_day') || (periods.includes('morning') && periods.includes('evening'))) {
                        dayElem.classList.add('date-fully-booked');
                    } else {
                        dayElem.classList.add('date-partially-booked');
                    }
                }
            }
        });

        document.getElementById('e-period').addEventListener('change', function(e) {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.getAttribute('data-booked') === 'true') {
                showToast(document.documentElement.lang === 'ar' ? 'هذه الفترة محجوزة مسبقاً، يرجى اختيار فترة أخرى.' : 'This period is already booked, please choose another.', 'error');
                this.value = ''; 
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('flatpickr-day') && e.target.classList.contains('flatpickr-disabled')) {
                if (e.target.classList.contains('date-fully-booked')) {
                    showToast(document.documentElement.lang === 'ar' ? 'هذا التاريخ محجوز بالكامل، يرجى اختيار تاريخ آخر.' : 'This date is fully booked, please choose another.', 'error');
                }
            }
        }, true);
    }

    async function loadEvents() {
      const res = await api.get('/events/list/my');
      const tbody = document.getElementById('events-body');
      if (!res.ok) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--danger)">Failed to load</td></tr>'; return; }
      allEvents = res.data;
      applyFilter();
    }

    function applyFilter() {
      const f = document.getElementById('filter-status').value;
      const q = (document.getElementById('search-input').value || '').toLowerCase().trim();
      const s = document.getElementById('sort-events').value;
      const now = new Date();

      // 1. Filter by status
      let filtered = f ? allEvents.filter(e => e.status === f) : [...allEvents];

      // 2. Filter by search query
      if (q) {
        filtered = filtered.filter(e => (e.title || '').toLowerCase().includes(q));
      }

      // 3. Filter by time-status when sort is live/ended
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
        // Sort live events by the one ending soonest first
        filtered.sort((a, b) => new Date(a.end_time) - new Date(b.end_time));
      } else if (s === 'ended') {
        // Sort ended events by most recently ended first
        filtered.sort((a, b) => new Date(b.end_time) - new Date(a.end_time));
      }

      renderEvents(filtered);
    }

    function renderEvents(events) {
      const tbody = document.getElementById('events-body');
      if (!events.length) { tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📅</div><p>No events found</p></div></td></tr>'; return; }
      tbody.innerHTML = events.map((ev, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i + 1}</td>
        <td><div style="font-weight:600">${ev.title}</div></td>
        <td style="color:var(--text-muted)">${ev.venue_id ? (ev.venue?.name || '—') : (ev.external_venue_name ? ev.external_venue_name + ' (External)' : '—')}</td>
        <td style="color:var(--text-muted);white-space:nowrap">${fmtDateShort(ev.start_time)}</td>
        <td style="color:var(--text-muted)">${ev.capacity}</td>
        <td>
           <div style="display:flex; align-items:center;">
             <input type="checkbox" id="spon-tog-${ev.id}" ${ev.is_sponsorship_open ? 'checked' : ''} onchange="toggleSponsorship(${ev.id}, this.checked)" style="width:16px; height:16px; margin-right:5px; cursor:pointer;"/>
             <label for="spon-tog-${ev.id}" style="font-size:12px; cursor:pointer;">Open</label>
           </div>
        </td>
        <td>${badge(ev.status)} ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}</td>
        <td style="display:flex;gap:6px;padding:14px 16px;flex-wrap:wrap">
          <button class="btn btn-ghost btn-sm" onclick="showEventDetails(${ev.id})" title="View Details">ℹ️ Details</button>
          <button class="btn btn-sm" style="background:rgba(34,211,238,.12);color:#22d3ee;border:1px solid rgba(34,211,238,.25)" onclick="window.location.href='/manager/event-stats/${ev.id}'" title="View Statistics">📊 Stats</button>
        </td>
      </tr>`).join('');
    }

    // Modal for event details
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

        const rejectionSection = (ev.status === 'rejected' && ev.rejection_reason)
          ? `<div class="ed-rejection"><span class="ed-rej-label">⚠ Rejection Reason</span><p>${ev.rejection_reason}</p></div>`
          : '';

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
          <div class="ed-section mt-4" style="margin-top: 16px;">
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
                      <div class="avatar" style="width:36px; height:36px; font-size:14px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:50%; overflow:hidden;">
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

        <!-- Header: Title + Type + Badges -->
        <div class="ed-header">
          <div class="ed-title-row">
            <h2 class="ed-title">${ev.title}</h2>
            <span class="ed-type-pill" style="--tcolor:${tColor}">${tIcon} ${eType}</span>
          </div>
          <div class="ed-badges">
            ${ev.status ? badge(ev.status) : ''}
            ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}
          </div>
        </div>

        ${rejectionSection}

        <!-- Description -->
        <div class="ed-section">
          <div class="ed-section-label">About this Event</div>
          <p class="ed-description">${ev.description || 'No description provided.'}</p>
        </div>

        <!-- Info Grid -->
        <div class="ed-info-grid">
          <div class="ed-info-card ed-info-accent2">
            <div class="ed-info-icon">🏛️</div>
            <div>
              <div class="ed-info-label">Venue</div>
              <div class="ed-info-value">${ev.venue_id ? (ev.venue?.name || '—') : (ev.external_venue_name || '—')}</div>
            </div>
          </div>
          <div class="ed-info-card ed-info-accent2">
            <div class="ed-info-icon">📍</div>
            <div>
              <div class="ed-info-label">Location</div>
              <div class="ed-info-value">
                ${ev.venue_id && ev.venue?.location ? `<a href="${ev.venue.location.startsWith('http') ? ev.venue.location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.venue.location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` 
                : (!ev.venue_id && ev.external_venue_location ? `<a href="${ev.external_venue_location.startsWith('http') ? ev.external_venue_location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.external_venue_location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` : '—')}
              </div>
            </div>
          </div>
          ${!ev.venue_id && ev.booking_proof_path ? `
          <div class="ed-info-card ed-info-accent2" style="grid-column: 1 / -1; background:rgba(34,211,238,0.05); border-color:rgba(34,211,238,0.2);">
            <div class="ed-info-icon">📎</div>
            <div><div class="ed-info-label" style="color:#22d3ee">Booking Proof</div><div class="ed-info-value"><a href="/storage/${ev.booking_proof_path}" target="_blank" style="color:#22d3ee;text-decoration:underline;">View Document ↗</a></div></div>
          </div>
          ` : ''}
          <div class="ed-info-card ed-info-accent">
            <div class="ed-info-icon">🕐</div>
            <div>
              <div class="ed-info-label">Start</div>
              <div class="ed-info-value">${fmtDate(ev.start_time)}</div>
            </div>
          </div>
          <div class="ed-info-card ed-info-accent">
            <div class="ed-info-icon">🕔</div>
            <div>
              <div class="ed-info-label">End</div>
              <div class="ed-info-value">${fmtDate(ev.end_time)}</div>
            </div>
          </div>
          <div class="ed-info-card ed-info-warning">
            <div class="ed-info-icon">👥</div>
            <div>
              <div class="ed-info-label">Capacity</div>
              <div class="ed-info-value">${ev.capacity}</div>
            </div>
          </div>
          <div class="ed-info-card ed-info-warning">
            <div class="ed-info-icon">🎟️</div>
            <div>
              <div class="ed-info-label">Tickets Booked</div>
              <div class="ed-info-value">${ev.tickets_count ?? '—'}</div>
            </div>
          </div>
          </div>
          
          ${sponsorsHtml}
          ${reviewsHtml}

          <!-- Footer -->
          <div class="ed-footer" style="margin-top: 8px;">
          <span class="ed-footer-label">Created by</span>
          <span class="ed-footer-name">${ev.creator?.name || ev.manager?.name || '—'}</span>
        </div>

      </div>
    `;
      });
    }

    function closeEventDetailsModal() {
      document.getElementById('event-details-modal').classList.remove('open');
      document.getElementById('event-details-content').innerHTML = '';
    }



    async function toggleSponsorship(eventId, checked) {
      const res = await api.patch(`/events/${eventId}/toggle-sponsorship`);
      if (res.ok) {
        showToast(res.data.is_sponsorship_open ? 'Sponsorship is now OPEN for this event.' : 'Sponsorship is now CLOSED for this event.', 'success');
      } else {
        showToast(res.data?.message || 'Error updating status', 'error');
        document.getElementById(`spon-tog-${eventId}`).checked = !checked; // revert UI visually
      }
    }

    async function loadVenues() {
      const res = await api.get('/venues');
      const sel = document.getElementById('e-venue');
      if (!res.ok) { sel.innerHTML = '<option value="">No venues available</option>'; return; }
      globalVenues = res.data;
      sel.innerHTML = '<option value="">Select a hall inside the exhibition...</option>' + res.data.map(v => `<option value="${v.id}">${v.name} (${v.location})</option>`).join('');
    }

    async function updatePeriodTimes() {
      const venueId = document.getElementById('e-venue').value;
      const periodSelect = document.getElementById('e-period').value;
      const timeLabel = document.getElementById('selected-period-time');
      
      if (!venueId) {
        timeLabel.textContent = document.documentElement.lang === 'ar' ? 'اختر قاعة أولاً لرؤية الوقت' : 'Select a venue first to see the time';
        currentVenueBookings = [];
        window.lastFetchedVenueId = null;
        if (fpInstance) fpInstance.redraw();
        checkAvailability();
        return;
      }
      
      const v = globalVenues.find(x => x.id == venueId);
      if (v) {
        const formatTime = (t24) => {
          if(!t24) return '';
          let [h, m] = t24.split(':');
          h = parseInt(h);
          const ampm = h >= 12 ? 'PM' : 'AM';
          h = h % 12;
          h = h ? h : 12; 
          return `${h.toString().padStart(2, '0')}:${m} ${ampm}`;
        };

        if (periodSelect === 'morning') {
          timeLabel.textContent = `Time: ${formatTime(v.morning_start)} - ${formatTime(v.morning_end)}`;
        } else if (periodSelect === 'evening') {
          timeLabel.textContent = `Time: ${formatTime(v.evening_start)} - ${formatTime(v.evening_end)}`;
        } else {
          timeLabel.textContent = `Time: ${formatTime(v.morning_start)} - ${formatTime(v.evening_end)}`;
        }
      }

      if (window.lastFetchedVenueId !== venueId) {
         window.lastFetchedVenueId = venueId;
         const res = await api.get(`/venues/${venueId}/bookings`);
         if (res.ok) {
            currentVenueBookings = res.data;
         } else {
            currentVenueBookings = [];
         }
         if (fpInstance) fpInstance.redraw();
      }
      checkAvailability();
    }

    function checkAvailability() {
      const date = document.getElementById('e-booking-date').value;
      const periodOpts = document.getElementById('e-period').options;
      
      if (!date) return;

      for(let i=0; i<periodOpts.length; i++) {
         periodOpts[i].removeAttribute('data-booked');
         periodOpts[i].text = periodOpts[i].text.replace(' (محجوز)', '').replace(' (Booked)', '');
      }

      const bookedPeriods = currentVenueBookings.filter(b => b.booking_date === date).map(b => b.period);
      
      for(let i=0; i<periodOpts.length; i++) {
         const p = periodOpts[i].value;
         if (bookedPeriods.includes(p) || (bookedPeriods.includes('full_day')) || (p === 'full_day' && bookedPeriods.length > 0)) {
             periodOpts[i].setAttribute('data-booked', 'true');
             periodOpts[i].text += ` (${document.documentElement.lang === 'ar' ? 'محجوز' : 'Booked'})`;
         }
      }
      
      const currentSel = document.getElementById('e-period');
      if (currentSel.options[currentSel.selectedIndex]?.getAttribute('data-booked') === 'true') {
         currentSel.value = '';
      }
    }

    function openModal() { document.getElementById('event-modal').classList.add('open'); }
    function closeModal() {
      document.getElementById('event-modal').classList.remove('open');
      document.getElementById('event-form').reset();
      setLocationMode('internal');
    }

    function setLocationMode(mode) {
      document.getElementById('e-location-type').value = mode;
      const internalWrap = document.getElementById('venue-internal-wrap');
      const externalWrap = document.getElementById('venue-external-wrap');
      const internalFields = document.getElementById('internal-fields');
      const externalFields = document.getElementById('external-fields');

      if (mode === 'internal') {
        internalWrap.style.display = 'flex';
        externalWrap.style.display = 'none';
        internalFields.style.display = 'block';
        externalFields.style.display = 'none';
        
        // required toggles
        document.getElementById('e-venue').required = true;
        document.getElementById('e-booking-date').required = true;
        document.getElementById('e-period').required = true;
        
        document.getElementById('e-ext-name').required = false;
        document.getElementById('e-booking-proof').required = false;
        document.getElementById('e-start').required = false;
        document.getElementById('e-end').required = false;
      } else {
        internalWrap.style.display = 'none';
        externalWrap.style.display = 'flex';
        internalFields.style.display = 'none';
        externalFields.style.display = 'block';
        
        // required toggles
        document.getElementById('e-venue').required = false;
        document.getElementById('e-booking-date').required = false;
        document.getElementById('e-period').required = false;
        
        document.getElementById('e-ext-name').required = true;
        document.getElementById('e-booking-proof').required = true;
        document.getElementById('e-start').required = true;
        document.getElementById('e-end').required = true;
      }
    }

    document.getElementById('event-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData();
      formData.append('title', document.getElementById('e-title').value);
      formData.append('description', document.getElementById('e-desc').value);

      const eventType = document.getElementById('e-type').value;
      formData.append('event_type', eventType);
      const locationType = document.getElementById('e-location-type').value;
      formData.append('location_type', locationType);

      if (locationType === 'internal') {
        formData.append('venue_id', document.getElementById('e-venue').value);
        formData.append('booking_date', document.getElementById('e-booking-date').value);
        formData.append('period', document.getElementById('e-period').value);
      } else {
        formData.append('external_venue_name', document.getElementById('e-ext-name').value);
        formData.append('external_venue_location', document.getElementById('e-ext-location').value);
        formData.append('start_time', document.getElementById('e-start').value);
        formData.append('end_time', document.getElementById('e-end').value);
        
        const proofFile = document.getElementById('e-booking-proof').files[0];
        if (proofFile) {
          formData.append('booking_proof', proofFile);
        }
      }

      formData.append('capacity', document.getElementById('e-capacity').value);

      const imageFile = document.getElementById('e-image').files[0];
      if (imageFile) {
        formData.append('image', imageFile);
      }

      const res = await api.postForm('/events', formData);

      if (res.ok) { showToast('Event submitted for approval!', 'success'); closeModal(); loadEvents(); }
      else {
        const msg = res.data?.errors ? Object.values(res.data.errors).flat().join('. ') : res.data?.message || 'Error';
        showToast(msg, 'error');
      }
    });
  </script>

  <!-- Event Details Modal -->
  <div class="modal-overlay" id="event-details-modal">
    <div class="modal ed-modal">
      <button class="ed-close-btn" onclick="closeEventDetailsModal()">✕</button>
      <div id="event-details-content" class="ed-content"></div>
    </div>
  </div>

  <style>
    /* Flatpickr Modern Overrides */
    .flatpickr-calendar {
      background: #171821 !important; /* System surface match */
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6) !important;
      border-radius: 16px !important;
      padding: 10px !important;
      font-family: 'Inter', system-ui, sans-serif !important;
    }
    .flatpickr-day {
      border-radius: 10px !important;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
      color: #e2e8f0 !important;
      border: 1px solid transparent !important;
    }
    .flatpickr-day:hover, .flatpickr-day:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: rgba(255, 255, 255, 0.15) !important;
      transform: scale(1.08);
      z-index: 2;
    }
    .flatpickr-day.selected, .flatpickr-day.selected:hover {
      background: #6e40f2 !important;
      border-color: #6e40f2 !important;
      box-shadow: 0 4px 15px rgba(110, 64, 242, 0.4) !important;
      color: #ffffff !important;
      font-weight: 600 !important;
      transform: scale(1.05);
    }
    .flatpickr-month {
      margin-bottom: 15px !important;
      height: 38px !important;
    }
    .flatpickr-current-month {
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 12px !important;
      padding: 0 !important;
      height: 100% !important;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months {
      appearance: none !important;
      -webkit-appearance: none !important;
      background: transparent !important;
      border: none !important;
      color: #fff !important;
      font-size: 16px !important;
      font-weight: 600 !important;
      padding: 0 !important;
      margin: 0 !important;
      cursor: pointer !important;
      width: auto !important;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
      background: transparent !important;
    }
    .flatpickr-current-month .numInputWrapper {
      width: 6ch !important;
      background: transparent !important;
    }
    .flatpickr-current-month .numInputWrapper input.cur-year {
      font-size: 16px !important;
      font-weight: 600 !important;
      color: #fff !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    .flatpickr-calendar.arrowTop:before, .flatpickr-calendar.arrowTop:after {
      display: none !important; /* Hide the arrow triangle for a cleaner look */
    }
    html[lang="ar"] .flatpickr-calendar {
      direction: rtl;
    }
    
    /* Flatpickr Custom Highlights */
    .date-fully-booked {
      background-color: rgba(239, 68, 68, 0.15) !important;
      border-color: rgba(239, 68, 68, 0.5) !important;
      color: #f87171 !important;
      font-weight: 600;
    }
    .date-partially-booked {
      background-color: rgba(234, 179, 8, 0.15) !important;
      border-color: rgba(234, 179, 8, 0.5) !important;
      color: #facc15 !important;
      font-weight: 600;
    }
    .date-fully-booked:hover {
      background-color: rgba(239, 68, 68, 0.25) !important;
    }
    .date-partially-booked:hover {
      background-color: rgba(234, 179, 8, 0.25) !important;
    }

    /* ── Form Sections ───────────────────────────── */
    .form-section {
      background: rgba(255,255,255,0.015);
      border: 1px solid rgba(255,255,255,0.04);
      border-radius: 12px;
      padding: 24px;
      margin-bottom: 24px;
    }
    .form-section-title {
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--primary);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* ── Location Cards ───────────────────────────── */
    .location-cards {
      display: flex;
      gap: 12px;
    }
    .loc-card {
      flex: 1;
      cursor: pointer;
    }
    .loc-card input {
      display: none;
    }
    .loc-card-content {
      display: flex;
      align-items: center;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid rgba(255,255,255,0.08);
      background: rgba(255,255,255,0.015);
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      height: 100%;
    }
    .loc-icon {
      font-size: 22px;
      margin-right: 14px;
      background: rgba(255,255,255,0.05);
      padding: 8px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    .loc-details {
      flex: 1;
    }
    .loc-title {
      font-weight: 700;
      color: #fff;
      font-size: 0.9rem;
      margin-bottom: 2px;
    }
    .loc-desc {
      font-size: 0.7rem;
      color: var(--text-muted);
      line-height: 1.3;
    }
    .loc-radio {
      width: 18px;
      height: 18px;
      border-radius: 50%;
      border: 2px solid rgba(255,255,255,0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-left: 12px;
    }
    .loc-radio-inner {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: transparent;
      transition: all 0.2s;
    }
    .loc-card:hover .loc-card-content {
      background: rgba(255,255,255,0.04);
      border-color: rgba(255,255,255,0.15);
    }
    
    /* Internal Theme */
    .loc-internal .loc-icon {
      background: color-mix(in srgb, var(--primary) 15%, transparent);
      text-shadow: 0 0 10px rgba(110,64,242,0.4);
    }
    .loc-internal input:checked + .loc-card-content {
      border-color: var(--primary);
      background: color-mix(in srgb, var(--primary) 6%, transparent);
      box-shadow: 0 4px 12px color-mix(in srgb, var(--primary) 10%, transparent);
    }
    .loc-internal input:checked + .loc-card-content .loc-radio { border-color: var(--primary); }
    .loc-internal input:checked + .loc-card-content .loc-radio-inner { background: var(--primary); }

    /* External Theme */
    .loc-external .loc-icon {
      background: color-mix(in srgb, #22d3ee 15%, transparent);
      text-shadow: 0 0 10px rgba(34,211,238,0.4);
    }
    .loc-external input:checked + .loc-card-content {
      border-color: #22d3ee;
      background: color-mix(in srgb, #22d3ee 6%, transparent);
      box-shadow: 0 4px 12px color-mix(in srgb, #22d3ee 10%, transparent);
    }
    .loc-external input:checked + .loc-card-content .loc-radio { border-color: #22d3ee; }
    .loc-external input:checked + .loc-card-content .loc-radio-inner { background: #22d3ee; }
    
    /* ── Period Cards ───────────────────────────── */
    
    /* ── Period Cards ───────────────────────────── */
    .period-cards {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .period-card {
      flex: 1;
      cursor: pointer;
    }
    .period-card input {
      display: none;
    }
    .period-card-content {
      border: 1px solid rgba(255,255,255,0.08);
      background: rgba(255,255,255,0.02);
      border-radius: 8px;
      padding: 8px 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: all 0.2s;
    }
    .period-card:hover .period-card-content {
      background: rgba(255,255,255,0.05);
    }
    .period-card input:checked + .period-card-content {
      border-color: var(--primary);
      background: color-mix(in srgb, var(--primary) 10%, transparent);
    }
    .period-title {
      font-weight: 600;
      color: #fff;
      font-size: 0.85rem;
    }
    .period-time {
      font-size: 0.75rem;
      color: var(--text-muted);
    }

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
  </style>


</body>

</html>