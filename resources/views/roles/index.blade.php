@extends('layouts.master')

@section('title', 'Role Management')
@section('page-title', 'Role Management')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Roles</li>
</ul>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Roles</h5>
                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-2"></i>Create Role
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Display Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->display_name }}</td>
                                <td>{{ $role->description ?? 'N/A' }}</td>
                                <td>
                                    @if($role->is_active)
                                        <span class="badge bg-light-success">Active</span>
                                    @else
                                        <span class="badge bg-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-primary">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="ti ti-inbox f-30 text-muted"></i>
                                    <p class="mb-0">No roles found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $roles->links() }}
                </div>
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
            if (confirm('Are you sure you want to delete this role?')) {
                this.submit();
            }
        });
    });
</script>
@endpush
