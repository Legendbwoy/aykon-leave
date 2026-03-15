@extends('layouts.master')

@section('title', 'Register')
@section('page-title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-xl-5">
        <div class="card">
            <div class="card-header">
                <h5>Create New Account</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               name="password" 
                               required>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" 
                               class="form-control" 
                               name="password_confirmation" 
                               required>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-user-plus me-2"></i>Register
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}">Already have an account? Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection