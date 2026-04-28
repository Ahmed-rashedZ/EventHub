<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - EventHub</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/i18n.js"></script>
    <style>
        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
        }
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .profile-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2.5rem;
        }
        .avatar-upload {
            position: relative;
            max-width: 150px;
            margin: 0 auto 2.5rem;
        }
        .avatar-preview {
            width: 150px;
            height: 150px;
            position: relative;
            border-radius: 100%;
            border: 4px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
            background: var(--bg-dark);
        }
        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            aspect-ratio: 1/1;
        }
        .social-links-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        @media (max-width: 600px) {
            .social-links-grid {
                grid-template-columns: 1fr;
            }
        }
        .alert {
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            color: var(--success);
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        h3 {
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <!-- Dynamic Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
            <nav class="sidebar-nav" id="sidebar-links">
                <!-- Will be populated by JS -->
            </nav>
            @include('partials._sidebar-footer')
        </aside>

        <main class="main-content">
            <div class="topbar">
                <div>
                    <h1 class="page-title">Profile Settings</h1>
                    <p class="page-subtitle">Manage your personal information and contact details</p>
                </div>
            </div>

            <div class="profile-container">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="profile-card">
                    <h3 style="margin-top: 0; margin-bottom: 1.5rem;">Profile Information</h3>
                    <form action="{{ route('profile.update.info') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Avatar Section -->
                        <div class="avatar-upload">
                            <div class="avatar-preview">
                                @php
                                    $userImage = $user->image ?? $user->avatar;
                                @endphp
                                @if($userImage)
                                    <img id="imagePreview" src="{{ asset('storage/' . $userImage) }}" alt="Avatar">
                                @else
                                    <img id="imagePreview" src="{{ asset('images/default-avatar.png') }}" alt="Avatar">
                                @endif
                            </div>
                            <div style="margin-top: 1rem; text-align: center;">
                                <label for="image" class="btn btn-ghost btn-sm" style="cursor: pointer;">
                                    Change Photo
                                </label>
                                <input type='file' id="image" name="image" accept=".png, .jpg, .jpeg" style="display: none;" onchange="previewImage(this);"/>
                                @error('image')
                                    <p style="color: red; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Basic Info -->
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" >
                            @error('name')
                                <p style="color: red; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror" value="{{ old('contact_email', $user->contact_email) }}" placeholder="For public/communication purposes">
                            @error('contact_email')
                                <p style="color: red; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</p>
                            @enderror
                        </div>



                        <!-- Phones -->
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; margin: 2rem 0 1rem; padding-bottom: 0.5rem;">
                            <h3 style="margin:0; font-size: 1.1rem; font-weight: 600;">Phone Numbers</h3>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="addPhoneRow()">+ Add Phone</button>
                        </div>
                        <div id="phones-container"></div>
                        @error('phones.*')
                            <p style="color: red; font-size: 0.8rem;">Invalid Libyan mobile number. Use 10 digits starting with 091, 092, 093, or 094.</p>
                        @enderror

                        <div class="form-group">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" rows="4" placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')
                                <p style="color: red; font-size: 0.8rem;">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Social Links -->
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; margin: 2rem 0 1rem; padding-bottom: 0.5rem;">
                            <h3 style="margin:0; font-size: 1.1rem; font-weight: 600;">Social Profiles & Links</h3>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="addSocialRow('', '')">+ Add Link</button>
                        </div>
                        <div id="social-links-container">
                            <!-- Populated by JS -->
                        </div>

                        <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                            <a href="{{ route('profile.show', $user) }}" class="btn btn-ghost">View Public Profile</a>
                            <button type="submit" class="btn btn-primary">Save Information</button>
                        </div>
                    </form>
                </div>

                <div class="profile-card" style="margin-top: 2rem;">
                    <h3 style="margin-top: 0; margin-bottom: 1.5rem;">Security Settings</h3>
                    <form action="{{ route('profile.update.security') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="form-label">Login Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <p style="color: red; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</p>
                            @enderror
                            <small style="color: var(--text-muted); display: block; margin-top: 5px;">This email is used for logging into your account.</small>
                        </div>

                        <div class="form-group" style="margin-top: 1.5rem;">
                            <label class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <p style="color: red; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary">Update Security Settings</button>
                        </div>
                    </form>
                </div>

                @if(in_array($user->role, ['Event Manager', 'Sponsor']))
                <div class="profile-card" style="margin-top: 2rem;">
                    <h3 style="margin-top: 0; margin-bottom: 0.5rem;"><script>document.write(t('Verification Documents'))</script></h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                        <script>document.write(t('View and update your verification documents. Your account remains active while updates are reviewed.'))</script>
                    </p>
                    
                    <div id="docs-loading" style="text-align: center; padding: 20px;">
                        <div class="spinner" style="margin: auto;"></div>
                    </div>

                    <form id="verification-docs-form" style="display: none;">
                        <div id="docs-grid" style="display: grid; grid-template-columns: 1fr; gap: 1rem;"></div>
                        <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                            <button type="button" class="btn btn-primary" id="btn-submit-docs" onclick="submitVerificationDocs()" style="display: none;">
                                <script>document.write(t('Submit Updated Documents'))</script>
                            </button>
                        </div>
                    </form>
                </div>
                @endif



            </div>
        </main>
    </div>

    <div id="toast-container"></div>
    <script src="/js/api.js"></script>
    <script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
    <script>
    </script>
    <script>
        const user = requireAuth();
        if (user) {
            populateSidebar(user);
            setActiveNav();
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        const existingLinks = @json(old('social_links', $user->social_links) ?: []);
        const platforms = {
            'twitter': 'X (Twitter)',
            'linkedin': 'LinkedIn',
            'website': 'Personal Website',
            'facebook': 'Facebook',
            'instagram': 'Instagram',
            'whatsapp': 'WhatsApp',
            'telegram': 'Telegram',
            'github': 'GitHub',
            'youtube': 'YouTube',
            'tiktok': 'TikTok',
            'discord': 'Discord'
        };

        let rowCount = 0;

        function addPhoneRow(value = '') {
            const container = document.getElementById('phones-container');
            const row = document.createElement('div');
            row.style.cssText = "display: flex; gap: 1rem; margin-bottom: 1rem; align-items: center; animation: slideUp 0.3s ease;";
            row.innerHTML = `
                <div style="flex-grow: 1;">
                    <input type="text" name="phones[]" class="form-control" 
                           placeholder="09X 0000000" value="${value}" 
                           maxlength="10" 
                           pattern="09[1234][0-9]{7}"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10)"
                           title="Please enter a valid Libyan mobile number (091, 092, 093, or 094 followed by 7 digits)">
                </div>
                <button type="button" class="btn btn-ghost" style="color: var(--danger); font-size: 1.25rem; min-width: 44px; height: 44px; padding: 0; justify-content: center;" onclick="this.parentElement.remove()">✕</button>
            `;
            container.appendChild(row);
        }

        const existingPhonesStr = @json(old('phone', $user->phone) ?: '');
        const existingPhones = existingPhonesStr.split(',').map(s => s.trim()).filter(s => s.length > 0);
        if (existingPhones.length > 0) {
            existingPhones.forEach(p => addPhoneRow(p));
        } else {
            addPhoneRow();
        }

        function addSocialRow(platformKey = '', urlValue = '') {
            const container = document.getElementById('social-links-container');
            const row = document.createElement('div');
            row.className = 'social-link-row';
            row.style.cssText = "display: flex; gap: 1rem; margin-bottom: 1.25rem; align-items: flex-start; animation: slideUp 0.3s ease;";
            
            const currentIdx = rowCount++;
            let isCustom = platformKey && !platforms[platformKey];
            
            let selectHTML = `<select style="width: 180px; flex-shrink: 0;" class="form-control platform-select" onchange="updateSocialName(this, ${currentIdx})">`;
            selectHTML += `<option value="">Choose Platform...</option>`;
            for (let [k, v] of Object.entries(platforms)) {
                selectHTML += `<option value="${k}" ${platformKey === k ? 'selected' : ''}>${v}</option>`;
            }
            if (isCustom) {
                const displayKey = platformKey.split('_')[0]; // removal of unique suffix if any
                selectHTML += `<option value="${platformKey}" selected>${displayKey.charAt(0).toUpperCase() + displayKey.slice(1)}</option>`;
            }
            selectHTML += `<option value="other">Other...</option>`;
            selectHTML += `</select>`;

            row.innerHTML = `
                ${selectHTML}
                <div style="flex-grow: 1;">
                    <input type="url" name="social_links[${platformKey || 'link_'+currentIdx}]" class="form-control url-input" placeholder="https://..." value="${urlValue}" required>
                </div>
                <button type="button" class="btn btn-ghost" style="color: var(--danger); font-size: 1.25rem; min-width: 44px; height: 44px; padding: 0; justify-content: center;" onclick="this.parentElement.remove()">✕</button>
            `;
            container.appendChild(row);
        }

        function updateSocialName(selectEl, idx) {
            let platform = selectEl.value;
            if (platform === 'other') {
                let custom = prompt("Enter platform name (e.g. Portfolio, Discord, WhatsApp):");
                if (!custom) {
                    selectEl.selectedIndex = 0;
                    return;
                }
                platform = custom.toLowerCase().replace(/[^a-z0-9]/g, '');
                if (platform === '') platform = 'custom';
                
                // Add to select temporarily
                const opt = document.createElement('option');
                opt.value = platform + '_' + idx;
                opt.text = custom;
                selectEl.add(opt, selectEl.options[selectEl.options.length - 1]);
                selectEl.value = opt.value;
                platform = opt.value;
            } else if (platform !== "") {
                platform = platform + '_' + idx;
            }
            
            const inputEl = selectEl.parentElement.querySelector('.url-input');
            inputEl.name = `social_links[${platform}]`;
        }

        // Initialize
        let hasInitializedLinks = false;
        if (existingLinks && typeof existingLinks === 'object' && !Array.isArray(existingLinks)) {
            Object.entries(existingLinks).forEach(([p, link]) => {
                if (link) {
                    const cleanPlatform = p.split('_')[0];
                    addSocialRow(cleanPlatform, link);
                    hasInitializedLinks = true;
                }
            });
        }
        
        if (!hasInitializedLinks) {
            addSocialRow('', '');
        }

        // Verification Docs Script
        const DOC_ICONS = {
            'doc_commercial_register': '📋',
            'doc_tax_number': '🔢',
            'doc_articles_of_association': '📝',
            'doc_practice_license': '🏢',
        };

        async function loadMyDocuments() {
            try {
                const res = await apiFetch('/verifications/my-documents');
                const docsLoading = document.getElementById('docs-loading');
                const docsForm = document.getElementById('verification-docs-form');
                const docsGrid = document.getElementById('docs-grid');

                if (!res || !res.ok || !res.data || !res.data.documents) {
                    if (docsLoading) docsLoading.innerHTML = '<p style="color:var(--text-muted);">' + t('Could not load documents.') + '</p>';
                    return;
                }

                docsGrid.innerHTML = res.data.documents.map(doc => {
                    const icon = DOC_ICONS[doc.key] || '📄';
                    const statusMap = {
                        'approved': { color: '#10b981', bg: 'rgba(16,185,129,0.1)', icon: '✅', label: t('Approved') },
                        'rejected': { color: '#ef4444', bg: 'rgba(239,68,68,0.1)', icon: '❌', label: t('Rejected') },
                        'pending_update': { color: '#3b82f6', bg: 'rgba(59,130,246,0.1)', icon: '🔄', label: t('Pending Review') },
                        'pending': { color: '#f59e0b', bg: 'rgba(245,158,11,0.1)', icon: '⏳', label: t('Pending') },
                    };
                    const s = statusMap[doc.status] || statusMap['pending'];
                    const userId = {{ $user->id }};

                    let noteHtml = '';
                    if (doc.status === 'rejected' && doc.note) {
                        noteHtml = `<p style="margin: 0.5rem 0 0; font-size: 0.8rem; color: #ef4444; background: rgba(239,68,68,0.05); padding: 6px 10px; border-radius: 6px;">💬 ${doc.note}</p>`;
                    }

                    return `
                        <div style="padding: 1rem; border: 1px solid var(--border); border-radius: var(--radius); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; transition: border-color 0.2s;"
                             onmouseover="this.style.borderColor='rgba(110,64,242,0.3)'" onmouseout="this.style.borderColor='var(--border)'">
                            <div style="flex: 1; min-width: 200px;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <span style="font-size: 1.2rem;">${icon}</span>
                                    <h4 style="margin: 0; font-size: 0.95rem;">${t(doc.label)}</h4>
                                </div>
                                <span style="font-size: 0.75rem; padding: 2px 8px; border-radius: 4px; font-weight: 600; background: ${s.bg}; color: ${s.color};">
                                    ${s.icon} ${s.label}
                                </span>
                                ${noteHtml}
                            </div>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                ${doc.has_file ? `<button class="btn btn-ghost btn-sm" onclick="viewDocument(${userId}, '${doc.key}')">📄 ${t('View')}</button>` : ''}
                                <label class="btn btn-secondary btn-sm" style="cursor: pointer; margin:0;">
                                    📎 ${t('Upload New')}
                                    <input type="file" name="${doc.key}" style="display: none;" accept=".pdf,.png,.jpg,.jpeg" onchange="updateDocFileName(this, '${doc.key}')">
                                </label>
                            </div>
                            <div id="filename-${doc.key}" style="flex-basis: 100%; font-size: 0.8rem; color: var(--primary); display:none; margin-top: 0.5rem;"></div>
                        </div>
                    `;
                }).join('');

                if (docsLoading) docsLoading.style.display = 'none';
                docsForm.style.display = 'block';
            } catch (err) {
                console.error('Error loading documents:', err);
                const docsLoading = document.getElementById('docs-loading');
                if (docsLoading) docsLoading.innerHTML = '<p style="color:var(--danger);">' + t('Error loading documents.') + '</p>';
            }
        }

        function viewDocument(userId, docKey) {
            // Open the document via the session-auth web route (no popup blocker issues)
            window.open('/my-document/' + docKey, '_blank');
        }

        function updateDocFileName(input, docKey) {
            const displayEl = document.getElementById('filename-' + docKey);
            if (input.files && input.files[0]) {
                displayEl.textContent = '📎 ' + t('Selected:') + ' ' + input.files[0].name;
                displayEl.style.display = 'block';
                document.getElementById('btn-submit-docs').style.display = 'inline-flex';
            } else {
                displayEl.style.display = 'none';
            }
        }

        async function submitVerificationDocs() {
            const form = document.getElementById('verification-docs-form');
            const formData = new FormData(form);
            const btn = document.getElementById('btn-submit-docs');
            const originalText = btn.innerHTML;
            btn.innerHTML = `<span class="spinner" style="width:16px;height:16px;border-width:2px;border-color:#fff;border-top-color:transparent;"></span> ${t('Submitting...')}`;
            btn.disabled = true;

            const token = localStorage.getItem('token');
            try {
                // Use postForm to avoid Content-Type: application/json being set (breaks multipart)
                const res = await fetch('/api/verifications/reupload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
                    }
                });
                const data = await res.json();
                if (res.ok) {
                    showToast(data.message || t('Documents submitted for review.'), 'success');
                    btn.style.display = 'none';
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    setTimeout(() => loadMyDocuments(), 800);
                } else {
                    throw new Error(data.message || t('Error updating documents'));
                }
            } catch (err) {
                showToast(err.message || t('Error updating documents'), 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        // Load documents on page load for partners
        @if(in_array($user->role, ['Event Manager', 'Sponsor']))
            loadMyDocuments();
        @endif
    </script>
</body>
</html>

