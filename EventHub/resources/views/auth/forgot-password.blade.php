<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password – EventHub</title>
  <meta name="description" content="Reset your EventHub account password"/>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <style>
    .step { display: none; animation: fadeSlideIn 0.4s ease; }
    .step.active { display: block; }
    @keyframes fadeSlideIn {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .otp-input {
      display: flex; gap: 8px; justify-content: center; margin: 20px 0;
    }
    .otp-input input {
      width: 48px; height: 56px;
      text-align: center; font-size: 1.4rem; font-weight: 700;
      font-family: 'Courier New', monospace;
      background: var(--bg-dark);
      border: 2px solid var(--border);
      border-radius: var(--radius-sm);
      color: var(--accent);
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .otp-input input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(110, 64, 242, 0.15);
    }
    .resend-link {
      display: inline-block; margin-top: 12px;
      font-size: 0.82rem; color: var(--text-muted);
      cursor: pointer; transition: color 0.2s;
    }
    .resend-link:hover { color: var(--accent); }
    .resend-link.disabled { pointer-events: none; opacity: 0.5; }
    .timer-badge {
      display: inline-block; background: rgba(239,68,68,0.1);
      color: #ef4444; font-size: 0.78rem; font-weight: 600;
      padding: 3px 10px; border-radius: 20px; margin-top: 8px;
    }
    .success-icon {
      font-size: 3rem; margin-bottom: 12px;
      animation: bounceIn 0.5s ease;
    }
    @keyframes bounceIn {
      0%   { transform: scale(0.3); opacity: 0; }
      50%  { transform: scale(1.1); }
      100% { transform: scale(1); opacity: 1; }
    }
  </style>
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

    <!-- ═══ STEP 1: Enter Email ═══ -->
    <div id="step-1" class="step active">
      <h2 class="auth-heading"><script>document.write(t('Forgot Password?'))</script></h2>
      <p class="auth-subheading"><script>document.write(t('Enter your email and we\'ll send you a verification code.'))</script></p>

      <form id="email-form">
        <div class="form-group">
          <label class="form-label" for="email"><script>document.write(t('Email Address'))</script></label>
          <input id="email" type="email" class="form-control" placeholder="you@example.com" required/>
        </div>
        <button type="submit" class="btn btn-primary btn-block" id="btn-send">
          <script>document.write(t('Send Verification Code'))</script>
        </button>
      </form>

      <div class="auth-footer">
        <script>document.write(t('Remember your password?'))</script> <a href="/login"><script>document.write(t('Sign In'))</script></a>
      </div>
    </div>

    <!-- ═══ STEP 2: Enter Code + New Password ═══ -->
    <div id="step-2" class="step">
      <h2 class="auth-heading"><script>document.write(t('Enter Verification Code'))</script></h2>
      <p class="auth-subheading" id="step2-subtitle"></p>

      <form id="reset-form">
        <div class="otp-input" id="otp-container">
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" data-idx="0" autocomplete="off"/>
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" data-idx="1" autocomplete="off"/>
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" data-idx="2" autocomplete="off"/>
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" data-idx="3" autocomplete="off"/>
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" data-idx="4" autocomplete="off"/>
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" data-idx="5" autocomplete="off"/>
        </div>

        <div style="text-align:center;">
          <div class="timer-badge" id="timer-badge">⏱ <span id="timer">5:00</span></div>
        </div>

        <div class="form-group" style="margin-top: 20px;">
          <label class="form-label" for="new-password"><script>document.write(t('New Password'))</script></label>
          <input id="new-password" type="password" class="form-control" placeholder="••••••••" required minlength="8"/>
        </div>
        <div class="form-group">
          <label class="form-label" for="confirm-password"><script>document.write(t('Confirm Password'))</script></label>
          <input id="confirm-password" type="password" class="form-control" placeholder="••••••••" required minlength="8"/>
        </div>

        <button type="submit" class="btn btn-primary btn-block" id="btn-reset">
          <script>document.write(t('Reset Password'))</script>
        </button>
      </form>

      <div style="text-align:center;">
        <span class="resend-link" id="resend-link" onclick="resendCode()">
          <script>document.write(t('Didn\'t receive the code? Resend'))</script>
        </span>
      </div>

      <div class="auth-footer">
        <a href="#" onclick="goBack()" style="color:var(--text-muted);"><script>document.write(t('← Back'))</script></a>
      </div>
    </div>

    <!-- ═══ STEP 3: Success ═══ -->
    <div id="step-3" class="step" style="text-align:center;">
      <div class="success-icon">✅</div>
      <h2 class="auth-heading"><script>document.write(t('Password Changed!'))</script></h2>
      <p class="auth-subheading" style="margin-bottom:24px;"><script>document.write(t('Your password has been reset successfully. You can now sign in with your new password.'))</script></p>
      <a href="/login" class="btn btn-primary btn-block" style="text-decoration:none;"><script>document.write(t('Go to Sign In'))</script></a>
    </div>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script>
  let userEmail = '';
  let timerInterval = null;
  let timerSeconds = 300; // 5 minutes

  // ── OTP Input Logic ──
  const otpInputs = document.querySelectorAll('.otp-input input');
  otpInputs.forEach((inp, i) => {
    inp.addEventListener('input', (e) => {
      const val = e.target.value.replace(/\D/g, '');
      e.target.value = val;
      if (val && i < otpInputs.length - 1) otpInputs[i + 1].focus();
    });
    inp.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !e.target.value && i > 0) {
        otpInputs[i - 1].focus();
      }
    });
    inp.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
      text.split('').forEach((ch, j) => { if (otpInputs[j]) otpInputs[j].value = ch; });
      if (text.length > 0) otpInputs[Math.min(text.length, 5)].focus();
    });
  });

  function getOtpValue() {
    return Array.from(otpInputs).map(i => i.value).join('');
  }

  // ── Step Navigation ──
  function showStep(n) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById('step-' + n).classList.add('active');
  }

  function goBack() {
    clearInterval(timerInterval);
    showStep(1);
  }

  // ── Timer ──
  function startTimer() {
    timerSeconds = 300;
    updateTimerDisplay();
    clearInterval(timerInterval);
    timerInterval = setInterval(() => {
      timerSeconds--;
      updateTimerDisplay();
      if (timerSeconds <= 0) {
        clearInterval(timerInterval);
        showToast(t('Code expired. Please request a new one.'), 'error');
        document.getElementById('resend-link').classList.remove('disabled');
      }
    }, 1000);
  }

  function updateTimerDisplay() {
    const m = Math.floor(timerSeconds / 60);
    const s = timerSeconds % 60;
    document.getElementById('timer').textContent = `${m}:${s.toString().padStart(2, '0')}`;
    const badge = document.getElementById('timer-badge');
    badge.style.color = timerSeconds <= 60 ? '#ef4444' : '#f59e0b';
    badge.style.background = timerSeconds <= 60 ? 'rgba(239,68,68,0.1)' : 'rgba(245,158,11,0.1)';
  }

  // ── Step 1: Send Code ──
  document.getElementById('email-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    userEmail = document.getElementById('email').value;
    const btn = document.getElementById('btn-send');
    btn.textContent = t('Sending...');
    btn.disabled = true;

    const res = await api.post('/password/forgot', { email: userEmail });

    if (res.ok) {
      showToast(t('Verification code sent to your email!'), 'success');
      document.getElementById('step2-subtitle').textContent =
        t('We sent a 6-digit code to') + ' ' + userEmail;
      showStep(2);
      otpInputs[0].focus();
      startTimer();
      document.getElementById('resend-link').classList.add('disabled');
      setTimeout(() => document.getElementById('resend-link').classList.remove('disabled'), 30000);

      // Dev mode: auto-fill the code if returned by the API
      if (res.data.debug_code) {
        const code = res.data.debug_code;
        code.split('').forEach((ch, j) => { if (otpInputs[j]) otpInputs[j].value = ch; });
        showToast('🔧 ' + t('Dev mode — code auto-filled:') + ' ' + code, 'info');
      }
    } else {
      showToast(res.data?.message || t('Error sending code'), 'error');
    }

    btn.textContent = t('Send Verification Code');
    btn.disabled = false;
  });

  // ── Step 2: Reset Password ──
  document.getElementById('reset-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const code = getOtpValue();
    const pwd = document.getElementById('new-password').value;
    const confirm = document.getElementById('confirm-password').value;

    if (code.length !== 6) {
      showToast(t('Please enter the full 6-digit code.'), 'error');
      return;
    }
    if (pwd !== confirm) {
      showToast(t('Passwords do not match.'), 'error');
      return;
    }
    if (pwd.length < 8) {
      showToast(t('Password must be at least 8 characters.'), 'error');
      return;
    }

    const btn = document.getElementById('btn-reset');
    btn.textContent = t('Resetting...');
    btn.disabled = true;

    const res = await api.post('/password/reset', {
      email: userEmail,
      code: code,
      password: pwd,
      password_confirmation: confirm,
    });

    if (res.ok) {
      clearInterval(timerInterval);
      showStep(3);
    } else {
      showToast(res.data?.message || t('Error resetting password'), 'error');
      // Clear OTP inputs on error
      otpInputs.forEach(inp => inp.value = '');
      otpInputs[0].focus();
    }

    btn.textContent = t('Reset Password');
    btn.disabled = false;
  });

  // ── Resend Code ──
  async function resendCode() {
    const link = document.getElementById('resend-link');
    link.classList.add('disabled');

    const res = await api.post('/password/forgot', { email: userEmail });
    if (res.ok) {
      showToast(t('New code sent!'), 'success');
      startTimer();
      otpInputs.forEach(inp => inp.value = '');
      otpInputs[0].focus();
      setTimeout(() => link.classList.remove('disabled'), 30000);

      // Dev mode: auto-fill the code if returned by the API
      if (res.data.debug_code) {
        const code = res.data.debug_code;
        code.split('').forEach((ch, j) => { if (otpInputs[j]) otpInputs[j].value = ch; });
        showToast('🔧 ' + t('Dev mode — code auto-filled:') + ' ' + code, 'info');
      }
    } else {
      showToast(res.data?.message || t('Error resending code'), 'error');
      link.classList.remove('disabled');
    }
  }
</script>
</body>
</html>
