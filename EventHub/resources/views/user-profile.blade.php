<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Profile – EventHub</title>
  <link rel="stylesheet" href="/css/style.css"/>
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
      top: 0; left: 0; right: 0; height: 4px;
      background: linear-gradient(90deg, var(--accent), var(--accent2));
    }
    .profile-avatar-large {
      width: 140px; height: 140px;
      border-radius: 20px;
      object-fit: cover;
      aspect-ratio: 1/1;
      border: 4px solid var(--border);
      background: var(--bg-dark);
    }
    .profile-info-main h1 { font-size: 2rem; margin-bottom: 8px; }
    .contact-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-top: 24px;
    }
    .contact-card {
      background: rgba(255,255,255,0.02);
      padding: 20px;
      border-radius: 16px;
      border: 1px solid var(--border);
      display: flex;
      align-items: center;
      gap: 16px;
      transition: transform 0.2s;
    }
    .contact-card:hover { transform: translateY(-2px); background: rgba(255,255,255,0.04); }
    .contact-icon { font-size: 1.4rem; color: var(--accent2); background: rgba(34, 211, 238, 0.1); width: 44px; height: 44px; display: grid; place-items: center; border-radius: 10px; }
    .bio-section { line-height: 1.8; color: var(--text-muted); font-size: 1.05rem; white-space: pre-line; }
  </style>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav" id="sidebar-nav">
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
            <img id="u-avatar" src="" class="profile-avatar-large" alt="Avatar"/>
            <div class="profile-info-main">
                <div id="u-role-badge" class="role-badge" style="margin-bottom:10px;"></div>
                <h1 id="u-name">User Name</h1>
                <div id="u-status-badge"></div>
            </div>
        </div>

        <div class="card" style="margin-bottom:24px;">
            <div class="card-header"><span class="card-title">About</span></div>
            <div id="u-bio" class="bio-section">No description provided.</div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Contact Information</span></div>
            <div class="contact-grid" id="contact-list"></div>
        </div>
    </div>
  </main>
</div>

<div id="toast-container"></div>

<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
  const me = requireAuth();
  if (me) { 
    populateSidebar(me); 
    // Manual sidebar since we aren't in a nav-item
    const nav = document.getElementById('sidebar-nav');
    const roleHtml = {
      'Admin': `
        <span class="nav-section-label">Overview</span>
        <a class="nav-item" href="/admin/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
        <a class="nav-item" href="/admin/users"><span class="nav-icon">👥</span> User Management</a>
        <a class="nav-item" href="/admin/events"><span class="nav-icon">📅</span> Event Management</a>
        <a class="nav-item" href="/admin/venues"><span class="nav-icon">🏢</span> Venues</a>
      `,
      'Event Manager': `
        <span class="nav-section-label">Overview</span>
        <a class="nav-item" href="/manager/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
        <span class="nav-section-label">Events</span>
        <a class="nav-item" href="/manager/events"><span class="nav-icon">📅</span> My Events</a>
        <a class="nav-item" href="/manager/assistants"><span class="nav-icon">👥</span> Assistants</a>
        <a class="nav-item" href="/manager/attendance"><span class="nav-icon">📍</span> Attendance</a>
        <a class="nav-item" href="/manager/sponsorship"><span class="nav-icon">💼</span> Sponsorship</a>
      `,
      'Sponsor': `
        <span class="nav-section-label">Overview</span>
        <a class="nav-item" href="/sponsor/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
        <span class="nav-section-label">Opportunities</span>
        <a class="nav-item" href="/sponsor/events"><span class="nav-icon">🌍</span> Browse Events</a>
        <a class="nav-item" href="/sponsor/requests"><span class="nav-icon">💼</span> Sponsorships</a>
      `,
      'User': `
        <a class="nav-item" href="/dashboard"><span class="nav-icon">🏠</span> Home</a>
        <a class="nav-item" href="/events"><span class="nav-icon">📅</span> Find Events</a>
        <a class="nav-item" href="/tickets"><span class="nav-icon">🎟️</span> My Tickets</a>
      `
    };
    nav.innerHTML = (roleHtml[me.role] || '') + `
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    `;
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
      roleBadge.className = 'role-badge role-' + u.role.toLowerCase().replace(' ', '-');

      const statusBadge = document.getElementById('u-status-badge');
      if (u.role === 'Sponsor') {
          if (p.is_available) {
              statusBadge.innerHTML = '<span class="badge badge-approved">Available for Sponsorship</span>';
          } else {
              statusBadge.innerHTML = '<span class="badge badge-rejected">Currently Not Available</span>';
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
                                <a href="${link.startsWith('http') ? link : 'https://'+link}" target="_blank" style="color:var(--text-primary); text-decoration:none;">${link.replace(/^https?:\/\//, '')}</a>
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
  }
</script>
</body>
</html>
