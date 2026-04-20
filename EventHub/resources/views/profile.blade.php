<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Profile – EventHub</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <style>
    .profile-card { max-width: 600px; margin: 40px auto; padding: 30px; }
  </style>
</head>
<body>
<div class="app-layout">
  <!-- Dynamic Sidebar placeholder -->
  <aside class="sidebar" id="dynamic-sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav" id="sidebar-links"></nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">My Profile</h1><p class="page-subtitle">Update your personal information</p></div>
    </div>

    <div class="card profile-card">
      <form id="profile-form">
        <!-- Avatar Header Grid -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div style="flex: 1; padding-right: 20px;">
                <div class="form-group">
                  <label class="form-label">Full Name</label>
                  <input id="p-name" type="text" class="form-control" required/>
                </div>
            </div>
            
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 120px;">
                <div id="avatar-preview-container" style="width: 100px; height: 100px; border-radius: 50%; background: #eef2f5; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 10px;">
                    <img id="current-logo" src="" alt="Profile Image" style="display:none; width: 100%; height: 100%; object-fit: cover;"/>
                    <span id="avatar-placeholder" style="color: #94a3b8; font-size: 24px;">👤</span>
                </div>
                <input id="p-logo" type="file" accept="image/*" style="display:none;" />
                <button type="button" class="btn btn-ghost" style="font-size: 12px; padding: 4px 8px; margin: 0; width: 100%;" onclick="document.getElementById('p-logo').click()">📷 Change Photo</button>
            </div>
        </div>
        <div class="form-group">
          <label class="form-label">Login Email</label>
          <input id="p-email" type="email" class="form-control" required/>
        </div>
        
        <!-- Extended Profile Fields -->
        <h3 style="margin-top:20px; margin-bottom:10px; font-weight:600; border-bottom:1px solid #ddd; padding-bottom:5px;">Public Profile</h3>
        
        <div class="form-group" id="company-name-group" style="display:none;">
          <label class="form-label">Company Name</label>
          <input id="p-company" type="text" class="form-control" placeholder="Acme Corp"/>
        </div>

        <div id="sponsor-visibility-notice" style="display:none; margin-bottom:14px; padding:12px 14px; background:rgba(239, 68, 68, 0.08); border:1px solid rgba(239, 68, 68, 0.25); border-radius:8px; color:#b91c1c; font-size:0.9rem; line-height:1.5;">
          You are currently not visible to event managers.<br/>
          You will remain hidden until you turn this option ON again.
        </div>

        <div class="form-group" id="availability-group" style="display:none; margin-top:15px; padding: 10px; background: #eef2f5; border-radius: 8px;">
          <label class="form-label" style="margin-bottom:0; display:flex; align-items:center; cursor:pointer;">
            <input type="checkbox" id="p-available" disabled style="margin-right:10px; width:18px; height:18px;"/>
            <span style="font-weight:600;">Open to Sponsorship</span>
          </label>
          <small style="display:block; margin-top:5px; color:#666; margin-left: 28px;">
            When enabled, your profile will be listed publicly, and Event Managers can send you sponsorship requests. You can also browse and request to sponsor published events.
          </small>
        </div>

        <div class="form-group">
          <label class="form-label">Bio / Description</label>
          <textarea id="p-bio" class="form-control" placeholder="Tell us about yourself or your company" rows="3"></textarea>
        </div>
        
        <div class="form-group">
          <label class="form-label">Contact Phone</label>
          <input id="p-phone" type="tel" class="form-control" placeholder="+123456789"/>
        </div>

        <div class="form-group">
          <label class="form-label">Contact Email (Public)</label>
          <input id="p-contact-email" type="email" class="form-control" placeholder="contact@example.com"/>
        </div>

        <div class="form-group">
          <label class="form-label">Website URL</label>
          <input id="p-website" type="url" class="form-control" placeholder="https://example.com"/>
        </div>

        <h3 style="margin-top:20px; margin-bottom:10px; font-weight:600; border-bottom:1px solid #ddd; padding-bottom:5px; display:flex; justify-content:space-between; align-items:center;">
          Additional Links / Socials
          <button type="button" class="btn btn-primary" id="add-contact-btn" style="padding:4px 10px; font-size:12px;">+ Add Link</button>
        </h3>
        <div id="contacts-container" style="display:flex; flex-direction:column; gap:10px; margin-bottom:15px;">
           <!-- Contacts will be dynamically injected here -->
        </div>

        <h3 style="margin-top:20px; margin-bottom:10px; font-weight:600; border-bottom:1px solid #ddd; padding-bottom:5px;">Security</h3>

        <div class="form-group">
          <label class="form-label">New Password</label>
          <input id="p-pass" type="password" class="form-control" placeholder="Leave blank to keep current password" minlength="8"/>
        </div>


        <div style="margin-top:24px; text-align:right">
          <button type="submit" class="btn btn-primary" id="save-btn">Save Changes</button>
        </div>
      </form>
    </div>
  </main>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script>
