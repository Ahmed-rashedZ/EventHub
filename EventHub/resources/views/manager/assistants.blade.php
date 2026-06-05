<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Assistants – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <style>
    .assistants-layout { display: grid; grid-template-columns: 1fr; gap: 24px; }
    @media(min-width: 1024px) { .assistants-layout { grid-template-columns: 380px 1fr; } }
    .invite-card { background: var(--surface2); padding: 24px; border-radius: 16px; border: 1px solid var(--border); position: sticky; top: 20px; }
    .invite-card h3 { margin: 0 0 4px; font-size: 1.1rem; }
    .invite-card .subtitle { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px; }
    .tab-bar { display: flex; gap: 4px; padding: 4px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--border); margin-bottom: 20px; }
    .tab-btn { flex: 1; padding: 10px 16px; border-radius: 10px; border: none; background: transparent; color: var(--text-muted); font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.25s; display: flex; align-items: center; justify-content: center; gap: 6px; }
    .tab-btn:hover { color: #fff; background: rgba(255,255,255,0.04); }
    .tab-btn.active { background: linear-gradient(135deg, #6e40f2, #8b5cf6); color: #fff; box-shadow: 0 4px 12px rgba(110,64,242,0.3); }
    .tab-count { background: rgba(255,255,255,0.15); padding: 2px 7px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; }
    .tab-btn:not(.active) .tab-count { background: rgba(255,255,255,0.08); }
    .available-grid { display: grid; grid-template-columns: 1fr; gap: 10px; max-height: 400px; overflow-y: auto; padding-right: 4px; }
    .available-grid::-webkit-scrollbar { width: 4px; }
    .available-grid::-webkit-scrollbar-track { background: transparent; }
    .available-grid::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }
    .avail-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 12px; transition: all 0.2s; }
    .avail-item:hover { border-color: rgba(139,92,246,0.3); background: rgba(139,92,246,0.04); }
    .avail-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #6e40f2, #2dd4bf); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; font-size: 0.9rem; flex-shrink: 0; overflow:hidden; }
    .avail-info { flex: 1; min-width: 0; }
    .avail-name { font-weight: 600; font-size: 0.9rem; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .avail-email { font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; }
    .invite-btn { padding: 6px 14px; border-radius: 8px; border: none; font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
    .invite-btn.primary { background: linear-gradient(135deg, #6e40f2, #8b5cf6); color: #fff; }
    .invite-btn.primary:hover { transform: scale(1.03); box-shadow: 0 2px 8px rgba(110,64,242,0.4); }
    .invite-btn.disabled { background: rgba(255,255,255,0.06); color: var(--text-muted); cursor: default; pointer-events: none; }
    .invite-btn.sending { background: rgba(245,158,11,0.2); color: #f59e0b; cursor: wait; }
    .inv-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .inv-table th { text-align: left; padding: 12px 14px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border-bottom: 1px solid var(--border); }
    .inv-table td { padding: 14px; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
    .inv-table tbody tr:hover { background: rgba(255,255,255,0.02); }
    .status-pill { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; }
    .status-pending  { background: rgba(245,158,11,0.1); color: #f59e0b; border: 1px solid rgba(245,158,11,0.2); }
    .status-accepted { background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }
    .status-rejected { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }
    .scans-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; background: rgba(139,92,246,0.1); color: #a78bfa; border: 1px solid rgba(139,92,246,0.2); }
    .cancel-btn { padding: 5px 12px; border-radius: 8px; border: 1px solid rgba(239,68,68,0.25); background: rgba(239,68,68,0.1); color: #ef4444; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .cancel-btn:hover { background: rgba(239,68,68,0.2); }
    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state .icon { font-size: 2.5rem; margin-bottom: 12px; }
  </style>
  <link rel="icon" href="/images/logo.png" type="image/png">
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
      <div>
        <h1 class="page-title">Assistants Management</h1>
        <p class="page-subtitle">Invite available assistants to your events and track their performance</p>
      </div>
    </div>

    <div class="assistants-layout">
      <!-- Left: Invite Panel -->
      <div class="invite-card">
        <h3>📨 Invite Assistants</h3>
        <p class="subtitle">Browse available assistants and send event invitations</p>
        <div class="form-group" style="margin-bottom: 14px;">
          <label class="form-label" style="font-size: 0.8rem;">Select Event to Invite For</label>
          <select id="invite-event" class="form-control"></select>
        </div>
        <div style="position:relative; margin-bottom: 16px;">
          <input type="text" id="search-available" class="form-control" placeholder="Search assistants..." style="padding-left: 36px;">
          <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:14px; opacity:0.5;">🔍</span>
        </div>
        <div id="available-list" class="available-grid">
          <div class="empty-state" style="padding: 30px;">
            <div class="icon">👆</div>
            <p>Select an event first to see available assistants</p>
          </div>
        </div>
      </div>

      <!-- Right: Invitations List -->
      <div>
        <div class="tab-bar">
          <button class="tab-btn active" id="tab-all">All <span class="tab-count" id="count-all">0</span></button>
          <button class="tab-btn" id="tab-pending">⏳ Pending <span class="tab-count" id="count-pending">0</span></button>
          <button class="tab-btn" id="tab-accepted">✅ Accepted <span class="tab-count" id="count-accepted">0</span></button>
          <button class="tab-btn" id="tab-rejected">❌ Rejected <span class="tab-count" id="count-rejected">0</span></button>
        </div>
        <div style="display: flex; gap: 12px; margin-bottom: 16px;">
          <select id="filter-event" class="form-control" style="max-width: 260px;"><option value="">All Events</option></select>
        </div>
        <div id="invitations-container" style="background: var(--surface2); border-radius: 16px; border: 1px solid var(--border); overflow: hidden;">
          <div class="empty-state"><div class="icon">📬</div><p>Loading invitations...</p></div>
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
// ════════════════════════════════════════════════════════════════
//  STATE
// ════════════════════════════════════════════════════════════════
var eventsMap = {};
var allInvitations = [];
var currentTab = 'all';
var availableCache = [];

// ════════════════════════════════════════════════════════════════
//  INIT
// ════════════════════════════════════════════════════════════════
var user = requireRole('Event Manager');
if (user) {
  populateSidebar(user);
  setActiveNav();
  loadEvents().then(function() { loadInvitations(); });
}

// ════════════════════════════════════════════════════════════════
//  EVENTS
// ════════════════════════════════════════════════════════════════
document.getElementById('invite-event').addEventListener('change', function() {
  loadAvailableAssistants();
});
document.getElementById('search-available').addEventListener('input', function() {
  loadAvailableAssistants();
});
document.getElementById('filter-event').addEventListener('change', function() {
  loadInvitations();
});

// Tab buttons
document.getElementById('tab-all').addEventListener('click', function() { switchTab('all', this); });
document.getElementById('tab-pending').addEventListener('click', function() { switchTab('pending', this); });
document.getElementById('tab-accepted').addEventListener('click', function() { switchTab('accepted', this); });
document.getElementById('tab-rejected').addEventListener('click', function() { switchTab('rejected', this); });

// ════════════════════════════════════════════════════════════════
//  LOAD EVENTS
// ════════════════════════════════════════════════════════════════
function loadEvents() {
  return api.get('/events/list/my').then(function(res) {
    var inviteSelect = document.getElementById('invite-event');
    var filterSelect = document.getElementById('filter-event');
    inviteSelect.innerHTML = '<option value="">Choose an event...</option>';
    if (res.ok && res.data) {
      res.data.forEach(function(ev) {
        eventsMap[ev.id] = ev.title;
        
        // Only allow inviting assistants to approved events that haven't ended
        if (ev.status === 'approved' && ev.time_status !== 'ended') {
          var opt = document.createElement('option');
          opt.value = ev.id;
          opt.textContent = ev.title;
          inviteSelect.appendChild(opt);
        }
        
        // Show all events in the filter dropdown for invitation history
        var fOpt = document.createElement('option');
        fOpt.value = ev.id;
        fOpt.textContent = ev.title;
        filterSelect.appendChild(fOpt);
      });
    }
  });
}

// ════════════════════════════════════════════════════════════════
//  LOAD AVAILABLE ASSISTANTS
// ════════════════════════════════════════════════════════════════
function loadAvailableAssistants() {
  var eventId = document.getElementById('invite-event').value;
  var search = document.getElementById('search-available').value;
  var container = document.getElementById('available-list');

  if (!eventId) {
    container.innerHTML = '<div class="empty-state" style="padding:30px;"><div class="icon">👆</div><p>' + t('Select an event first to see available assistants') + '</p></div>';
    return;
  }

  container.innerHTML = '<div style="text-align:center; padding:20px; color:var(--text-muted);">Loading...</div>';

  var url = '/manager/available-assistants?event_id=' + eventId;
  if (search) url += '&search=' + encodeURIComponent(search);

  api.get(url).then(function(res) {
    if (!res.ok) {
      container.innerHTML = '<div class="empty-state" style="padding:20px;"><p style="color:var(--error);">' + t('Failed to load') + '</p></div>';
      return;
    }
    if (res.data.length === 0) {
      container.innerHTML = '<div class="empty-state" style="padding:30px;"><div class="icon">🔍</div><p>' + t('No available assistants found') + '</p></div>';
      return;
    }

    availableCache = res.data;
    var html = '';
    res.data.forEach(function(a, idx) {
      var initials = a.name.charAt(0).toUpperCase();
      var btnHtml = '';
      
      if (a.invitation_status === 'accepted') {
        btnHtml = '<button class="invite-btn disabled" style="background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2);">' + t('Joined') + '</button>';
      } else if (a.invitation_status === 'pending') {
        btnHtml = '<button class="invite-btn disabled">⏳ ' + t('Invited') + '</button>';
      } else if (a.invitation_status === 'rejected') {
        btnHtml = '<button class="invite-btn disabled" style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2);">' + t('Rejected') + '</button>';
      } else if (a.invitation_status === 'busy') {
        btnHtml = '<button class="invite-btn disabled" style="background: rgba(245,158,11,0.1); color: #f59e0b; border: 1px solid rgba(245,158,11,0.2); cursor: not-allowed;" title="' + t('Assistant has another event at the same time') + '">⚠️ ' + t('Busy') + '</button>';
      } else {
        btnHtml = '<button class="invite-btn primary" data-idx="' + idx + '">' + t('Invite') + '</button>';
      }

      html += '<div class="avail-item">' +
        '<div class="avail-avatar">' + initials + '</div>' +
        '<div class="avail-info">' +
          '<div class="avail-name">' + a.name + '</div>' +
          '<div class="avail-email">' + a.email + '</div>' +
        '</div>' +
        btnHtml +
      '</div>';
    });
    container.innerHTML = html;

    // Attach click handlers to all invite buttons
    var buttons = container.querySelectorAll('.invite-btn.primary');
    buttons.forEach(function(btn) {
      btn.addEventListener('click', function() {
        var idx = parseInt(this.getAttribute('data-idx'));
        sendInvite(idx, this);
      });
    });
  });
}

// ════════════════════════════════════════════════════════════════
//  SEND INVITE (direct — no modal)
// ════════════════════════════════════════════════════════════════
function sendInvite(idx, btnElement) {
  var assistant = availableCache[idx];
  var eventId = document.getElementById('invite-event').value;

  if (!assistant || !eventId) {
    showToast(t('Please select an event first'), 'error');
    return;
  }

  var eventName = eventsMap[eventId] || t('this event');
  if (!confirm(t('Send invitation to ') + '"' + assistant.name + '"' + t(' for ') + '"' + eventName + '"?')) {
    return;
  }

  // Visual feedback
  btnElement.textContent = t('Sending...');
  btnElement.className = 'invite-btn sending';

  var body = {
    assistant_id: assistant.id,
    event_id: parseInt(eventId)
  };

  api.post('/manager/invite-assistant', body).then(function(res) {
    if (res.ok) {
      showToast('✅ ' + t('Invitation sent to') + ' ' + assistant.name + '!', 'success');
      btnElement.textContent = '⏳ ' + t('Invited');
      btnElement.className = 'invite-btn disabled';
      loadInvitations();
    } else {
      var msg = (res.data && res.data.message) ? res.data.message : t('Failed to send invitation');
      showToast('❌ ' + msg, 'error');
      btnElement.textContent = t('Invite');
      btnElement.className = 'invite-btn primary';
    }
  }).catch(function(err) {
    showToast(t('Network error'), 'error');
    btnElement.textContent = t('Invite');
    btnElement.className = 'invite-btn primary';
  });
}

// ════════════════════════════════════════════════════════════════
//  LOAD INVITATIONS
// ════════════════════════════════════════════════════════════════
function loadInvitations() {
  var eventId = document.getElementById('filter-event').value;
  var url = '/manager/invitations';
  if (eventId) url += '?event_id=' + eventId;

  api.get(url).then(function(res) {
    var container = document.getElementById('invitations-container');
    if (!res.ok) {
      container.innerHTML = '<div class="empty-state"><p style="color:var(--error);">' + t('Failed to load invitations') + '</p></div>';
      return;
    }
    allInvitations = res.data;
    updateCounts();
    renderInvitations();
  });
}

function updateCounts() {
  document.getElementById('count-all').textContent = allInvitations.length;
  document.getElementById('count-pending').textContent = allInvitations.filter(function(i) { return i.status === 'pending'; }).length;
  document.getElementById('count-accepted').textContent = allInvitations.filter(function(i) { return i.status === 'accepted'; }).length;
  document.getElementById('count-rejected').textContent = allInvitations.filter(function(i) { return i.status === 'rejected'; }).length;
}

function switchTab(tab, el) {
  currentTab = tab;
  document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
  el.classList.add('active');
  renderInvitations();
}

function renderInvitations() {
  var container = document.getElementById('invitations-container');
  var data = allInvitations;
  if (currentTab !== 'all') {
    data = data.filter(function(i) { return i.status === currentTab; });
  }

  if (data.length === 0) {
    var msgs = {
      all: t('No invitations sent yet. Invite assistants from the left panel.'),
      pending: t('No pending invitations.'),
      accepted: t('No accepted invitations yet.'),
      rejected: t('No rejected invitations.')
    };
    container.innerHTML = '<div class="empty-state"><div class="icon">📬</div><p>' + (msgs[currentTab] || '') + '</p></div>';
    return;
  }

  var html = '<table class="inv-table"><thead><tr>' +
    '<th>' + t('Assistant') + '</th><th>' + t('Event') + '</th><th>' + t('Status') + '</th><th>' + t('Scans') + '</th><th>' + t('Date') + '</th><th style="text-align:right;">' + t('Actions') + '</th>' +
    '</tr></thead><tbody>';

  data.forEach(function(inv) {
    var name = (inv.assistant && inv.assistant.name) ? inv.assistant.name : 'Unknown';
    var email = (inv.assistant && inv.assistant.email) ? inv.assistant.email : '';
    var eventTitle = (inv.event && inv.event.title) ? inv.event.title : 'Unknown';
    var statusCls = 'status-' + inv.status;
    var statusLabel = inv.status.charAt(0).toUpperCase() + inv.status.slice(1);
    var statusIcons = { pending: '⏳', accepted: '✅', rejected: '❌' };
    var scans = inv.scans_count || 0;
    var createdDate = inv.created_at ? new Date(inv.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '—';
    var initials = name.charAt(0).toUpperCase();

    var actions = '';
    if (inv.status === 'pending') {
      actions = '<button class="cancel-btn" data-inv-id="' + inv.id + '" data-is-remove="false">✕ ' + t('Cancel') + '</button>';
    } else if (inv.status === 'accepted') {
      var timeStatus = inv.event ? inv.event.time_status : 'upcoming';
      if (timeStatus !== 'ended') {
        actions = '<button class="cancel-btn" data-inv-id="' + inv.id + '" data-is-remove="true" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.25);">🗑️ ' + t('Remove') + '</button>';
      } else {
        actions = '<span style="font-size:0.75rem; color:var(--text-muted);" title="' + t('Cannot remove from ended event') + '">' + t('Ended') + '</span>';
      }
    }

    html += '<tr>' +
      '<td><div style="display:flex;align-items:center;gap:10px;">' +
        '<div class="avail-avatar" style="width:34px;height:34px;font-size:0.75rem;">' + initials + '</div>' +
        '<div><div style="font-weight:600;color:#fff;font-size:0.9rem;">' + name + '</div>' +
        '<div style="font-size:0.72rem;color:var(--text-muted);">' + email + '</div></div>' +
      '</div></td>' +
      '<td style="color:var(--text-secondary);font-size:0.88rem;">' + eventTitle + '</td>' +
      '<td><span class="status-pill ' + statusCls + '">' + (statusIcons[inv.status] || '') + ' ' + statusLabel + '</span></td>' +
      '<td>' + (inv.status === 'accepted' ? '<span class="scans-badge">📱 ' + scans + '</span>' : '<span style="color:var(--text-muted);">—</span>') + '</td>' +
      '<td style="font-size:0.82rem;color:var(--text-muted);">' + createdDate + '</td>' +
      '<td style="text-align:right;">' + actions + '</td>' +
    '</tr>';
  });

  html += '</tbody></table>';
  container.innerHTML = html;

  // Attach cancel handlers
  container.querySelectorAll('.cancel-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var invId = this.getAttribute('data-inv-id');
      var isRemove = this.getAttribute('data-is-remove') === 'true';
      cancelInvite(invId, isRemove);
    });
  });
}

function cancelInvite(id, isRemove = false) {
  if (!confirm(isRemove ? t('Are you sure you want to remove this assistant from the event?') : t('Cancel this invitation?'))) return;
  api.delete('/manager/invitations/' + id).then(function(res) {
    if (res.ok) {
      showToast(isRemove ? t('Assistant removed') : t('Invitation cancelled'), 'success');
      loadInvitations();
      loadAvailableAssistants();
    } else {
      showToast((res.data && res.data.message) ? res.data.message : t('Failed to cancel'), 'error');
    }
  });
}
</script>
</body>
</html>
