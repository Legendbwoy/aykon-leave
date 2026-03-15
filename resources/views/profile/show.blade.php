@extends('layouts.master')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Profile</li>
</ul>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="card">
            <div class="card-body text-center">
                <div class="profile-image-container mb-3">
                    @if($user->employee && $user->employee->profile_photo)
                        <img src="{{ asset('storage/' . $user->employee->profile_photo) }}" 
                             alt="Profile Photo" 
                             class="img-fluid rounded-circle" 
                             style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="avtar avtar-xxl bg-light-primary rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                             style="width: 150px; height: 150px;">
                            <i class="ti ti-user f-50"></i>
                        </div>
                    @endif
                </div>
                
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ ucfirst($user->role) }}</p>
                
                @if($user->employee)
                    <p class="mb-1"><strong>Employee ID:</strong> {{ $user->employee->employee_id }}</p>
                    <p class="mb-1"><strong>Department:</strong> {{ $user->employee->department->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Position:</strong> {{ $user->employee->position ?? 'N/A' }}</p>
                @endif
                
                <div class="mt-3">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="ti ti-edit me-2"></i>Edit Profile
                    </a>
                    <a href="{{ route('profile.password') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-lock me-2"></i>Change Password
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Account Status Card -->
        <div class="card mt-3">
            <div class="card-header">
                <h5>Account Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Account Status:</span>
                    @if($user->is_active)
                        <span class="badge bg-light-success">Active</span>
                    @else
                        <span class="badge bg-light-danger">Inactive</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Email Verified:</span>
                    @if($user->email_verified_at)
                        <span class="badge bg-light-success">Verified</span>
                    @else
                        <span class="badge bg-light-warning">Unverified</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Face Registered:</span>
                    @if($user->employee && $user->employee->face_registered)
                        <span class="badge bg-light-success">Yes</span>
                    @else
                        <span class="badge bg-light-warning">No</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Member Since:</span>
                    <span>{{ $user->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Personal Information Card -->
        <div class="card">
            <div class="card-header">
                <h5>Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Full Name</label>
                        <p class="form-control-static fw-bold">{{ $user->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Email Address</label>
                        <p class="form-control-static">{{ $user->email }}</p>
                    </div>
                    @if($user->employee)
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Phone Number</label>
                        <p class="form-control-static">{{ $user->employee->phone ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Address</label>
                        <p class="form-control-static">{{ $user->employee->address ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Hire Date</label>
                        <p class="form-control-static">
                            {{ $user->employee->hire_date ? $user->employee->hire_date->format('d M Y') : 'Not provided' }}
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Salary</label>
                        <p class="form-control-static">
                            @if($user->employee->salary)
                                ${{ number_format($user->employee->salary, 2) }}
                            @else
                                Not provided
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Recent Activity Card -->
        @if(isset($attendances) && count($attendances) > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h5>Recent Attendance Activity</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->check_in ? $attendance->check_in->format('d M Y') : 'N/A' }}</td>
                                <td>{{ $attendance->check_in ? $attendance->check_in->format('H:i:s') : '---' }}</td>
                                <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i:s') : '---' }}</td>
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
                                    <span class="badge bg-light-{{ $statusClass }}">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>
                                <td>{{ $attendance->work_hours ? number_format($attendance->work_hours, 2) : '0.00' }} hrs</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('attendance.index') }}" class="btn btn-link">View All Attendance</a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection