@extends('layouts.master')

@section('title', 'Attendance Records')
@section('page-title', 'Attendance Management')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Attendance</li>
</ul>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Attendance Records</h5>
                @can('create', App\Models\Attendance::class)
                <a href="{{ route('attendance.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-2"></i>Add Record
                </a>
                @endcan
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form method="GET" action="{{ route('attendance.index') }}" id="filter-form" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" 
                                   value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" 
                                   value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                                <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                                <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="half-day" {{ request('status') == 'half-day' ? 'selected' : '' }}>Half Day</option>
                                <option value="overtime" {{ request('status') == 'overtime' ? 'selected' : '' }}>Overtime</option>
                            </select>
                        </div>
                        @if(auth()->user()->isAdmin())
                        <div class="col-md-2">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">All</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" 
                                    {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-2">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Attendance Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Employee ID</th>
                                <th>Department</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Work Hours</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $attendance)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($attendance->employee && $attendance->employee->profile_photo)
                                            <img src="{{ asset('storage/' . $attendance->employee->profile_photo) }}" 
                                                 alt="" class="wid-35 rounded-circle me-2">
                                        @else
                                            <div class="wid-35 rounded-circle bg-light-primary me-2 d-flex align-items-center justify-content-center">
                                                <i class="ti ti-user"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ $attendance->employee->user->name ?? 'N/A' }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $attendance->employee->employee_id ?? 'N/A' }}</td>
                                <td>{{ $attendance->employee->department->name ?? 'N/A' }}</td>
                                <td>
                                    @if($attendance->check_in)
                                        {{ $attendance->check_in->format('d M Y H:i:s') }}
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->check_out)
                                        {{ $attendance->check_out->format('d M Y H:i:s') }}
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
                                    <span class="badge bg-light-{{ $statusClass }}">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($attendance->work_hours)
                                        {{ number_format($attendance->work_hours, 2) }} hrs
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('attendance.show', $attendance) }}" 
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @can('update', $attendance)
                                    <a href="{{ route('attendance.edit', $attendance) }}" 
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete', $attendance)
                                    <form action="{{ route('attendance.destroy', $attendance) }}" 
                                          method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="ti ti-inbox f-30 text-muted"></i>
                                    <p class="mb-0">No attendance records found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $attendances->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit filter when select changes
    document.querySelectorAll('select[name="status"], select[name="department_id"]').forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    });

    // Confirm delete
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this attendance record?')) {
                this.submit();
            }
        });
    });
</script>
@endpush