<div class="sidebar-footer">
    <div class="sidebar-user">
        @if(Auth::check())
            <div class="avatar" id="sidebar-avatar">
                @if(Auth::user()->image)
                    <img src="{{ asset('storage/' . Auth::user()->image) }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;" alt="User Image">
                @elseif(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;" alt="User Image">
                @else
                    <img src="{{ asset('images/default-avatar.png') }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;" alt="Default Avatar">
                @endif
            </div>
            <div class="user-info">
                <div class="user-name" id="sidebar-username">{{ Auth::user()->name }}</div>
                <div class="user-role" id="sidebar-role">{{ Auth::user()->role }}</div>
            </div>
        @endif
    </div>
    <button class="btn btn-logout" id="logout-btn">🚪 Sign Out</button>
</div>
