<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Partner Registration – EventHub</title>
  <link rel="stylesheet" href="/css/style.css" />
  <style>
    .pr-file-input {
      display: block; width: 100%; padding: 10px; background: rgba(255,255,255,.03);
      border: 1px dashed var(--border); border-radius: 8px; color: var(--text-muted);
      cursor: pointer; transition: all .2s; font-size: 0.85rem;
    }
    .pr-file-input:hover { border-color: var(--primary); background: rgba(110,64,242,.05); color: #fff; }
    .pr-file-input input[type="file"] { display: none; }
  </style>
</head>
<body>
  <div class="auth-page" style="padding: 40px 20px;">
    <div class="auth-bg-glow"></div>
    <div class="auth-bg-glow auth-bg-glow-2"></div>

    <div class="auth-card" style="max-width: 500px;">
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
            <option value="Event Manager">Event Manager (منظم فعاليات)</option>
            <option value="Sponsor">Sponsor (جهة راعية)</option>
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

        <div class="form-group">
          <label class="form-label">Verification Document (KYB / KYC)</label>
          <p style="font-size:0.75rem; color:var(--text-muted); margin:0 0 8px 0;">Please upload your Commercial Register, License, or Authorization Letter (PDF/JPG/PNG up to 5MB).</p>
          <label class="pr-file-input">
            <span id="file-name-display">📁 Click to select file...</span>
            <input id="pr-doc" type="file" accept=".pdf,.png,.jpg,.jpeg" required onchange="document.getElementById('file-name-display').textContent = '📄 ' + this.files[0].name" />
          </label>
        </div>

        <div class="form-group" style="margin-top:20px; font-size:0.8rem; color:var(--text-muted);">
          <label style="display:flex; gap:10px; align-items:flex-start; cursor:pointer;">
            <input type="checkbox" required style="margin-top:3px;">
            <span>I declare that all the information and documents provided are authentic, and I agree to EventHub's Partner Terms of Service.</span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary" id="pr-btn" style="width: 100%; margin-top:10px; padding: 14px; font-size:1rem;">Submit Application</button>
      </form>

      <div class="auth-footer" style="margin-top: 24px;">
        Already confirmed? <a href="/login">Sign in</a>
      </div>
    </div>
  </div>

  <div id="toast-container"></div>
  <script>
    function showToast(msg, type='info') {
      const container = document.getElementById('toast-container');
      const toast = document.createElement('div');
      toast.className = `toast toast-${type}`;
      toast.textContent = msg;
      container.appendChild(toast);
      setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
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
      
      const fileInput = document.getElementById('pr-doc');
      if (fileInput.files.length > 0) {
        fd.append('verification_document', fileInput.files[0]);
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
        showToast('Network error', 'error');
      }

      btn.textContent = 'Submit Application'; btn.disabled = false;
    });

    document.getElementById('pr-role').addEventListener('change', (e) => {
      document.getElementById('lbl-name').textContent = e.target.value === 'Sponsor' ? 'Company Name / Entity Name' : 'Manager Full Name';
    });
  </script>
</body>
</html>
