@extends('layouts.master')

@section('title', 'Edit Employee - ' . $employee->user->name)
@section('page-title', 'Edit Employee')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employees.show', $employee) }}">{{ $employee->user->name }}</a></li>
    <li class="breadcrumb-item" aria-current="page">Edit</li>
</ul>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Edit Employee: {{ $employee->user->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $employee->user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email', $employee->user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control @error('employee_id') is-invalid @enderror"
                                       id="employee_id" name="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" required>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select @error('department_id') is-invalid @enderror"
                                        id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control @error('position') is-invalid @enderror"
                                       id="position" name="position" value="{{ old('position', $employee->position) }}" required>
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone', $employee->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hire_date" class="form-label">Hire Date</label>
                                <input type="date" class="form-control @error('hire_date') is-invalid @enderror"
                                       id="hire_date" name="hire_date" value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}" required>
                                @error('hire_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="salary" class="form-label">Salary</label>
                                <input type="number" step="0.01" class="form-control @error('salary') is-invalid @enderror"
                                       id="salary" name="salary" value="{{ old('salary', $employee->salary) }}">
                                @error('salary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address" name="address" rows="3">{{ old('address', $employee->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="profile_photo" class="form-label">Profile Photo</label>
                        <input type="file" class="form-control @error('profile_photo') is-invalid @enderror"
                               id="profile_photo" name="profile_photo" accept="image/*">
                        @error('profile_photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($employee->profile_photo)
                            <div class="mt-2">
                                <small class="text-muted">Current photo will be replaced if you upload a new one.</small>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-2"></i>Update Employee
                        </button>
                        <a href="{{ route('employees.show', $employee) }}" class="btn btn-info">
                            <i class="ti ti-eye me-2"></i>View Employee
                        </a>
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-2"></i>Back to Employees
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Current Profile Photo</h5>
            </div>
            <div class="card-body text-center">
                @if($employee->profile_photo)
                    <img src="{{ asset('storage/' . $employee->profile_photo) }}"
                         alt="Profile Photo" class="img-fluid rounded mb-3" style="max-width: 200px;">
                    <form action="{{ route('profile.photo.remove', $employee->user) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="ti ti-trash me-1"></i>Remove Photo
                        </button>
                    </form>
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 150px;">
                        <i class="ti ti-user f-30 text-muted"></i>
                    </div>
                    <p class="text-muted">No profile photo uploaded</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Danger Zone</h5>
            </div>
            <div class="card-body">
                <p class="text-danger small">Deleting an employee will permanently remove all associated data including attendance records.</p>
                <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="ti ti-trash me-2"></i>Delete Employee
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
                this.submit();
            }
        });
    });
</script>
@endpush