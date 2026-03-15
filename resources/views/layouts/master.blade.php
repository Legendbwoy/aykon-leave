<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title', 'Facial Attendance System') - {{ config('app.name') }}</title>
    
    <!-- [Meta] -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Facial Recognition Time Attendance System">
    <meta name="keywords" content="Facial Recognition, Attendance, Time Tracking, HR System">
    <meta name="author" content="Your Company">

    <!-- [Favicon] icon -->
    <link rel="icon" href="{{ asset('assets/images/aykon-logo-main.png') }}" type="image/x-icon">
    
    <!-- [Google Font] Family -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">
    
    <!-- [Tabler Icons] -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    
    <!-- [Feather Icons] -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    
    <!-- [Font Awesome Icons] -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    
    <!-- [Material Icons] -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
    
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    <link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}">
    
    @stack('styles')
</head>

<body data-pc-preset="preset-1" data-pc-direction="ltr" data-pc-theme="light">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    @auth
        @include('layouts.sidebar')
        @include('layouts.header')
    @endauth

    @guest
        <!-- Simple header for guest pages -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('assets/images/aykon-logo.png') }}" alt="logo" height="40">
                </a>
                <div class="ms-auto">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                </div>
            </div>
        </nav>
    @endguest

    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            @auth
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h5 class="m-b-10">@yield('page-title', 'Dashboard')</h5>
                            </div>
                            @yield('breadcrumb')
                        </div>
                    </div>
                </div>
            </div>
            @endauth
            <!-- [ breadcrumb ] end -->

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="ti ti-info-circle me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- [ Main Content ] start -->
            @yield('content')
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <!-- [ Main Content ] end -->

    @auth
        @include('layouts.footer')
    @endauth

    <!-- Required Js -->
    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/fonts/custom-font.js') }}"></script>
    <script src="{{ asset('assets/js/pcoded.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    
    <!-- Chart JS -->
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    
    <!-- Custom JS -->
    <script>
        @auth
        layout_change('light');
        change_box_container('false');
        layout_rtl_change('false');
        preset_change("preset-1");
        font_change("Public-Sans");
        @endauth
        
        // Initialize Feather Icons
        feather.replace();
    </script>
    
    @stack('scripts')
</body>
</html>