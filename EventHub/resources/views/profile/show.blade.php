<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }} - Profile - EventHub</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/i18n.js"></script>
    <style>
        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
        }
        .profile-header {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent2) 100%);
            height: 250px;
            position: relative;
            margin-bottom: 5rem;
            border-radius: 0 0 24px 24px;
            box-shadow: 0 10px 30px rgba(110, 64, 242, 0.2);
        }
        .profile-avatar-container {
            position: absolute;
            bottom: -80px;
            left: 50%;
            transform: translateX(-50%);
            width: 160px;
            height: 160px;
            border-radius: 50%;
            border: 6px solid var(--bg-dark);
            box-shadow: var(--shadow);
            background: var(--bg-card);
            overflow: hidden;
        }
        .profile-avatar-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-content {
            text-align: center;
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 1rem 5rem;
        }
        .profile-name {
            font-size: 2.75rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }
        .profile-role {
            font-size: 0.9rem;
            color: var(--accent2);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-bottom: 1.5rem;
            display: inline-block;
            background: rgba(34, 211, 238, 0.1);
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            font-weight: 700;
            border: 1px solid rgba(34, 211, 238, 0.2);
        }
        .social-links {
            display: flex;
            justify-content: center;
            gap: 1.25rem;
        }
        .social-link {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1.3rem;
        }
        .social-link:hover {
            background: rgba(110, 64, 242, 0.1);
            border-color: var(--accent);
            transform: translateY(-5px);
            color: var(--accent2);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
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
                <a class="nav-item" href="{{ route('profile.edit') }}"><span class="nav-icon">⚙️</span> My Profile</a>
            </nav>
            @include('partials._sidebar-footer')
        </aside>

        <main class="main-content" style="padding: 0;">
            <div class="profile-header">
                @if(Auth::id() === $user->id)
                    <a href="{{ route('profile.edit') }}" style="position: absolute; top: 1rem; right: 1rem; z-index: 10;" class="btn btn-ghost btn-sm">Edit Profile</a>
                @endif
                <div class="profile-avatar-container">
                    @php
                        $userImage = $user->image ?? $user->avatar;
                    @endphp
                    @if($userImage)
                        <img src="{{ asset('storage/' . $userImage) }}" alt="{{ $user->name }}" style="aspect-ratio: 1/1;">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&size=200" alt="{{ $user->name }}" style="aspect-ratio: 1/1;">
                    @endif
                </div>
            </div>

            <div class="profile-content">
                <h1 class="profile-name">{{ $user->name }}</h1>
                <span class="profile-role badge role-{{ strtolower(str_replace(' ', '-', $user->role)) }}">{{ $user->role }}</span>
                
                <div class="profile-info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 3rem; text-align: left;">
                    <!-- About Section -->
                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--accent2); text-transform: uppercase; letter-spacing: 0.05em;">About</h3>
                        <p style="color: var(--text-muted); line-height: 1.6;">
                            {{ $user->bio ?: 'No bio provided yet.' }}
                        </p>
                    </div>

                    <!-- Contact Details -->
                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--accent2); text-transform: uppercase; letter-spacing: 0.05em;">Contact Information</h3>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            @if($user->contact_email)
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span style="font-size: 1.2rem; min-width: 24px;">📧</span>
                                <div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Contact Email</div>
                                    <div style="font-weight: 500;">{{ $user->contact_email }}</div>
                                </div>
                            </div>
                            @endif
                            @if($user->phone)
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span style="font-size: 1.2rem; min-width: 24px;">📱</span>
                                <div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Phone</div>
                                    <div style="font-weight: 500;"><a href="tel:{{ $user->phone }}" style="color: inherit; text-decoration: none;">{{ $user->phone }}</a></div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($user->social_links)
                <div class="social-links" style="margin-top: 2rem;">
                    @php
                        $iconMap = [
                            'twitter' => '𝕏', 'x' => '𝕏',
                            'linkedin' => 'in', 
                            'website' => '🌐', 'portfolio' => '🎨',
                            'facebook' => 'f', 'instagram' => '📸',
                            'whatsapp' => '💬', 'telegram' => '✈️',
                            'github' => 'gh', 'youtube' => 'yt',
                            'tiktok' => '🎵', 'discord' => '👾'
                        ];
                    @endphp
                    @foreach($user->social_links as $p => $url)
                        @if($url)
                        @php
                            $platform = explode('_', $p)[0];
                            $icon = $iconMap[$platform] ?? '🔗';
                        @endphp
                        <a href="{{ str_starts_with($url, 'http') ? $url : 'https://'.$url }}" target="_blank" class="social-link" title="{{ ucfirst($platform) }}">
                            {{ $icon }}
                        </a>
                        @endif
                    @endforeach
                </div>
                @endif
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
    </script>
</body>
</html>
