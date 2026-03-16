@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Settings</h1>
    <a href="{{ route('settings.edit', $settings->first()) }}" class="btn btn-primary mb-3">Edit Settings</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Key</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($settings as $setting)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $setting->key }}</td>
                    <td>{{ $setting->value }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if (method_exists($settings, 'links'))
    <div class="d-flex justify-content-center">
        {{ $settings->links() }}
    </div>
    @endif
</div>
@endsection
