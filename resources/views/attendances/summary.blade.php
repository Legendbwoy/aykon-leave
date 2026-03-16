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
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Department</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($departmentSummary as $department => $summary)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $department }}</td>
                    <td>{{ $summary['present'] }}</td>
                    <td>{{ $summary['absent'] }}</td>
                    <td>{{ $summary['late'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $summary->links() }}
    </div>
</div>
@endsection
