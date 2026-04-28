<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Partner Registration – EventHub</title>
  <link rel="stylesheet" href="/css/style.css" />
  <script src="/js/i18n.js"></script>
  <style>
    .pr-docs-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 8px;
    }

    @media (max-width: 540px) {
      .pr-docs-grid { grid-template-columns: 1fr; }
    }

    .pr-doc-card {
      background: rgba(255, 255, 255, .02);
      border: 1px dashed var(--border);
      border-radius: 10px;
      padding: 14px;
      transition: all .25s;
      cursor: pointer;
      position: relative;
    }

    .pr-doc-card:hover {
      border-color: var(--primary);
      background: rgba(110, 64, 242, .04);
    }

    .pr-doc-card.has-file {
      border-color: #10b981;
      border-style: solid;
      background: rgba(16, 185, 129, .05);
    }

    .pr-doc-card .doc-icon {
      font-size: 1.4rem;
      margin-bottom: 6px;
    }

    .pr-doc-card .doc-label {
      font-size: 0.82rem;
      font-weight: 600;
      color: #fff;
      margin-bottom: 4px;
    }

    .pr-doc-card .doc-hint {
      font-size: 0.7rem;
      color: var(--text-muted);
      margin-bottom: 8px;
    }

    .pr-doc-card .doc-file-name {
      font-size: 0.72rem;
      color: #10b981;
      word-break: break-all;
    }

    .pr-doc-card input[type="file"] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
    }

    .pr-doc-card .doc-status {
      display: inline-block;
      font-size: 0.65rem;
      padding: 2px 6px;
      border-radius: 4px;
      margin-top: 4px;
    }
  </style>
</head>

