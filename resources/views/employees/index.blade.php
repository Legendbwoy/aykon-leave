@extends('layouts.master')

@section('title', 'Employees')
@section('page-title', 'Employee Management')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Employees</li>
</ul>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>All Employees</h5>
                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-2"></i>Add Employee
                </a>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('employees.index') }}" id="filter-form">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by name, email, or ID" 
                                       value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <select name="department_id" form="filter-form" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" 
                                    {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Employees Table -->
                <div class="table-responsive rounded shadow-sm">
                    <table class="table table-hover align-middle mb-0 bg-white rounded">
                        <thead class="bg-light">
                            <tr>
                                <th>Employee</th>
                                <th>Employee ID</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Phone</th>
                                <th>Face Registered</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($employee->profile_photo)
                                            <img src="{{ asset('storage/' . $employee->profile_photo) }}" alt="" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-light-primary me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="ti ti-user"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ $employee->user->name }}</h6>
                                            <small class="text-muted">{{ $employee->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><code>{{ $employee->employee_id }}</code></td>
                                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                <td>{{ $employee->position }}</td>
                                <td>{{ $employee->phone ?? 'N/A' }}</td>
                                <td>
                                    @if($employee->face_registered)
                                        <span class="badge bg-success-subtle text-success">Registered</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">Not Registered</span>
                                    @endif
                                </td>
                                <td>
                                    @if($employee->user->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-info" title="View"><i class="ti ti-eye"></i></a>
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-primary" title="Edit"><i class="ti ti-edit"></i></a>
                                    <a href="{{ route('employees.qr-code', $employee) }}" class="btn btn-sm btn-secondary" title="QR Code"><i class="ti ti-qrcode"></i></a>
                                    <form action="{{ route('employees.toggle-status', $employee) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning" title="{{ $employee->user->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="ti ti-{{ $employee->user->is_active ? 'lock' : 'unlock' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="ti ti-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="ti ti-inbox f-30 text-muted"></i>
                                    <p class="mb-0">No employees found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $employees->links() }}
                    </div>
                                <th>Face Registered</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($employee->profile_photo)
                                            <img src="{{ asset('storage/' . $employee->profile_photo) }}" 
                                                 alt="" class="wid-35 rounded-circle me-2">
                                        @else
                                            <div class="wid-35 rounded-circle bg-light-primary me-2 d-flex align-items-center justify-content-center">
                                                <i class="ti ti-user"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ $employee->user->name }}</h6>
                                            <small class="text-muted">{{ $employee->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $employee->employee_id }}</td>
                                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                <td>{{ $employee->position }}</td>
                                <td>{{ $employee->phone ?? 'N/A' }}</td>
                                <td>
                                    @if($employee->face_registered)
                                        <span class="badge bg-light-success">Registered</span>
                                    @else
                                        <span class="badge bg-light-warning">Not Registered</span>
                                    @endif
                                </td>
                                <td>
                                    @if($employee->user->is_active)
                                        <span class="badge bg-light-success">Active</span>
                                    @else
                                        <span class="badge bg-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('employees.show', $employee) }}" 
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $employee) }}" 
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <a href="{{ route('employees.qr-code', $employee) }}" 
                                       class="btn btn-sm btn-secondary" title="QR Code">
                                        <i class="ti ti-qrcode"></i>
                                    </a>
                                    <form action="{{ route('employees.toggle-status', $employee) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning" 
                                                title="{{ $employee->user->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="ti ti-{{ $employee->user->is_active ? 'lock' : 'unlock' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('employees.destroy', $employee) }}" 
                                          method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="ti ti-inbox f-30 text-muted"></i>
                                    <p class="mb-0">No employees found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-3">
                    {{ $employees->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit filter when department changes
    document.querySelector('select[name="department_id"]').addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    
    // Confirm delete
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this employee?')) {
                this.submit();
            }
        });
    });
</script>
@endpush