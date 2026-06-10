<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register – EventHub</title>
  <link rel="stylesheet" href="/css/style.css" />
  <script src="/js/i18n.js"></script>
  <link rel="icon" href="/images/logo.png" type="image/png">
</head>

<body>
  <div class="auth-page">
    <div class="auth-bg-glow"></div>
    <div class="auth-bg-glow auth-bg-glow-2"></div>

    <div class="auth-card">
      <div class="auth-logo" style="display:flex; justify-content:center; align-items:center; margin-bottom: 20px;"><img
          src="/images/logo.png?v=3" alt="EventHub Logo"
          style="width: 95px; height: 95px; object-fit: contain; background: transparent !important;">
      </div>

      <h2 class="auth-heading">
        <script>document.write(t('Registration Restricted'))</script>
      </h2>
      <p class="auth-subheading">
        <script>document.write(t('To join the EventHub platform:'))</script>
      </p>

      <div style="margin-top: 24px; text-align: left;">
        <div class="card"
          style="padding: 16px; margin-bottom: 16px; background: rgba(59,130,246,0.1); border: 1px solid var(--primary);">
          <h4 style="margin: 0 0 8px 0; color: var(--primary); display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink: 0;"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
            <script>document.write(t('Attendees (Users)'))</script>
          </h4>
          <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">
            <script>document.write(t('Please download the EventHub Mobile App to create your account and buy tickets.'))</script>
          </p>
        </div>

        <div class="card"
          style="padding: 20px; background: rgba(139,92,246,0.08); border: 1px solid var(--accent); border-radius: 16px;">
          <h4 style="margin: 0 0 10px 0; color: var(--accent2); font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink: 0;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            <script>document.write(t('Partners & Exhibitors'))</script>
          </h4>
          <p style="margin: 0 0 16px 0; font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5;">
            <script>document.write(t('Join as an Event Manager, Sponsor, or Exhibiting Company. Your account will be reviewed and verified by our team.'))</script>
          </p>
          <a href="/register/partner" class="btn btn-primary"
            style="display:block; text-align:center; text-decoration:none; font-weight:700;">
            <script>document.write(t('Apply as Partner →'))</script>
          </a>
        </div>
      </div>

      <div class="auth-footer" style="margin-top: 32px;">
        Already have an account? <a href="/login">Sign in here</a>
      </div>
    </div>
  </div>

  <div id="toast-container"></div>
  <script src="/js/api.js"></script>
  <script>
    // Registration is restricted to Mobile App and Admins.
  </script>
</body>

</html>