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
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item" href="/admin/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Management</span>
      <a class="nav-item" href="/admin/users"><span class="nav-icon">👥</span> Users</a>
      <a class="nav-item" href="/admin/events"><span class="nav-icon">📅</span> Events</a>
      <a class="nav-item active" href="/admin/venues"><span class="nav-icon">🏛️</span> Venues</a>
      <a class="nav-item" href="/admin/verifications"><span class="nav-icon">🛡️</span> Verifications</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Venues</h1><p class="page-subtitle">Manage event locations</p></div>
      <div class="topbar-actions">
        <button class="btn btn-primary" onclick="openModal()">+ Add Venue</button>
      </div>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Name</th><th>Location</th><th>Capacity</th><th>Status</th><th>Maintenance</th><th>Actions</th></tr></thead>
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
      <h3 class="modal-title" id="modal-title">Add Venue</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form id="venue-form">
      <input type="hidden" id="venue-id"/>
      <div class="form-group">
        <label class="form-label">Venue Name</label>
        <input id="v-name" type="text" class="form-control" placeholder="City Hall" required/>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Google Maps Link</label>
          <input id="v-location" type="url" class="form-control" placeholder="https://maps.google.com/..." required/>
          <small style="color:var(--text-muted);font-size:12px;margin-top:4px;display:block">Open Google Maps → Select location → Copy share link and paste here</small>
        </div>
        <div class="form-group">
          <label class="form-label">Capacity</label>
          <input id="v-capacity" type="number" class="form-control" placeholder="500" min="1" required/>
        </div>
      </div>

      <!-- Period Settings -->
      <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:16px;margin-bottom:16px">
        <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.35);margin-bottom:12px">⏰ Period Settings</div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Morning Start</label>
            <input id="v-morning-start" type="time" class="form-control" value="09:00" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Morning End</label>
            <input id="v-morning-end" type="time" class="form-control" value="13:00" required/>
          </div>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Evening Start</label>
            <input id="v-evening-start" type="time" class="form-control" value="15:00" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Evening End</label>
            <input id="v-evening-end" type="time" class="form-control" value="19:00" required/>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Status</label>
        <select id="v-status" class="form-control">
          <option value="available">Available</option>
          <option value="maintenance">Maintenance (Full Lockdown)</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="save-btn">Save Venue</button>
      </div>
    </form>
  </div>
</div>

