<nav class="sv-sidebar">
    <a href="{{ url('/') }}" class="brand"><span>SOCVIAL</span></a>

    <div class="sv-nav">
        @php $user = auth()->user(); @endphp

        @if($user->isSaasAdmin())
            <div class="sv-nav-label">Platform</div>
            <a href="{{ route('saas.companies.index') }}" class="{{ request()->routeIs('saas.companies.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Companies
            </a>
            <a href="{{ route('saas.platforms.index') }}" class="{{ request()->routeIs('saas.platforms.*') ? 'active' : '' }}">
                <i class="bi bi-share"></i> Platforms
            </a>

        @else
            <div class="sv-nav-label">{{ $user->isClient() ? 'My content' : 'Workspace' }}</div>

            <a href="{{ route('calendar.index') }}" class="{{ request()->routeIs('calendar.*') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> {{ $user->isClient() ? 'My Calendar' : 'Calendar' }}
            </a>

            @unless($user->isClient())
                <a href="{{ route('posts.index') }}" class="{{ request()->routeIs('posts.*') ? 'active' : '' }}">
                    <i class="bi bi-sticky"></i> Posts
                </a>
            @endunless

            @if($user->isCompanyAdmin())
                <div class="sv-nav-label">Administration</div>
                <a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Clients
                </a>
                <a href="{{ route('team.index') }}" class="{{ request()->routeIs('team.*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear"></i> Team
                </a>
            @endif
        @endif
    </div>

    <div class="sv-sidebar-footer">
        <div class="whoami">
            <i class="bi bi-person-circle me-1"></i>
            {{ $user->name }}<br>
            <small>{{ str_replace('_', ' ', $user->role) }}</small>
        </div>
    </div>
</nav>
