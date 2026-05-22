<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register – EventHub</title>
  <link rel="stylesheet" href="/css/style.css" />
  <script src="/js/i18n.js"></script>
  <link rel="icon" href="/images/logo.jpg" type="image/jpeg">
</head>

<body>
  <div class="auth-page">
    <div class="auth-bg-glow"></div>
    <div class="auth-bg-glow auth-bg-glow-2"></div>

    <div class="auth-card">
      <div class="auth-logo" style="display:flex; justify-content:center; align-items:center; margin-bottom: 20px;"><img
          src="/images/logo.jpg" alt="EventHub Logo"
          style="width: 95px; height: 95px; object-fit: contain; border-radius: 50%; box-shadow: 0 8px 24px rgba(0,0,0,0.1);">
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
          <h4 style="margin: 0 0 8px 0; color: var(--primary);">📱
            <script>document.write(t('Attendees (Users)'))</script>
          </h4>
          <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">
            <script>document.write(t('Please download the EventHub Mobile App to create your account and buy tickets.'))</script>
          </p>
        </div>

        <div class="card"
          style="padding: 20px; background: rgba(139,92,246,0.08); border: 1px solid var(--accent); border-radius: 16px;">
          <h4 style="margin: 0 0 10px 0; color: var(--accent2); font-size: 1.1rem;">🤝 الشركاء والعارضون</h4>
          <p style="margin: 0 0 16px 0; font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5;">
            انضم كمدير فعاليات أو راعٍ أو شركة عارضة. سيتم مراجعة حسابك والتحقق منه من قِبل فريقنا.
          </p>
          <a href="/register/partner" class="btn btn-primary"
            style="display:block; text-align:center; text-decoration:none; font-weight:700;">
            التقديم كشريك →
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