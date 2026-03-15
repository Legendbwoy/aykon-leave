@extends('layouts.master')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Admin</li>
</ul>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="row">
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-primary">
                            <i class="ti ti-users f-20"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 f-w-400 text-muted">Total Employees</h6>
                        <h4 class="mb-0">{{ $totalEmployees }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-success">
                            <i class="ti ti-building f-20"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 f-w-400 text-muted">Departments</h6>
                        <h4 class="mb-0">{{ $totalDepartments }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-info">
                            <i class="ti ti-clock f-20"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 f-w-400 text-muted">Present Today</h6>
                        <h4 class="mb-0">{{ $presentToday }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-warning">
                            <i class="ti ti-calendar-stats f-20"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 f-w-400 text-muted">Total Today</h6>
                        <h4 class="mb-0">{{ $totalAttendances }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Statistics -->
<div class="row">
    <div class="col-md-12 col-xl-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Attendance Overview</h5>
            <ul class="nav nav-pills justify-content-end mb-0" id="chart-tab-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="chart-tab-home-tab" data-bs-toggle="pill" data-bs-target="#chart-tab-home"
                        type="button" role="tab">Month</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="chart-tab-profile-tab" data-bs-toggle="pill"
                        data-bs-target="#chart-tab-profile" type="button" role="tab">Week</button>
                </li>
            </ul>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="tab-content" id="chart-tab-tabContent">
                    <div class="tab-pane" id="chart-tab-home" role="tabpanel">
                        <div id="monthly-attendance-chart"></div>
                    </div>
                    <div class="tab-pane show active" id="chart-tab-profile" role="tabpanel">
                        <div id="weekly-attendance-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-12 col-xl-4">
        <h5 class="mb-3">Today's Statistics</h5>
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Attendance Status</h6>
                    <span class="badge bg-light-primary">Today</span>
                </div>
                
                @foreach($attendanceStats as $key => $value)
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted">
                        <i class="ti ti-circle me-2 text-{{ 
                            $key == 'present' ? 'success' : 
                            ($key == 'late' ? 'warning' : 'danger') 
                        }} f-10"></i>
                        {{ ucfirst($key) }}
                    </span>
                    <span class="fw-bold">{{ $value }}</span>
                </div>
                @endforeach
                
                <hr>
                
                <div class="text-center">
                    <h3 class="mb-0">{{ round(($attendanceStats['present'] / max($totalEmployees, 1)) * 100, 1) }}%</h3>
                    <p class="text-muted mb-0">Attendance Rate</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Attendance -->
<div class="row">
    <div class="col-md-12">
        <h5 class="mb-3">Recent Attendances</h5>
        <div class="card tbl-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Work Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendances as $attendance)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($attendance->employee && $attendance->employee->profile_photo)
                                            <img src="{{ asset('storage/' . $attendance->employee->profile_photo) }}" 
                                                 alt="" class="wid-35 rounded-circle me-2">
                                        @else
                                            <div class="wid-35 rounded-circle bg-light-primary me-2 d-flex align-items-center justify-content-center">
                                                <i class="ti ti-user"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ $attendance->employee && $attendance->employee->user ? $attendance->employee->user->name : 'N/A' }}</h6>
                                            <small class="text-muted">{{ $attendance->employee ? $attendance->employee->employee_id : 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $attendance->employee && $attendance->employee->department ? $attendance->employee->department->name : 'N/A' }}</td>
                                <td>
                                    @if($attendance->check_in)
                                        {{ $attendance->check_in->format('H:i:s') }}
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->check_out)
                                        {{ $attendance->check_out->format('H:i:s') }}
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'present' => 'success',
                                            'late' => 'warning',
                                            'absent' => 'danger',
                                            'half-day' => 'info',
                                            'overtime' => 'primary'
                                        ][$attendance->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-light-{{ $statusClass }}">{{ ucfirst($attendance->status) }}</span>
                                </td>
                                <td>
                                    @if($attendance->work_hours)
                                        {{ number_format($attendance->work_hours, 2) }} hrs
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="ti ti-inbox f-30 text-muted"></i>
                                    <p class="mb-0">No attendance records found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Weekly Attendance Chart
    var weeklyOptions = {
        chart: { type: 'area', height: 350, toolbar: { show: false } },
        series: [{
            name: 'Attendance',
            data: [30, 40, 35, 50, 49, 60, 70]
        }],
        xaxis: {
            categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
        },
        colors: ['#4e73df'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.3
            }
        }
    };
    
    var weeklyChart = new ApexCharts(document.querySelector("#weekly-attendance-chart"), weeklyOptions);
    weeklyChart.render();
    
    // Monthly Attendance Chart
    var monthlyOptions = {
        chart: { type: 'bar', height: 350, toolbar: { show: false } },
        series: [{
            name: 'Attendance',
            data: [65, 59, 80, 81, 56, 55, 40, 70, 85, 90, 75, 95]
        }],
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        },
        colors: ['#1cc88a'],
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: '60%'
            }
        }
    };
    
    var monthlyChart = new ApexCharts(document.querySelector("#monthly-attendance-chart"), monthlyOptions);
    monthlyChart.render();
</script>
@endpush