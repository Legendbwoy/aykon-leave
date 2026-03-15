@extends('layouts.master')

@section('title', 'Manager Dashboard')
@section('page-title', 'Manager Dashboard')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Manager</li>
</ul>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="row">
    <div class="col-md-6 col-xl-4">
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
    
    <div class="col-md-6 col-xl-4">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-success">
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
    
    <div class="col-md-6 col-xl-4">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-info">
                            <i class="ti ti-percentage f-20"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 f-w-400 text-muted">Attendance Rate</h6>
                        <h4 class="mb-0">{{ round(($presentToday / max($totalEmployees, 1)) * 100, 1) }}%</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Attendance -->
<div class="row">
    <div class="col-md-12">
        <h5 class="mb-3">Recent Attendances in Your Department</h5>
        <div class="card tbl-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Employee ID</th>
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
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $attendance->employee ? $attendance->employee->employee_id : 'N/A' }}</td>
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