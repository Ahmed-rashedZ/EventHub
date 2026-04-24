<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register – EventHub</title>
  <link rel="stylesheet" href="/css/style.css" />
  <script src="/js/i18n.js"></script>
</head>

<body>
  <div class="auth-page">
    <div class="auth-bg-glow"></div>
    <div class="auth-bg-glow auth-bg-glow-2"></div>

    <div class="auth-card">
      <div class="auth-logo">
        <div class="logo-icon">🎯</div>
        <h1>EventHub</h1>
      </div>

      <h2 class="auth-heading">Registration Restricted</h2>
      <p class="auth-subheading">To join the EventHub platform:</p>

      <div style="margin-top: 24px; text-align: left;">
        <div class="card"
          style="padding: 16px; margin-bottom: 16px; background: rgba(59,130,246,0.1); border: 1px solid var(--primary);">
          <h4 style="margin: 0 0 8px 0; color: var(--primary);">📱 Attendees (Users)</h4>
          <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">Please download the <strong>EventHub
              Mobile App</strong> to create your account and buy tickets.</p>
        </div>

        <div class="card" style="padding: 16px; background: rgba(16,185,129,0.1); border: 1px solid var(--success);">
          <h4 style="margin: 0 0 8px 0; color: var(--success);">💼 Managers & Sponsors</h4>
          <p style="margin: 0 0 12px 0; font-size: 0.85rem; color: var(--text-secondary);">Accounts for Event Managers and Sponsors must be verified by the System Administrator.</p>
          <a href="/register/partner" class="btn btn-success btn-sm" style="display:inline-block; text-decoration:none;">Apply as Partner →</a>
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