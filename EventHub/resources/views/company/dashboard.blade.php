<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Company Dashboard – EventHub</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <style>
    .switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 26px;
    }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: rgba(255,255,255,0.1);
      transition: .4s;
      border-radius: 34px;
    }
    .slider:before {
      position: absolute;
      content: "";
      height: 18px; width: 18px;
      left: 4px; bottom: 4px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }
    input:checked + .slider { background-color: var(--success); }
    input:checked + .slider:before { transform: translateX(24px); }
    
    .availability-card {
      margin-bottom: 28px;
      padding: 24px;
      border-radius: var(--radius);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: var(--bg-card);
      border: 1px solid var(--border);
      transition: all 0.3s ease;
    }
    .availability-card.active-status {
      border-color: rgba(34, 197, 94, 0.3);
      background: rgba(34, 197, 94, 0.03);
    }
    .completion-bar {
      width: 100%;
      height: 8px;
      background: rgba(255,255,255,0.1);
      border-radius: 10px;
      margin-top: 10px;
      overflow: hidden;
    }
    .completion-fill {
      height: 100%;
      background: var(--primary);
      transition: width 0.5s ease;
    }
  </style>
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
        <h1 class="page-title">Company Dashboard</h1>
        <p class="page-subtitle">Manage your exhibition participation and booth bookings</p>
      </div>
      <div class="topbar-actions">
        <a href="/company/events" class="btn btn-primary">Browse Exhibitions</a>
      </div>
    </div>

    <!-- Hidden Warning -->
    <div id="hidden-alert" style="display:none; background:rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 16px; border-radius: var(--radius); margin-bottom: 24px; color: #ff6b6b; font-weight: 500;">
      <div style="display:flex; align-items:flex-start; gap:12px;">
        <span style="font-size:1.5rem;">⚠️</span>
        <p style="margin:0; font-size:0.95rem; line-height:1.5;">
          You are currently hidden from exhibition managers. Turn on "Open to Invitations" to be visible to organizers.
        </p>
      </div>
    </div>

    <div class="availability-card" id="avail-card" style="display:none">
      <div>
        <h3 id="avail-title" style="margin:0; font-size:1.1rem; font-weight:700;">Open to Invitations</h3>
        <p id="avail-desc" style="margin:4px 0 0; font-size:0.85rem; color:var(--text-muted);">You are visible to event managers who may invite you to exhibitions.</p>
      </div>
      <div style="display:flex; align-items:center; gap:12px;">
        <span id="avail-badge" class="badge">--</span>
        <label class="switch">
          <input type="checkbox" id="avail-toggle" disabled onchange="handleToggle(this.checked)">
          <span class="slider"></span>
        </label>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Accepted Apps</div>
        <div class="stat-value" id="stat-accepted">—</div>
        <div class="stat-icon">✅</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Pending Apps</div>
        <div class="stat-value" id="stat-pending">—</div>
        <div class="stat-icon">⏳</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Acceptance Rate</div>
        <div class="stat-value" id="stat-rate">—</div>
        <div class="stat-icon">📈</div>
      </div>
    </div>

    <div class="card" style="margin-top:24px;">
      <div class="card-header">
        <span class="card-title">Recent Applications</span>
        <a href="/company/exhibitions" class="btn btn-ghost btn-sm">View Status</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Exhibition</th><th>Date Selected</th><th>Booth Info</th><th>Status</th></tr></thead>
          <tbody id="history-body">
            <tr class="loading-row"><td colspan="4"><div class="spinner" style="margin:auto"></div></td></tr>
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
  const user = requireRole('Company');
  if (user) {
    populateSidebar(user);
    setActiveNav();
    loadDashboard();
  }

  async function loadDashboard() {
    // 1. Availability from Profile
    const profRes = await api.get('/profile');
    if (profRes.ok) {
        syncToggleUI(profRes.data.user?.profile?.is_available);
    }

    // 2. Stats and History from Analytics
    const res = await api.get('/company/analytics');
    if (!res.ok) {
        document.getElementById('history-body').innerHTML = '<tr><td colspan="4" style="text-align:center">Failed to load data</td></tr>';
        return;
    }

    const data = res.data;
    document.getElementById('stat-accepted').textContent = data.accepted;
    document.getElementById('stat-pending').textContent = data.pending;
    document.getElementById('stat-rate').textContent = data.acceptance_rate + '%';

    const tbody = document.getElementById('history-body');
    if (!data.application_history.length) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted)">${t('No applications yet.')}</td></tr>`;
        return;
    }

    tbody.innerHTML = data.application_history.map(app => `
      <tr>
        <td style="font-weight:600" class="i18n-skip">${app.event_title}</td>
        <td style="color:var(--text-muted)">${app.submitted_at}</td>
        <td style="color:var(--text-muted)">
            ${app.booth ? `${t('Booth')} #${app.booth.number} (${app.booth.size})` : `<span style="font-size:0.8rem;opacity:0.6">${t('Not assigned yet')}</span>`}
        </td>
        <td>
           <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            ${badge(app.status)}
            ${app.status === 'pending' && app.initiator === 'event_manager' ? `
              <button class="btn btn-success btn-sm" style="padding:4px 10px;" onclick="respondRequest(${app.application_id}, 'accepted')">✅ ${t('Accept')}</button>
              <button class="btn btn-danger btn-sm" style="padding:4px 10px;" onclick="respondRequest(${app.application_id}, 'rejected')">❌ ${t('Reject')}</button>
            ` : ''}
            ${app.status === 'negotiating' ? `<a href="/company/exhibitions" class="btn btn-sm btn-primary" style="padding:4px 10px;">${t('Review Offer')}</a>` : ''}
            <button class="btn btn-sm btn-ghost" style="padding:4px 10px;" onclick="showEventDetails(${app.event_id})" title="${t('View Details')}">ℹ️ ${t('Details')}</button>
           </div>
        </td>
      </tr>
    `).join('');
  }

  async function respondRequest(id, status) {
    if (!confirm(t('Are you sure you want to ') + t(status) + t(' this invitation?'))) return;
    const res = await api.put(`/exhibition/${id}`, { status });
    if (res.ok) {
      showToast(status === 'accepted' ? t('Accepted! Contract negotiation started.') : t('Invitation rejected.'), 'success');
      loadDashboard(); // reload table
    } else {
      showToast(res.data?.message || t('Error processing request'), 'error');
    }
  }

  function syncToggleUI(isON) {
    const card = document.getElementById('avail-card');
    const title = document.getElementById('avail-title');
    const desc = document.getElementById('avail-desc');
    const badgeEl = document.getElementById('avail-badge');
    const alertBox = document.getElementById('hidden-alert');
    const toggle = document.getElementById('avail-toggle');

    toggle.checked = !!isON;
    toggle.disabled = false;

    if (isON) {
      card.classList.add('active-status');
      title.innerText = t('Open to Invitations');
      title.style.color = 'var(--success)';
      badgeEl.innerText = t('Visible');
      badgeEl.className = 'badge badge-approved';
      alertBox.style.display = 'none';
    } else {
      card.classList.remove('active-status');
      title.innerText = t('Closed to Invitations');
      title.style.color = 'var(--text-muted)';
      badgeEl.innerText = t('Hidden');
      badgeEl.className = 'badge badge-rejected';
      alertBox.style.display = 'block';
    }
    card.style.display = 'flex';
  }

  async function handleToggle(isNowChecked) {
    const toggle = document.getElementById('avail-toggle');
    toggle.disabled = true;
    const res = await api.patch('/profile/availability', { is_available: !!isNowChecked });
    if (res.ok) {
        syncToggleUI(res.data.is_available);
        showToast(isNowChecked ? t('You are now visible to managers.') : t('You are now hidden.'), 'info');
    }
    toggle.disabled = false;
  }
  const typeIcons = { 'مؤتمر': '🎙️', 'ندوة': '📖', 'ورشة عمل': '🔧', 'دورة تدريبية': '🎓', 'ترفيه': '🎭', 'ملتقى علمي': '🔬', 'رياضة': '⚽', 'تقنية': '💻', 'اجتماعية': '🤝', 'معرض': '🎪', 'Other': '📌' };
  const typeColors = { 'مؤتمر': '#3b82f6', 'ندوة': '#8b5cf6', 'ورشة عمل': '#10b981', 'دورة تدريبية': '#06b6d4', 'ترفيه': '#ec4899', 'ملتقى علمي': '#f59e0b', 'رياضة': '#22c55e', 'تقنية': '#6366f1', 'اجتماعية': '#f97316', 'معرض': '#f43f5e', 'Other': '#64748b' };

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
        content.innerHTML = `<div class="empty-state"><div class="empty-icon">❌</div><p>${t('Could not fetch event details')}</p></div>`;
        return;
      }
      const ev = res.data;
      const apps = appRes.ok ? appRes.data : [];
      const myApp = apps.find(a => a.event_id == eventId);

      const eType = ev.event_type || 'Other';
      const tColor = typeColors[eType] || typeColors.Other || '#64748b';
      const tIcon = typeIcons[eType] || typeIcons.Other || '📌';

      const bannerSection = ev.image
        ? `<div class="ed-banner" style="background-image:url('/storage/${ev.image}')"><div class="ed-banner-fade"></div></div>`
        : `<div class="ed-banner ed-banner-placeholder"><span class="ed-banner-emoji">${tIcon}</span><div class="ed-banner-fade"></div></div>`;


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
          <div class="ed-section-label">${t('About this Event')}</div>
          <p class="ed-description">${ev.description || t('No description provided.')}</p>
        </div>

        <div class="ed-info-grid">
          <div class="ed-info-card ed-info-accent2">
            <div class="ed-info-icon">🏛️</div>
            <div><div class="ed-info-label">${t('Venue')}</div><div class="ed-info-value">${ev.venue?.name || ev.external_venue_name || '—'}</div></div>
          </div>
          <div class="ed-info-card ed-info-accent2">
            <div class="ed-info-icon">📍</div>
            <div><div class="ed-info-label">${t('Location')}</div><div class="ed-info-value">
              ${ev.location_link ? `<a href="${ev.location_link}" target="_blank" style="color:inherit;text-decoration:underline;">${t('Open in Maps')} ↗</a>`
              : (ev.venue?.location ? `<a href="${ev.venue.location.startsWith('http') ? ev.venue.location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.venue.location)}" target="_blank" style="color:inherit;text-decoration:underline;">${t('Open in Maps')} ↗</a>` 
              : (ev.external_venue_location ? `<a href="${ev.external_venue_location.startsWith('http') ? ev.external_venue_location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.external_venue_location)}" target="_blank" style="color:inherit;text-decoration:underline;">${t('Open in Maps')} ↗</a>` : '—'))}
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
                  <div class="ed-info-label" style="margin-bottom:0">${t('Event Objective')}</div>
              </div>
              <div class="ed-info-value" style="font-weight:400; font-size:0.85rem; line-height:1.4; color:rgba(255,255,255,0.7)">${ev.objective}</div>
          </div>
        ` : ''}

        ${ev.target_audience ? `
          <div class="ed-info-card" style="grid-column: 1 / -1; flex-direction:column; align-items:flex-start; gap:5px; margin-top:10px;">
              <div style="display:flex; align-items:center; gap:8px;">
                  <span style="font-size:1.1rem;">👥</span>
                  <div class="ed-info-label" style="margin-bottom:0">${t('Target Audience')}</div>
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
                  scheduleHtml += '<span style="color:#64748b;font-size:0.8rem;">←</span>';
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
              <div><div class="ed-info-label">${t('Tickets Booked')}</div><div class="ed-info-value">${ev.tickets_count || 0}</div></div>
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
      if(typeof showToast !== 'undefined') showToast(t('Error loading event details'), 'error');
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