<!-- Maintenance Schedule Modal -->
<div class="modal-overlay" id="maintenance-modal">
  <div class="modal" style="max-width:560px; max-height:85vh; overflow-y:auto;">
    <div class="modal-header">
      <h3 class="modal-title">🔧 Schedule Maintenance — <span id="maint-venue-name"></span></h3>
      <button class="modal-close" onclick="closeMaintenanceModal()">✕</button>
    </div>
    
    <!-- Add New Period -->
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:20px;margin-bottom:20px">
      <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.35);margin-bottom:16px">📅 Add Maintenance Period</div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Start Date</label>
          <input id="m-start" type="text" class="form-control" placeholder="Select start date..." readonly required/>
        </div>
        <div class="form-group">
          <label class="form-label">End Date</label>
          <input id="m-end" type="text" class="form-control" placeholder="Select end date..." readonly required/>
        </div>
      </div>
      <div style="display: flex; gap: 14px; margin: 10px 0 14px; font-size: 12px; color: rgba(255,255,255,0.45); flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 5px;">
          <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#ef4444;"></span>
          Booked (Event)
        </div>
        <div style="display: flex; align-items: center; gap: 5px;">
          <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:repeating-linear-gradient(45deg,rgba(245,158,11,0.6),rgba(245,158,11,0.6) 2px,rgba(239,68,68,0.6) 2px,rgba(239,68,68,0.6) 4px);border:1px solid #f59e0b;"></span>
          Maintenance
        </div>
        <div style="display: flex; align-items: center; gap: 5px;">
          <span style="display:inline-block;width:10px;height:10px;border-radius:3px;border:2px solid #8b5cf6;"></span>
          Today
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Reason (Optional)</label>
        <input id="m-reason" type="text" class="form-control" placeholder="e.g. Annual renovation, Equipment upgrade..."/>
      </div>
      <button type="button" class="btn btn-primary" onclick="addMaintenancePeriod()" style="width:100%;margin-top:8px">
        + Add Maintenance Period
      </button>
    </div>

    <!-- Existing Periods List -->
    <div>
      <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.35);margin-bottom:12px">📋 Scheduled Maintenance Periods</div>
      <div id="maintenance-list">
        <div style="text-align:center;color:var(--text-muted);padding:20px">Loading...</div>
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
    if (!res.ok || !res.data.length) { tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">🏛️</div><p>No venues yet. Add one!</p></div></td></tr>'; return; }
    tbody.innerHTML = res.data.map((v, i) => {
      // Count upcoming maintenance periods
      const now = new Date().toISOString().split('T')[0];
      const upcomingMaint = (v.maintenance_periods || []).filter(p => p.end_date >= now);
      const activeMaint = (v.maintenance_periods || []).filter(p => p.start_date <= now && p.end_date >= now);
      
      let statusHtml;
      if (v.status === 'maintenance') {
        statusHtml = '<span class="badge" style="background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3)">🔒 Full Lockdown</span>';
      } else if (activeMaint.length > 0) {
        statusHtml = '<span class="badge" style="background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3)">🔧 Under Maintenance</span>';
      } else {
        statusHtml = '<span class="badge" style="background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3)">✅ Available</span>';
      }

      const maintBadge = upcomingMaint.length > 0
        ? `<span class="badge" style="background:rgba(245,158,11,0.12);color:#f59e0b;border:1px solid rgba(245,158,11,0.25);font-size:11px">${upcomingMaint.length} scheduled</span>`
        : '<span style="color:var(--text-muted);font-size:12px">None</span>';

      return `
      <tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td><div style="font-weight:600">${v.name}</div></td>
        <td>${v.location && v.location.startsWith('http') ? `<a href="${v.location}" target="_blank" rel="noopener" style="color:var(--primary);text-decoration:none;display:inline-flex;align-items:center;gap:4px">📍 Open in Maps <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></a>` : `<span style="color:var(--text-muted)">📍 ${v.location || '—'}</span>`}</td>
        <td style="color:var(--text-muted)">${v.capacity.toLocaleString()}</td>
        <td>${statusHtml}</td>
        <td>${maintBadge}</td>
        <td style="display:flex;gap:6px;padding:14px 16px">
          <button class="btn btn-ghost btn-sm" onclick='editVenue(${JSON.stringify(v)})'>✏️ Edit</button>
          <button class="btn btn-sm" style="background:rgba(245,158,11,0.1);color:#f59e0b;border:1px solid rgba(245,158,11,0.25)" onclick='openMaintenanceModal(${v.id}, ${JSON.stringify(v.name)})'>🔧 Maintenance</button>
        </td>
      </tr>`;
    }).join('');
  }

  function openModal(v = null) {
    editingId = v ? v.id : null;
    document.getElementById('modal-title').textContent = v ? 'Edit Venue' : 'Add Venue';
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
    if (res.ok) { showToast(editingId ? 'Venue updated!' : 'Venue added!', 'success'); closeModal(); loadVenues(); }
    else showToast(res.data?.message || 'Error', 'error');
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
      container.innerHTML = '<div style="text-align:center;color:var(--text-muted);padding:24px;background:rgba(255,255,255,0.02);border:1px dashed rgba(255,255,255,0.08);border-radius:10px"><div style="font-size:2rem;margin-bottom:8px">📋</div><p style="margin:0">No maintenance periods scheduled</p></div>';
      return;
    }

    const now = new Date().toISOString().split('T')[0];
    container.innerHTML = res.data.map(p => {
      const isActive = p.start_date <= now && p.end_date >= now;
      const isPast = p.end_date < now;
      const isFuture = p.start_date > now;
      
      let statusBadge;
      if (isActive) statusBadge = '<span style="background:rgba(245,158,11,0.15);color:#f59e0b;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700">🔧 ACTIVE NOW</span>';
      else if (isPast) statusBadge = '<span style="background:rgba(107,114,128,0.15);color:#6b7280;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700">✓ COMPLETED</span>';
      else statusBadge = '<span style="background:rgba(59,130,246,0.15);color:#3b82f6;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700">⏳ UPCOMING</span>';

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
              <span style="color:var(--text-muted);font-size:11px">${days} day${days > 1 ? 's' : ''}</span>
            </div>
            <div style="font-weight:600;font-size:0.9rem;color:#fff;margin-bottom:4px">
              📅 ${startFmt} → ${endFmt}
            </div>
            ${p.reason ? `<div style="color:var(--text-muted);font-size:0.8rem">💬 ${p.reason}</div>` : ''}
          </div>
          ${!isPast ? `<button class="btn btn-sm" style="background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.25)" onclick="deleteMaintenancePeriod(${p.id})">🗑️</button>` : ''}
        </div>`;
    }).join('');
  }

  async function addMaintenancePeriod() {
    const startDate = document.getElementById('m-start').value;
    const endDate = document.getElementById('m-end').value;
    const reason = document.getElementById('m-reason').value;

    if (!startDate || !endDate) {
      showToast('Please select both start and end dates', 'error');
      return;
    }

    const res = await api.post(`/venues/${currentMaintenanceVenueId}/maintenance`, {
      start_date: startDate,
      end_date: endDate,
      reason: reason || null,
    });

    if (res.ok) {
      showToast('Maintenance period scheduled!', 'success');
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
              <span style="font-size:1.3rem">⛔</span>
              <span style="font-weight:700;color:#ef4444;font-size:0.9rem">Cannot Schedule Maintenance</span>
            </div>
            <p style="color:rgba(255,255,255,0.8);font-size:0.82rem;margin:0 0 8px;line-height:1.6">
              The following events are already booked in this date range:
            </p>
            <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:8px">
              ${data.conflicting_events.map(name => `
                <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;background:rgba(239,68,68,0.06);border-radius:8px;border:1px solid rgba(239,68,68,0.15)">
                  <span style="font-size:0.85rem">📅</span>
                  <span style="font-weight:600;color:#fff;font-size:0.82rem">${name}</span>
                </div>
              `).join('')}
            </div>
            <p style="color:rgba(255,255,255,0.5);font-size:0.75rem;margin:0;font-style:italic">
              💡 Please choose dates that don't conflict with existing bookings.
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
        showToast(data.message || 'Error scheduling maintenance', 'error');
      }
    }
  }

  async function deleteMaintenancePeriod(periodId) {
    if (!confirm('Are you sure you want to remove this maintenance period?')) return;
    
    const res = await api.delete(`/venues/${currentMaintenanceVenueId}/maintenance/${periodId}`);
    if (res.ok) {
      showToast('Maintenance period removed', 'success');
      loadMaintenancePeriods();
      loadVenues();
      // Refresh bookings and calendar
      const bookRes = await api.get(`/venues/${currentMaintenanceVenueId}/bookings`);
      adminVenueBookings = bookRes.ok ? bookRes.data : [];
      initMaintFlatpickrs();
    } else {
      showToast(res.data?.message || 'Error', 'error');
    }
  }

  // Venue deletion is disabled to preserve archive history
</script>
</body>
</html>
