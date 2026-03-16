@extends('layouts.master')

@section('title', 'Employee Dashboard')
@section('page-title', 'My Dashboard')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Employee</li>
</ul>
@endsection

@section('content')
<!-- Today's Status Card -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Today's Attendance</h5>
            </div>
            <div class="card-body">
                @if($todayAttendance)
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="avtar avtar-l bg-light-{{ $todayAttendance->check_out ? 'success' : 'primary' }} rounded-circle mb-3">
                                    <i class="ti ti-{{ $todayAttendance->check_out ? 'check-circle' : 'clock' }} f-30"></i>
                                </div>
                                <h5>{{ $todayAttendance->check_out ? 'Completed' : 'Checked In' }}</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Check In Time</h6>
                            <h4>
                                @if($todayAttendance->check_in)
                                    {{ $todayAttendance->check_in->format('H:i:s') }}
                                @else
                                    <span class="text-muted">Not checked in</span>
                                @endif
                            </h4>
                            <p class="text-muted">
                                @if($todayAttendance->check_in)
                                    {{ $todayAttendance->check_in->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Check Out Time</h6>
                            <h4>
                                @if($todayAttendance->check_out)
                                    {{ $todayAttendance->check_out->format('H:i:s') }}
                                @else
                                    <span class="text-muted">Not checked out yet</span>
                                @endif
                            </h4>
                            <p class="text-muted">
                                @if($todayAttendance->check_out)
                                    {{ $todayAttendance->check_out->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if(!$todayAttendance->check_out)
                        <div class="text-center mt-3">
                            <a href="{{ route('attendance.qr-scan') }}" class="btn btn-primary me-2">
                                <i class="ti ti-qrcode me-2"></i>Check Out via QR Scan
                            </a>
                            <a href="{{ route('face.recognize') }}" class="btn btn-secondary">
                                <i class="ti ti-camera me-2"></i>Check Out via Face Recognition
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <div class="avtar avtar-l bg-light-warning rounded-circle mb-3 mx-auto">
                            <i class="ti ti-alert-circle f-30"></i>
                        </div>
                        <h5>You haven't checked in today</h5>
                        <p class="text-muted mb-3">Choose a method below to mark your attendance</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('attendance.qr-scan') }}" class="btn btn-primary">
                                <i class="ti ti-qrcode me-2"></i>Check In via QR Scan
                            </a>
                            <a href="{{ route('face.recognize') }}" class="btn btn-secondary">
                                <i class="ti ti-camera me-2"></i>Check In via Face Recognition
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Monthly Statistics -->
<div class="row">
    <div class="col-md-4">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-success">
                            <i class="ti ti-calendar-check f-20"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 f-w-400 text-muted">Total Present</h6>
                        <h4 class="mb-0">{{ $monthlyStats['total'] }}</h4>
                        <small class="text-muted">This month</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-warning">
                            <i class="ti ti-clock f-20"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 f-w-400 text-muted">Late Arrivals</h6>
                        <h4 class="mb-0">{{ $monthlyStats['late'] }}</h4>
                        <small class="text-muted">This month</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s bg-light-danger">
                            <i class="ti ti-calendar-off f-20"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 f-w-400 text-muted">Absences</h6>
                        <h4 class="mb-0">{{ $monthlyStats['absent'] }}</h4>
                        <small class="text-muted">This month</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Attendance History -->
<div class="row">
    <div class="col-md-12">
        <h5 class="mb-3">Recent Attendance History</h5>
        <div class="card tbl-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
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
                                    @if($attendance->check_in)
                                        {{ $attendance->check_in->format('d M Y') }}
                                    @else
                                        <span class="text-muted">{{ $attendance->date ?? 'N/A' }}</span>
                                    @endif
                                </td>
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
                                <td colspan="5" class="text-center py-4">
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