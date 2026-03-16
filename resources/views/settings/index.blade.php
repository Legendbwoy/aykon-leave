@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Settings</h1>
    <a href="{{ route('settings.edit', $settings->first()) }}" class="btn btn-primary mb-3">Edit Settings</a>
    <div class="table-responsive rounded shadow-sm">
        <table class="table table-hover align-middle mb-0 bg-white rounded">
            <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Key</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($settings as $setting)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $setting->key }}</td>
                    <td>{{ $setting->value }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-4">
                        <i class="ti ti-inbox f-30 text-muted"></i>
                        <p class="mb-0">No settings found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if (method_exists($settings, 'links'))
        <div class="d-flex justify-content-center mt-3">
            {{ $settings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
