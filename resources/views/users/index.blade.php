@extends('layouts.master')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Users</li>
</ul>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>All Users</h5>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-2"></i>Add User
                </a>
            </div>
            <div class="card-body">
                <!-- Search -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('users.index') }}" id="filter-form">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search by name or email"
                                       value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <select name="role" form="filter-form" class="form-select">
                            <option value="">All Roles</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="employee" {{ request('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                        </select>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Employee</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="wid-35 rounded-circle bg-light-primary me-2 d-flex align-items-center justify-content-center">
                                            <i class="ti ti-user"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'manager' ? 'warning' : 'info') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-light-success">Active</span>
                                    @else
                                        <span class="badge bg-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->employee)
                                        <a href="{{ route('employees.show', $user->employee) }}">{{ $user->employee->employee_id }}</a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('users.toggle-status', $user) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning"
                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="ti ti-{{ $user->is_active ? 'lock' : 'unlock' }}"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="ti ti-inbox f-30 text-muted"></i>
                                    <p class="mb-0">No users found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit filter when role changes
    document.querySelector('select[name="role"]').addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
</script>
@endpush