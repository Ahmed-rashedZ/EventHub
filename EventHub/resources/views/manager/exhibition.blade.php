<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Exhibitions – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <link rel="icon" href="/images/logo.png" type="image/png">
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

    .tab-btn { padding: 12px 20px; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s; }
    .tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); }
    .tab-pane { display: none; }
    .tab-pane.active { display: block; }

    .layout-grid { display: flex; flex-direction: column; gap: 24px; padding: 10px 0; }
    .zone-box { 
      background: rgba(255,255,255,0.01); 
      border: 1px solid rgba(255,255,255,0.05); 
      border-radius: 16px; 
      padding: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .zone-header { 
      display: flex; 
      justify-content: space-between; 
      align-items: center; 
      margin-bottom: 16px; 
      border-bottom: 1px solid rgba(255,255,255,0.05); 
      padding-bottom: 12px; 
    }
    .booth-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; }
    .booth-item { 
      background: rgba(255,255,255,0.02); 
      border: 1px solid rgba(255,255,255,0.05); 
      border-radius: 12px; 
      padding: 12px; 
      position: relative;
      display: flex; 
      flex-direction: column; 
      gap: 2px; 
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: default;
    }
    .booth-item:hover {
      background: rgba(255,255,255,0.04);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .booth-item.available { border-top: 3px solid #10b981; }
    .booth-item.allocated { border-top: 3px solid var(--accent); background: rgba(110,64,242,0.03); }
    .booth-num { font-weight: 800; font-size: 0.95rem; color: #fff; }
    .booth-size { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; }
    .booth-comp { 
      font-size: 0.75rem; 
      color: var(--accent); 
      font-weight: 700; 
      text-overflow: ellipsis; 
      overflow: hidden; 
      white-space: nowrap; 
      margin-top: 4px;
      border-top: 1px solid rgba(110,64,242,0.1);
      padding-top: 4px;
    }
    .booth-actions { 
      position: absolute; 
      top: 8px; 
      right: 8px; 
      display: none; 
      gap: 6px; 
      background: rgba(0,0,0,0.6);
      padding: 4px;
      border-radius: 6px;
      backdrop-filter: blur(4px);
    }
    .booth-item:hover .booth-actions { display: flex; }
    .btn-icon { 
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: none; 
      border: none; 
      color: var(--text-muted); 
      cursor: pointer; 
      font-size: 0.85rem; 
      border-radius: 4px;
      transition: all 0.2s;
    }
    .btn-icon:hover { background: rgba(255,255,255,0.1); color: #fff; }

    /* Fix optical group styling in dark mode */
    select optgroup {
      background: #191c24;
      color: var(--accent2);
      font-weight: 700;
      font-style: normal;
    }
    select option {
      background: #191c24;
      color: #fff;
    }
  </style>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px;"><img src="/images/logo.png" alt="EventHub Logo" style="height: 60px; width: auto; object-fit: contain;"></div>
    <nav class="sidebar-nav" id="sidebar-links"></nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title"><script>document.write(t('Exhibition Management'))</script></h1><p class="page-subtitle"><script>document.write(t('Manage exhibitions, companies, booths & rankings'))</script></p></div>
      <div style="display:flex; gap:10px; align-items:center;">
        <div id="notification-bell"></div>
      </div>
    </div>

    <!-- Discovery Section: Available Companies -->
    <div class="discovery-section">
      <h2 class="page-title" style="font-size: 1.1rem; font-weight: 600; margin-top:20px;"><script>document.write(t('Available Companies'))</script></h2>
      <div class="card" style="margin-bottom: 30px;">
        <div class="table-wrap">
          <table>
            <thead><tr><th><script>document.write(t('Company'))</script></th><th><script>document.write(t('Sector'))</script></th><th><script>document.write(t('Contact'))</script></th><th><script>document.write(t('Actions'))</script></th></tr></thead>
            <tbody id="companies-body">
              <tr class="loading-row"><td colspan="4"><div class="spinner" style="margin:auto"></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Exhibition Groups -->
    <h2 class="page-title" style="font-size: 1.2rem; font-weight: 600;"><script>document.write(t('Exhibitions'))</script></h2>
    <div id="exhibition-groups">
      <div class="card" style="padding:40px; text-align:center;"><div class="spinner" style="margin:auto"></div></div>
    </div>
  </main>
</div>

<!-- Invitation Modal -->
<div class="modal-overlay" id="req-modal">
  <div class="modal" style="max-width: 450px;">
    <div class="modal-header">
      <h3 class="modal-title"><script>document.write(t('Invite to Exhibition'))</script></h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form id="req-form">
      <div class="form-group">
        <label class="form-label"><script>document.write(t('Event'))</script></label>
        <select id="r-event" class="form-control i18n-skip" required title="Select Exhibition Event">
          <option value="">Select an exhibition event…</option>
        </select>
      </div>
      <div class="form-group" id="target-company-group" style="display:none; background: rgba(34,211,238,0.05); padding: 15px; border-radius: 12px; border: 1px solid rgba(34,211,238,0.1);">
        <label class="form-label"><script>document.write(t('Target Company'))</script></label>
        <div id="r-company-display" style="font-weight:700; color:var(--text); margin-bottom: 5px; font-size: 1.1rem;"></div>
        <input type="hidden" id="r-company-id" value=""/>
      </div>
      <div class="form-group">
        <label class="form-label"><script>document.write(t('Invitation Message'))</script></label>
        <textarea id="r-message" class="form-control" placeholder="Tell the company why they should exhibit..." data-placeholder="Tell the company why they should exhibit..." style="min-height: 120px;"></textarea>
        <script>document.querySelector('#r-message').placeholder = t('Tell the company why they should exhibit...')</script>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()"><script>document.write(t('Cancel'))</script></button>
        <button type="submit" class="btn btn-primary"><script>document.write(t('Send Invitation'))</script></button>
      </div>
    </form>
  </div>
</div>


<!-- View Message Modal -->
<div class="modal-overlay" id="msg-modal">
  <div class="modal" style="max-width: 500px;">
    <div class="modal-header">
      <h3 class="modal-title"><script>document.write(t('Invitation Message'))</script></h3>
      <button class="modal-close" onclick="closeMsgModal()">✕</button>
    </div>
    <div class="modal-body" id="msg-content" style="padding: 20px; font-size: 15px; line-height: 1.6; color: var(--text);"></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary" onclick="closeMsgModal()" style="width: 100%;"><script>document.write(t('Close'))</script></button>
    </div>
  </div>
</div>

  </div>
</div>

<!-- Agreement Negotiation Modal -->
<div class="modal-overlay" id="agreement-modal">
  <div class="modal" style="max-width:520px; width:95%; padding:0; border-top:3px solid #0ea5e9; max-height:85vh; display:flex; flex-direction:column; border-radius:16px;">
    <div style="padding:16px 20px 0; display:flex; justify-content:space-between; align-items:center;">
      <div><h3 class="modal-title" style="margin:0;font-size:1rem;"><script>document.write(t('Contract Negotiation'))</script></h3><p style="font-size:0.7rem;color:var(--text-muted);margin:2px 0 0"><script>document.write(t('Negotiate terms with the company'))</script></p></div>
      <button class="modal-close" onclick="closeAgreementModal()">✕</button>
    </div>
    <div id="agreement-content" style="padding:12px 20px 20px; overflow-y:auto; flex:1;">
      <div class="spinner" style="margin:40px auto"></div>
    </div>
  </div>
</div>

<!-- Assign Booth Modal -->
<div class="modal-overlay" id="booth-modal">
  <div class="modal" style="max-width: 400px;">
    <div class="modal-header">
      <h3 class="modal-title"><script>document.write(t('Assign Booth Location'))</script></h3>
      <button class="modal-close" onclick="closeBoothModal()">✕</button>
    </div>
    <form id="booth-form">
      <input type="hidden" id="b-app-id" />
      <div style="margin-bottom:15px; color:var(--text-muted); font-size:0.9rem;">
        <script>document.write(t('Assigning for:'))</script> <span id="b-comp-name" style="font-weight:700; color:var(--text);"></span>
      </div>
      <div class="form-group">
        <label class="form-label"><script>document.write(t('Select Booth'))</script></label>
        <select id="b-id-select" class="form-control" required></select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeBoothModal()"><script>document.write(t('Cancel'))</script></button>
        <button type="submit" class="btn btn-primary"><script>document.write(t('Save Assignment'))</script></button>
      </div>
    </form>
  </div>
</div>

<!-- Add Zone Modal -->
<div class="modal-overlay" id="zone-modal">
  <div class="modal" style="max-width: 400px;">
    <div class="modal-header">
      <h3 class="modal-title"><script>document.write(t('Add New Zone'))</script></h3>
      <button class="modal-close" onclick="closeZoneModalUI()">✕</button>
    </div>
    <form id="zone-form">
      <input type="hidden" id="z-event-id" />
      <div class="form-group">
        <label class="form-label"><script>document.write(t('Zone Name'))</script></label>
        <input type="text" id="z-name" class="form-control" placeholder="e.g. Zone A, Hall 1" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeZoneModalUI()"><script>document.write(t('Cancel'))</script></button>
        <button type="submit" class="btn btn-primary"><script>document.write(t('Create Zone'))</script></button>
      </div>
    </form>
  </div>
</div>

<!-- Add/Edit Booth Modal -->
<div class="modal-overlay" id="booth-item-modal">
  <div class="modal" style="max-width: 400px;">
    <div class="modal-header">
      <h3 class="modal-title" id="booth-item-title"><script>document.write(t('Add Booth'))</script></h3>
      <button class="modal-close" onclick="closeBoothItemModal()">✕</button>
    </div>
    <form id="booth-item-form">
      <input type="hidden" id="bi-id" />
      <input type="hidden" id="bi-zone-id" />
      <input type="hidden" id="bi-event-id" />
      <div class="form-group">
        <label class="form-label"><script>document.write(t('Booth Number'))</script></label>
        <input type="text" id="bi-number" class="form-control" placeholder="e.g. A-1" required>
      </div>
      <div class="form-group">
        <label class="form-label"><script>document.write(t('Size'))</script></label>
        <input type="text" id="bi-size" class="form-control" placeholder="e.g. 9 or 3x3">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeBoothItemModal()"><script>document.write(t('Cancel'))</script></button>
        <button type="submit" class="btn btn-primary"><script>document.write(t('Save Booth'))</script></button>
      </div>
    </form>
  </div>
</div>

<!-- Batch Generate Modal -->
<div class="modal-overlay" id="batch-modal">
  <div class="modal" style="max-width: 450px;">
    <div class="modal-header">
      <h3 class="modal-title"><script>document.write(t('Batch Generate Booths'))</script></h3>
      <button class="modal-close" onclick="closeBatchModalUI()">✕</button>
    </div>
    <form id="batch-form-ui">
      <input type="hidden" id="batch-zone-id" />
      <input type="hidden" id="batch-event-id" />
      <div class="booth-form-row">
        <div class="form-group">
          <label class="form-label"><script>document.write(t('Prefix'))</script></label>
          <input type="text" id="batch-prefix" class="form-control" placeholder="e.g. A-" value="A-" required>
        </div>
        <div class="form-group">
          <label class="form-label"><script>document.write(t('Start Number'))</script></label>
          <input type="number" id="batch-start" class="form-control" placeholder="e.g. 1" value="1" min="1" required>
        </div>
      </div>
      <div class="booth-form-row">
        <div class="form-group">
          <label class="form-label"><script>document.write(t('How many?'))</script></label>
          <input type="number" id="batch-count" class="form-control" placeholder="e.g. 10" value="8" min="1" max="100" required>
        </div>
        <div class="form-group">
          <label class="form-label"><script>document.write(t('Default Size'))</script></label>
          <input type="text" id="batch-size" class="form-control" placeholder="e.g. 9 or 3x3">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeBatchModalUI()"><script>document.write(t('Cancel'))</script></button>
        <button type="submit" class="btn btn-primary"><script>document.write(t('Generate Batch'))</script></button>
      </div>
    </form>
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

  let exhGroups = [];

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

    exhGroups = sorted;

    container.innerHTML = exhGroups.map((g, gi) => {
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
          if (app.status === 'accepted') {
            const isWithin14Days = ev.start_time && (new Date(ev.start_time) - new Date()) / (1000 * 60 * 60 * 24) < 14;
            const canChange = !app.booth || !isWithin14Days;

            if (canChange) {
              let boothBtnText = app.booth ? t('Change Booth') : t('Assign Booth');
              let boothBtnIcon = app.booth ? '🔄' : '📍';
              actionHtml += `
                <button class="btn btn-sm" onclick="openBoothModal(${app.id}, '${comp.name.replace(/'/g, "\\'")}', ${app.event_id})" style="padding:3px 10px;font-size:11px;background:rgba(16,185,129,0.1);color:#10b981;border:1px solid rgba(16,185,129,0.2);font-weight:600;">${boothBtnIcon} ${boothBtnText}</button>
              `;
            } else {
              actionHtml += `
                <button class="btn btn-sm" disabled style="padding:3px 10px;font-size:11px;background:rgba(255,255,255,0.05);color:var(--text-muted);border:1px solid var(--border);cursor:not-allowed;opacity:0.6;">🔒 ${t('Booth Locked')}</button>
              `;
            }
          }
        }

        return `
        <tr>
          <td style="font-weight:600;cursor:pointer;color:var(--accent2);" onclick="navigateToProfile(${app.company_id})">
            <div class="i18n-skip">${comp.name || '—'}</div>
            <div style="font-size:0.7rem; color:var(--text-muted); font-weight:400;">${comp.profile?.company_type || t('General')}</div>
          </td>
          <td style="color:var(--text-muted);font-size:0.82rem;">${app.product_category || '—'}</td>
          <td style="color:var(--accent); font-weight:600; font-size:0.82rem;">
            <div>${app.booth ? (app.booth.zone?.name + ': ' + app.booth.booth_number) : (app.booth_number || '—')}</div>
            <div style="font-size:0.65rem; color:var(--text-muted); font-weight:400;">${app.booth ? (app.booth.size || '') : (app.booth_size || '')}</div>
          </td>
          <td>
            ${badge(app.status)}
            ${app.status === 'accepted' && app.booth ? `
              <div style="margin-top:5px; display: flex; align-items: center; gap: 4px; color: #10b981; font-size: 11px; font-weight: 600;">
                <span style="background:#10b981; color:white; border-radius:50%; width:14px; height:14px; display:inline-flex; align-items:center; justify-content:center; font-size:9px;">✓</span>
                ${t('Confirmed')}
              </div>
            ` : ''}
          </td>
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
          <div style="display:flex;gap:20px;border-bottom:1px solid var(--border);padding:0 28px;">
            <div class="tab-btn active" onclick="switchExhTab(event, ${gi}, 'apps')" id="tab-apps-${gi}">${t('Applications')}</div>
            <div class="tab-btn" onclick="switchExhTab(event, ${gi}, 'layout')" id="tab-layout-${gi}">${t('Exhibition Layout')}</div>
          </div>

          <div id="content-apps-${gi}" class="tab-pane active" style="padding:24px 28px;">
            <div class="search-bar" style="margin-bottom: 20px;">
              <div style="position:relative;">
                <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:0.9rem; opacity:0.5;">🔍</span>
                <input type="text" class="form-control" placeholder="${t('Search company...')}" style="padding-left:38px; border-radius:10px; background:rgba(255,255,255,0.03);" onkeyup="filterCompanies(${gi}, this.value)">
              </div>
            </div>
            <div class="table-wrap" style="margin:0;">
              <table id="table-${gi}">
                <thead><tr><th>${t('Company')}</th><th>${t('Category')}</th><th>${t('Booth')}</th><th>${t('Status')}</th><th>${t('Actions')}</th></tr></thead>
                <tbody>${rows}</tbody>
              </table>
            </div>
          </div>

          <div id="content-layout-${gi}" class="tab-pane" style="padding:24px 28px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
              <div>
                <h4 style="margin:0; font-size:1rem;">${t('Zones & Booths Configuration')}</h4>
                <p style="font-size:0.75rem; color:var(--text-muted); margin:4px 0 0;">${t('Manage exhibition zones and available slots')}</p>
              </div>
              <button class="btn btn-primary btn-sm" onclick="openZoneModalUI(${g.event.id})">➕ ${t('Add Zone')}</button>
            </div>
            <div id="layout-container-${g.event.id}" class="layout-grid">
              <div class="spinner" style="margin:20px auto"></div>
            </div>
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

  function switchExhTab(e, gi, tab) {
    const parent = document.getElementById(`exh-body-${gi}`);
    parent.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    parent.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    
    document.getElementById(`tab-${tab}-${gi}`).classList.add('active');
    document.getElementById(`content-${tab}-${gi}`).classList.add('active');

    if (tab === 'layout') {
      const g = exhGroups[gi];
      const eventId = g.event_id || g.event.id;
      loadLayoutData(eventId);
    }
  }

  async function loadLayoutData(eventId) {
    const container = document.getElementById(`layout-container-${eventId}`);
    const res = await api.get(`/exhibition/inventory/${eventId}`);
    if (!res.ok) return showToast('Failed to load layout', 'error');

    const g = exhGroups.find(x => (x.event_id || (x.event && x.event.id)) == eventId);
    const ev = g ? (g.event || {}) : {};
    const isWithin14Days = ev.start_time && (new Date(ev.start_time) - new Date()) / (1000 * 60 * 60 * 24) < 14;

    const zones = res.data;
    if (zones.length === 0) {
      container.innerHTML = `<div style="text-align:center; padding:40px; color:var(--text-muted);">
        <p>${t('No zones defined yet.')}</p>
        ${!isWithin14Days ? `<button class="btn btn-ghost btn-sm" onclick="openZoneModalUI(${eventId})">➕ ${t('Create First Zone')}</button>` : ''}
      </div>`;
      return;
    }

    let headerMsg = '';
    if (isWithin14Days) {
      headerMsg = `<div style="background:rgba(245,158,11,0.1); color:#f59e0b; border:1px solid rgba(245,158,11,0.2); padding:10px 15px; border-radius:12px; margin-bottom:20px; font-size:0.85rem; font-weight:600; display:flex; align-items:center; gap:10px;">
        <span>🔒</span> ${t('Exhibition layout is locked (Less than 14 days remaining). You can no longer add, edit, or delete zones and booths.')}
      </div>`;
    }

    container.innerHTML = headerMsg + zones.map(z => `
      <div class="zone-box">
        <div class="zone-header">
          <h5 style="margin:0; font-size:0.9rem;">${z.name}</h5>
          ${!isWithin14Days ? `
            <div style="display:flex; gap:8px;">
              <button class="btn btn-ghost btn-sm" style="padding:2px 8px; font-size:0.7rem;" onclick="openBatchModalUI(${z.id}, ${eventId})">➕ ${t('Batch Add')}</button>
              <button class="btn btn-ghost btn-sm" style="padding:2px 8px; font-size:0.7rem;" onclick="openBoothModalInZone(${z.id}, ${eventId})">➕ ${t('Booth')}</button>
              <button class="btn-icon" style="color:#ef4444;" onclick="deleteZone(${z.id}, ${eventId})">✕</button>
            </div>
          ` : ''}
        </div>
        <div class="booth-list">
          ${z.booths.map(b => `
            <div class="booth-item ${b.exhibition_application_id ? 'allocated' : 'available'}">
              <div class="booth-num">${b.booth_number}</div>
              <div class="booth-size">${b.size || '—'}</div>
              ${b.application ? `<div class="booth-comp" title="${b.application.company.name}">${b.application.company.name}</div>` : ''}
              ${!isWithin14Days ? `
                <div class="booth-actions">
                  <button class="btn-icon" onclick="editBooth(${b.id}, '${b.booth_number}', '${b.size || ''}', ${eventId})">✏️</button>
                  <button class="btn-icon" style="color:#f87171;" onclick="deleteBooth(${b.id}, ${eventId})">🗑</button>
                </div>
              ` : ''}
            </div>
          `).join('')}
        </div>
      </div>
    `).join('');
  }

  // --- Zone / Booth Management Modals & Logic ---
  function openZoneModalUI(eventId) {
    document.getElementById('z-event-id').value = eventId;
    document.getElementById('z-name').value = '';
    document.getElementById('zone-modal').classList.add('open');
  }
  function closeZoneModalUI() { document.getElementById('zone-modal').classList.remove('open'); }

  async function deleteZone(id, eventId) {
    if (!confirm(t('Are you sure?'))) return;
    const res = await api.delete(`/exhibition/inventory/zones/${id}`);
    if (res.ok) { showToast(t('Zone deleted'), 'success'); loadLayoutData(eventId); }
  }

  function openBatchModalUI(zoneId, eventId) {
    document.getElementById('batch-zone-id').value = zoneId;
    document.getElementById('batch-event-id').value = eventId;
    document.getElementById('batch-modal').classList.add('open');
  }
  function closeBatchModalUI() { document.getElementById('batch-modal').classList.remove('open'); }

  function openBoothModalInZone(zoneId, eventId) {
    document.getElementById('bi-id').value = '';
    document.getElementById('bi-zone-id').value = zoneId;
    document.getElementById('bi-event-id').value = eventId;
    document.getElementById('bi-number').value = '';
    document.getElementById('bi-size').value = '';
    document.getElementById('booth-item-title').innerText = t('Booth');
    document.getElementById('booth-item-modal').classList.add('open');
  }
  function closeBoothItemModal() { document.getElementById('booth-item-modal').classList.remove('open'); }

  function editBooth(id, num, size, eventId) {
    document.getElementById('bi-id').value = id;
    document.getElementById('bi-number').value = num;
    document.getElementById('bi-size').value = size;
    document.getElementById('bi-event-id').value = eventId;
    document.getElementById('booth-item-title').innerText = t('Booth');
    document.getElementById('booth-item-modal').classList.add('open');
  }

  async function deleteBooth(id, eventId) {
    if (!confirm(t('Are you sure?'))) return;
    const res = await api.delete(`/exhibition/inventory/booths/${id}`);
    if (res.ok) { showToast(t('Booth deleted'), 'success'); loadLayoutData(eventId); }
    else { showToast(res.data?.message || 'Error', 'error'); }
  }

  const formatSize = (s) => {
    if (!s || s.trim() === '') return '';
    const trimmed = s.trim();
    // If it's a numeric value (e.g. "3" or "3.5"), append "m²"
    if (!isNaN(trimmed)) return trimmed + ' m²';
    return trimmed;
  };

  // --- Form Handlers ---
  document.getElementById('zone-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const eventId = document.getElementById('z-event-id').value;
    const name = document.getElementById('z-name').value;
    const res = await api.post(`/exhibition/inventory/${eventId}/zones`, { name });
    if (res.ok) { 
      showToast(t('Zone created'), 'success'); 
      closeZoneModalUI();
      loadLayoutData(eventId); 
    }
  });

  document.getElementById('booth-item-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('bi-id').value;
    const zoneId = document.getElementById('bi-zone-id').value;
    const eventId = document.getElementById('bi-event_id')?.value || document.getElementById('bi-event-id').value;
    const number = document.getElementById('bi-number').value;
    const size = formatSize(document.getElementById('bi-size').value);

    let res;
    if (id) {
       res = await api.put(`/exhibition/inventory/booths/${id}`, { booth_number: number, size });
    } else {
       res = await api.post(`/exhibition/inventory/zones/${zoneId}/booths`, { booth_number: number, size });
    }

    if (res.ok) {
       showToast(id ? 'Updated' : 'Created', 'success');
       closeBoothItemModal();
       loadLayoutData(eventId);
    } else {
       showToast(res.data?.message || 'Error', 'error');
    }
  });

  document.getElementById('batch-form-ui').addEventListener('submit', async (e) => {
    e.preventDefault();
    const zoneId = document.getElementById('batch-zone-id').value;
    const eventId = document.getElementById('batch-event-id').value;
    const prefix = document.getElementById('batch-prefix').value;
    const start = document.getElementById('batch-start').value;
    const count = document.getElementById('batch-count').value;
    const size = formatSize(document.getElementById('batch-size').value);

    const res = await api.post(`/exhibition/inventory/zones/${zoneId}/booths/batch`, {
      prefix, start, count, size
    });
    if (res.ok) { 
      showToast(t('Booths generated'), 'success'); 
      closeBatchModalUI();
      loadLayoutData(eventId); 
    } else {
      showToast(res.data?.message || 'Error', 'error');
    }
  });

  // --- Assign Booth Update ---
  async function openBoothModal(id, name, eventId) {
    document.getElementById('b-app-id').value = id;
    document.getElementById('b-comp-name').innerText = name;
    
    const boothSelect = document.getElementById('b-id-select');
    boothSelect.innerHTML = '<option value="">' + t('Loading booths...') + '</option>';
    document.getElementById('booth-modal').classList.add('open');

    const res = await api.get(`/exhibition/inventory/${eventId}`);
    if (!res.ok) {
       boothSelect.innerHTML = '<option value="">Error loading</option>';
       return;
    }

    let options = `<option value="">-- Select Booth --</option>`;
    res.data.forEach(zone => {
      const availableInZone = zone.booths.filter(b => !b.exhibition_application_id || b.exhibition_application_id == id);
      if (availableInZone.length > 0) {
        options += `<optgroup label="${zone.name}">`;
        availableInZone.forEach(b => {
          const selected = b.exhibition_application_id == id ? 'selected' : '';
          options += `<option value="${b.id}" ${selected}>${b.booth_number} (${b.size || 'No size'})</option>`;
        });
        options += `</optgroup>`;
      }
    });

    boothSelect.innerHTML = options;
  }

  function closeBoothModal() {
    document.getElementById('booth-modal').classList.remove('open');
  }

  document.getElementById('booth-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('b-app-id').value;
    const boothId = document.getElementById('b-id-select').value;

    const res = await api.patch(`/exhibition/${id}/booth`, {
      booth_id: boothId || null
    });

    if (res.ok) {
      showToast(t('Booth assigned successfully!'), 'success');
      closeBoothModal();
      loadData();
    } else {
      showToast(res.data?.message || t('Error assigning booth'), 'error');
    }
  });

</script>
</body>
</html>
