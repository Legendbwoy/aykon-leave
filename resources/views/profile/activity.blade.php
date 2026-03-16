@extends('layouts.master')

@section('content')
<div class="container">
    <h1>User Activity</h1>
    <h3>{{ $user->name }}</h3>
    <div class="table-responsive rounded shadow-sm">
        <table class="table table-hover align-middle mb-0 bg-white rounded">
            <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $attendance->date }}</td>
                    <td>
                        @if($attendance->status === 'Present')
                            <span class="badge bg-success-subtle text-success">Present</span>
                        @elseif($attendance->status === 'Absent')
                            <span class="badge bg-danger-subtle text-danger">Absent</span>
                        @elseif($attendance->status === 'Late')
                            <span class="badge bg-warning-subtle text-warning">Late</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">{{ $attendance->status }}</span>
                        @endif
                    </td>
                    <td>{{ $attendance->check_in }}</td>
                    <td>{{ $attendance->check_out }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <i class="ti ti-inbox f-30 text-muted"></i>
                        <p class="mb-0">No activity found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-3">
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection
