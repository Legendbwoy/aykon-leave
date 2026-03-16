@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Edit Attendance</h1>
    <form action="{{ route('attendances.update', $attendance) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="employee" class="form-label">Employee</label>
            <input type="text" class="form-control" id="employee" value="{{ $attendance->employee->name }}" readonly>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" value="{{ $attendance->date }}" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status">
                <option value="Present" {{ $attendance->status == 'Present' ? 'selected' : '' }}>Present</option>
                <option value="Absent" {{ $attendance->status == 'Absent' ? 'selected' : '' }}>Absent</option>
                <option value="Late" {{ $attendance->status == 'Late' ? 'selected' : '' }}>Late</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="check_in" class="form-label">Check In</label>
            <input type="time" class="form-control" id="check_in" name="check_in" value="{{ $attendance->check_in }}">
        </div>
        <div class="mb-3">
            <label for="check_out" class="form-label">Check Out</label>
            <input type="time" class="form-control" id="check_out" name="check_out" value="{{ $attendance->check_out }}">
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('attendances.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
