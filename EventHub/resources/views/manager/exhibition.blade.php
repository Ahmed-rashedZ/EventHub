<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Exhibitions – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <link rel="icon" href="/images/logo.jpg" type="image/jpeg">
  <style>
    .exh-card { 
      background: linear-gradient(135deg, rgba(25,28,36,0.4) 0%, rgba(35,38,58,0.5) 100%); 
      border: 1px solid var(--border); 
      border-radius: 20px; 
      margin-bottom: 28px; 
      overflow: hidden; 
      box-shadow: var(--shadow); 
      backdrop-filter: blur(12px);
      position: relative;
    }
    .exh-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--accent), var(--accent2));
      z-index: 10;
    }
    .exh-header { 
      padding: 24px 28px; 
      background: rgba(255,255,255,0.01); 
      border-bottom: 1px solid var(--border); 
      cursor: pointer; 
      display: flex; 
      justify-content: space-between; 
      align-items: center; 
      gap: 16px; 
      transition: all 0.25s; 
    }
    .exh-body .form-control { 
      background: rgba(255,255,255,0.03); 
      border: 1px solid var(--border); 
      color: #fff; 
      transition: all 0.2s;
    }
    .exh-body .form-control:focus {
      background: rgba(110,64,242,0.08);
      border-color: var(--accent2);
    }
    .exh-header:hover { background: rgba(110,64,242,0.05); }
    .exh-title { 
      font-size: 1.3rem; 
      font-weight: 800; 
      color: #fff; 
      display: flex; 
      align-items: center; 
      gap: 12px; 
      letter-spacing: -0.01em;
      text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
    .exh-meta { font-size: 0.85rem; color: var(--text-muted); display: flex; gap: 20px; align-items: center; margin-top: 8px; }
    .exh-stats { display: flex; gap: 12px; flex-shrink: 0; }
    .exh-stat { 
      background: rgba(0,0,0,0.2); 
      border: 1px solid var(--border); 
      border-radius: 12px; 
      padding: 8px 16px; 
      text-align: center; 
      min-width: 80px; 
      transition: all 0.2s; 
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
    }
    .exh-stat:hover { border-color: rgba(255,255,255,0.15); transform: translateY(-2px); }
    .exh-stat .label { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8; margin-top: 2px; }
    .exh-stat .val { font-size: 1.2rem; font-weight: 900; line-height: 1; }
    .exh-body { padding: 0; max-height: 0; overflow: hidden; transition: max-height 0.35s ease; }
    .exh-body.open { max-height: 2000px; padding: 0; }
    .exh-chevron { transition: transform 0.3s; font-size: 0.8rem; color: var(--text-muted); }
    .exh-chevron.open { transform: rotate(180deg); }



    /* Booth modal */
    .booth-form-row { display: flex; gap: 10px; margin-bottom: 10px; }
    .booth-form-row .form-group { flex: 1; margin-bottom: 0; }
    .booth-form-row .form-control { font-size: 0.85rem; padding: 8px 10px; }
  </style>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo" style="display:flex; justify-content:center; align-items:center; padding: 8px 0;"><img src="/images/logo.jpg" alt="EventHub Logo" style="width: 85px; height: 85px; object-fit: contain; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></div>
    <nav class="sidebar-nav" id="sidebar-links"></nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Exhibition Management</h1><p class="page-subtitle">Manage exhibitions, companies, booths & rankings</p></div>
      <div style="display:flex; gap:10px; align-items:center;">
        <div id="notification-bell"></div>
      </div>
    </div>

    <!-- Discovery Section: Available Companies -->
    <div class="discovery-section">
      <h2 class="page-title" style="font-size: 1.1rem; font-weight: 600; margin-top:20px;">Available Companies</h2>
      <div class="card" style="margin-bottom: 30px;">
        <div class="table-wrap">
          <table>
            <thead><tr><th>Company</th><th>Sector</th><th>Contact</th><th>Action</th></tr></thead>
            <tbody id="companies-body">
              <tr class="loading-row"><td colspan="4"><div class="spinner" style="margin:auto"></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Exhibition Groups -->
    <h2 class="page-title" style="font-size: 1.2rem; font-weight: 600;">My Exhibitions</h2>
    <div id="exhibition-groups">
      <div class="card" style="padding:40px; text-align:center;"><div class="spinner" style="margin:auto"></div></div>
    </div>
  </main>
</div>

<!-- Invitation Modal -->
<div class="modal-overlay" id="req-modal">
  <div class="modal" style="max-width: 450px;">
    <div class="modal-header">
      <h3 class="modal-title">Invite to Exhibition</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form id="req-form">
      <div class="form-group">
        <label class="form-label">Event</label>
        <select id="r-event" class="form-control i18n-skip" required title="Select Exhibition Event">
          <option value="">Select an exhibition event…</option>
        </select>
      </div>
      <div class="form-group" id="target-company-group" style="display:none; background: rgba(34,211,238,0.05); padding: 15px; border-radius: 12px; border: 1px solid rgba(34,211,238,0.1);">
        <label class="form-label">Target Company</label>
        <div id="r-company-display" style="font-weight:700; color:var(--text); margin-bottom: 5px; font-size: 1.1rem;"></div>
        <input type="hidden" id="r-company-id" value=""/>
      </div>
      <div class="form-group">
        <label class="form-label">Invitation Message</label>
        <textarea id="r-message" class="form-control" placeholder="Tell the company why they should exhibit..." style="min-height: 120px;"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Send Invitation</button>
      </div>
    </form>
  </div>
</div>


<!-- View Message Modal -->
<div class="modal-overlay" id="msg-modal">
  <div class="modal" style="max-width: 500px;">
    <div class="modal-header">
      <h3 class="modal-title">Invitation Message</h3>
      <button class="modal-close" onclick="closeMsgModal()">✕</button>
    </div>
    <div class="modal-body" id="msg-content" style="padding: 20px; font-size: 15px; line-height: 1.6; color: var(--text);"></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary" onclick="closeMsgModal()" style="width: 100%;">Close</button>
    </div>
  </div>
</div>

<!-- Agreement Negotiation Modal -->
<div class="modal-overlay" id="agreement-modal">
  <div class="modal" style="max-width:520px; width:95%; padding:0; border-top:3px solid #0ea5e9; max-height:85vh; display:flex; flex-direction:column; border-radius:16px;">
    <div style="padding:16px 20px 0; display:flex; justify-content:space-between; align-items:center;">
      <div><h3 class="modal-title" style="margin:0;font-size:1rem;">Contract Negotiation</h3><p style="font-size:0.7rem;color:var(--text-muted);margin:2px 0 0">Negotiate terms with the company</p></div>
      <button class="modal-close" onclick="closeAgreementModal()">✕</button>
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
  const user = requireRole('Event Manager');
  if (user) { populateSidebar(user); setActiveNav(); loadData(); loadMyExhibitionEvents(); loadAvailableCompanies(); }

  function badge(status) {
    const map = {
      pending:     { bg:'rgba(234,179,8,0.12)', color:'#eab308', border:'rgba(234,179,8,0.25)', label:'Pending' },
      negotiating: { bg:'rgba(34,211,238,0.12)', color:'#22d3ee', border:'rgba(34,211,238,0.25)', label:'Negotiating' },
      accepted:    { bg:'rgba(16,185,129,0.12)', color:'#10b981', border:'rgba(16,185,129,0.25)', label:'Accepted' },
      rejected:    { bg:'rgba(239,68,68,0.12)', color:'#ef4444', border:'rgba(239,68,68,0.25)', label:'Rejected' },
      cancelled:   { bg:'rgba(156,163,175,0.12)', color:'#9ca3af', border:'rgba(156,163,175,0.25)', label:'Cancelled' },
    };
    const s = map[status] || { bg:'rgba(156,163,175,0.1)', color:'#9ca3af', border:'rgba(156,163,175,0.2)', label: status };
    return `<span style="padding:3px 10px;border-radius:6px;font-size:0.68rem;font-weight:700;background:${s.bg};color:${s.color};border:1px solid ${s.border}">${t(s.label)}</span>`;
  }

  function fmtDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-GB', { year: 'numeric', month: 'short', day: 'numeric' });
  }

  async function loadData() {
    const res = await api.get('/exhibition');
    const container = document.getElementById('exhibition-groups');

    if (!res.ok || !res.data.length) {
      container.innerHTML = `<div class="card" style="padding:40px;text-align:center;">
        <div style="font-size:2.5rem;margin-bottom:10px;">🏛️</div>
        <p style="color:var(--text-muted)">${t('No exhibition applications yet')}</p>
        <p style="color:var(--text-muted);font-size:0.8rem;margin-top:6px;">${t('Invite companies from the list above to get started.')}</p>
      </div>`;
      return;
    }

    // Group by event
    const groups = {};
    res.data.forEach(app => {
      const eid = app.event_id;
      if (!groups[eid]) {
        groups[eid] = { event: app.event, apps: [] };
      }
      groups[eid].apps.push(app);
    });

    // Sort: events with pending first, then by date desc
    const sorted = Object.values(groups).sort((a, b) => {
      const aHasPending = a.apps.some(x => x.status === 'pending');
      const bHasPending = b.apps.some(x => x.status === 'pending');
      if (aHasPending && !bHasPending) return -1;
      if (!aHasPending && bHasPending) return 1;
      return new Date(b.event?.start_time || 0) - new Date(a.event?.start_time || 0);
    });

    container.innerHTML = sorted.map((g, gi) => {
      const ev = g.event || {};
      const apps = g.apps;
      const total = apps.length;
      const accepted = apps.filter(a => a.status === 'accepted').length;
      const pending = apps.filter(a => a.status === 'pending').length;
      const negotiating = apps.filter(a => a.status === 'negotiating').length;
      

      // Sort apps: accepted (by rank) first, then negotiating, then pending, then others
      apps.sort((a, b) => {
        const order = { accepted: 0, negotiating: 1, pending: 2 };
        const aO = order[a.status] ?? 3;
        const bO = order[b.status] ?? 3;
        return aO - bO;
      });

      const isPast = ev.end_time && new Date(ev.end_time) < new Date();
      const isOpen = gi === 0; // auto-open first group

      const rows = apps.map((app, i) => {
        const comp = app.company || {};

        let actionHtml = '';
        if (app.status === 'pending') {
          if (app.initiator === 'company') {
            actionHtml = `
              <button class="btn btn-success" style="padding:3px 10px;font-size:11px;font-weight:600;" onclick="respondReq(${app.id}, 'accepted')">✅ ${t('Accept')}</button>
              <button class="btn btn-danger" style="padding:3px 10px;font-size:11px;font-weight:600;" onclick="respondReq(${app.id}, 'rejected')">❌ ${t('Reject')}</button>
            `;
          } else {
            actionHtml = `<span style="font-size:11px;color:var(--text-muted);">${t('Awaiting Response')}</span>`;
          }
        } else if (app.status === 'negotiating' || app.status === 'accepted') {
          actionHtml = `
            <button class="btn btn-sm" onclick="openAgreementModal(${app.id}, 'exhibition')" style="padding:3px 10px;font-size:11px;background:rgba(34,211,238,0.1);color:#22d3ee;border:1px solid rgba(34,211,238,0.2);font-weight:600;">📋 ${t('Contract')}</button>
          `;
        }

        return `
        <tr>
          <td style="font-weight:600;cursor:pointer;color:var(--accent2);" onclick="navigateToProfile(${app.company_id})">
            <div class="i18n-skip">${comp.name || '—'}</div>
            <div style="font-size:0.7rem; color:var(--text-muted); font-weight:400;">${comp.profile?.company_type || t('General')}</div>
          </td>
          <td style="color:var(--text-muted);font-size:0.82rem;">${app.product_category || '—'}</td>
          <td>${badge(app.status)}</td>
          <td><div style="display:flex;gap:6px;flex-wrap:wrap;">${actionHtml}</div></td>
        </tr>`;
      }).join('');

      return `
      <div class="exh-card">
        <div class="exh-header" onclick="toggleExhGroup(${gi})">
          <div>
            <div class="exh-title" style="color:var(--accent2);">
              ${isPast ? '📁' : '🏢'} <span class="i18n-skip">${ev.title || '—'}</span>
              ${isPast ? `<span style="font-size:0.65rem;background:rgba(156,163,175,0.1);color:var(--text-muted);padding:2px 8px;border-radius:4px;border:1px solid var(--border);">${t('Past')}</span>` : ''}
            </div>
            <div class="exh-meta">
              <span style="display:flex; align-items:center; gap:4px;">📅 ${fmtDate(ev.start_time)}</span>
              <span style="display:flex; align-items:center; gap:4px;">📍 ${ev.venue?.name || '—'}</span>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:16px;">
            <div class="exh-stats">
              <div class="exh-stat" style="color:var(--success); border-color:rgba(34,197,94,0.2);">
                <div class="val">${accepted}</div>
                <div class="label">${t('Accepted')}</div>
              </div>
              <div class="exh-stat" style="color:var(--accent); border-color:rgba(110,64,242,0.2);">
                <div class="val">${negotiating}</div>
                <div class="label">${t('Negotiating')}</div>
              </div>
              <div class="exh-stat" style="color:var(--warning); border-color:rgba(245,158,11,0.2);">
                <div class="val">${pending}</div>
                <div class="label">${t('Pending')}</div>
              </div>
              <div class="exh-stat" style="color:var(--text-primary); border-color:var(--border);">
                <div class="val">${total}</div>
                <div class="label">${t('Total')}</div>
              </div>
            </div>
            <span class="exh-chevron ${isOpen ? 'open' : ''}" id="chev-${gi}">▼</span>
          </div>
        </div>
        <div class="exh-body ${isOpen ? 'open' : ''}" id="exh-body-${gi}">
          <div style="padding:10px 20px; background:rgba(0,0,0,0.1); display:flex; gap:10px; align-items:center; border-bottom:1px solid var(--border);">
            <div style="position:relative; flex:1;">
               <input type="text" class="form-control" placeholder="${t('Search company...')}" style="padding-left:32px; font-size:0.75rem; height:32px; border-radius:6px;" onkeyup="filterCompanies(${gi}, this.value)">
               <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:0.8rem; opacity:0.5;">🔍</span>
            </div>
          </div>
          <div class="table-wrap" style="margin:0;">
            <table id="table-${gi}">
              <thead><tr><th>${t('Company')}</th><th>${t('Category')}</th><th>${t('Status')}</th><th>${t('Actions')}</th></tr></thead>
              <tbody>${rows}</tbody>
            </table>
          </div>
        </div>
      </div>`;
    }).join('');
  }

  function toggleExhGroup(idx) {
    const body = document.getElementById(`exh-body-${idx}`);
    const chev = document.getElementById(`chev-${idx}`);
    body.classList.toggle('open');
    chev.classList.toggle('open');
  }

  function filterCompanies(idx, query = '') {
    const table = document.getElementById(`table-${idx}`);
    if (!table) return;
    const rows = table.querySelectorAll('tbody tr');
    const q = query.toLowerCase().trim();
    
    rows.forEach(row => {
      // The first cell contains name and type divs
      const companyName = row.cells[0].querySelector('div')?.innerText.toLowerCase() || row.cells[0].innerText.toLowerCase();
      row.style.display = companyName.includes(q) ? '' : 'none';
    });
  }


  // ── Accept / Reject ──
  async function respondReq(id, status) {
    const res = await api.put(`/exhibition/${id}`, { status });
    if (res.ok) {
      showToast(status === 'accepted' ? t('Preliminary acceptance successful! Contract negotiation started.') + ' 📝' : t('Rejected.'), 'success');
      loadData();
    } else {
      showToast(res.data?.message || t('Error'), 'error');
    }
  }

  // ── Available Companies ──
  async function loadAvailableCompanies() {
    const res = await api.get('/companies/available');
    const tbody = document.getElementById('companies-body');
    if (!res.ok || !res.data.length) {
      tbody.innerHTML = '<tr><td colspan="4"><div class="empty-state"><p>' + t('No available companies right now.') + '</p></div></td></tr>';
      return;
    }

    tbody.innerHTML = res.data.map(c => {
      let logo = getLogo(c);
      return `
      <tr>
        <td>
          <div style="display:flex; align-items:center; gap:10px; cursor:pointer;" onclick="navigateToProfile(${c.id})">
            <img src="${logo}" style="width:34px; height:34px; border-radius:6px; object-fit:cover; border: 1px solid var(--border);"/>
            <div>
              <div style="font-weight:600;" class="i18n-skip">${c.name}</div>
              <div style="font-size:0.7rem; color:var(--text-muted);">${t('Company')}</div>
            </div>
          </div>
        </td>
        <td style="color:var(--text-muted); font-size: 0.85rem;" class="i18n-skip">${c.profile?.company_type || t('Other')}</td>
        <td style="color:var(--text-muted)">${c.email}</td>
        <td>
          <div style="display:flex; gap:8px;">
            <button class="btn btn-ghost btn-sm" onclick="navigateToProfile(${c.id})">${t('View Profile')}</button>
            <button class="btn btn-primary btn-sm" onclick="openModal(${c.id}, '${c.name}')">${t('Invite')}</button>
          </div>
        </td>
      </tr>`;
    }).join('');
  }

  function getLogo(u) {
    let logo = '/images/default-avatar.png';
    if (u.image && u.image.trim() !== '') {
      logo = (u.image.startsWith('http') || u.image.startsWith('/')) ? u.image : '/storage/' + u.image;
    } else if (u.avatar && u.avatar.trim() !== '') {
      logo = (u.avatar.startsWith('http') || u.avatar.startsWith('/')) ? u.avatar : '/storage/' + u.avatar;
    } else if (u.profile && u.profile.logo) {
      logo = (u.profile.logo.startsWith('http') || u.profile.logo.startsWith('/')) ? u.profile.logo : '/' + u.profile.logo;
    }
    return logo;
  }

  async function loadMyExhibitionEvents() {
    const res = await api.get('/events/list/my');
    const sel = document.getElementById('r-event');
    if (!res.ok) return;
    const exhibitionEvents = res.data.filter(e => e.is_exhibition);
    sel.innerHTML = '<option value="">' + t('Select an exhibition event…') + '</option>' + exhibitionEvents.map(e => `<option value="${e.id}">${e.title}</option>`).join('');
  }

  function navigateToProfile(id) { window.open(`/profile/${id}`, '_blank'); }

  // ── Invite Modal ──
  function openModal(targetId = null, targetName = null) {
    document.getElementById('req-modal').classList.add('open');
    if (targetId) {
      document.getElementById('target-company-group').style.display = 'block';
      document.getElementById('r-company-id').value = targetId;
      document.getElementById('r-company-display').innerText = targetName;
    } else {
      document.getElementById('target-company-group').style.display = 'none';
      document.getElementById('r-company-id').value = '';
    }
  }
  function closeModal() {
    document.getElementById('req-modal').classList.remove('open');
    document.getElementById('req-form').reset();
    document.getElementById('target-company-group').style.display = 'none';
    document.getElementById('r-company-id').value = '';
  }

  function showMsg(message) {
    document.getElementById('msg-content').innerText = message;
    document.getElementById('msg-modal').classList.add('open');
  }
  function closeMsgModal() { document.getElementById('msg-modal').classList.remove('open'); }

  document.getElementById('req-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const targetId = document.getElementById('r-company-id').value;
    const eventId = +document.getElementById('r-event').value;
    const message = document.getElementById('r-message').value;

    if (!eventId) return showToast(t('Please select an exhibition event'), 'error');

    const res = await api.post('/exhibition', {
      event_id: eventId,
      company_id: +targetId,
      message: message,
      initiator: 'event_manager'
    });

    if (res.ok) {
      showToast(t('Invitation sent successfully!'), 'success');
      closeModal();
      loadData();
    } else {
      showToast(res.data?.message || t('Error sending invitation'), 'error');
    }
  });
</script>
</body>
</html>
