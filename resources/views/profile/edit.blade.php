@extends('layouts.master')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Profile</a></li>
    <li class="breadcrumb-item" aria-current="page">Edit</li>
</ul>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>Edit Profile Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="text-center mb-4">
                        <div class="profile-image-upload">
                            @if($user->employee && $user->employee->profile_photo)
                                <img src="{{ asset('storage/' . $user->employee->profile_photo) }}" 
                                     id="profile-preview"
                                     alt="Profile Photo" 
                                     class="img-fluid rounded-circle" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div id="profile-preview-container">
                                    <div class="avtar avtar-xxl bg-light-primary rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                                         style="width: 150px; height: 150px;">
                                        <i class="ti ti-user f-50"></i>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="mt-3">
                                <label for="profile_photo" class="btn btn-outline-primary">
                                    <i class="ti ti-upload me-2"></i>Choose Photo
                                </label>
                                <input type="file" name="profile_photo" id="profile_photo" 
                                       class="d-none" accept="image/*">
                                @if($user->employee && $user->employee->profile_photo)
                                    <a href="{{ route('profile.remove-photo') }}" 
                                       class="btn btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to remove your profile photo?')">
                                        <i class="ti ti-trash me-2"></i>Remove
                                    </a>
                                @endif
                            </div>
                            @error('profile_photo')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        @if($user->employee)
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $user->employee->phone) }}">
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee ID</label>
                            <input type="text" class="form-control" 
                                   value="{{ $user->employee->employee_id }}" readonly disabled>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" 
                                   value="{{ $user->employee->department->name ?? 'N/A' }}" readonly disabled>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position</label>
                            <input type="text" class="form-control" 
                                   value="{{ $user->employee->position ?? 'N/A' }}" readonly disabled>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" 
                                      rows="3">{{ old('address', $user->employee->address) }}</textarea>
                            @error('address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        @endif
                    </div>
                    
                    <div class="text-end">
                        <a href="{{ route('profile.show') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Danger Zone -->
        <div class="card mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0 text-white">Danger Zone</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Delete Account</h6>
                        <p class="text-muted mb-0">Once you delete your account, there is no going back.</p>
                    </div>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="ti ti-trash me-2"></i>Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="text-danger">Warning: This action cannot be undone!</p>
                    <p>Please enter your password to confirm account deletion.</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete My Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Preview profile photo before upload
    document.getElementById('profile_photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('profile-preview');
                if (preview) {
                    preview.src = e.target.result;
                } else {
                    const container = document.getElementById('profile-preview-container');
                    container.innerHTML = `<img src="${e.target.result}" 
                                               id="profile-preview"
                                               alt="Profile Photo" 
                                               class="img-fluid rounded-circle" 
                                               style="width: 150px; height: 150px; object-fit: cover;">`;
                }
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush