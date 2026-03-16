@extends('layouts.master')

@section('title', 'Department Details')
@section('page-title', 'Department Information')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ $department->name }}</li>
</ul>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Department Details</h5>
                <div>
                    <a href="{{ route('departments.edit', $department) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-edit me-1"></i>Edit
                    </a>
                    @if($department->employees()->count() == 0)
                        <form action="{{ route('departments.destroy', $department) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this department?')">
                                <i class="ti ti-trash me-1"></i>Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Department Code</label>
                            <p class="mb-0"><code>{{ $department->code }}</code></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Department Name</label>
                            <p class="mb-0">{{ $department->name }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <p class="mb-0">{{ $department->description ?? 'No description provided' }}</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="mb-0">
                                @if($department->is_active)
                                    <span class="badge bg-light-success">Active</span>
                                @else
                                    <span class="badge bg-light-danger">Inactive</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Total Employees</label>
                            <p class="mb-0">{{ $department->employees()->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Created At</label>
                    <p class="mb-0">{{ $department->created_at ? $department->created_at->format('M d, Y H:i') : 'N/A' }}</p>
                </div>

                @if($department->updated_at != $department->created_at)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Last Updated</label>
                        <p class="mb-0">{{ $department->updated_at ? $department->updated_at->format('M d, Y H:i') : 'N/A' }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6>Employees in this Department</h6>
            </div>
            <div class="card-body">
                @if($department->employees->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($department->employees as $employee)
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    @if($employee->profile_photo)
                                        <img src="{{ asset('storage/' . $employee->profile_photo) }}" alt="Profile" class="avatar-img rounded-circle">
                                    @else
                                        <div class="avatar-initial bg-primary rounded-circle">{{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}</div>
                                    @endif
                                </div>
                                <div class="flex-fill">
                                    <h6 class="mb-0">{{ $employee->first_name }} {{ $employee->last_name }}</h6>
                                    <small class="text-muted">{{ $employee->employee_id }}</small>
                                </div>
                                <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ti ti-users f-30 text-muted"></i>
                        <p class="mb-0 text-muted">No employees assigned</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <a href="{{ route('departments.index') }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-2"></i>Back to Departments
        </a>
    </div>
</div>
@endsection