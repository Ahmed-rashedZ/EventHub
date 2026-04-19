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
            <nav class="sidebar-nav">
                @if(Auth::user()->role === 'Admin')
                    <span class="nav-section-label">Overview</span>
                    <a class="nav-item" href="/admin/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
                    <span class="nav-section-label">Management</span>
                    <a class="nav-item" href="/admin/users"><span class="nav-icon">👥</span> Users</a>
                    <a class="nav-item" href="/admin/events"><span class="nav-icon">📅</span> Events</a>
                    <a class="nav-item" href="/admin/venues"><span class="nav-icon">🏛️</span> Venues</a>
                @elseif(Auth::user()->role === 'Event Manager')
                    <span class="nav-section-label">Overview</span>
                    <a class="nav-item" href="/manager/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
                    <span class="nav-section-label">Events</span>
                    <a class="nav-item" href="/manager/events"><span class="nav-icon">📅</span> My Events</a>
                    <a class="nav-item" href="/manager/assistants"><span class="nav-icon">👥</span> Assistants</a>
                    <a class="nav-item" href="/manager/attendance"><span class="nav-icon">📍</span> Attendance</a>
                    <a class="nav-item" href="/manager/sponsorship"><span class="nav-icon">💼</span> Sponsorship</a>
                @elseif(Auth::user()->role === 'Sponsor')
                    <span class="nav-section-label">Overview</span>
                    <a class="nav-item" href="/sponsor/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
                    <span class="nav-section-label">Opportunities</span>
                    <a class="nav-item" href="/sponsor/events"><span class="nav-icon">🌍</span> Browse Events</a>
                    <a class="nav-item" href="/sponsor/requests"><span class="nav-icon">💼</span> Sponsorships</a>
                @endif

                <span class="nav-section-label">Settings</span>
                <a class="nav-item active" href="{{ route('profile.edit') }}"><span class="nav-icon">⚙️</span> My Profile</a>
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

                        @if(Auth::user()->role === 'Sponsor')
                        <div class="form-group">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $user->profile->company_name ?? '') }}" >
                            @error('company_name')
                                <p style="color: red; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <!-- Phones -->
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; margin: 2rem 0 1rem; padding-bottom: 0.5rem;">
                            <h3 style="margin:0; font-size: 1.1rem; font-weight: 600;">Phone Numbers</h3>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="addPhoneRow()">+ Add Phone</button>
                        </div>
                        <div id="phones-container"></div>
                        @error('phones.*')
                            <p style="color: red; font-size: 0.8rem;">Some phone numbers are invalid (max 20 chars).</p>
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

                <!-- ── Language Settings Card ───────────────────── -->
                <div class="profile-card" style="margin-top: 2rem;">
                    <h3 style="margin-top: 0; margin-bottom: 6px;">Language / اللغة</h3>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.4rem;">Choose the interface language for all pages in the system.</p>

                    <div class="lang-buttons-wrap">
                        <button type="button" id="lang-en-btn" class="lang-btn" onclick="setLanguage('en')">
                            <span class="flag">🇬🇧</span> English
                        </button>
                        <button type="button" id="lang-ar-btn" class="lang-btn" onclick="setLanguage('ar')" style="font-family:'Cairo',sans-serif;">
                            <span class="flag">🇸🇦</span> العربية
                        </button>
                    </div>
                    <small style="display:block; margin-top:12px; color:var(--text-muted); font-size:0.8rem;">
                        Changes take effect immediately &mdash; التغيير يُطبَّق فوراً
                    </small>
                </div>

            </div>
        </main>
    </div>

    <div id="toast-container"></div>
    <script src="/js/api.js"></script>
    <script src="/js/auth.js"></script>
    <script>
        /* ── Highlight the active language button (✖ no reload needed) ── */
        document.addEventListener('DOMContentLoaded', function () {
            const lang  = localStorage.getItem('lang') || 'en';
            const enBtn = document.getElementById('lang-en-btn');
            const arBtn = document.getElementById('lang-ar-btn');
            if (enBtn) enBtn.classList.toggle('active', lang === 'en');
            if (arBtn) arBtn.classList.toggle('active', lang === 'ar');
        });
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
                    <input type="text" name="phones[]" class="form-control" placeholder="+218 (092) 000-0000" value="${value}" maxlength="20">
                </div>
                <button type="button" class="btn btn-ghost" style="color: var(--danger); font-size: 1.25rem; min-width: 44px; height: 44px; padding: 0;" onclick="this.parentElement.remove()">✕</button>
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
                <button type="button" class="btn btn-ghost" style="color: var(--danger); font-size: 1.25rem; min-width: 44px; height: 44px; padding: 0;" onclick="this.parentElement.remove()">✕</button>
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
    </script>
</body>
</html>
