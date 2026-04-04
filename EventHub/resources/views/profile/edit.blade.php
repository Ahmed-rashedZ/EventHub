<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - EventHub</title>
    <link rel="stylesheet" href="/css/style.css">
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
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="avatar" id="sidebar-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                    <div class="user-info">
                        <div class="user-name" id="sidebar-username">{{ Auth::user()->name }}</div>
                        <div class="user-role" id="sidebar-role">{{ Auth::user()->role }}</div>
                    </div>
                </div>
                <button class="btn btn-logout" id="logout-btn">🚪 Sign Out</button>
            </div>
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
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Avatar Section -->
                        <div class="avatar-upload">
                            <div class="avatar-preview">
                                <img id="imagePreview" src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random&size=150' }}" alt="Avatar">
                            </div>
                            <div style="margin-top: 1rem; text-align: center;">
                                <label for="avatar" class="btn btn-ghost btn-sm" style="cursor: pointer;">
                                    Change Photo
                                </label>
                                <input type='file' id="avatar" name="avatar" accept=".png, .jpg, .jpeg" style="display: none;" onchange="previewImage(this);"/>
                                @error('avatar')
                                    <p style="color: red; font-size: 0.8rem;">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Basic Info -->
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="+1 (555) 000-0000">
                            @error('phone')
                                <p style="color: red; font-size: 0.8rem;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" rows="4" placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')
                                <p style="color: red; font-size: 0.8rem;">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Social Links -->
                        <h3 style="margin: 2rem 0 1rem; font-size: 1.1rem; font-weight: 600; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Social Profiles</h3>
                        <div class="social-links-grid">
                            <div class="form-group">
                                <label class="form-label">Twitter</label>
                                <input type="url" name="social_links[twitter]" class="form-control" value="{{ old('social_links.twitter', $user->social_links['twitter'] ?? '') }}" placeholder="https://twitter.com/username">
                            </div>
                            <div class="form-group">
                                <label class="form-label">LinkedIn</label>
                                <input type="url" name="social_links[linkedin]" class="form-control" value="{{ old('social_links.linkedin', $user->social_links['linkedin'] ?? '') }}" placeholder="https://linkedin.com/in/username">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Website</label>
                                <input type="url" name="social_links[website]" class="form-control" value="{{ old('social_links.website', $user->social_links['website'] ?? '') }}" placeholder="https://yourwebsite.com">
                            </div>
                        </div>

                        <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                            <a href="{{ route('profile.show', $user) }}" class="btn btn-ghost">View Public Profile</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <div id="toast-container"></div>
    <script src="/js/api.js"></script>
    <script src="/js/auth.js"></script>
    <script>
        const user = AuthUser();
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
    </script>
</body>
</html>
