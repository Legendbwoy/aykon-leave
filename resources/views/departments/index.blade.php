@extends('layouts.master')

@section('title', 'Departments')
@section('page-title', 'Department Management')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Departments</li>
</ul>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>All Departments</h5>
                <a href="{{ route('departments.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-2"></i>Add Department
                </a>
            </div>
            <div class="card-body">
                <!-- Search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('departments.index') }}" id="filter-form">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search by name or code"
                                       value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Departments Table -->
                <div class="table-responsive rounded shadow-sm">
                    <table class="table table-hover align-middle mb-0 bg-white rounded">
                        <thead class="bg-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Employees</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $department)
                            <tr>
                                <td><code>{{ $department->code }}</code></td>
                                <td>{{ $department->name }}</td>
                                <td>{{ $department->description ?? 'N/A' }}</td>
                                <td>{{ $department->employees()->count() }}</td>
                                <td>
                                    @if($department->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('departments.show', $department) }}" class="btn btn-sm btn-info" title="View"><i class="ti ti-eye"></i></a>
                                    <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-primary" title="Edit"><i class="ti ti-edit"></i></a>
                                    @if($department->employees()->count() == 0)
                                        <form action="{{ route('departments.destroy', $department) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="ti ti-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="ti ti-inbox f-30 text-muted"></i>
                                    <p class="mb-0">No departments found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $departments->links() }}
                    </div>
                                    @if($department->is_active)
                                        <span class="badge bg-light-success">Active</span>
                                    @else
                                        <span class="badge bg-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('departments.show', $department) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ route('departments.edit', $department) }}"
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    @if($department->employees()->count() == 0)
                                        <form action="{{ route('departments.destroy', $department) }}"
                                              method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="ti ti-inbox f-30 text-muted"></i>
                                    <p class="mb-0">No departments found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $departments->withQueryString()->links() }}
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
            if (confirm('Are you sure you want to delete this department?')) {
                this.submit();
            }
        });
    });
</script>
@endpush