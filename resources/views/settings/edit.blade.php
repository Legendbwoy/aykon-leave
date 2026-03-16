@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Edit Setting</h1>
    <form action="{{ route('settings.update', $setting) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="key" class="form-label">Key</label>
            <input type="text" class="form-control" id="key" name="key" value="{{ $setting->key }}" required readonly>
        </div>
        <div class="mb-3">
            <label for="value" class="form-label">Value</label>
            <input type="text" class="form-control" id="value" name="value" value="{{ $setting->value }}" required>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('settings.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
