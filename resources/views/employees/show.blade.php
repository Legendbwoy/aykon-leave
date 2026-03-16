@extends('layouts.master')

@section('title', 'Employee Details - ' . $employee->user->name)
@section('page-title', 'Employee Details')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ $employee->user->name }}</li>
</ul>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Profile</h5>
            </div>
            <div class="card-body text-center">
                @if($employee->profile_photo)
                    <img src="{{ asset('storage/' . $employee->profile_photo) }}"
                         alt="Profile Photo" class="img-fluid rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                @else
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px;">
                        <i class="ti ti-user f-30 text-muted"></i>
                    </div>
                @endif
                <h5>{{ $employee->user->name }}</h5>
                <p class="text-muted">{{ $employee->position }}</p>
                <span class="badge bg-{{ $employee->user->is_active ? 'success' : 'danger' }}">
                    {{ $employee->user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-2"></i>Edit Employee
                    </a>
                    <a href="{{ route('attendance.employee', $employee) }}" class="btn btn-secondary">
                        <i class="ti ti-calendar me-2"></i>View Attendance
                    </a>
                    <form action="{{ route('employees.toggle-status', $employee) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="ti ti-{{ $employee->user->is_active ? 'lock' : 'unlock' }} me-2"></i>
                            {{ $employee->user->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Employee Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Employee ID</label>
                            <p>{{ $employee->employee_id }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <p>{{ $employee->user->email }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Department</label>
                            <p>{{ $employee->department->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Position</label>
                            <p>{{ $employee->position }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone</label>
                            <p>{{ $employee->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hire Date</label>
                            <p>{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Salary</label>
                            <p>{{ $employee->salary ? '$' . number_format($employee->salary, 2) : 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Face Registered</label>
                            <p>
                                <span class="badge bg-{{ $employee->face_registered ? 'success' : 'warning' }}">
                                    {{ $employee->face_registered ? 'Yes' : 'No' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Address</label>
                    <p>{{ $employee->address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Recent Attendance</h5>
            </div>
            <div class="card-body">
                @forelse($employee->attendances()->latest()->take(5)->get() as $attendance)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <strong>{{ $attendance->date->format('M d, Y') }}</strong>
                            <br>
                            <small class="text-muted">
                                Check In: {{ $attendance->check_in ? $attendance->check_in->format('H:i') : 'N/A' }}
                                @if($attendance->check_out)
                                    | Check Out: {{ $attendance->check_out->format('H:i') }}
                                @endif
                            </small>
                        </div>
                        <span class="badge bg-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : 'danger') }}">
                            {{ ucfirst($attendance->status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-muted">No attendance records found.</p>
                @endforelse

                <div class="mt-3">
                    <a href="{{ route('attendance.employee', $employee) }}" class="btn btn-sm btn-outline-primary">
                        View All Attendance
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection