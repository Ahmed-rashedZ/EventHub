<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pending Verification – EventHub</title>
  <link rel="stylesheet" href="/css/style.css" />
</head>
<body>
  <div class="auth-page">
    <div class="auth-bg-glow"></div>
    <div class="auth-card" style="text-align: center; max-width: 450px;">
      <div style="font-size: 3rem; margin-bottom: 16px;">⏳</div>
      <h2 style="margin: 0 0 16px 0; color: #fff;">Application Under Review</h2>
      <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 24px;">
        Your partner application has been received successfully. Our administration team is currently verifying the documents you uploaded. You will receive full access to the platform once your status is approved.
      </p>
      
      <div style="padding: 16px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 8px; margin-bottom: 24px;">
        <h4 style="color: #f59e0b; margin: 0 0 4px 0; font-size: 0.85rem; text-transform: uppercase;">Current Status</h4>
        <div style="font-size: 1.1rem; font-weight: 700; color: #fff;" id="status-display">PENDING</div>
        <p style="color: #f59e0b; font-size: 0.8rem; margin: 8px 0 0 0;" id="notes-display"></p>
      </div>

      <div id="reupload-section" style="display:none; text-align: left; margin-bottom: 24px;">
        <label class="form-label">Upload Revised Document (PDF/JPG)</label>
        <div style="display:flex; gap: 8px;">
            <input type="file" id="new-document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="flex:1" />
            <button onclick="resubmitDocument()" id="btn-resubmit" class="btn btn-primary">Resubmit</button>
        </div>
      </div>

      <button onclick="logout()" class="btn btn-ghost" style="width: 100%;">Sign Out</button>
    </div>
  </div>

  <script src="/js/api.js"></script>
  <script>
    const uStr = localStorage.getItem('user');
    if (!uStr) window.location.href = '/login';
    else {
      try {
        const u = JSON.parse(uStr);
        if (u.verification_status === 'verified') {
          window.location.href = u.role === 'Event Manager' ? '/manager/dashboard' : '/sponsor/dashboard';
        }
        else if (u.verification_status === 'rejected') {
          const sd = document.getElementById('status-display');
          sd.textContent = 'REJECTED';
          sd.style.color = '#ef4444';
          sd.parentElement.style.background = 'rgba(239, 68, 68, 0.1)';
          sd.parentElement.style.borderColor = 'rgba(239, 68, 68, 0.4)';
          if (u.verification_notes) {
            document.getElementById('notes-display').textContent = 'Reason: ' + u.verification_notes;
            document.getElementById('notes-display').style.color = '#ef4444';
          }
        }
        else if (u.verification_status === 'changes_requested') {
          const sd = document.getElementById('status-display');
          sd.textContent = 'ACTION REQUIRED';
          sd.style.color = '#f59e0b';
          if (u.verification_notes) {
            document.getElementById('notes-display').textContent = 'Admin Feedback: ' + u.verification_notes;
          }
          document.getElementById('reupload-section').style.display = 'block';
        }
      } catch(e) {}
    }

    async function logout() {
      try {
        await fetch('/api/logout', { method: 'POST', headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token'), 'Accept': 'application/json' } });
      } catch(e) {}
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
      window.location.href = '/login';
    }

    async function resubmitDocument() {
        const fileInput = document.getElementById('new-document');
        if (!fileInput.files.length) return alert('Please select a document first.');
        
        const btn = document.getElementById('btn-resubmit');
        btn.textContent = '...'; btn.disabled = true;

        const formData = new FormData();
        formData.append('verification_document', fileInput.files[0]);

        const res = await api.postForm('/verifications/reupload', formData);
        
        if (res.ok) {
            alert('Document submitted successfully!');
            localStorage.setItem('user', JSON.stringify(res.data.user));
            window.location.reload();
        } else {
            alert('Error re-uploading document.');
            btn.textContent = 'Resubmit'; btn.disabled = false;
        }
    }
  </script>
</body>
</html>
