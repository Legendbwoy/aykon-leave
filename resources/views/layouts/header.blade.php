<!-- [ Header Topbar ] start -->
<header class="pc-header">
    <div class="header-wrapper">
        <!-- [Mobile Media Block] start -->
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="dropdown pc-h-item d-inline-flex d-md-none">
                    <a class="pc-head-link dropdown-toggle arrow-none m-0" data-bs-toggle="dropdown" href="#" role="button">
                        <i class="ti ti-search"></i>
                    </a>
                    <div class="dropdown-menu pc-h-dropdown drp-search">
                        <form class="px-3">
                            <div class="form-group mb-0 d-flex align-items-center">
                                <i data-feather="search"></i>
                                <input type="search" class="form-control border-0 shadow-none" placeholder="Search...">
                            </div>
                        </form>
                    </div>
                </li>
                @auth
                <li class="pc-h-item d-none d-md-inline-flex">
                    <form class="header-search">
                        <i data-feather="search" class="icon-search"></i>
                        <input type="search" class="form-control" placeholder="Search employees...">
                    </form>
                </li>
                @endauth
            </ul>
        </div>
        <!-- [Mobile Media Block end] -->
        
        <div class="ms-auto">
            <ul class="list-unstyled">
                @auth
                <!-- Notifications Dropdown -->
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button">
                        <i class="ti ti-bell"></i>
                        @php
                            $todayCount = \App\Models\Attendance::whereDate('check_in', now()->toDateString())
                                ->whereNotNull('check_in')
                                ->count();
                            $pendingCount = \App\Models\Attendance::whereDate('check_in', now()->toDateString())
                                ->whereNull('check_out')
                                ->count();
                            $notificationCount = $pendingCount;
                        @endphp
                        @if($notificationCount > 0)
                            <span class="badge bg-danger">{{ $notificationCount }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header d-flex align-items-center justify-content-between">
                            <h5 class="m-0">Notifications</h5>
                            <span class="badge bg-light-primary">{{ $todayCount }} Today</span>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-header px-0 text-wrap header-notification-scroll position-relative" style="max-height: calc(100vh - 215px)">
                            <div class="list-group list-group-flush w-100">
                                @php
                                    $recentAttendances = \App\Models\Attendance::with('employee.user')
                                        ->whereDate('check_in', now()->toDateString())
                                        ->latest()
                                        ->take(5)
                                        ->get();
                                @endphp
                                
                                @forelse($recentAttendances as $attendance)
                                <a class="list-group-item list-group-item-action" href="{{ route('attendance.show', $attendance) }}">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            @if($attendance->employee && $attendance->employee->profile_photo)
                                                <img src="{{ asset('storage/' . $attendance->employee->profile_photo) }}" 
                                                     alt="{{ $attendance->employee->user->name ?? 'User' }}" 
                                                     class="user-avtar rounded-circle">
                                            @else
                                                <div class="user-avtar bg-light-primary rounded-circle d-flex align-items-center justify-content-center">
                                                    <i class="ti ti-user f-12"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">{{ $attendance->employee->user->name ?? 'Unknown' }}</h6>
                                                <span class="text-muted small">{{ $attendance->check_in->format('H:i') }}</span>
                                            </div>
                                            <p class="mb-0 text-muted small">
                                                @if($attendance->check_out)
                                                    <span class="text-success">Checked out</span> • {{ $attendance->work_hours ?? '0' }} hrs
                                                @else
                                                    <span class="text-primary">Checked in</span> • {{ $attendance->check_in->diffForHumans() }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </a>
                                @empty
                                <div class="text-center py-4">
                                    <i class="ti ti-bell-off f-30 text-muted"></i>
                                    <p class="text-muted mb-0 mt-2">No notifications</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                        @if($todayCount > 0)
                        <div class="dropdown-divider"></div>
                        <div class="text-center py-2">
                            <a href="{{ route('attendance.index') }}?date={{ now()->toDateString() }}" class="link-primary">
                                View All Today's Activity
                            </a>
                        </div>
                        @endif
                    </div>
                </li>
                
                <!-- User Profile Dropdown -->
                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" data-bs-auto-close="outside">
                        @if(auth()->user() && auth()->user()->employee && auth()->user()->employee->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->employee->profile_photo) }}" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="user-avtar rounded-circle">
                        @else
                            <div class="user-avtar bg-light-primary rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ti ti-user f-16"></i>
                            </div>
                        @endif
                        <span class="d-none d-lg-inline-block">{{ auth()->user()->name }}</span>
                    </a>
                    
                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                        <!-- User Info Header -->
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    @if(auth()->user()->employee && auth()->user()->employee->profile_photo)
                                        <img src="{{ asset('storage/' . auth()->user()->employee->profile_photo) }}" 
                                             alt="{{ auth()->user()->name }}" 
                                             class="user-avtar wid-40 rounded-circle">
                                    @else
                                        <div class="user-avtar wid-40 bg-light-primary rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="ti ti-user f-20"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                                    <span class="text-muted small">{{ ucfirst(auth()->user()->role) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dropdown-divider"></div>
                        
                        <!-- Profile Tabs -->
                        <ul class="nav nav-tabs nav-fill px-3" id="profileTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" 
                                        data-bs-target="#profile" type="button" role="tab">
                                    <i class="ti ti-user me-1"></i>Profile
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" 
                                        data-bs-target="#settings" type="button" role="tab">
                                    <i class="ti ti-settings me-1"></i>Settings
                                </button>
                            </li>
                        </ul>
                        
                        <!-- Tab Content -->
                        <div class="tab-content px-3 py-2" id="profileTabContent">
                            <!-- Profile Tab -->
                            <div class="tab-pane fade show active" id="profile" role="tabpanel">
                                <a href="{{ route('profile.show') }}" class="dropdown-item">
                                    <i class="ti ti-user"></i>
                                    <span>View Profile</span>
                                </a>
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                    <i class="ti ti-edit-circle"></i>
                                    <span>Edit Profile</span>
                                </a>
                                <a href="{{ route('profile.password') }}" class="dropdown-item">
                                    <i class="ti ti-lock"></i>
                                    <span>Change Password</span>
                                </a>
                                <a href="{{ route('profile.activity') }}" class="dropdown-item">
                                    <i class="ti ti-activity"></i>
                                    <span>My Activity</span>
                                </a>
                                @if(auth()->user()->employee)
                                <a href="{{ route('attendance.employee', auth()->user()->employee->id) }}" class="dropdown-item">
                                    <i class="ti ti-calendar-check"></i>
                                    <span>My Attendance</span>
                                </a>
                                @endif
                            </div>
                            
                            <!-- Settings Tab -->
                            <div class="tab-pane fade" id="settings" role="tabpanel">
                                <a href="{{ route('profile.edit') }}#notifications" class="dropdown-item">
                                    <i class="ti ti-bell"></i>
                                    <span>Notifications</span>
                                </a>
                                <a href="#" class="dropdown-item">
                                    <i class="ti ti-shield"></i>
                                    <span>Privacy & Security</span>
                                </a>
                                <a href="#" class="dropdown-item">
                                    <i class="ti ti-help"></i>
                                    <span>Help & Support</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="ti ti-power"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Quick Stats Footer -->
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-footer text-center py-2">
                            <small class="text-muted">
                                <i class="ti ti-clock me-1"></i>
                                Last login: {{ auth()->user()->updated_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                </li>
                @endauth
                
                @guest
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">
                        <i class="ti ti-login me-1"></i>Login
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('register') }}" class="btn btn-primary">
                        <i class="ti ti-user-plus me-1"></i>Register
                    </a>
                </li>
                @endguest
            </ul>
        </div>
    </div>
</header>
<!-- [ Header ] end -->