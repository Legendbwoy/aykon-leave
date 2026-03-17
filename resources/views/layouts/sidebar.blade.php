<!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand text-primary">
                <img src="{{ asset('assets/images/aykon-logo.png') }}" class="img-fluid logo-lg" alt="logo">
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                @auth
                <li class="pc-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

                @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                <li class="pc-item pc-caption">
                    <label>HR Management</label>
                    <i class="ti ti-users"></i>
                </li>
                
                <li class="pc-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <a href="{{ route('employees.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-user"></i></span>
                        <span class="pc-mtext">Employees</span>
                    </a>
                </li>
                
                <li class="pc-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                    <a href="{{ route('departments.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-building"></i></span>
                        <span class="pc-mtext">Departments</span>
                    </a>
                </li>
                @endif

                <li class="pc-item pc-caption">
                    <label>Attendance</label>
                    <i class="ti ti-clock"></i>
                </li>
                
                <li class="pc-item {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                    <a href="{{ route('attendance.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-calendar-check"></i></span>
                        <span class="pc-mtext">Attendance Log</span>
                    </a>
                </li>
                
                <!-- Face Registration - Commented Out
                @if(auth()->user() && auth()->user()->employee && !auth()->user()->employee->face_registered)
                <li class="pc-item {{ request()->routeIs('face.register') ? 'active' : '' }}">
                    <a href="{{ route('face.register') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-face-id"></i></span>
                        <span class="pc-mtext">Register Face</span>
                    </a>
                </li>
                @endif
                -->
                
                <!-- Face Recognition - Commented Out
                <li class="pc-item {{ request()->routeIs('face.recognize') ? 'active' : '' }}">
                    <a href="{{ route('face.recognize') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-camera"></i></span>
                        <span class="pc-mtext">Face Recognition</span>
                    </a>
                </li>
                -->
                
                <li class="pc-item {{ request()->routeIs('attendance.qr-scan') ? 'active' : '' }}">
                    <a href="{{ route('attendance.qr-scan') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-qrcode"></i></span>
                        <span class="pc-mtext">QR Code Scan</span>
                    </a>
                </li>

                @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                <li class="pc-item pc-caption">
                    <label>Reports</label>
                    <i class="ti ti-report"></i>
                </li>
                
                <li class="pc-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <a href="{{ route('reports.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-chart-bar"></i></span>
                        <span class="pc-mtext">Reports</span>
                    </a>
                </li>

                <li class="pc-item {{ request()->routeIs('qr-code.*') ? 'active' : '' }}">
                    <a href="{{ route('qr-code.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-qrcode"></i></span>
                        <span class="pc-mtext">QR Code Management</span>
                    </a>
                </li>

                @if(auth()->user() && auth()->user()->isAdmin())
                <li class="pc-item pc-caption">
                    <label>Administration</label>
                    <i class="ti ti-settings"></i>
                </li>

                <li class="pc-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-shield-check"></i></span>
                        <span class="pc-mtext">User Management</span>
                    </a>
                </li>

                <li class="pc-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-user-check"></i></span>
                        <span class="pc-mtext">Role Management</span>
                    </a>
                </li>

                <li class="pc-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                    <a href="{{ route('permissions.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-lock"></i></span>
                        <span class="pc-mtext">Permissions</span>
                    </a>
                </li>

                <li class="pc-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <a href="{{ route('settings.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-settings-2"></i></span>
                        <span class="pc-mtext">System Settings</span>
                    </a>
                </li>
                @endif
                @endif

                <li class="pc-item pc-caption">
                    <label>Profile</label>
                    <i class="ti ti-user-circle"></i>
                </li>
                
                <li class="pc-item {{ request()->routeIs('profile.show') ? 'active' : '' }}">
                    <a href="{{ route('profile.show') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-id"></i></span>
                        <span class="pc-mtext">My Profile</span>
                    </a>
                </li>
                @endauth

                @guest
                <li class="pc-item">
                    <a href="{{ route('login') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-login"></i></span>
                        <span class="pc-mtext">Login</span>
                    </a>
                </li>
                <li class="pc-item">
                    <a href="{{ route('register') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-user-plus"></i></span>
                        <span class="pc-mtext">Register</span>
                    </a>
                </li>
                @endguest
            </ul>
            
            @auth
                @if(auth()->user() && auth()->user()->isAdmin())
                <div class="card text-center">
                    <div class="card-body">
                        <img src="{{ asset('assets/images/img-navbar-card.png') }}" alt="images" class="img-fluid mb-2">
                        <h5>System Info</h5>
                        <p>QR Code Attendance System</p>
                        <p class="text-muted small">Total Employees: {{ \App\Models\Employee::count() }}</p>
                    </div>
                </div>
                @endif
            @endauth
        </div>
    </div>
</nav>
<!-- [ Sidebar Menu ] end -->