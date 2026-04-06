<div class="sidebar-footer">
    <div class="sidebar-user">
        @if(Auth::check())
            <a href="{{ route('profile.show', Auth::user()) }}" style="text-decoration: none;" class="avatar" id="sidebar-avatar" title="View Profile">
                @if(Auth::user()->image)
                    <img src="{{ asset('storage/' . Auth::user()->image) }}"
                        style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; aspect-ratio: 1/1;" alt="User Image">
                @elseif(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}"
                        style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; aspect-ratio: 1/1;" alt="User Image">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random&size=150"
                        style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; aspect-ratio: 1/1;" alt="Default Avatar">
                @endif
            </a>
            <div class="user-info">
                <div class="user-name" id="sidebar-username">{{ Auth::user()->name }}</div>
                <div class="user-role" id="sidebar-role">{{ Auth::user()->role }}</div>
            </div>
        @endif
    </div>
    <button class="btn btn-logout" id="logout-btn">🚪 Sign Out</button>
</div>