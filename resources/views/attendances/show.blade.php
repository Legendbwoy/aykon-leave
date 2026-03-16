@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Show Attendance</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Employee: {{ $attendance->employee->name }}</h5>
            <p class="card-text">Date: {{ $attendance->date }}</p>
            <p class="card-text">Status: {{ $attendance->status }}</p>
            <p class="card-text">Check In: {{ $attendance->check_in }}</p>
            <p class="card-text">Check Out: {{ $attendance->check_out }}</p>
        </div>
    </div>
    <a href="{{ route('attendance.index') }}" class="btn btn-secondary mt-3">Back to List</a>
</div>
@endsection
