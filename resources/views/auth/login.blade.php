@extends('layouts.master')

@section('title', 'Login')
@section('page-title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-xl-5">
        <div class="card">
            <div class="card-header">
                <h5>Login to Your Account</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus>
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
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Remember Me
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-login me-2"></i>Login
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('register') }}">Don't have an account? Register</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection