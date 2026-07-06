<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }} - Profile - EventHub</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/i18n.js?v=3"></script>
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
<link rel="icon" href="/images/logo.png" type="image/png">
</head>
<body>
    <div class="app-layout">
        <!-- Dynamic Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px;"><img src="/images/logo.png?v=3" alt="EventHub Logo" style="height: 60px; width: auto; object-fit: contain; background: transparent !important;"></div>
            <nav class="sidebar-nav" id="sidebar-links">
                <!-- Will be populated by JS -->
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
                <span class="profile-role badge role-{{ strtolower(str_replace(' ', '-', $user->role)) }}"><script>document.write(t('{{ $user->role }}'))</script></span>
                @if(optional($user->profile)->company_type)
                    <span class="profile-role badge" style="background: rgba(110, 64, 242, 0.1); color: var(--accent); border-color: rgba(110, 64, 242, 0.2); margin-left: 8px;">
                        {{ optional($user->profile)->company_type }}
                    </span>
                @endif
                
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
                                <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 24px; color: var(--accent2);"><svg style="width:18px; height:18px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></span>
                                <div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Contact Email</div>
                                    <div style="font-weight: 500;">{{ $user->contact_email }}</div>
                                </div>
                            </div>
                            @endif
                            @if($user->phone)
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 24px; color: var(--accent2);"><svg style="width:18px; height:18px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></span>
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
                            'twitter' => '<svg style="width:18px; height:18px; fill:currentColor;" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg>',
                            'x' => '<svg style="width:18px; height:18px; fill:currentColor;" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg>',
                            'linkedin' => '<svg style="width:18px; height:18px; fill:currentColor;" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"></path></svg>',
                            'website' => '<svg style="width:18px; height:18px; stroke:currentColor; stroke-width:2; fill:none;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>',
                            'portfolio' => '<svg style="width:18px; height:18px; stroke:currentColor; stroke-width:2; fill:none;" viewBox="0 0 24 24"><path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"></path><circle cx="7.5" cy="10.5" r="1.5" fill="currentColor"></circle><circle cx="11.5" cy="7.5" r="1.5" fill="currentColor"></circle><circle cx="16.5" cy="9.5" r="1.5" fill="currentColor"></circle><circle cx="15.5" cy="14.5" r="1.5" fill="currentColor"></circle></svg>',
                            'facebook' => '<svg style="width:18px; height:18px; fill:currentColor;" viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.75z"></path></svg>',
                            'instagram' => '<svg style="width:18px; height:18px; stroke:currentColor; stroke-width:2; fill:none;" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>',
                            'whatsapp' => '<svg style="width:18px; height:18px; fill:currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.413 9.864-9.843.002-2.63-1.023-5.101-2.886-6.968C16.586 1.928 14.113.874 11.5.874c-5.442 0-9.87 4.414-9.874 9.845-.001 1.777.466 3.513 1.353 5.053L1.936 21.5l5.847-1.53c-1.28.69-2.223 1.187-1.136.684z"></path><path d="M16.945 13.91c-.27-.134-1.602-.79-1.85-.88-.25-.09-.432-.134-.61.134-.18.27-.696.88-.853 1.062-.157.18-.314.2-.584.067-.27-.134-1.14-.42-2.17-1.34-.8-.713-1.34-1.597-1.498-1.867-.157-.27-.017-.417.118-.552.12-.12.27-.315.405-.472.135-.157.18-.27.27-.45.09-.18.045-.337-.023-.472-.067-.135-.61-1.472-.837-2.013-.22-.53-.442-.457-.61-.466-.157-.008-.337-.01-.518-.01-.18 0-.473.067-.72.337-.248.27-.945.923-.945 2.25 0 1.328.966 2.61 1.1 2.78.136.17 1.902 2.904 4.607 4.07 2.7.5 2.7.8.647.785 1.472.585 2.7-.45.74-.27 2.014-.82 2.3-1.58.285-.758.285-1.407.2-1.543-.085-.136-.314-.22-.584-.354z"></path></svg>',
                            'telegram' => '<svg style="width:18px; height:18px; fill:currentColor;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.11.02-1.93 1.23-5.46 3.62-.51.35-.98.53-1.39.51-.46-.01-1.35-.26-2.01-.48-.81-.27-1.46-.42-1.4-.88.03-.24.37-.49 1.02-.75 3.99-1.74 6.66-2.88 7.99-3.43 3.8-1.57 4.59-1.85 5.1-.11.01.2.03.43.03.66z"></path></svg>',
                            'github' => '<svg style="width:18px; height:18px; fill:currentColor;" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.579.688.481C19.137 20.164 22 16.418 22 12c0-5.523-4.477-10-10-10z"></path></svg>',
                            'youtube' => '<svg style="width:18px; height:18px; fill:currentColor;" viewBox="0 0 24 24"><path d="M23.498 6.163a3.003 3.003 0 0 0-2.11-2.11C19.517 3.545 12 3.545 12 3.545s-7.517 0-9.388.508a3.003 3.003 0 0 0-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 0 0 2.11 2.11c1.871.508 9.388.508 9.388.508s7.517 0 9.388-.508a3.003 3.003 0 0 0 2.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"></path></svg>',
                            'tiktok' => '<svg style="width:18px; height:18px; fill:currentColor;" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.52-4.06-1.37-.28-.2-.53-.43-.77-.68v5.89c.04 2.87-1.52 5.74-4.22 6.78-2.73 1.05-6.03.46-8.15-1.59-2.48-2.39-2.82-6.52-.78-9.29 1.93-2.61 5.71-3.48 8.65-2.07v4.11c-1.46-.86-3.43-.63-4.63.56-.96.96-1.2 2.5-.59 3.7.61 1.2 2.03 1.9 3.37 1.72 1.34-.17 2.45-1.32 2.47-2.68V.02z"></path></svg>',
                            'discord' => '<svg style="width:18px; height:18px; fill:currentColor;" viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.094 13.094 0 0 1-1.873-.894.077.077 0 0 1-.008-.128c.126-.093.252-.19.372-.287a.075.075 0 0 1 .077-.011c3.92 1.793 8.18 1.793 12.061 0a.073.073 0 0 1 .078.009c.12.099.246.195.373.289a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.894.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.156-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.156 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.156-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.156 2.418z"></path></svg>',
                        ];
                    @endphp
                    @foreach($user->social_links as $p => $url)
                        @if($url)
                        @php
                            $platform = explode('_', $p)[0];
                            $icon = $iconMap[$platform] ?? '<svg style="width:18px; height:18px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.53-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>';
                        @endphp
                        <a href="{{ str_starts_with($url, 'http') ? $url : 'https://'.$url }}" target="_blank" class="social-link" title="{{ ucfirst($platform) }}">
                            {!! $icon !!}
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
    <script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
    <script>
        const user = requireAuth();
        if (user) {
            populateSidebar(user);
            // On this page, we might want to ensure the sidebar is always populated
            const linksDiv = document.getElementById('sidebar-links');
            if (linksDiv) linksDiv.innerHTML = buildSidebarLinks(user.role);
            setActiveNav();
        }
    </script>
</body>
</html>






