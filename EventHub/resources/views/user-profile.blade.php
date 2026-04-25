<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Profile – EventHub</title>
  <link rel="stylesheet" href="/css/style.css" />
  <script src="/js/i18n.js"></script>
  <style>
    .profile-hero {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 40px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 32px;
      position: relative;
      overflow: hidden;
    }

    .profile-hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--accent), var(--accent2));
    }

    .profile-avatar-large {
      width: 140px;
      height: 140px;
      border-radius: 20px;
      object-fit: cover;
      aspect-ratio: 1/1;
      border: 4px solid var(--border);
      background: var(--bg-dark);
    }

    .profile-info-main h1 {
      font-size: 2rem;
      margin-bottom: 8px;
    }

    .contact-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-top: 24px;
    }

    .contact-card {
      background: rgba(255, 255, 255, 0.02);
      padding: 20px;
      border-radius: 16px;
      border: 1px solid var(--border);
      display: flex;
      align-items: center;
      gap: 16px;
      transition: transform 0.2s;
    }

    .contact-card:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.04);
    }

    .contact-icon {
      font-size: 1.4rem;
      color: var(--accent2);
      background: rgba(34, 211, 238, 0.1);
      width: 44px;
      height: 44px;
      display: grid;
      place-items: center;
      border-radius: 10px;
    }

    .bio-section {
      line-height: 1.8;
      color: var(--text-muted);
      font-size: 1.05rem;
      white-space: pre-line;
    }

    /* Manager Stats Row */
    .mgr-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 14px;
      margin-bottom: 24px;
    }

    .mgr-stat {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 18px 20px;
      position: relative;
      overflow: hidden;
      transition: transform .25s, box-shadow .25s;
    }

    .mgr-stat:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 36px rgba(0, 0, 0, .3);
    }

    .mgr-stat-stripe {
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 4px;
      border-radius: 4px 0 0 4px;
    }

    .mgr-stat-icon {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      display: grid;
      place-items: center;
      font-size: 1.1rem;
      margin-bottom: 10px;
    }

    .mgr-stat-label {
      font-size: .68rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: var(--text-muted);
      margin-bottom: 3px;
    }

    .mgr-stat-value {
      font-size: 1.7rem;
      font-weight: 800;
      color: #fff;
      line-height: 1;
    }

    .mgr-stat-sub {
      font-size: .7rem;
      color: var(--text-muted);
      margin-top: 3px;
    }

    /* Events Table Card */
    .mgr-events-section {
      margin-bottom: 24px;
    }

    .mgr-section-title {
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .mgr-filter-row {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 14px;
    }

    .mgr-filter-row input,
    .mgr-filter-row select {
      background: var(--bg-card);
      border: 1px solid var(--border);
      color: #fff;
      border-radius: 8px;
      padding: 7px 12px;
      font-size: .85rem;
      outline: none;
    }

    .mgr-filter-row input {
      width: 220px;
    }
  </style>
</head>

<body>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-logo">
        <div class="logo-icon">🎯</div><span>EventHub</span>
      </div>
      <nav class="sidebar-nav" id="sidebar-links">
        <!-- Will be populated by JS -->
      </nav>
      @include('partials._sidebar-footer')
    </aside>

    <main class="main-content">
      <div class="topbar">
        <button class="btn btn-ghost" onclick="history.back()">← Back</button>
        <div class="topbar-actions" id="action-buttons">
          <!-- Optional action buttons -->
        </div>
      </div>

      <div id="profile-loader" style="text-align:center; padding:100px;">
        <div class="spinner" style="margin:auto"></div>
        <p style="margin-top:20px; color:var(--text-muted)">Loading profile...</p>
      </div>

      <div id="profile-content" style="display:none">
        <div class="profile-hero">
          <img id="u-avatar" src="" class="profile-avatar-large" alt="Avatar" />
          <div class="profile-info-main">
            <div id="u-role-badge" class="role-badge" style="margin-bottom:10px;"></div>
            <h1 id="u-name" class="i18n-skip">User Name</h1>
            <div id="u-status-badge"></div>
          </div>
        </div>

        <div class="card" style="margin-bottom:24px;">
          <div class="card-header"><span class="card-title">About</span></div>
          <div id="u-bio" class="bio-section">No description provided.</div>
        </div>

        <div class="card" style="margin-bottom:24px;">
          <div class="card-header"><span class="card-title">Contact Information</span></div>
          <div class="contact-grid" id="contact-list"></div>
        </div>

        <!-- Manager Stats (shown only for Event Manager) -->
        <div id="mgr-stats-section" style="display:none">
          <div class="mgr-stats" id="mgr-stats-row">
            <div class="mgr-stat">
              <div class="mgr-stat-stripe" style="background:#6e40f2"></div>
              <div class="mgr-stat-icon" style="background:rgba(110,64,242,.15)">📅</div>
              <div class="mgr-stat-label">Total Events</div>
              <div class="mgr-stat-value" id="ms-total">—</div>
            </div>
            <div class="mgr-stat" id="mgr-stat-attendance">
              <div class="mgr-stat-stripe" style="background:#22c55e"></div>
              <div class="mgr-stat-icon" style="background:rgba(34,197,94,.15)">👥</div>
              <div class="mgr-stat-label">Total Attendees</div>
              <div class="mgr-stat-value" id="ms-attendance">—</div>
            </div>
            <div class="mgr-stat">
              <div class="mgr-stat-stripe" style="background:#eab308"></div>
              <div class="mgr-stat-icon" style="background:rgba(234,179,8,.15)">⭐</div>
              <div class="mgr-stat-label">Avg Rating</div>
              <div class="mgr-stat-value" id="ms-rating">—</div>
            </div>
          </div>

          <!-- Events Table -->
          <div class="mgr-events-section">
            <div class="mgr-section-title"><span>📅</span> All Events</div>
            <div class="mgr-filter-row">
              <input id="mgr-search" type="text" placeholder="Search by name…" oninput="applyMgrFilter()">
            </div>
            <div class="card">
              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Title</th>
                      <th>Venue</th>
                      <th>Date</th>
                      <th>Capacity</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="mgr-events-body">
                    <tr>
                      <td colspan="7">
                        <div class="spinner" style="margin:auto"></div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <div id="toast-container"></div>

  <script src="/js/api.js"></script>
  <script src="/js/notifications.js"></script>
  <script src="/js/auth.js"></script>
  <script>
    const me = requireAuth();
    if (me) {
      populateSidebar(me);
    }

    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    if (!userId) {
      window.location.href = '/dashboard';
    } else {
      loadProfile(userId);
    }

    async function loadProfile(id) {
      const res = await api.get('/profile/' + id);
      if (!res.ok) {
        showToast('User not found', 'error');
        setTimeout(() => history.back(), 2000);
        return;
      }

      const u = res.data.user;
      const p = u.profile || {};

      document.getElementById('u-name').innerText = (u.role === 'Sponsor' && p.company_name) ? p.company_name : u.name;

      let avatar = '/images/default-avatar.png';
      if (u.image && u.image.trim() !== '') {
        avatar = (u.image.startsWith('http') || u.image.startsWith('/')) ? u.image : '/storage/' + u.image;
      } else if (u.avatar && u.avatar.trim() !== '') {
        avatar = (u.avatar.startsWith('http') || u.avatar.startsWith('/')) ? u.avatar : '/storage/' + u.avatar;
      } else if (p.logo) {
        avatar = (p.logo.startsWith('http') || p.logo.startsWith('/')) ? p.logo : '/' + p.logo;
      }
      document.getElementById('u-avatar').src = avatar;

      const roleBadge = document.getElementById('u-role-badge');
      roleBadge.innerText = u.role;
      const roleStyles = {
        'Admin': 'background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.3)',
        'Event Manager': 'background:rgba(110,64,242,.15);color:#a78bfa;border:1px solid rgba(110,64,242,.3)',
        'Sponsor': 'background:rgba(234,179,8,.15);color:#eab308;border:1px solid rgba(234,179,8,.3)',
        'User': 'background:rgba(34,211,238,.15);color:#22d3ee;border:1px solid rgba(34,211,238,.3)',
        'Assistant': 'background:rgba(34,197,94,.15);color:#22c55e;border:1px solid rgba(34,197,94,.3)',
      };
      roleBadge.setAttribute('style', (roleStyles[u.role] || 'background:rgba(255,255,255,.1);color:#fff') + ';display:inline-block;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px');

      const statusBadge = document.getElementById('u-status-badge');
      if (u.role === 'Sponsor') {
        if (p.is_available) {
          statusBadge.innerHTML = '<span class="badge badge-approved">Available for Sponsorship</span>';
        } else {
          statusBadge.innerHTML = '<span class="badge badge-rejected">Currently Not Available</span>';
        }
      } else if (u.role === 'Event Manager') {
        if (u.manager_average_rating !== undefined && u.manager_average_rating !== null) {
          statusBadge.innerHTML = `<div style="display:inline-flex; align-items:center; gap:6px; background:rgba(234,179,8,0.15); border:1px solid rgba(234,179,8,0.3); color:#eab308; padding:4px 10px; border-radius:8px; font-weight:700; font-size:0.85rem; margin-top:8px;"><span style="font-size:1rem">⭐</span> ${Number(u.manager_average_rating).toFixed(1)} / 5.0 Average Event Rating</div>`;
        } else {
          statusBadge.innerHTML = '';
        }
      } else {
        statusBadge.innerHTML = '';
      }

      const bioText = (u.role === 'Sponsor' ? p.company_description : p.bio) || u.bio || 'No description provided.';
      document.getElementById('u-bio').innerText = bioText;

      // Contacts
      const contactList = document.getElementById('contact-list');
      let html = '';

      if (u.contact_email) {
        html += `
            <div class="contact-card">
                <span class="contact-icon">📧</span>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Contact Email</div>
                    <div style="font-weight:500;">${u.contact_email}</div>
                </div>
            </div>
          `;
      } else if (me && me.id === u.id) {
        html += `
            <div class="contact-card">
                <span class="contact-icon">📧</span>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Login Email (Private)</div>
                    <div style="font-weight:500;">${u.email}</div>
                </div>
            </div>
          `;
      }

      if (u.phone) {
        html += `
            <div class="contact-card">
                <span class="contact-icon">📞</span>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Phone</div>
                    <div style="font-weight:500;"><a href="tel:${u.phone}" style="color:var(--text-primary); text-decoration:none;">${u.phone}</a></div>
                </div>
            </div>
          `;
      }

      if (u.social_links) {
        const iconMap = {
          'twitter': '𝕏', 'x': '𝕏',
          'linkedin': '💼',
          'website': '🌐', 'portfolio': '🎨',
          'facebook': '👥', 'instagram': '📸',
          'whatsapp': '💬', 'telegram': '✈️',
          'github': '💻', 'youtube': '🎬',
          'tiktok': '🎵', 'discord': '👾'
        };

        for (let [p, link] of Object.entries(u.social_links)) {
          if (link) {
            const platform = p.split('_')[0]; // strip our unique suffix
            let icon = iconMap[platform] || '🔗';
            let dispPlatform = platform.charAt(0).toUpperCase() + platform.slice(1);
            if (platform === 'twitter' || platform === 'x') dispPlatform = 'X (Twitter)';

            html += `
                    <div class="contact-card">
                        <span class="contact-icon">${icon}</span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">${dispPlatform}</div>
                            <div style="font-weight:500; font-size:0.9rem; word-break: break-all;">
                                <a href="${link.startsWith('http') ? link : 'https://' + link}" target="_blank" style="color:var(--text-primary); text-decoration:none;">${link.replace(/^https?:\/\//, '')}</a>
                            </div>
                        </div>
                    </div>
                  `;
          }
        }
      }

      if (p.contacts && p.contacts.length > 0) {
        p.contacts.forEach(c => {
          let icon = '🔗';
          if (c.type === 'phone') icon = '📞';
          if (c.type === 'website') icon = '🌐';
          if (c.type === 'email') icon = '✉️';

          html += `
                <div class="contact-card">
                    <span class="contact-icon">${icon}</span>
                    <div>
                        <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">${c.type}</div>
                        <div style="font-weight:500;">${c.value}</div>
                    </div>
                </div>
              `;
        });
      }
      contactList.innerHTML = html;

      document.getElementById('profile-loader').style.display = 'none';
      document.getElementById('profile-content').style.display = 'block';

      if (u.role === 'Event Manager') {
        loadManagerEvents(u.id, u.role);
      } else if (u.role === 'Sponsor') {
        loadManagerEvents(u.id, u.role);
      }
    }

    let allMgrEvents = [];

    async function loadManagerEvents(userId, role) {
      const res = await api.get('/profile/' + userId + '/portfolio');
      const section = document.getElementById('mgr-stats-section');
      section.style.display = 'block';

      if (role === 'Sponsor') {
        const title = document.querySelector('.mgr-section-title');
        if (title) title.innerHTML = '<span>💼</span> Sponsored Events History';

        const msTotalLabel = document.querySelector('#ms-total').previousElementSibling;
        if (msTotalLabel) msTotalLabel.textContent = 'Sponsored';

        const ratingStat = document.querySelector('#ms-rating').parentElement;
        if (ratingStat) ratingStat.style.display = 'none';
      }

      const tbody = document.getElementById('mgr-events-body');
      if (!res.ok) {
        tbody.innerHTML = '<tr><td colspan="7" style="color:var(--danger);text-align:center">Failed to load events</td></tr>';
        return;
      }

      allMgrEvents = res.data.events || [];

      // Compute stats
      const total = allMgrEvents.length;
      const attendees = allMgrEvents.reduce((sum, e) => sum + (e.tickets_count || 0), 0);
      const ratings = allMgrEvents.filter(e => e.average_rating > 0).map(e => e.average_rating);
      const avgRating = ratings.length ? Math.round(ratings.reduce((a, b) => a + b, 0) / ratings.length) : null;

      document.getElementById('ms-total').textContent = total;
      
      const msAttendance = document.getElementById('ms-attendance');
      if (msAttendance) msAttendance.textContent = attendees;

      document.getElementById('ms-rating').innerHTML = avgRating !== null
        ? [1, 2, 3, 4, 5].map(i => `<span style="color:${i <= avgRating ? '#eab308' : 'rgba(255,255,255,.15)'};font-size:1.1rem">★</span>`).join('')
        : '—';

      applyMgrFilter();
    }

    function applyMgrFilter() {
      const q = (document.getElementById('mgr-search')?.value || '').toLowerCase().trim();
      const f = document.getElementById('mgr-filter-status')?.value || '';
      let filtered = allMgrEvents;
      if (f) filtered = filtered.filter(e => e.status === f);
      if (q) filtered = filtered.filter(e => e.title.toLowerCase().includes(q));
      renderMgrEvents(filtered);
    }

    function renderMgrEvents(events) {
      const tbody = document.getElementById('mgr-events-body');
      if (!events.length) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📅</div><p>No events found</p></div></td></tr>';
        return;
      }
      tbody.innerHTML = events.map((ev, i) => {
        const ratingBadge = ev.average_rating > 0
          ? `<span style="background:rgba(234,179,8,.12);color:#eab308;border:1px solid rgba(234,179,8,.25);padding:2px 8px;border-radius:12px;font-size:.72rem;font-weight:700">⭐ ${Math.round(ev.average_rating)}/5</span>`
          : '';
        return `<tr>
            <td style="color:var(--text-muted)">${i + 1}</td>
            <td><div style="font-weight:600">${ev.title}</div>${ratingBadge}</td>
            <td style="color:var(--text-muted)">${ev.venue?.name || '—'}</td>
            <td style="color:var(--text-muted);white-space:nowrap">${fmtDateShort(ev.start_time)}</td>
            <td style="color:var(--text-muted)">${ev.capacity || '—'}</td>
            <td>${badge(ev.status)} ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}</td>
            <td style="display:flex;gap:6px;flex-wrap:wrap">
              <button class="btn btn-ghost btn-sm" onclick="showMgrEventDetails(${ev.id})">ℹ️ Details</button>
              ${(me && me.role !== 'Sponsor') ? `<button class="btn btn-sm" style="background:rgba(34,211,238,.12);color:#22d3ee;border:1px solid rgba(34,211,238,.25)" onclick="window.location.href='/admin/event-stats/${ev.id}'">📊 Stats</button>` : ''}
            </td>
          </tr>`;
      }).join('');
    }

    // ── Event Details Modal ─────────────────────────────────────────────────────
    const _tIcons = { 'مؤتمر': '🎙️', 'ندوة': '📖', 'ورشة عمل': '🔧', 'دورة تدريبية': '🎓', 'ترفيه': '🎭', 'ملتقى علمي': '🔬', 'رياضة': '⚽', 'تقنية': '💻', 'اجتماعية': '🤝' };
    const _tColors = { 'مؤتمر': '#3b82f6', 'ندوة': '#8b5cf6', 'ورشة عمل': '#10b981', 'دورة تدريبية': '#06b6d4', 'ترفيه': '#ec4899', 'ملتقى علمي': '#f59e0b', 'رياضة': '#22c55e', 'تقنية': '#6366f1', 'اجتماعية': '#f97316' };

    function showMgrEventDetails(eventId) {
      const modal = document.getElementById('prof-event-modal');
      const content = document.getElementById('prof-event-content');
      modal.classList.add('open');
      content.innerHTML = '<div class="spinner" style="margin:auto"></div>';

      api.get(`/events/${eventId}`).then(res => {
        if (!res.ok) {
          content.innerHTML = '<div class="empty-state"><div class="empty-icon">❌</div><p>Could not fetch event details</p></div>';
          return;
        }
        const ev = res.data;
        const eType = ev.event_type || 'Other';
        const tColor = _tColors[eType] || '#6b7280';
        const tIcon = _tIcons[eType] || '📌';

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
                   <div style="display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.04); padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,0.05); cursor:pointer;" onclick="window.location.href='/user-profile?id=${sp.id}'">
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
                          <div style="font-size:0.85rem; font-weight:600; color:#fff;">${sp.profile?.company_name || sp.name}</div>
                          <div style="margin-top: 2px;">${getTierBadge(sp.pivot?.tier)}</div>
                      </div>
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
              ${ev.status ? badge(ev.status) : ''}
              ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}
            </div>
          </div>

          ${rejectionSection}

          <div class="ed-section">
            <div class="ed-section-label">About this Event</div>
            <p class="ed-description">${ev.description || 'No description provided.'}</p>
          </div>

          <div class="ed-info-grid">
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon">🏛️</div>
              <div><div class="ed-info-label">Venue</div><div class="ed-info-value">${ev.venue?.name || '—'}</div></div>
            </div>
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon">📍</div>
              <div><div class="ed-info-label">Location</div><div class="ed-info-value">${ev.venue?.location ? `<a href="${ev.venue.location.startsWith('http') ? ev.venue.location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.venue.location)}" target="_blank" style="color:inherit;text-decoration:underline;">Open in Maps ↗</a>` : '—'}</div></div>
            </div>
            <div class="ed-info-card ed-info-accent">
              <div class="ed-info-icon">🕐</div>
              <div><div class="ed-info-label">Start</div><div class="ed-info-value">${fmtDate(ev.start_time)}</div></div>
            </div>
            <div class="ed-info-card ed-info-accent">
              <div class="ed-info-icon">🕔</div>
              <div><div class="ed-info-label">End</div><div class="ed-info-value">${fmtDate(ev.end_time)}</div></div>
            </div>
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

          <div class="ed-footer" style="margin-top: 8px;">
            <span class="ed-footer-label">Created by</span>
            <span class="ed-footer-name">${ev.creator?.name || ev.manager?.name || '—'}</span>
          </div>

        </div>
      `;
      });
    }

    function closeProfEventModal() {
      document.getElementById('prof-event-modal').classList.remove('open');
      document.getElementById('prof-event-content').innerHTML = '';
    }
  </script>

  <!-- Event Details Modal (Profile Page) -->
  <div class="modal-overlay" id="prof-event-modal">
    <div class="modal ed-modal">
      <button class="ed-close-btn" onclick="closeProfEventModal()">✕</button>
      <div id="prof-event-content" class="ed-content"></div>
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
      box-shadow: 0 32px 80px rgba(0, 0, 0, 0.6);
      background: #13131f;
      max-height: 90vh;
      display: flex;
      flex-direction: column;
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