</script>
<script src="/js/auth.js"></script>
<script>
  // Sponsors: load /profile first so is_available matches the database (no UI default).
  let u = JSON.parse(localStorage.getItem('user'));
  if (!u) { window.location.href = '/login'; }

  (async function initProfilePage() {
    if (!u) return;
    if (u.role === 'Sponsor') {
      document.getElementById('p-available').disabled = true;
      const pres = await api.get('/profile');
      if (pres.ok && pres.data?.user) {
        u = pres.data.user;
        localStorage.setItem('user', JSON.stringify(u));
      } else {
        showToast('Could not load your profile from the server.', 'error');
      }
    }

    populateSidebar(u);

    // Inject links using the shared helper (respects i18n)
    const linksDiv = document.getElementById('sidebar-links');
    if (linksDiv) linksDiv.innerHTML = buildSidebarLinks(u.role, '/profile');


    // Populate form
    document.getElementById('p-name').value = u.name || '';
    document.getElementById('p-email').value = u.email || '';
    
    // Show company name field only for sponsors
    if (u.role === 'Sponsor') {
        document.getElementById('company-name-group').style.display = 'block';
    }

    if (u.profile) {
        document.getElementById('p-bio').value = u.profile.bio || '';
        
        if (u.profile.logo) {
            const currentLogoUrl = u.profile.logo.startsWith('http') ? u.profile.logo : (u.profile.logo.startsWith('/') ? u.profile.logo : '/' + u.profile.logo);
            const imgEl = document.getElementById('current-logo');
            imgEl.src = currentLogoUrl;
            imgEl.style.display = 'block';
            document.getElementById('avatar-placeholder').style.display = 'none';
            document.getElementById('avatar-preview-container').style.border = 'none';
        }

        if (u.role === 'Sponsor') {
            document.getElementById('p-company').value = u.profile.company_name || '';
            document.getElementById('availability-group').style.display = 'block';
            const avail = availabilityFromDatabase(u.profile.is_available);
            if (avail === null) {
              showToast('Could not read visibility from the server.', 'error');
              document.getElementById('p-available').disabled = true;
            } else {
              document.getElementById('p-available').checked = avail;
              document.getElementById('sponsor-visibility-notice').style.display = avail ? 'none' : 'block';
              document.getElementById('p-available').disabled = false;
            }
        }

        if (u.profile.contacts && u.profile.contacts.length > 0) {
            const seenTypes = new Set();
            u.profile.contacts.forEach(c => {
                if (c.type === 'phone' && !seenTypes.has('phone')) {
                    document.getElementById('p-phone').value = c.value;
                    seenTypes.add('phone');
                } else if (c.type === 'email' && !seenTypes.has('email')) {
                    document.getElementById('p-contact-email').value = c.value;
                    seenTypes.add('email');
                } else if (c.type === 'website' && !seenTypes.has('website')) {
                    document.getElementById('p-website').value = c.value;
                    seenTypes.add('website');
                } else {
                    addContactRow(c.type, c.value);
                }
            });
        }
    }

    document.getElementById('p-logo').addEventListener('change', function(e) {
      if (e.target.files && e.target.files[0]) {
          const reader = new FileReader();
          reader.onload = function(e) {
              const imgEl = document.getElementById('current-logo');
              imgEl.src = e.target.result;
              imgEl.style.display = 'block';
              document.getElementById('avatar-placeholder').style.display = 'none';
              document.getElementById('avatar-preview-container').style.border = 'none';
          }
          reader.readAsDataURL(e.target.files[0]);
      }
  });

  // Dynamic Contact Form Logic
  const contactsContainer = document.getElementById('contacts-container');
  document.getElementById('add-contact-btn').addEventListener('click', () => addContactRow());

  function addContactRow(type = 'email', value = '') {
      const row = document.createElement('div');
      row.className = 'contact-row';
      row.style.cssText = 'display:flex; gap:10px; align-items:flex-start; margin-bottom:10px;';
      
      row.innerHTML = `
        <select class="form-control c-type" style="width: 140px; min-width: 140px;">
            <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
            <option value="phone" ${type === 'phone' ? 'selected' : ''}>Phone</option>
            <option value="website" ${type === 'website' ? 'selected' : ''}>Website</option>
            <option value="linkedin" ${type === 'linkedin' ? 'selected' : ''}>LinkedIn</option>
            <option value="facebook" ${type === 'facebook' ? 'selected' : ''}>Facebook</option>
            <option value="instagram" ${type === 'instagram' ? 'selected' : ''}>Instagram</option>
            <option value="x" ${type === 'x' ? 'selected' : ''}>X / Twitter</option>
        </select>
        <div style="flex:1; display:flex; flex-direction:column;">
            <input type="text" class="form-control c-value" placeholder="Enter link or username..." value="${value}" style="width:100%; min-width: 200px; padding: 10px;" />
            <span class="c-error" style="color:#e11d48; font-size:12px; margin-top:4px; display:none; font-weight:500;"></span>
        </div>
        <button type="button" class="btn remove-contact-btn" style="padding:0; margin:0; height:26px; width:26px; min-width:26px; border-radius:4px; font-size:14px; background:#fee2e2; color:#ef4444; border:1px solid #fca5a5; display:flex; align-items:center; justify-content:center; cursor:pointer;" title="Remove">✕</button>
      `;

      const selectType = row.querySelector('.c-type');
      const inputValue = row.querySelector('.c-value');
      const errorTip   = row.querySelector('.c-error');

      function checkValidation() {
          const t = selectType.value;
          const v = inputValue.value.trim();
          
          if (!v) { errorTip.style.display = 'none'; inputValue.style.borderColor = '#ccc'; return true; }
          
          let isValid = true;
          let msg = '';
          
          if (t === 'email') { 
              isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); 
              msg = 'Must be a valid email (e.g., user@email.com)'; 
          }
          else if (t === 'phone') { 
              // Strip all non-numeric/plus chars easily just for checking logic if they type letters
              isValid = /^[\+0-9\-\(\)\s]{7,25}$/.test(v) && !/[a-zA-Z]/.test(v);
              msg = 'Must be a valid phone number, digits only';
              // Force clean up
              if(!isValid) inputValue.value = v.replace(/[^\+0-9\-\(\)\s]/g, '');
          }
          else { 
              isValid = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/.test(v); 
              msg = 'Must be a valid URL (e.g., https://example.com)'; 
          }

          if (isValid) {
              errorTip.style.display = 'none';
              inputValue.style.borderColor = '#10b981'; // Green outline for success!
          } else {
              errorTip.innerText = msg;
              errorTip.style.display = 'block';
              inputValue.style.borderColor = '#ef4444'; // Red error
          }
          return isValid;
      }

      function updateInputRules() {
          const t = selectType.value;
          if (t === 'email') { inputValue.type = 'email'; inputValue.placeholder = 'you@example.com'; document.getElementById('p-contact-email') ? inputValue.maxLength=255 : null; }
          else if (t === 'phone') { inputValue.type = 'tel'; inputValue.placeholder = '+123456789...'; }
          else { inputValue.type = 'url'; inputValue.placeholder = 'https://...'; }
          checkValidation();
      }

      selectType.addEventListener('change', updateInputRules);
      inputValue.addEventListener('input', checkValidation);
      updateInputRules(); // Initialize immediately

      row.querySelector('.remove-contact-btn').addEventListener('click', () => row.remove());
      contactsContainer.appendChild(row);
  }

  document.getElementById('profile-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('save-btn');
    btn.textContent = 'Saving...'; btn.disabled = true;

    const fd = new FormData();
    fd.append('_method', 'PUT'); // Fake PUT for Laravel form handling
    fd.append('name', document.getElementById('p-name').value);
    fd.append('email', document.getElementById('p-email').value);
    fd.append('bio', document.getElementById('p-bio').value);

    // Dynamic Contacts + Static Fields merged into array
    const contactRows = document.querySelectorAll('.contact-row');
    const dynamicContacts = [];
    
    // Add the explicit fields if filled
    const pPhone = document.getElementById('p-phone').value;
    if(pPhone) dynamicContacts.push({ type: 'phone', value: pPhone });
    
    const pEmail = document.getElementById('p-contact-email').value;
    if(pEmail) dynamicContacts.push({ type: 'email', value: pEmail });
    
    const pWebsite = document.getElementById('p-website').value;
    if(pWebsite) dynamicContacts.push({ type: 'website', value: pWebsite });

    contactRows.forEach(row => {
        dynamicContacts.push({
            type: row.querySelector('.c-type').value,
            value: row.querySelector('.c-value').value
        });
    });
    fd.append('contacts', JSON.stringify(dynamicContacts));
    
    if (u.role === 'Sponsor') {
        fd.append('company_name', document.getElementById('p-company').value);
        fd.append('is_available', document.getElementById('p-available').checked ? '1' : '0');
    }

    const pass = document.getElementById('p-pass').value;
    if (pass) fd.append('password', pass);

    const fileInput = document.getElementById('p-logo');
    if (fileInput.files.length > 0) {
        fd.append('logo', fileInput.files[0]);
    }

    const res = await api.postForm('/profile', fd);
    
    if (res.ok) {
      showToast('Profile updated!', 'success');
      localStorage.setItem('user', JSON.stringify(res.data.user)); // update cache
      u = res.data.user;

      // Update local view
      if (res.data.user.profile && res.data.user.profile.logo) {
          const u_logo = res.data.user.profile.logo.startsWith('/') ? res.data.user.profile.logo : '/' + res.data.user.profile.logo;
          document.getElementById('current-logo').src = u_logo;
          document.getElementById('current-logo').style.display = 'block';
      }
      if (res.data.user.role === 'Sponsor' && res.data.user.profile) {
          const avail = availabilityFromDatabase(res.data.user.profile.is_available);
          if (avail !== null) {
            document.getElementById('p-available').checked = avail;
            document.getElementById('sponsor-visibility-notice').style.display = avail ? 'none' : 'block';
          }
      }
      populateSidebar(res.data.user);
      document.getElementById('p-pass').value = '';
    } else {
      const msg = res.data?.errors ? Object.values(res.data.errors).flat().join('. ') : res.data?.message || 'Error updating profile';
      showToast(msg, 'error');
    }
    btn.textContent = 'Save Changes'; btn.disabled = false;
  });

  })();
</script>
</body>
</html>
