@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Permissions</h1>
    <a href="{{ route('permissions.create') }}" class="btn btn-primary mb-3">Add Permission</a>
    <div class="table-responsive rounded shadow-sm">
        <table class="table table-hover align-middle mb-0 bg-white rounded">
            <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Guard</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($permissions as $permission)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $permission->name }}</td>
                    <td>{{ $permission->guard_name }}</td>
                    <td>
                        <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-sm btn-warning"><i class="ti ti-edit"></i></a>
                        <form action="{{ route('permissions.destroy', $permission) }}" method="POST" class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="ti ti-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <i class="ti ti-inbox f-30 text-muted"></i>
                        <p class="mb-0">No permissions found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-3">
            {{ $permissions->links() }}
        </div>
    </div>
</div>
@endsection
