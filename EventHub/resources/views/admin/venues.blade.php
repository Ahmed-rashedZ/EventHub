<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Venues – EventHub Admin</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="/js/i18n.js"></script>
  <style>
    /* Admin Maintenance Flatpickr Styles */
    .maint-flatpickr {
      background: #0f1219 !important;
      border: 1px solid rgba(139, 92, 246, 0.12) !important;
      border-radius: 14px !important;
      padding: 18px !important;
      font-family: 'Inter', system-ui, sans-serif !important;
      box-shadow: 0 12px 32px rgba(0,0,0,0.6) !important;
    }

    /* Month navigation - transparent background */
    .maint-flatpickr .flatpickr-months {
      margin-bottom: 12px !important;
      background: transparent !important;
      border: none !important;
    }
    .maint-flatpickr .flatpickr-months .flatpickr-month {
      background: transparent !important;
      border: none !important;
      overflow: visible !important;
    }
    .maint-flatpickr .flatpickr-current-month {
      color: #f1f5f9 !important;
      font-weight: 700 !important;
      font-size: 1rem !important;
      background: transparent !important;
    }
    .maint-flatpickr .flatpickr-current-month .flatpickr-monthDropdown-months {
      background: transparent !important;
      color: #f1f5f9 !important;
      font-weight: 700 !important;
      font-size: 1rem !important;
      border: none !important;
    }
    .maint-flatpickr .flatpickr-current-month .numInputWrapper {
      background: transparent !important;
    }
    .maint-flatpickr .flatpickr-current-month .numInputWrapper span {
      display: none !important;
    }
    .maint-flatpickr .flatpickr-current-month .numInputWrapper input.cur-year {
      color: #f1f5f9 !important;
      font-weight: 700 !important;
      font-size: 1rem !important;
      background: transparent !important;
    }

    /* Arrow buttons */
    .maint-flatpickr .flatpickr-months .flatpickr-prev-month,
    .maint-flatpickr .flatpickr-months .flatpickr-next-month {
      fill: #64748b !important;
      color: #64748b !important;
      background: transparent !important;
      border: none !important;
      border-radius: 8px !important;
      transition: all 0.2s ease !important;
    }
    .maint-flatpickr .flatpickr-months .flatpickr-prev-month:hover,
    .maint-flatpickr .flatpickr-months .flatpickr-next-month:hover {
      fill: #c4b5fd !important;
      color: #c4b5fd !important;
      background: rgba(139, 92, 246, 0.1) !important;
    }

    /* Weekday labels */
    .maint-flatpickr .flatpickr-weekdays {
      background: transparent !important;
      border: none !important;
    }
    .maint-flatpickr span.flatpickr-weekday {
      color: #64748b !important;
      font-weight: 600 !important;
      font-size: 0.7rem !important;
      text-transform: uppercase !important;
      letter-spacing: 0.04em !important;
      background: transparent !important;
    }

    /* Day cells */
    .maint-flatpickr .flatpickr-day {
      color: #cbd5e1 !important;
      border-radius: 8px !important;
      border: 1px solid rgba(255,255,255,0.05) !important;
      background: rgba(255,255,255,0.02) !important;
      transition: all 0.15s ease !important;
    }
    .maint-flatpickr .flatpickr-day:hover {
      background: rgba(139, 92, 246, 0.08) !important;
      border-color: rgba(139, 92, 246, 0.2) !important;
      color: #f1f5f9 !important;
    }
    .maint-flatpickr .flatpickr-day.today {
      border: 2px solid #8b5cf6 !important;
      background: rgba(139, 92, 246, 0.05) !important;
      color: #c4b5fd !important;
    }
    .maint-flatpickr .flatpickr-day.selected {
      background: linear-gradient(135deg, #8b5cf6, #7c3aed) !important;
      border-color: #8b5cf6 !important;
      color: #fff !important;
      box-shadow: 0 3px 12px rgba(139, 92, 246, 0.35) !important;
    }
    .maint-flatpickr .flatpickr-day.flatpickr-disabled {
      color: rgba(255,255,255,0.12) !important;
    }

    /* Booked event days */
    .maint-flatpickr .flatpickr-day.admin-booked {
      background: rgba(239, 68, 68, 0.18) !important;
      border-color: rgba(239, 68, 68, 0.5) !important;
      color: #fca5a5 !important;
      cursor: not-allowed !important;
    }
    .maint-flatpickr .flatpickr-day.admin-booked:hover {
      background: rgba(239, 68, 68, 0.25) !important;
    }

    /* Existing maintenance days */
    .maint-flatpickr .flatpickr-day.admin-maint-existing {
      background: 
        repeating-linear-gradient(
          -45deg,
          transparent,
          transparent 3px,
          rgba(245, 158, 11, 0.12) 3px,
          rgba(245, 158, 11, 0.12) 6px
        ),
        rgba(245, 158, 11, 0.06) !important;
      border: 1.5px solid rgba(245, 158, 11, 0.45) !important;
      color: #fbbf24 !important;
      cursor: not-allowed !important;
    }

    .maint-flatpickr .flatpickr-day.prevMonthDay,
    .maint-flatpickr .flatpickr-day.nextMonthDay {
      opacity: 0.15 !important;
      background: transparent !important;
      border-color: transparent !important;
    }

    .maint-flatpickr .flatpickr-calendar.arrowTop:before,
    .maint-flatpickr .flatpickr-calendar.arrowTop:after {
      display: none !important;
    }
  </style>
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
      <div><h1 class="page-title"><script>document.write(t('Venues'))</script></h1><p class="page-subtitle"><script>document.write(t('Manage event locations'))</script></p></div>
      <div class="topbar-actions">
        <button class="btn btn-primary" onclick="openModal()">+ <script>document.write(t('Add Venue'))</script></button>
      </div>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th><script>document.write(t('Name'))</script></th><th><script>document.write(t('Location'))</script></th><th><script>document.write(t('Capacity'))</script></th><th><script>document.write(t('Status'))</script></th><th><script>document.write(t('Maintenance'))</script></th><th><script>document.write(t('Actions'))</script></th></tr></thead>
          <tbody id="venues-body">
            <tr class="loading-row"><td colspan="7"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="venue-modal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-title"><script>document.write(t('Add Venue'))</script></h3>
      <button class="modal-close" onclick="closeModal()">&times;</button>
    </div>
    <form id="venue-form">
      <input type="hidden" id="venue-id"/>
      <div class="form-group">
        <label class="form-label"><script>document.write(t('Venue Name'))</script></label>
        <input id="v-name" type="text" class="form-control" placeholder="City Hall" 
          data-placeholder="City Hall" required/>
        <script>document.getElementById('v-name').placeholder = t(document.getElementById('v-name').getAttribute('data-placeholder'))</script>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label"><script>document.write(t('Google Maps Link'))</script></label>
          <input id="v-location" type="url" class="form-control" placeholder="https://maps.google.com/..." 
            data-placeholder="https://maps.google.com/..." required/>
          <script>document.getElementById('v-location').placeholder = t(document.getElementById('v-location').getAttribute('data-placeholder'))</script>
          <small style="color:var(--text-muted);font-size:12px;margin-top:4px;display:block"><script>document.write(t('Open Google Maps → Select location → Copy share link and paste here'))</script></small>
        </div>
        <div class="form-group">
          <label class="form-label"><script>document.write(t('Capacity'))</script></label>
          <input id="v-capacity" type="number" class="form-control" placeholder="500" min="1" required/>
        </div>
      </div>

      <!-- Period Settings -->
      <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:16px;margin-bottom:16px">
        <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.35);margin-bottom:12px"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> <script>document.write(t('Period Settings'))</script></div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label"><script>document.write(t('Morning Start'))</script></label>
            <input id="v-morning-start" type="time" class="form-control" value="09:00" required/>
          </div>
          <div class="form-group">
            <label class="form-label"><script>document.write(t('Morning End'))</script></label>
            <input id="v-morning-end" type="time" class="form-control" value="13:00" required/>
          </div>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label"><script>document.write(t('Evening Start'))</script></label>
            <input id="v-evening-start" type="time" class="form-control" value="15:00" required/>
          </div>
          <div class="form-group">
            <label class="form-label"><script>document.write(t('Evening End'))</script></label>
            <input id="v-evening-end" type="time" class="form-control" value="19:00" required/>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label"><script>document.write(t('Status'))</script></label>
        <select id="v-status" class="form-control">
          <option value="available"><script>document.write(t('Available'))</script></option>
          <option value="maintenance"><script>document.write(t('Maintenance (Full Lockdown)'))</script></option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()"><script>document.write(t('Cancel'))</script></button>
        <button type="submit" class="btn btn-primary" id="save-btn"><script>document.write(t('Save Venue'))</script></button>
      </div>
    </form>
  </div>
