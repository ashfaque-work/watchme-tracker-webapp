@php
    $name = Auth::user()->name;
    $words = explode(' ', $name);
    $formattedName = '';

    foreach ($words as $word) {
        $formattedName .= substr($word, 0, 1);
    }
@endphp
<div class="topbar">
    <!-- Navbar -->
    <nav class="navbar-custom">
        <ul class="list-unstyled topbar-nav float-end mb-0">
            <li class="dropdown">
                <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-bs-toggle="dropdown"
                    href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <div class="avatar-box thumb-sm align-self-center me-2">
                        <span class="avatar-title bg-soft-purple rounded-circle">{{ $formattedName }}</span>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('profile.edit') }}"><i data-feather="user"
                            class="align-self-center icon-xs icon-dual me-1"></i> Profile</a>
                    <div class="dropdown-divider mb-0"></div>
                    @role('super-admin|admin')
                    <a class="dropdown-item" href="{{ route('admin.settings') }}"><i data-feather="settings"
                            class="align-self-center icon-xs icon-dual me-1"></i> Settings</a>
                    <div class="dropdown-divider mb-0"></div>
                    @endrole
                    {{-- <a class="dropdown-item" href="#"><i data-feather="power" class="align-self-center icon-xs icon-dual me-1"></i> Logout</a> --}}
                    <a class="dropdown-item" href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i data-feather="power" class="align-self-center icon-xs icon-dual me-1"></i> Logout
                    </a>

                    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                        @csrf
                    </form>

                </div>
            </li>
        </ul><!--end topbar-nav-->

        <ul class="list-unstyled topbar-nav mb-0">
            <li>
                <button class="nav-link button-menu-mobile">
                    <i data-feather="menu" class="align-self-center topbar-icon"></i>
                </button>
            </li>
        </ul>
    </nav>
    <!-- end navbar-->
</div>
