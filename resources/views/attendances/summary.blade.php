@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Attendance Summary</h1>
    <form method="GET" action="{{ route('attendances.summary') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="startDate" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="startDate" name="startDate" value="{{ $startDate }}">
        </div>
        <div class="col-md-3">
            <label for="endDate" class="form-label">End Date</label>
            <input type="date" class="form-control" id="endDate" name="endDate" value="{{ $endDate }}">
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    <div class="table-responsive rounded shadow-sm">
        <table class="table table-hover align-middle mb-0 bg-white rounded">
            <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Department</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($departmentSummary as $department => $summary)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $department }}</td>
                    <td><span class="badge bg-success-subtle text-success">{{ $summary['present'] }}</span></td>
                    <td><span class="badge bg-danger-subtle text-danger">{{ $summary['absent'] }}</span></td>
                    <td><span class="badge bg-warning-subtle text-warning">{{ $summary['late'] }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <i class="ti ti-inbox f-30 text-muted"></i>
                        <p class="mb-0">No summary data found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-3">
            {{ $summary->links() }}
        </div>
    </div>
</div>
@endsection