</div>

<!-- Maintenance Schedule Modal -->
<div class="modal-overlay" id="maintenance-modal">
  <div class="modal" style="max-width:560px; max-height:85vh; overflow-y:auto;">
    <div class="modal-header">
      <h3 class="modal-title"><script>document.write(t('Schedule Maintenance'))</script> — <span id="maint-venue-name"></span></h3>
      <button class="modal-close" onclick="closeMaintenanceModal()">&times;</button>
    </div>
    
    <!-- Add New Period -->
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:20px;margin-bottom:20px">
      <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.35);margin-bottom:16px"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg> <script>document.write(t('Add Maintenance Period'))</script></div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label"><script>document.write(t('Start Date'))</script></label>
          <input id="m-start" type="text" class="form-control" placeholder="Select start date..." 
            data-placeholder="Select start date..." readonly required/>
          <script>document.getElementById('m-start').placeholder = t(document.getElementById('m-start').getAttribute('data-placeholder'))</script>
        </div>
        <div class="form-group">
          <label class="form-label"><script>document.write(t('End Date'))</script></label>
          <input id="m-end" type="text" class="form-control" placeholder="Select end date..." 
            data-placeholder="Select end date..." readonly required/>
          <script>document.getElementById('m-end').placeholder = t(document.getElementById('m-end').getAttribute('data-placeholder'))</script>
        </div>
      </div>
      <div style="display: flex; gap: 14px; margin: 10px 0 14px; font-size: 12px; color: rgba(255,255,255,0.45); flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 5px;">
          <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#ef4444;"></span>
          <script>document.write(t('Booked (Event)'))</script>
        </div>
        <div style="display: flex; align-items: center; gap: 5px;">
          <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:repeating-linear-gradient(45deg,rgba(245,158,11,0.6),rgba(245,158,11,0.6) 2px,rgba(239,68,68,0.6) 2px,rgba(239,68,68,0.6) 4px);border:1px solid #f59e0b;"></span>
          <script>document.write(t('Maintenance'))</script>
        </div>
        <div style="display: flex; align-items: center; gap: 5px;">
          <span style="display:inline-block;width:10px;height:10px;border-radius:3px;border:2px solid #8b5cf6;"></span>
          <script>document.write(t('Today'))</script>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label"><script>document.write(t('Reason (Optional)'))</script></label>
        <input id="m-reason" type="text" class="form-control" placeholder="e.g. Annual renovation, Equipment upgrade..." 
          data-placeholder="e.g. Annual renovation, Equipment upgrade..."/>
        <script>document.getElementById('m-reason').placeholder = t(document.getElementById('m-reason').getAttribute('data-placeholder'))</script>
      </div>
      <button type="button" class="btn btn-primary" onclick="addMaintenancePeriod()" style="width:100%;margin-top:8px">
        + <script>document.write(t('Add Maintenance Period'))</script>
      </button>
    </div>

    <!-- Existing Periods List -->
    <div>
      <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.35);margin-bottom:12px"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg> <script>document.write(t('Scheduled Maintenance Periods'))</script></div>
      <div id="maintenance-list">
        <div style="text-align:center;color:var(--text-muted);padding:20px"><script>document.write(t('Loading...'))</script></div>
      </div>
    </div>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script>
  let editingId = null;
  let currentMaintenanceVenueId = null;
  let adminVenueBookings = [];
  let fpStartInstance = null;
  let fpEndInstance = null;
  const user = requireRole('Admin');
  if (user) { populateSidebar(user); setActiveNav(); loadVenues(); }

  // Initialize flatpickr for maintenance date pickers
  const todayStr = new Date().toISOString().split('T')[0];

  function initMaintFlatpickrs() {
    // Destroy existing instances
    if (fpStartInstance) { fpStartInstance.destroy(); fpStartInstance = null; }
    if (fpEndInstance) { fpEndInstance.destroy(); fpEndInstance = null; }

    const disableFn = function(date) {
      if (!adminVenueBookings || !adminVenueBookings.length) return false;
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const d = String(date.getDate()).padStart(2, '0');
      const ds = `${y}-${m}-${d}`;
      return adminVenueBookings.some(b => b.booking_date === ds);
    };

    const dayCreateFn = function(dObj, dStr, fp, dayElem) {
      dayElem.classList.remove('admin-booked', 'admin-maint-existing');
      if (!adminVenueBookings || !adminVenueBookings.length) return;
      const y = dayElem.dateObj.getFullYear();
      const m = String(dayElem.dateObj.getMonth() + 1).padStart(2, '0');
      const d = String(dayElem.dateObj.getDate()).padStart(2, '0');
      const ds = `${y}-${m}-${d}`;
      const bookings = adminVenueBookings.filter(b => b.booking_date === ds);
      if (bookings.length > 0) {
        const hasMaint = bookings.some(b => b.type === 'maintenance');
        if (hasMaint) {
          dayElem.classList.add('admin-maint-existing');
        } else {
          dayElem.classList.add('admin-booked');
        }
      }
    };

    fpStartInstance = flatpickr('#m-start', {
      dateFormat: 'Y-m-d',
      minDate: 'today',
      disable: [disableFn],
      onDayCreate: dayCreateFn,
      appendTo: document.querySelector('#maintenance-modal .modal'),
      onChange: function(selectedDates) {
        if (selectedDates.length > 0 && fpEndInstance) {
          fpEndInstance.set('minDate', selectedDates[0]);
          const endVal = fpEndInstance.selectedDates[0];
          if (endVal && endVal < selectedDates[0]) {
            fpEndInstance.clear();
          }
        }
      }
    });

    fpEndInstance = flatpickr('#m-end', {
      dateFormat: 'Y-m-d',
      minDate: 'today',
      disable: [disableFn],
      onDayCreate: dayCreateFn,
      appendTo: document.querySelector('#maintenance-modal .modal'),
    });

    // Add custom class to calendars
    if (fpStartInstance.calendarContainer) fpStartInstance.calendarContainer.classList.add('maint-flatpickr');
    if (fpEndInstance.calendarContainer) fpEndInstance.calendarContainer.classList.add('maint-flatpickr');
  }

  async function loadVenues() {
    const res = await api.get('/venues');
    const tbody = document.getElementById('venues-body');
    if (!res.ok || !res.data.length) { tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><div class="empty-icon" style="display:flex; justify-content:center; margin-bottom:12px; color:var(--text-muted);"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></div><p>${t('No venues yet. Add one!')}</p></div></td></tr>`; return; }
    tbody.innerHTML = res.data.map((v, i) => {
      // Count upcoming maintenance periods
      const now = new Date().toISOString().split('T')[0];
      const upcomingMaint = (v.maintenance_periods || []).filter(p => p.end_date >= now);
      const activeMaint = (v.maintenance_periods || []).filter(p => p.start_date <= now && p.end_date >= now);
      
      let statusHtml;
      if (v.status === 'maintenance') {
        statusHtml = `<span class="badge" style="background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3)">${t('Full Lockdown')}</span>`;
      } else if (activeMaint.length > 0) {
        statusHtml = `<span class="badge" style="background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3)">${t('Under Maintenance')}</span>`;
      } else {
        statusHtml = `<span class="badge" style="background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3)">${t('Available')}</span>`;
      }

      const maintBadge = upcomingMaint.length > 0
        ? `<span class="badge" style="background:rgba(245,158,11,0.12);color:#f59e0b;border:1px solid rgba(245,158,11,0.25);font-size:11px">${upcomingMaint.length} ${t('scheduled')}</span>`
        : `<span style="color:var(--text-muted);font-size:12px">${t('None')}</span>`;

      return `
      <tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td><div style="font-weight:600">${v.name}</div></td>
        <td><div style="font-size:0.9rem; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${v.location && v.location.startsWith('http') ? `<a href="${v.location}" target="_blank" rel="noopener" style="color:var(--primary);text-decoration:none;display:inline-flex;align-items:center;gap:4px"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg> ${t('Open in Maps')}</a>` : `<span style="color:var(--text-muted)"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg> ${v.location || '—'}</span>`}</div></td>
        <td style="color:var(--text-muted)">${v.capacity.toLocaleString()}</td>
        <td>${statusHtml}</td>
        <td>${maintBadge}</td>
        <td style="display:flex;gap:6px;padding:14px 16px">
          <button class="btn btn-ghost btn-sm" onclick='editVenue(${JSON.stringify(v)})'>${t('Edit')}</button>
          <button class="btn btn-sm" style="background:rgba(245,158,11,0.1);color:#f59e0b;border:1px solid rgba(245,158,11,0.25)" onclick='openMaintenanceModal(${v.id}, ${JSON.stringify(v.name)})'>${t('Maintenance')}</button>
        </td>
      </tr>`;
    }).join('');
  }

  function openModal(v = null) {
    editingId = v ? v.id : null;
    document.getElementById('modal-title').textContent = v ? t('Edit Venue') : t('Add Venue');
    document.getElementById('v-name').value     = v ? v.name : '';
    document.getElementById('v-location').value = v ? (v.location || '') : '';
    document.getElementById('v-capacity').value = v ? v.capacity : '';
    document.getElementById('v-status').value   = v ? v.status : 'available';
    document.getElementById('v-morning-start').value = v ? v.morning_start : '09:00';
    document.getElementById('v-morning-end').value   = v ? v.morning_end : '13:00';
    document.getElementById('v-evening-start').value = v ? v.evening_start : '15:00';
    document.getElementById('v-evening-end').value   = v ? v.evening_end : '19:00';
    document.getElementById('venue-modal').classList.add('open');
  }

  function editVenue(v) { openModal(v); }

  function closeModal() { document.getElementById('venue-modal').classList.remove('open'); editingId = null; }

  document.getElementById('venue-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = { 
      name: document.getElementById('v-name').value, 
      location: document.getElementById('v-location').value, 
      capacity: +document.getElementById('v-capacity').value, 
      status: document.getElementById('v-status').value,
      morning_start: document.getElementById('v-morning-start').value,
      morning_end: document.getElementById('v-morning-end').value,
      evening_start: document.getElementById('v-evening-start').value,
      evening_end: document.getElementById('v-evening-end').value
    };
    const res = editingId ? await api.put(`/venues/${editingId}`, body) : await api.post('/venues', body);
    if (res.ok) { showToast(editingId ? t('Venue updated!') : t('Venue added!'), 'success'); closeModal(); loadVenues(); }
    else showToast(t(res.data?.message || 'Error'), 'error');
  });

  // ── Maintenance Modal ─────────────────────────────────────────────────

  async function openMaintenanceModal(venueId, venueName) {
    currentMaintenanceVenueId = venueId;
    document.getElementById('maint-venue-name').textContent = venueName;
    document.getElementById('m-reason').value = '';
    document.getElementById('maintenance-modal').classList.add('open');

    // Remove any old conflict alert
    const oldAlert = document.getElementById('conflict-alert');
    if (oldAlert) oldAlert.remove();

    // Load bookings for this venue to disable booked dates
    const bookRes = await api.get(`/venues/${venueId}/bookings`);
    adminVenueBookings = bookRes.ok ? bookRes.data : [];

    // Init flatpickr with the loaded bookings data
    initMaintFlatpickrs();

    loadMaintenancePeriods();
  }

  function closeMaintenanceModal() {
    document.getElementById('maintenance-modal').classList.remove('open');
    currentMaintenanceVenueId = null;
    adminVenueBookings = [];
    if (fpStartInstance) { fpStartInstance.destroy(); fpStartInstance = null; }
    if (fpEndInstance) { fpEndInstance.destroy(); fpEndInstance = null; }
  }

  async function loadMaintenancePeriods() {
    const container = document.getElementById('maintenance-list');
    container.innerHTML = '<div style="text-align:center;padding:16px"><div class="spinner" style="margin:auto"></div></div>';
    
    const res = await api.get(`/venues/${currentMaintenanceVenueId}/maintenance`);
    if (!res.ok || !res.data.length) {
      container.innerHTML = `<div style="text-align:center;color:var(--text-muted);padding:24px;background:rgba(255,255,255,0.02);border:1px dashed rgba(255,255,255,0.08);border-radius:10px"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 8px;color:var(--text-muted);display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg><p style="margin:0">${t('No maintenance periods scheduled')}</p></div>`;
      return;
    }

    const now = new Date().toISOString().split('T')[0];
    container.innerHTML = res.data.map(p => {
      const isActive = p.start_date <= now && p.end_date >= now;
      const isPast = p.end_date < now;
      const isFuture = p.start_date > now;
      
      let statusBadge;
      if (isActive) statusBadge = `<span style="background:rgba(245,158,11,0.15);color:#f59e0b;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700">${t('ACTIVE NOW')}</span>`;
      else if (isPast) statusBadge = `<span style="background:rgba(107,114,128,0.15);color:#6b7280;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700">${t('COMPLETED')}</span>`;
      else statusBadge = `<span style="background:rgba(59,130,246,0.15);color:#3b82f6;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700">${t('UPCOMING')}</span>`;

      // Calculate duration
      const start = new Date(p.start_date);
      const end = new Date(p.end_date);
      const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

      // Format dates nicely
      const fmtOptions = { year: 'numeric', month: 'short', day: 'numeric' };
      const startFmt = start.toLocaleDateString('en-US', fmtOptions);
      const endFmt = end.toLocaleDateString('en-US', fmtOptions);

      return `
        <div style="display:flex;align-items:center;gap:12px;padding:14px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px;margin-bottom:8px;${isActive ? 'border-left:3px solid #f59e0b;' : ''}${isPast ? 'opacity:0.5;' : ''}">
          <div style="flex:1">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
              ${statusBadge}
              <span style="color:var(--text-muted);font-size:11px">${days} ${t(days > 1 ? 'days' : 'day')}</span>
            </div>
            <div style="font-weight:600;font-size:0.9rem;color:#fff;margin-bottom:4px">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg> ${startFmt} → ${endFmt}
            </div>
            ${p.reason ? `<div style="color:var(--text-muted);font-size:0.8rem"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg> ${p.reason}</div>` : ''}
          </div>
          ${!isPast ? `<button class="btn btn-sm" style="background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.25);display:inline-flex;align-items:center;justify-content:center;padding:6px 8px;" onclick="deleteMaintenancePeriod(${p.id})"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>` : ''}
        </div>`;
    }).join('');
  }

  async function addMaintenancePeriod() {
    const startDate = document.getElementById('m-start').value;
    const endDate = document.getElementById('m-end').value;
    const reason = document.getElementById('m-reason').value;

    if (!startDate || !endDate) {
      showToast(t('Please select both start and end dates'), 'error');
      return;
    }

    const res = await api.post(`/venues/${currentMaintenanceVenueId}/maintenance`, {
      start_date: startDate,
      end_date: endDate,
      reason: reason || null,
    });

    if (res.ok) {
      showToast(t('Maintenance period scheduled!'), 'success');
      document.getElementById('m-reason').value = '';
      loadMaintenancePeriods();
      loadVenues();
      // Refresh bookings and calendar
      const bookRes = await api.get(`/venues/${currentMaintenanceVenueId}/bookings`);
      adminVenueBookings = bookRes.ok ? bookRes.data : [];
      initMaintFlatpickrs();
    } else {
      // Show detailed conflict error
      const data = res.data || {};
      if (data.conflicting_events && data.conflicting_events.length > 0) {
        const conflictList = document.getElementById('maintenance-list');
        const evNames = data.conflicting_events.map(n => `<span style="font-weight:600;color:#fff">${n}</span>`).join('، ');
        const dates = (data.conflicting_dates || []).join('، ');
        
        // Show inline conflict alert above the list
        const alertHtml = `
          <div id="conflict-alert" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:16px;margin-bottom:12px;animation:fadeIn 0.3s ease">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#ef4444;vertical-align:middle;margin-inline-end:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
              <span style="font-weight:700;color:#ef4444;font-size:0.9rem">${t('Cannot Schedule Maintenance')}</span>
            </div>
            <p style="color:rgba(255,255,255,0.8);font-size:0.82rem;margin:0 0 8px;line-height:1.6">
              ${t('The following events are already booked in this date range:')}
            </p>
            <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:8px">
              ${data.conflicting_events.map(name => `
                <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;background:rgba(239,68,68,0.06);border-radius:8px;border:1px solid rgba(239,68,68,0.15)">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  <span style="font-weight:600;color:#fff;font-size:0.82rem">${name}</span>
                </div>
              `).join('')}
            </div>
            <p style="color:rgba(255,255,255,0.5);font-size:0.75rem;margin:0;font-style:italic">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-inline-end:4px;color:#f59e0b;"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg> ${t('Please choose dates that don\'t conflict with existing bookings.')}
            </p>
          </div>
        `;

        // Remove any existing conflict alert first
        const existingAlert = document.getElementById('conflict-alert');
        if (existingAlert) existingAlert.remove();
        
        conflictList.insertAdjacentHTML('beforebegin', alertHtml);
        
        // Auto-remove after 8 seconds
        setTimeout(() => {
          const alert = document.getElementById('conflict-alert');
          if (alert) {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
          }
        }, 8000);
      } else {
        showToast(t(data.message || 'Error scheduling maintenance'), 'error');
      }
    }
  }

  async function deleteMaintenancePeriod(periodId) {
    if (!confirm(t('Are you sure you want to remove this maintenance period?'))) return;
    
    const res = await api.delete(`/venues/${currentMaintenanceVenueId}/maintenance/${periodId}`);
    if (res.ok) {
      showToast(t('Maintenance period removed'), 'success');
      loadMaintenancePeriods();
      loadVenues();
      // Refresh bookings and calendar
      const bookRes = await api.get(`/venues/${currentMaintenanceVenueId}/bookings`);
      adminVenueBookings = bookRes.ok ? bookRes.data : [];
      initMaintFlatpickrs();
    } else {
      showToast(t(res.data?.message || 'Error'), 'error');
    }
  }

  // Venue deletion is disabled to preserve archive history
</script>
</body>
</html>






