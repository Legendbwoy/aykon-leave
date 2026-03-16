@extends('layouts.app')

@section('title', 'QR Code - ' . $employee->user->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">QR Code for {{ $employee->user->name }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center">
                                <h5>Employee Details</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $employee->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $employee->user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Employee ID</th>
                                        <td>{{ $employee->employee_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Department</th>
                                        <td>{{ $employee->department->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Position</th>
                                        <td>{{ $employee->position }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h5>QR Code</h5>
                                <div class="mb-3">
                                    {!! $qrCode !!}
                                </div>
                                <p class="text-muted">Scan this QR code to mark attendance</p>

                                <div class="btn-group">
                                    <form action="{{ route('employees.regenerate-qr', $employee) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-warning">
                                            <i class="ti ti-refresh"></i> Regenerate QR Code
                                        </button>
                                    </form>
                                    <a href="{{ route('employees.export-qr-pdf', $employee) }}" class="btn btn-success">
                                        <i class="ti ti-download"></i> Export as PDF
                                    </a>
                                    <button onclick="window.print()" class="btn btn-info">
                                        <i class="ti ti-printer"></i> Print
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .btn-group, .card-header {
        display: none !important;
    }
    .card-body {
        border: none !important;
    }
}
</style>
@endpush