@extends('layouts.master')

@section('title', 'Change Password')
@section('page-title', 'Change Password')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Profile</a></li>
    <li class="breadcrumb-item" aria-current="page">Change Password</li>
</ul>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" 
                               class="form-control @error('current_password') is-invalid @enderror" 
                               required>
                        @error('current_password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" 
                               class="form-control @error('new_password') is-invalid @enderror" 
                               required>
                        <small class="text-muted">Minimum 8 characters with at least one uppercase, lowercase, number and special character</small>
                        @error('new_password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_password_confirmation" 
                               class="form-control" required>
                    </div>
                    
                    <div class="text-end">
                        <a href="{{ route('profile.show') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-2"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-body">
                <h6>Password Requirements:</h6>
                <ul class="text-muted">
                    <li>Minimum 8 characters long</li>
                    <li>At least one uppercase letter</li>
                    <li>At least one lowercase letter</li>
                    <li>At least one number</li>
                    <li>At least one special character (!@#$%^&*)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection