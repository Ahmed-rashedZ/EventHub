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
  <link rel="icon" href="/images/logo.png" type="image/png">
</head>

<body>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-logo" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px;"><img src="/images/logo.png" alt="EventHub Logo" style="height: 60px; width: auto; object-fit: contain;"></div>
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
              <div class="mgr-stat-icon" style="background:rgba(110,64,242,.15); color:#6e40f2;">
                <svg style="width:20px; height:20px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
              </div>
              <div class="mgr-stat-label">Total Events</div>
              <div class="mgr-stat-value" id="ms-total">—</div>
            </div>
            <div class="mgr-stat" id="mgr-stat-attendance">
              <div class="mgr-stat-stripe" style="background:#22c55e"></div>
              <div class="mgr-stat-icon" style="background:rgba(34,197,94,.15); color:#22c55e;">
                <svg style="width:20px; height:20px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
              </div>
              <div class="mgr-stat-label">Total Attendees</div>
              <div class="mgr-stat-value" id="ms-attendance">—</div>
            </div>
            <div class="mgr-stat">
              <div class="mgr-stat-stripe" style="background:#eab308"></div>
              <div class="mgr-stat-icon" style="background:rgba(234,179,8,.15); color:#eab308;">
                <svg style="width:20px; height:20px; fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              </div>
              <div class="mgr-stat-label">Avg Rating</div>
              <div class="mgr-stat-value" id="ms-rating">—</div>
            </div>
          </div>

          <!-- Events Table -->
          <div class="mgr-events-section">
            <div class="mgr-section-title"><svg style="width:16px; height:16px; stroke:currentColor; fill:none; stroke-width:2; margin-inline-end: 6px; vertical-align: middle; display: inline-block;" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> All Events</div>
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
    function getContactIcon(type) {
      const typeLower = (type || '').toLowerCase();
      const icons = {
        'email': `<svg style="width:18px; height:18px; stroke:currentColor; stroke-width:2; fill:none; display:block;" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>`,
        'phone': `<svg style="width:18px; height:18px; stroke:currentColor; stroke-width:2; fill:none; display:block;" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>`,
        'twitter': `<svg style="width:18px; height:18px; fill:currentColor; display:block;" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg>`,
        'x': `<svg style="width:18px; height:18px; fill:currentColor; display:block;" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg>`,
        'linkedin': `<svg style="width:18px; height:18px; fill:currentColor; display:block;" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"></path></svg>`,
        'website': `<svg style="width:18px; height:18px; stroke:currentColor; stroke-width:2; fill:none; display:block;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>`,
        'portfolio': `<svg style="width:18px; height:18px; stroke:currentColor; stroke-width:2; fill:none; display:block;" viewBox="0 0 24 24"><path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"></path><circle cx="7.5" cy="10.5" r="1.5" fill="currentColor"></circle><circle cx="11.5" cy="7.5" r="1.5" fill="currentColor"></circle><circle cx="16.5" cy="9.5" r="1.5" fill="currentColor"></circle><circle cx="15.5" cy="14.5" r="1.5" fill="currentColor"></circle></svg>`,
        'facebook': `<svg style="width:18px; height:18px; fill:currentColor; display:block;" viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.75z"></path></svg>`,
        'instagram': `<svg style="width:18px; height:18px; stroke:currentColor; stroke-width:2; fill:none; display:block;" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>`,
        'whatsapp': `<svg style="width:18px; height:18px; fill:currentColor; display:block;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.413 9.864-9.843.002-2.63-1.023-5.101-2.886-6.968C16.586 1.928 14.113.874 11.5.874c-5.442 0-9.87 4.414-9.874 9.845-.001 1.777.466 3.513 1.353 5.053L1.936 21.5l5.847-1.53c-1.28.69-2.223 1.187-1.136.684z"></path><path d="M16.945 13.91c-.27-.134-1.602-.79-1.85-.88-.25-.09-.432-.134-.61.134-.18.27-.696.88-.853 1.062-.157.18-.314.2-.584.067-.27-.134-1.14-.42-2.17-1.34-.8-.713-1.34-1.597-1.498-1.867-.157-.27-.017-.417.118-.552.12-.12.27-.315.405-.472.135-.157.18-.27.27-.45.09-.18.045-.337-.023-.472-.067-.135-.61-1.472-.837-2.013-.22-.53-.442-.457-.61-.466-.157-.008-.337-.01-.518-.01-.18 0-.473.067-.72.337-.248.27-.945.923-.945 2.25 0 1.328.966 2.61 1.1 2.78.136.17 1.902 2.904 4.607 4.07 2.7.5 2.7.8.647.785 1.472.585 2.7-.45.74-.27 2.014-.82 2.3-1.58.285-.758.285-1.407.2-1.543-.085-.136-.314-.22-.584-.354z"></path></svg>`,
        'telegram': `<svg style="width:18px; height:18px; fill:currentColor; display:block;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.11.02-1.93 1.23-5.46 3.62-.51.35-.98.53-1.39.51-.46-.01-1.35-.26-2.01-.48-.81-.27-1.46-.42-1.4-.88.03-.24.37-.49 1.02-.75 3.99-1.74 6.66-2.88 7.99-3.43 3.8-1.57 4.59-1.85 5.1-.11.01.2.03.43.03.66z"></path></svg>`,
        'github': `<svg style="width:18px; height:18px; fill:currentColor; display:block;" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.579.688.481C19.137 20.164 22 16.418 22 12c0-5.523-4.477-10-10-10z"></path></svg>`,
        'youtube': `<svg style="width:18px; height:18px; fill:currentColor; display:block;" viewBox="0 0 24 24"><path d="M23.498 6.163a3.003 3.003 0 0 0-2.11-2.11C19.517 3.545 12 3.545 12 3.545s-7.517 0-9.388.508a3.003 3.003 0 0 0-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 0 0 2.11 2.11c1.871.508 9.388.508 9.388.508s7.517 0 9.388-.508a3.003 3.003 0 0 0 2.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"></path></svg>`,
        'tiktok': `<svg style="width:18px; height:18px; fill:currentColor; display:block;" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.52-4.06-1.37-.28-.2-.53-.43-.77-.68v5.89c.04 2.87-1.52 5.74-4.22 6.78-2.73 1.05-6.03.46-8.15-1.59-2.48-2.39-2.82-6.52-.78-9.29 1.93-2.61 5.71-3.48 8.65-2.07v4.11c-1.46-.86-3.43-.63-4.63.56-.96.96-1.2 2.5-.59 3.7.61 1.2 2.03 1.9 3.37 1.72 1.34-.17 2.45-1.32 2.47-2.68V.02z"></path></svg>`,
        'discord': `<svg style="width:18px; height:18px; fill:currentColor; display:block;" viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.094 13.094 0 0 1-1.873-.894.077.077 0 0 1-.008-.128c.126-.093.252-.19.372-.287a.075.075 0 0 1 .077-.011c3.92 1.793 8.18 1.793 12.061 0a.073.073 0 0 1 .078.009c.12.099.246.195.373.289a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.894.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.156-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.156 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.156-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.156 2.418z"></path></svg>`
      };
      return icons[typeLower] || `<svg style="width:18px; height:18px; stroke:currentColor; stroke-width:2; fill:none; display:block;" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.53-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>`;
    }

    const me = requireAuth();
    if (me) {
      populateSidebar(me);
    }

    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    if (!userId) {
      // Redirect to the correct dashboard based on role
      const dashMap = {
        'Admin': '/admin/dashboard',
        'Event Manager': '/manager/dashboard',
        'Sponsor': '/sponsor/dashboard',
        'Attendee': '/profile',
        'Assistant': '/profile'
      };
      window.location.replace((me && dashMap[me.role]) || '/profile');
    } else {
      loadProfile(userId);
    }

    let profileUserId = null;

    async function loadProfile(id) {
      try {
        const res = await api.get('/profile/' + id);
        if (!res.ok) {
          showToast('User not found', 'error');
          document.getElementById('profile-loader').innerHTML = `<div class="empty-state"><div class="empty-icon"><svg style="width:48px; height:48px; stroke:#ef4444; fill:none; stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg></div><p>User not found</p></div>`;
          return;
        }

        const u = res.data.user;
        const p = u.profile || {};

        document.getElementById('u-name').innerText = u.name;

        let avatar = '/images/default-avatar.png';
        if (typeof u.image === 'string' && u.image.trim() !== '') {
          avatar = (u.image.startsWith('http') || u.image.startsWith('/')) ? u.image : '/storage/' + u.image;
        } else if (typeof u.avatar === 'string' && u.avatar.trim() !== '') {
          avatar = (u.avatar.startsWith('http') || u.avatar.startsWith('/')) ? u.avatar : '/storage/' + u.avatar;
        } else if (typeof p.logo === 'string' && p.logo.trim() !== '') {
          avatar = (p.logo.startsWith('http') || p.logo.startsWith('/')) ? p.logo : '/' + p.logo;
        }
        document.getElementById('u-avatar').src = avatar;

        const roleBadge = document.getElementById('u-role-badge');
        roleBadge.innerText = u.role;
        const roleStyles = {
          'Admin': 'background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.3)',
          'Event Manager': 'background:rgba(110,64,242,.15);color:#a78bfa;border:1px solid rgba(110,64,242,.3)',
          'Sponsor': 'background:rgba(234,179,8,.15);color:#eab308;border:1px solid rgba(234,179,8,.3)',
          'Company': 'background:rgba(59,130,246,.15);color:#60a5fa;border:1px solid rgba(59,130,246,.3)',
          'Attendee': 'background:rgba(34,211,238,.15);color:#22d3ee;border:1px solid rgba(34,211,238,.3)',
          'Assistant': 'background:rgba(34,197,94,.15);color:#22c55e;border:1px solid rgba(34,197,94,.3)',
        };
        roleBadge.setAttribute('style', (roleStyles[u.role] || 'background:rgba(255,255,255,.1);color:#fff') + ';display:inline-block;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px');

        const statusBadge = document.getElementById('u-status-badge');
        if (u.role === 'Sponsor' || u.role === 'Company') {
          if (p.is_available) {
            const label = u.role === 'Company' ? t('Available for Exhibitions') : t('Available for Sponsorship');
            statusBadge.innerHTML = `<span class="badge badge-approved">${label}</span>`;
          } else {
            const label = u.role === 'Company' ? t('Not Available for Exhibitions') : t('Currently Not Available');
            statusBadge.innerHTML = `<span class="badge badge-rejected">${label}</span>`;
          }
        } else if (u.role === 'Event Manager') {
          if (u.manager_average_rating !== undefined && u.manager_average_rating !== null && statusBadge) {
            statusBadge.innerHTML = `<div style="display:inline-flex; align-items:center; gap:6px; background:rgba(234,179,8,0.15); border:1px solid rgba(234,179,8,0.3); color:#eab308; padding:4px 10px; border-radius:8px; font-weight:700; font-size:0.85rem; margin-top:8px;"><span style="font-size:1rem">⭐</span> ${Number(u.manager_average_rating).toFixed(1)} / 5.0 Average Event Rating</div>`;
          } else if (statusBadge) {
            statusBadge.innerHTML = '';
          }
        } else if (statusBadge) {
          statusBadge.innerHTML = '';
        }

        const bioText = (u.role === 'Sponsor' || u.role === 'Company' ? p.company_description : p.bio) || u.bio || 'No description provided.';
        document.getElementById('u-bio').innerText = bioText;

        // Contacts
        const contactList = document.getElementById('contact-list');
        let html = '';

        if (u.contact_email) {
          html += `
            <div class="contact-card">
                <span class="contact-icon">${getContactIcon('email')}</span>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Contact Email</div>
                    <div style="font-weight:500;">${u.contact_email}</div>
                </div>
            </div>
          `;
        } else if (me && me.id === u.id) {
          html += `
            <div class="contact-card">
                <span class="contact-icon">${getContactIcon('email')}</span>
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
                <span class="contact-icon">${getContactIcon('phone')}</span>
                <div>
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Phone</div>
                    <div style="font-weight:500;"><a href="tel:${u.phone}" style="color:var(--text-primary); text-decoration:none;">${u.phone}</a></div>
                </div>
            </div>
          `;
        }

        if (u.social_links) {
          for (let [p, link] of Object.entries(u.social_links)) {
            if (link) {
              const platform = p.split('_')[0]; // strip our unique suffix
              let icon = getContactIcon(platform);
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
            let icon = getContactIcon(c.type);

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

        if (u.role === 'Event Manager' || u.role === 'Sponsor') {
          profileUserId = u.id;
          loadManagerEvents(u.id, u.role);
        }
      } catch (err) {
        console.error('Error loading profile:', err);
        document.getElementById('profile-loader').innerHTML = '<div class="empty-state"><div class="empty-icon"><svg style="width:36px; height:36px; stroke:var(--danger); stroke-width:2; fill:none; display:block; margin:0 auto;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg></div><p>Error loading profile</p></div>';
      }
    }

    let allMgrEvents = [];

    async function loadManagerEvents(userId, role) {
      try {
        const res = await api.get('/profile/' + userId + '/portfolio');
        const section = document.getElementById('mgr-stats-section');
        if (!section) return;
        section.style.display = 'block';

        if (role === 'Sponsor' || role === 'Company') {
          const title = document.querySelector('.mgr-section-title');
          if (title) title.innerHTML = `<svg style="width:16px; height:16px; stroke:currentColor; fill:none; stroke-width:2; margin-inline-end:6px; vertical-align:middle; display:inline-block;" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg> ${t('Sponsored Events History')}`;

          const msTotalLabel = document.querySelector('#ms-total').previousElementSibling;
          if (msTotalLabel) msTotalLabel.textContent = t('Sponsored');

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
          ? [1, 2, 3, 4, 5].map(i => `<span style="color:${i <= avgRating ? '#eab308' : 'rgba(255,255,255,.15)'};font-size:1.1rem">&#9733;</span>`).join('')
          : '—';

        applyMgrFilter();
      } catch (err) {
        console.error('Error loading manager events:', err);
        const tbody = document.getElementById('mgr-events-body');
        if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="color:var(--danger);text-align:center">Failed to load events</td></tr>';
      }
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
        tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><svg style="width:48px; height:48px; stroke:var(--accent2); fill:none; stroke-width:1.5;" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></div><p>No events found</p></div></td></tr>`;
        return;
      }
      tbody.innerHTML = events.map((ev, i) => {
        const ratingBadge = ev.average_rating > 0
          ? `<span style="background:rgba(234,179,8,.12);color:#eab308;border:1px solid rgba(234,179,8,.25);padding:2px 8px;border-radius:12px;font-size:.72rem;font-weight:700">&#9733; ${Math.round(ev.average_rating)}/5</span>`
          : '';
        return `<tr>
            <td style="color:var(--text-muted)">${i + 1}</td>
            <td>
                <div style="font-weight:600; ${ev.status === 'cancelled' ? 'text-decoration:line-through; color:var(--danger)' : ''}">${ev.title}</div>
                ${ratingBadge}
                ${ev.status === 'cancelled' ? `<span class="badge badge-cancelled" style="font-size:9px; padding:1px 6px; margin-top:4px;">${t('cancelled')}</span>` : ''}
            </td>
            <td style="color:var(--text-muted)">${ev.venue?.name || '—'}</td>
            <td style="color:var(--text-muted);white-space:nowrap">${fmtDateShort(ev.start_time)}</td>
            <td style="color:var(--text-muted)">${ev.capacity ? ev.capacity : t('Unlimited')}</td>
            <td><div style="display:inline-flex;flex-wrap:wrap;gap:6px;align-items:center;">${badge(ev.status)} ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}</div></td>
            <td style="display:flex;gap:6px;flex-wrap:wrap">
              <button class="btn btn-ghost btn-sm" onclick="showMgrEventDetails(${ev.id})" title="${t('View Details')}">${t('Details')}</button>
              ${(me && (me.role === 'Admin' || me.id == profileUserId)) ? `<button class="btn btn-sm" style="background:rgba(34,211,238,.12);color:#22d3ee;border:1px solid rgba(34,211,238,.25)" onclick="window.location.href='${me.role === 'Admin' ? '/admin' : '/manager'}/event-stats/${ev.id}'" title="${t('View Statistics')}">${t('Stats')}</button>` : ''}
            </td>
          </tr>`;
      }).join('');
    }

    // ── Event Details Modal ─────────────────────────────────────────────────────
    const _tColors = { 'مؤتمر': '#3b82f6', 'ندوة': '#8b5cf6', 'ورشة عمل': '#10b981', 'دورة تدريبية': '#06b6d4', 'ترفيه': '#ec4899', 'ملتقى علمي': '#f59e0b', 'رياضة': '#22c55e', 'تقنية': '#6366f1', 'اجتماعية': '#f97316' };

    function showMgrEventDetails(eventId) {
      const modal = document.getElementById('prof-event-modal');
      const content = document.getElementById('prof-event-content');
      modal.classList.add('open');
      content.innerHTML = '<div class="spinner" style="margin:auto"></div>';

      api.get(`/events/${eventId}`).then(res => {
        if (!res.ok) {
          content.innerHTML = `<div class="empty-state"><div class="empty-icon"><svg style="width:48px; height:48px; stroke:#ef4444; fill:none; stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg></div><p>Could not fetch event details</p></div>`;
          return;
        }
        const ev = res.data;
        const eType = ev.event_type || 'Other';
        const tColor = _tColors[eType] || '#6b7280';
        const tagSvg = `<svg style="width:18px; height:18px; stroke:currentColor; fill:none; stroke-width:2; margin-inline-end:8px; vertical-align:middle; display:inline-block;" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>`;

        const bannerSection = ev.image
          ? `<div class="ed-banner" style="background-image:url('/storage/${ev.image}')"><div class="ed-banner-fade"></div></div>`
          : `<div class="ed-banner ed-banner-placeholder"><span class="ed-banner-emoji" style="color:var(--text-muted); opacity: 0.3; display: flex; align-items: center; justify-content: center;">${tagSvg}</span><div class="ed-banner-fade"></div></div>`;

        const rejectionSection = (ev.status === 'rejected' && ev.rejection_reason)
          ? `<div class="ed-rejection"><span class="ed-rej-label"><svg style="width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2; margin-inline-end:4px; vertical-align:middle; display:inline-block;" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg> ${t('Rejection Reason')}</span><p>${ev.rejection_reason}</p></div>`
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
                          <div style="font-size:0.85rem; font-weight:600; color:#fff;">${sp.name}</div>
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
              <span class="ed-type-pill" style="--tcolor:${tColor}">${eType}</span>
            </div>
            <div class="ed-badges">
              ${ev.status ? badge(ev.status) : ''}
              ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}
            </div>
          </div>

          ${ev.status === 'cancelled' ? `
            <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 12px; padding: 14px; margin-bottom: 20px;">
              <span style="display: block; font-size: 0.72rem; font-weight: 700; color: #ef4444; text-transform: uppercase; margin-bottom: 4px; display:flex; align-items:center; gap:6px;">
                <svg style="width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                Event Cancelled
              </span>
              <p style="margin: 0; color: #e2e8f0; font-size: 0.9rem; line-height: 1.5;">${ev.cancellation_reason || 'This event has been cancelled by the manager.'}</p>
            </div>
          ` : ''}

          ${ev.status === 'cancellation_requested' ? `
            <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 12px; padding: 14px; margin-bottom: 20px;">
              <span style="display: block; font-size: 0.72rem; font-weight: 700; color: #f59e0b; text-transform: uppercase; margin-bottom: 4px; display:flex; align-items:center; gap:6px;">
                <svg style="width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                Cancellation Pending
              </span>
              <p style="margin: 0; color: #e2e8f0; font-size: 0.9rem; line-height: 1.5;">The manager has requested to cancel this event. Awaiting administrator approval.</p>
            </div>
          ` : ''}

          ${rejectionSection}

          <div class="ed-info-grid">
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></div>
              <div><div class="ed-info-label">${t('Venue')}</div><div class="ed-info-value">${ev.venue?.name || ev.external_venue_name || '—'}</div></div>
            </div>
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg></div>
              <div><div class="ed-info-label">${t('Location')}</div><div class="ed-info-value">
                ${ev.venue?.location ? `<a href="${ev.venue.location.startsWith('http') ? ev.venue.location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.venue.location)}" target="_blank" style="color:inherit;text-decoration:underline;">${t('Open in Maps')} ↗</a>` 
                : (ev.external_venue_location ? `<a href="${ev.external_venue_location.startsWith('http') ? ev.external_venue_location : 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(ev.external_venue_location)}" target="_blank" style="color:inherit;text-decoration:underline;">${t('Open in Maps')} ↗</a>` : '—')}
              </div></div>
            </div>
            ${(me && (me.role === 'Admin' || me.id == ev.creator_id || me.id == ev.manager_id)) ? `
            ${!ev.venue_id && ev.booking_proof_path ? `
            <div class="ed-info-card ed-info-accent2" style="grid-column: 1 / -1; background:rgba(34,211,238,0.05); border-color:rgba(34,211,238,0.2);">
              <div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l4.5-4.5m.718-2.262a9 9 0 019-9" /></svg></div>
              <div><div class="ed-info-label" style="color:#22d3ee">${t('Booking Proof')}</div><div class="ed-info-value"><a href="/storage/${ev.booking_proof_path}" target="_blank" style="color:#22d3ee;text-decoration:underline;">${t('View Document ↗')}</a></div></div>
            </div>
            ` : ''}
            ${ev.ministry_document_path ? `
            <div class="ed-info-card" style="grid-column: 1 / -1; background:rgba(139,92,246,0.05); border-color:rgba(139,92,246,0.2); border: 1px solid rgba(139,92,246,0.2);">
              <div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></div>
              <div><div class="ed-info-label" style="color:#a78bfa">${t('Competent Authority Approval')}</div><div class="ed-info-value"><a href="/storage/${ev.ministry_document_path}" target="_blank" style="color:#a78bfa;text-decoration:underline;">${t('View Document ↗')}</a></div></div>
            </div>
            ` : `
            <div class="ed-info-card" style="grid-column: 1 / -1; background:rgba(239,68,68,0.05); border-color:rgba(239,68,68,0.2); border: 1px solid rgba(239,68,68,0.2);">
              <div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg></div>
              <div><div class="ed-info-label" style="color:#ef4444">${t('Competent Authority Approval')}</div><div class="ed-info-value" style="color:#ef4444;">${t('Not uploaded')}</div></div>
            </div>
            `}
            ` : ''}
            ${(() => {
              const schedule = (ev.published_schedule && ev.published_schedule.length > 0) ? ev.published_schedule : 
                               (ev.external_schedule && ev.external_schedule.length > 0 ? ev.external_schedule : 
                               (ev.internal_schedule && ev.internal_schedule.length > 0 ? ev.internal_schedule : null));
              const pubDates = (ev.published_schedule && ev.published_schedule.length > 0) ? ev.published_schedule.map(p => p.date) : [];
              if (schedule) {
                const dn = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                let scheduleHtml = '<div style="grid-column: 1 / -1;">';
                scheduleHtml += '<div style="font-size:0.72rem;font-weight:700;color:#a78bfa;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">' + t('Event Schedule') + ' (' + schedule.length + ' ' + (schedule.length > 1 ? t('days') : t('day')) + ')</div>';
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
                return '<div class="ed-info-card ed-info-accent"><div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div><div><div class="ed-info-label">' + t('Start') + '</div><div class="ed-info-value">' + fmtDate(ev.start_time) + '</div></div></div>' +
                       '<div class="ed-info-card ed-info-accent"><div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div><div><div class="ed-info-label">' + t('End') + '</div><div class="ed-info-value">' + fmtDate(ev.end_time) + '</div></div></div>';
              }
            })()}
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg></div>
              <div><div class="ed-info-label">${t('Capacity')}</div><div class="ed-info-value">${ev.capacity ? ev.capacity : t('Unlimited')}</div></div>
            </div>
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon" style="display:flex; align-items:center; justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-12v.75m0 3v.75m0 3v.75m0 3V18M3 6.75A1.75 1.75 0 014.75 5h14.5A1.75 1.75 0 0121 6.75v10.5a1.75 1.75 0 01-1.75 1.75H4.75A1.75 1.75 0 013 17.25V6.75z" /></svg></div>
              <div><div class="ed-info-label">${t('Tickets Booked')}</div><div class="ed-info-value">${ev.tickets_count ?? '—'}</div></div>
            </div>
          </div>
          
          ${sponsorsHtml}

          ${(() => { let ag = ev.agenda; if (!ag || typeof ag !== 'object') return ''; const isArr = Array.isArray(ag); if (isArr && !ag.length) return ''; if (!isArr && !Object.keys(ag).length) return ''; const pubDates = ev.published_schedule && ev.published_schedule.length > 0 ? ev.published_schedule.map(p => p.date) : null; if (pubDates && !isArr) { const f = {}; Object.keys(ag).forEach(ds => { if (pubDates.includes(ds)) f[ds] = ag[ds]; }); ag = f; if (!Object.keys(ag).length) return ''; } const agendaIcon = `<svg style="width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2; display:inline-block; vertical-align:middle; margin-inline-end:4px;" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><line x1="9" y1="12" x2="15" y2="12"></line><line x1="9" y1="16" x2="15" y2="16"></line><line x1="9" y1="8" x2="10" y2="8"></line></svg>`; let h = '<div style="margin-top:16px;"><div style="font-size:0.72rem;font-weight:700;color:#22d3ee;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;display:flex;align-items:center;">' + agendaIcon + ' Event Agenda</div>'; const dn = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'], mn = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']; const renderItem = a => `<div style="display:flex;flex-direction:column;gap:4px;background:rgba(34,211,238,0.04);border:1px solid rgba(34,211,238,0.12);border-radius:10px;padding:8px 14px;margin:0 8px;"><div style="display:flex;align-items:center;gap:10px;"><div style="display:flex;align-items:center;gap:6px;min-width:110px;"><span style="background:rgba(34,211,238,0.1);color:#22d3ee;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.start_time}</span><span style="color:#64748b;font-size:0.7rem;">→</span><span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">${a.end_time}</span></div><div style="flex:1;font-size:0.85rem;color:#e2e8f0;font-weight:500;">${a.title}</div></div>${a.description ? `<div style="font-size:0.78rem;color:#94a3b8;margin-top:4px;padding-inline-start:12px;border-inline-start:2px solid rgba(34,211,238,0.2);text-align:start;line-height:1.4;">${a.description}</div>` : ''}</div>`; if (!isArr) { Object.keys(ag).sort().forEach(ds => { const items = ag[ds]; if (!items || !items.length) return; const d = new Date(ds + 'T00:00:00'); const itemCalIcon = `<svg style="width:12px; height:12px; stroke:currentColor; fill:none; stroke-width:2; display:inline-block; vertical-align:middle; margin-inline-end:4px;" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>`; h += `<div style="margin-bottom:10px;"><div style="font-size:0.68rem;font-weight:600;color:#a78bfa;margin-bottom:6px;padding:4px 10px;background:rgba(139,92,246,0.08);border-radius:6px;display:inline-flex;align-items:center;">` + itemCalIcon + ` ${dn[d.getDay()]} ${d.getDate()} ${mn[d.getMonth()]} ${d.getFullYear()}</div><div style="display:flex;flex-direction:column;gap:4px;">${items.map(renderItem).join('')}</div></div>`; }); } else { h += `<div style="display:flex;flex-direction:column;gap:4px;">${ag.map(renderItem).join('')}</div>`; } return h + '</div>'; })()}

          <div class="ed-footer" style="margin-top: 8px;">
            <span class="ed-footer-label">${t('Created by')}</span>
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
      <button class="ed-close-btn" onclick="closeProfEventModal()">&times;</button>
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
    .ed-info-accent .ed-info-icon {
      color: var(--accent);
    }

    .ed-info-accent2 .ed-info-label {
      color: var(--accent2);
    }
    .ed-info-accent2 .ed-info-icon {
      color: var(--accent2);
    }

    .ed-info-warning .ed-info-label {
      color: var(--warning);
    }
    .ed-info-warning .ed-info-icon {
      color: var(--warning);
    }

    .ed-info-danger .ed-info-label {
      color: #ef4444;
    }
    .ed-info-danger .ed-info-icon {
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
  </style>
</body>

</html>