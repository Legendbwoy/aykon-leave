@extends('layouts.master')

@section('title', 'QR Code Management')
@section('page-title', 'QR Code Management')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">QR Code Management</li>
</ul>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Company QR Code</h5>
                <form action="{{ route('qr-code.regenerate') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="ti ti-refresh"></i> Regenerate QR Code
                    </button>
                </form>
            </div>
            <div class="card-body text-center">
                @if($qrCode)
                    <div class="mb-3">
                        {!! $qrCodeSvg ?? '' !!}
                    </div>
                    <p><strong>Token:</strong> {{ $qrCode->token }}</p>
                    <p><strong>Generated at:</strong> {{ $qrCode->created_at->format('Y-m-d H:i:s') }}</p>
                    @if($qrCode->expires_at)
                        <p><strong>Expires at:</strong> {{ $qrCode->expires_at->format('Y-m-d H:i:s') }}</p>
                    @endif
                    <a href="{{ route('qr-code.export') }}" class="btn btn-success">
                        <i class="ti ti-download"></i> Download PDF
                    </a>
                @else
                    <p class="text-muted">No QR code generated yet.</p>
                    <form action="{{ route('qr-code.regenerate') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">Generate QR Code</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
