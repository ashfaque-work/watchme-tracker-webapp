<div class="left-sidenav">
    <div class="brand">
        <a href="{{ route('dashboard') }}" class="logo">
            <span>
                <img src="{{ asset('images/watchme-logo-white.png') }}" alt="logo-small" class="logo-sm">
            </span>
        </a>
    </div>
    <div class="menu-content h-100 position-relative" data-simplebar>
        <ul class="metismenu left-sidenav-menu">
            <li>
                <a class="nav-link" href="{{ URL::to('/dashboard') }}">
                    <i class="fas fa-home"></i><span>Dashboard</span>
                </a>
            </li>
            @role('super-admin|admin|manager|hr')
                <li>
                    <a href="javascript: void(0);" class="text-decoration-none">
                        <i data-feather="users" class="align-self-center menu-icon"></i><span>User Management</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        @role('super-admin|admin|hr')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.user-list') }}"><i class="fas fa-users"></i>Users</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('shift.create') }}"><i class="fas fa-plus"></i>Create
                                    Shift</a>
                            </li>
                        @endrole

                        @role('manager')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('manager.user-list') }}"><i class="fas fa-users"></i>Team
                                    Members</a>
                            </li>
                        @endrole
                    </ul>
                </li>
                <li>
                    <a href="javascript: void(0);" class="text-decoration-none">
                        <i data-feather="server" class="align-self-center menu-icon"></i><span>Monitoring</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        @role('hr')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('activity') }}">
                                <i class="fas fa-users-cog"></i>My Logs
                            </a>
                        </li>
                        @endrole
                        @role('super-admin|admin|hr')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.getUserLog') }}">
                                    <i class="fas fa-users-cog"></i>User Logs
                                </a>
                            </li>
                        @endrole
                        @role('manager')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('activity') }}">
                                    <i class="fas fa-users-cog"></i>My Logs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('manager.getUserLog') }}">
                                    <i class="fas fa-users-cog"></i>Team Logs
                                </a>
                            </li>
                        @endrole
                    </ul>
                </li>
                <li>
                    <a href="javascript: void(0);" class="text-decoration-none">
                        <i data-feather="activity" class="align-self-center menu-icon"></i><span>Reporting</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        @role('super-admin|admin|hr')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.manualEntries')}}">
                                    <i class="fas fa-users-cog"></i>Manual Entries
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.timesheet') }}">
                                    <i class="fas fa-users-cog"></i>Timesheet
                                </a>
                            </li>                        
                        @endrole
                    </ul>
                </li>
            @else
                <li>
                    <a class="nav-link" href="{{ route('activity') }}">
                        <i class="fas fa-users-cog"></i>Activity
                    </a>
                </li>
            @endrole
        </ul>
        <div class="update-msg text-center download-software">
            <a href="javascript: void(0);" class="float-end close-btn text-muted" data-dismiss="update-msg" aria-label="Close" aria-hidden="true">
            </a>
            <h5 class="mt-3">Download WatchMe in your system</h5>
            {{-- <p class="mb-3">We Design and Develop Clean and High Quality Web Applications</p> --}}
            <a href="https://server.ibsofts.com/watchme/downloads/setup-WatchMe.exe"><i class="fab fa-windows fa-lg me-3 text-primary"></i></a>
            <a href="https://server.ibsofts.com/watchme/downloads/WatchMe.pkg"><i class="fab fa-apple fa-lg text-light"></i></a>
        </div>
    </div>
</div>
