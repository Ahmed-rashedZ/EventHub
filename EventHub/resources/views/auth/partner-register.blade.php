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
          <label class="form-label">Role / Account Type</label>
          <select id="pr-role" class="form-control" required style="cursor:pointer;">
            <option value="" disabled selected>Select Role</option>
            <option value="Event Manager">Event Manager</option>
            <option value="Sponsor">Sponsor</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" id="lbl-name">Company / Individual Name</label>
          <input id="pr-name" type="text" class="form-control" required />
        </div>

        <div class="form-group">
          <label class="form-label">Business Email Address</label>
          <input id="pr-email" type="email" class="form-control" required />
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <input id="pr-pass" type="password" class="form-control" required minlength="8" />
        </div>

        <!-- ── Verification Documents ── -->
        <div class="form-group">
          <label class="form-label" style="margin-bottom: 4px;">Verification Documents (KYB)</label>
          <p style="font-size:0.73rem; color:var(--text-muted); margin:0 0 12px 0;">Upload all required documents below (PDF/JPG/PNG, max 5 MB each).</p>

          <div class="pr-docs-grid">
            <!-- Commercial Register -->
            <label class="pr-doc-card" id="card-doc_commercial_register">
              <div class="doc-icon">📋</div>
              <div class="doc-label">Commercial Register</div>
              <div class="doc-hint i18n-skip">السجل التجاري</div>
              <div class="doc-file-name" id="fname-doc_commercial_register">Click to select file...</div>
              <input type="file" accept=".pdf,.png,.jpg,.jpeg" required
                onchange="onDocSelected(this, 'doc_commercial_register')" />
            </label>

            <!-- Tax Number -->
            <label class="pr-doc-card" id="card-doc_tax_number">
              <div class="doc-icon">🔢</div>
              <div class="doc-label">Tax Number Certificate</div>
              <div class="doc-hint i18n-skip">شهادة الرقم الضريبي</div>
              <div class="doc-file-name" id="fname-doc_tax_number">Click to select file...</div>
              <input type="file" accept=".pdf,.png,.jpg,.jpeg" required
                onchange="onDocSelected(this, 'doc_tax_number')" />
            </label>

            <!-- Articles of Association -->
            <label class="pr-doc-card" id="card-doc_articles_of_association">
              <div class="doc-icon">📝</div>
              <div class="doc-label">Articles of Association</div>
              <div class="doc-hint i18n-skip">عقد التأسيس</div>
              <div class="doc-file-name" id="fname-doc_articles_of_association">Click to select file...</div>
              <input type="file" accept=".pdf,.png,.jpg,.jpeg" required
                onchange="onDocSelected(this, 'doc_articles_of_association')" />
            </label>

            <!-- Practice License -->
            <label class="pr-doc-card" id="card-doc_practice_license">
              <div class="doc-icon">🏢</div>
              <div class="doc-label">Practice License</div>
              <div class="doc-hint i18n-skip">إذن المزاولة</div>
              <div class="doc-file-name" id="fname-doc_practice_license">Click to select file...</div>
              <input type="file" accept=".pdf,.png,.jpg,.jpeg" required
                onchange="onDocSelected(this, 'doc_practice_license')" />
            </label>
          </div>
        </div>

        <div class="form-group" style="margin-top:20px; font-size:0.8rem; color:var(--text-muted);">
          <label style="display:flex; gap:10px; align-items:flex-start; cursor:pointer;">
            <input type="checkbox" required style="margin-top:3px;">
            <span>I declare that all the information and documents provided are authentic, and I agree to EventHub's Partner Terms of Service.</span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary" id="pr-btn"
          style="width: 100%; margin-top:10px; padding: 14px; font-size:1rem; justify-content: center;">Submit Application</button>
      </form>

      <div class="auth-footer" style="margin-top: 24px;">
        Already confirmed? <a href="/login">Sign in</a>
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
        label.textContent = 'Click to select file...';
        card.classList.remove('has-file');
      }
    }

    document.getElementById('partner-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('pr-btn');
      btn.textContent = 'Submitting...'; btn.disabled = true;

      const fd = new FormData();
      fd.append('role', document.getElementById('pr-role').value);
      fd.append('name', document.getElementById('pr-name').value);
      fd.append('email', document.getElementById('pr-email').value);
      fd.append('password', document.getElementById('pr-pass').value);

      // Append all 4 documents
      const docTypes = ['doc_commercial_register', 'doc_tax_number', 'doc_articles_of_association', 'doc_practice_license'];
      for (const dt of docTypes) {
        const card = document.getElementById('card-' + dt);
        const fileInput = card.querySelector('input[type="file"]');
        if (fileInput.files.length > 0) {
          fd.append(dt, fileInput.files[0]);
        }
      }

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

    document.getElementById('pr-role').addEventListener('change', (e) => {
      document.getElementById('lbl-name').textContent = e.target.value === 'Sponsor' ? t('Company Name / Entity Name') : t('Manager Full Name');
    });
  </script>
</body>

</html>