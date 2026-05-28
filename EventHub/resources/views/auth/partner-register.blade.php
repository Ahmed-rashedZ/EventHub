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
      .pr-docs-grid {
        grid-template-columns: 1fr;
      }
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
  <link rel="icon" href="/images/logo.png" type="image/png">
</head>

<body>
  <div class="auth-page" style="padding: 40px 20px;">
    <div class="auth-bg-glow"></div>
    <div class="auth-bg-glow auth-bg-glow-2"></div>

    <div class="auth-card" style="max-width: 560px;">
      <div class="auth-logo">
        <div style="display:flex; justify-content:center; align-items:center; width: 100%;"><img src="/images/logo.png"
            alt="EventHub Logo"
            style="width: 85px; height: 85px; object-fit: contain; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        </div>
        <h1>EventHub Partner</h1>
      </div>

      <h2 class="auth-heading" style="margin-bottom:8px;">
        <script>document.write(t('Partner Registration'))</script>
      </h2>
      <p class="auth-subheading">
        <script>document.write(t('Apply as an Event Manager, Sponsor, or Company.'))</script>
      </p>

      <form id="partner-form" style="text-align:left; margin-top: 24px;">
        <div class="form-group">
          <label class="form-label">
            <script>document.write(t('Role / Account Type'))</script>
          </label>
          <select id="pr-role" class="form-control" required style="cursor:pointer;">
            <option value="" disabled selected>
              <script>document.write(t('Select Role'))</script>
            </option>
            <option value="Event Manager">
              <script>document.write(t('Event Manager'))</script>
            </option>
            <option value="Sponsor">
              <script>document.write(t('Sponsor'))</script>
            </option>
            <option value="Company">
              <script>document.write(t('Company'))</script>
            </option>
          </select>
        </div>

        <div class="form-group" id="grp-company_type" style="display:none;">
          <label class="form-label">قطاع الشركة / الفئة</label>
          <select id="pr-company_type" class="form-control" style="cursor:pointer;">
            <option value="" disabled selected>اختر الفئة</option>
            <option value="sports">رياضة</option>
            <option value="tech">تقنية</option>
            <option value="aviation">طيران</option>
            <option value="telecom">اتصالات</option>
            <option value="arts">فنون</option>
            <option value="entertainment">ترفيه</option>
            <option value="food">غداء</option>
            <option value="construction">مقاولات</option>
            <option value="other">أخرى</option>
          </select>
          <input id="pr-company_type_other" class="form-control" type="text" placeholder="حدد الفئة الأخرى"
            style="margin-top:8px; display:none;" />
          <input id="pr-company_type_slug" type="hidden" />
        </div>

        <div class="form-group">
          <label class="form-label" id="lbl-name">
            <script>document.write(t('Company / Individual Name'))</script>
          </label>
          <input id="pr-name" type="text" class="form-control" required />
        </div>

        <div class="form-group">
          <label class="form-label">
            <script>document.write(t('Business Email Address'))</script>
          </label>
          <input id="pr-email" type="email" class="form-control" required />
        </div>

        <div class="form-group">
          <label class="form-label">
            <script>document.write(t('Password'))</script>
          </label>
          <input id="pr-pass" type="password" class="form-control" required minlength="8" />
        </div>

        <!-- ── Verification Documents ── -->
        <div class="form-group">
          <label class="form-label" style="margin-bottom: 4px;">
            <script>document.write(t('Verification Documents (KYB)'))</script>
          </label>
          <p style="font-size:0.73rem; color:var(--text-muted); margin:0 0 12px 0;">
            <script>document.write(t('Upload all required documents below (PDF/JPG/PNG, max 5 MB each).'))</script>
          </p>

          <div class="pr-docs-grid" id="pr-docs-grid">
            <!-- Commercial Register (all roles) -->
            <label class="pr-doc-card" id="card-doc_commercial_register" data-roles="Event Manager,Sponsor,Company">
              <div class="doc-icon">📋</div>
              <div class="doc-label">
                <script>document.write(t('Commercial Register'))</script>
              </div>
              <div class="doc-file-name" id="fname-doc_commercial_register">
                <script>document.write(t('Click to select file...'))</script>
              </div>
              <input type="file" accept=".pdf,.png,.jpg,.jpeg" required
                onchange="onDocSelected(this, 'doc_commercial_register')" />
            </label>

            <!-- Tax Number (all roles) -->
            <label class="pr-doc-card" id="card-doc_tax_number" data-roles="Event Manager,Sponsor,Company">
              <div class="doc-icon">🔢</div>
              <div class="doc-label">
                <script>document.write(t('Tax Number Certificate'))</script>
              </div>
              <div class="doc-file-name" id="fname-doc_tax_number">
                <script>document.write(t('Click to select file...'))</script>
              </div>
              <input type="file" accept=".pdf,.png,.jpg,.jpeg" required
                onchange="onDocSelected(this, 'doc_tax_number')" />
            </label>

            <!-- Articles of Association (Manager only) -->
            <label class="pr-doc-card" id="card-doc_articles_of_association" data-roles="Event Manager"
              style="display:none;">
              <div class="doc-icon">📝</div>
              <div class="doc-label">
                <script>document.write(t('Articles of Association'))</script>
              </div>
              <div class="doc-file-name" id="fname-doc_articles_of_association">
                <script>document.write(t('Click to select file...'))</script>
              </div>
              <input type="file" accept=".pdf,.png,.jpg,.jpeg"
                onchange="onDocSelected(this, 'doc_articles_of_association')" />
            </label>

            <!-- Practice License (Manager only) -->
            <label class="pr-doc-card" id="card-doc_practice_license" data-roles="Event Manager" style="display:none;">
              <div class="doc-icon">🏢</div>
              <div class="doc-label">
                <script>document.write(t('Practice License'))</script>
              </div>
              <div class="doc-file-name" id="fname-doc_practice_license">
                <script>document.write(t('Click to select file...'))</script>
              </div>
              <input type="file" accept=".pdf,.png,.jpg,.jpeg" onchange="onDocSelected(this, 'doc_practice_license')" />
            </label>
          </div>
        </div>

        <div class="form-group" style="margin-top:20px; font-size:0.8rem; color:var(--text-muted);">
          <label style="display:flex; gap:10px; align-items:flex-start; cursor:pointer;">
            <input type="checkbox" required style="margin-top:3px;">
            <span>
              <script>document.write(t("I declare that all the information and documents provided are authentic, and I agree to EventHub's Partner Terms of Service."))</script>
            </span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary" id="pr-btn"
          style="width: 100%; margin-top:10px; padding: 14px; font-size:1rem; justify-content: center;">
          <script>document.write(t('Submit Application'))</script>
        </button>
      </form>

      <div class="auth-footer" style="margin-top: 24px;">
        <script>document.write(t('Already confirmed?'))</script> <a href="/login">
          <script>document.write(t('Sign in'))</script>
        </a>
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
      document.getElementById('grp-company_type').style.display = (role === 'Company') ? '' : 'none';
      if (role === 'Sponsor' || role === 'Company') {
        document.getElementById('lbl-name').textContent = t('Company Name / Entity Name');
      } else {
        document.getElementById('lbl-name').textContent = t('Manager Full Name');
      }
      
      if (role === 'Company') {
        document.getElementById('pr-company_type').required = true;
      } else {
        document.getElementById('pr-company_type').required = false;
        document.getElementById('pr-company_type').value = '';
        document.getElementById('pr-company_type_other').style.display = 'none';
        document.getElementById('pr-company_type_other').required = false;
        document.getElementById('pr-company_type_slug').value = '';
      }
      updateDocsForRole(role);
    });

    // ── Company type 'Other' handling ──
    document.getElementById('pr-company_type').addEventListener('change', (e) => {
      const val = e.target.value;
      const otherInput = document.getElementById('pr-company_type_other');
      const slugInput = document.getElementById('pr-company_type_slug');
      if (val === 'other') {
        otherInput.style.display = '';
        otherInput.required = true;
        otherInput.focus();
        // generate a safe slug from the other input when user types (fallback empty for now)
        slugInput.value = '';
      } else {
        otherInput.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
        // set slug to the option value for known options
        slugInput.value = val;
      }
    });

    // update slug when user types in the 'other' text box
    document.getElementById('pr-company_type_other').addEventListener('input', (e) => {
      const raw = e.target.value || '';
      // simple slugify: trim, replace spaces with hyphens, remove non-url-safe chars, lowercase
      const slug = raw.trim().replace(/\s+/g, '-').replace(/[^\u0000-\u007F\w-]/g, '').toLowerCase();
      document.getElementById('pr-company_type_slug').value = slug;
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
      if (role === 'Company') {
        const select = document.getElementById('pr-company_type');
        const ct = select.value;
        const slug = document.getElementById('pr-company_type_slug').value || '';
        if (ct === 'other') {
          const other = document.getElementById('pr-company_type_other').value.trim();
          if (!other) {
            showToast('يرجى كتابة الفئة الأخرى', 'error');
            btn.textContent = t('Submit Application'); btn.disabled = false;
            return;
          }
          fd.append('company_type', other);
          fd.append('company_type_slug', slug);
        } else {
          // Append the displayed label text so the server stores exactly what the user sees
          const selectedLabel = (select.options[select.selectedIndex] && select.options[select.selectedIndex].text) || ct;
          fd.append('company_type', selectedLabel);
          fd.append('company_type_slug', slug || ct);
        }
      }

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
          sessionStorage.setItem('token', data.token);
          sessionStorage.setItem('user', JSON.stringify(data.user));
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