<body>
  <div class="auth-page" style="padding: 40px 20px;">
    <div class="auth-bg-glow"></div>
    <div class="auth-bg-glow auth-bg-glow-2"></div>

    <div class="auth-card" style="max-width: 560px;">
      <div class="auth-logo">
        <div class="logo-icon">🎯</div>
        <h1>EventHub Partner</h1>
      </div>

      <h2 class="auth-heading" style="margin-bottom:8px;">Partner Registration</h2>
      <p class="auth-subheading">Apply as an Event Manager or Sponsor.</p>

      <form id="partner-form" style="text-align:left; margin-top: 24px;">
        <div class="form-group">
          <label class="form-label"><script>document.write(t('Role / Account Type'))</script></label>
          <select id="pr-role" class="form-control" required style="cursor:pointer;">
            <option value="" disabled selected><script>document.write(t('Select Role'))</script></option>
            <option value="Event Manager"><script>document.write(t('Event Manager'))</script></option>
            <option value="Sponsor"><script>document.write(t('Sponsor'))</script></option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" id="lbl-name"><script>document.write(t('Company / Individual Name'))</script></label>
          <input id="pr-name" type="text" class="form-control" required />
        </div>

        <div class="form-group">
          <label class="form-label"><script>document.write(t('Business Email Address'))</script></label>
          <input id="pr-email" type="email" class="form-control" required />
        </div>

        <div class="form-group">
          <label class="form-label"><script>document.write(t('Password'))</script></label>
          <input id="pr-pass" type="password" class="form-control" required minlength="8" />
        </div>

        <!-- ── Verification Documents ── -->
        <div class="form-group">
          <label class="form-label" style="margin-bottom: 4px;"><script>document.write(t('Verification Documents (KYB)'))</script></label>
          <p style="font-size:0.73rem; color:var(--text-muted); margin:0 0 12px 0;"><script>document.write(t('Upload all required documents below (PDF/JPG/PNG, max 5 MB each).'))</script></p>

          <div class="pr-docs-grid" id="pr-docs-grid">
            <!-- Commercial Register (both roles) -->
            <label class="pr-doc-card" id="card-doc_commercial_register" data-roles="Event Manager,Sponsor">
              <div class="doc-icon">📋</div>
              <div class="doc-label"><script>document.write(t('Commercial Register'))</script></div>
              <div class="doc-file-name" id="fname-doc_commercial_register"><script>document.write(t('Click to select file...'))</script></div>
              <input type="file" accept=".pdf,.png,.jpg,.jpeg" required
                onchange="onDocSelected(this, 'doc_commercial_register')" />
            </label>

            <!-- Tax Number (both roles) -->
            <label class="pr-doc-card" id="card-doc_tax_number" data-roles="Event Manager,Sponsor">
              <div class="doc-icon">🔢</div>
              <div class="doc-label"><script>document.write(t('Tax Number Certificate'))</script></div>
              <div class="doc-file-name" id="fname-doc_tax_number"><script>document.write(t('Click to select file...'))</script></div>
              <input type="file" accept=".pdf,.png,.jpg,.jpeg" required
                onchange="onDocSelected(this, 'doc_tax_number')" />
            </label>

            <!-- Articles of Association (Manager only) -->
            <label class="pr-doc-card" id="card-doc_articles_of_association" data-roles="Event Manager" style="display:none;">
              <div class="doc-icon">📝</div>
              <div class="doc-label"><script>document.write(t('Articles of Association'))</script></div>
              <div class="doc-file-name" id="fname-doc_articles_of_association"><script>document.write(t('Click to select file...'))</script></div>
              <input type="file" accept=".pdf,.png,.jpg,.jpeg"
                onchange="onDocSelected(this, 'doc_articles_of_association')" />
            </label>

            <!-- Practice License (Manager only) -->
            <label class="pr-doc-card" id="card-doc_practice_license" data-roles="Event Manager" style="display:none;">
              <div class="doc-icon">🏢</div>
              <div class="doc-label"><script>document.write(t('Practice License'))</script></div>
              <div class="doc-file-name" id="fname-doc_practice_license"><script>document.write(t('Click to select file...'))</script></div>
              <input type="file" accept=".pdf,.png,.jpg,.jpeg"
                onchange="onDocSelected(this, 'doc_practice_license')" />
            </label>
          </div>
        </div>

        <div class="form-group" style="margin-top:20px; font-size:0.8rem; color:var(--text-muted);">
          <label style="display:flex; gap:10px; align-items:flex-start; cursor:pointer;">
            <input type="checkbox" required style="margin-top:3px;">
            <span><script>document.write(t("I declare that all the information and documents provided are authentic, and I agree to EventHub's Partner Terms of Service."))</script></span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary" id="pr-btn"
          style="width: 100%; margin-top:10px; padding: 14px; font-size:1rem; justify-content: center;"><script>document.write(t('Submit Application'))</script></button>
      </form>

      <div class="auth-footer" style="margin-top: 24px;">
        <script>document.write(t('Already confirmed?'))</script> <a href="/login"><script>document.write(t('Sign in'))</script></a>
      </div>
    </div>
  </div>

  <div id="toast-container"></div>
  <script>
    function showToast(msg, type = 'info') {
      const container = document.getElementById('toast-container');
      const toast = document.createElement('div');
      toast.className = `toast toast-${type}`;
      toast.textContent = msg;
      container.appendChild(toast);
      setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
    }

    function onDocSelected(input, docType) {
      const card = document.getElementById('card-' + docType);
      const label = document.getElementById('fname-' + docType);
      if (input.files.length > 0) {
        label.textContent = '📄 ' + input.files[0].name;
        card.classList.add('has-file');
      } else {
        label.textContent = t('Click to select file...');
        card.classList.remove('has-file');
      }
    }

    // ── Role-based document visibility ──
    function updateDocsForRole(role) {
      const allCards = document.querySelectorAll('.pr-doc-card[data-roles]');
      allCards.forEach(card => {
        const roles = card.getAttribute('data-roles').split(',');
        const visible = roles.includes(role);
        card.style.display = visible ? '' : 'none';
        const fileInput = card.querySelector('input[type="file"]');
        if (fileInput) {
          fileInput.required = visible;
          if (!visible) {
            // Clear file if hidden
            fileInput.value = '';
            card.classList.remove('has-file');
            const fnameEl = card.querySelector('.doc-file-name');
            if (fnameEl) fnameEl.textContent = t('Click to select file...');
          }
        }
      });
    }

    document.getElementById('pr-role').addEventListener('change', (e) => {
      const role = e.target.value;
      document.getElementById('lbl-name').textContent = role === 'Sponsor' ? t('Company Name / Entity Name') : t('Manager Full Name');
      updateDocsForRole(role);
    });

    document.getElementById('partner-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('pr-btn');
      btn.textContent = t('Submitting...'); btn.disabled = true;

      const role = document.getElementById('pr-role').value;
      const fd = new FormData();
      fd.append('role', role);
      fd.append('name', document.getElementById('pr-name').value);
      fd.append('email', document.getElementById('pr-email').value);
      fd.append('password', document.getElementById('pr-pass').value);

      // Append only visible documents based on role
      const visibleCards = document.querySelectorAll('.pr-doc-card[data-roles]');
      visibleCards.forEach(card => {
        const roles = card.getAttribute('data-roles').split(',');
        if (!roles.includes(role)) return;
        const fileInput = card.querySelector('input[type="file"]');
        const docType = card.id.replace('card-', '');
        if (fileInput && fileInput.files.length > 0) {
          fd.append(docType, fileInput.files[0]);
        }
      });

      try {
        const res = await fetch('/api/register/partner', {
          method: 'POST',
          headers: { 'Accept': 'application/json' },
          body: fd
        });

        const data = await res.json();
        if (res.ok) {
          localStorage.setItem('token', data.token);
          localStorage.setItem('user', JSON.stringify(data.user));
          document.cookie = "auth_token=" + data.token + "; path=/; max-age=86400;";
          showToast('Registration successful! Redirecting...', 'success');
          setTimeout(() => { window.location.href = '/pending-verification'; }, 1500);
        } else {
          showToast(data.message || (data.errors ? Object.values(data.errors).flat().join('. ') : 'Error occurred'), 'error');
        }
      } catch (err) {
        showToast(t('Network error'), 'error');
      }

      btn.textContent = t('Submit Application'); btn.disabled = false;
    });
  </script>
</body>

</html>