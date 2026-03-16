@extends('layouts.master')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">QR Code Attendance</li>
</ul>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">QR Code Attendance</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center">
                                <h5>Scan QR Code</h5>
                                <p>Point your camera at the company QR code to mark attendance.</p>

                                <div id="qr-reader" style="width: 100%; max-width: 400px; margin: 0 auto;"></div>

                                <div id="qr-reader-results" class="mt-3">
                                    <div id="result" class="alert alert-info" style="display: none;">
                                        <strong>Scanned:</strong> <span id="result-text"></span>
                                    </div>
                                    <div id="error" class="alert alert-danger" style="display: none;">
                                        <strong>Error:</strong> <span id="error-text"></span>
                                    </div>
                                    <div id="time-mismatch" class="alert alert-warning" style="display: none;">
                                        <strong>Time Mismatch:</strong> <span id="time-mismatch-text"></span>
                                        <br>
                                        <small>Please update your device time and try again.</small>
                                    </div>
                                </div>

                                <button id="start-scan" class="btn btn-primary mt-3">Start Scanning</button>
                                <button id="stop-scan" class="btn btn-secondary mt-3" style="display: none;">Stop Scanning</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h5>Instructions</h5>
                                <ul class="list-unstyled text-left">
                                    <li class="mb-2"><i class="ti ti-check text-success"></i> Ensure your device time is accurate</li>
                                    <li class="mb-2"><i class="ti ti-check text-success"></i> Point camera at the company QR code</li>
                                    <li class="mb-2"><i class="ti ti-check text-success"></i> Wait for successful scan confirmation</li>
                                    <li class="mb-2"><i class="ti ti-check text-success"></i> Attendance will be marked automatically</li>
                                </ul>

                                <div class="mt-4">
                                    <h6>Device Time Check</h6>
                                    <p class="text-muted small">Current device time: <span id="device-time"></span></p>
                                    <p class="text-muted small">Server time: <span id="server-time"></span></p>
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

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let html5QrcodeScanner = null;
    const startBtn = document.getElementById('start-scan');
    const stopBtn = document.getElementById('stop-scan');
    const resultDiv = document.getElementById('result');
    const errorDiv = document.getElementById('error');
    const timeMismatchDiv = document.getElementById('time-mismatch');
    const deviceTimeSpan = document.getElementById('device-time');
    const serverTimeSpan = document.getElementById('server-time');

    // Update device time display
    function updateDeviceTime() {
        const now = new Date();
        deviceTimeSpan.textContent = now.toLocaleString();
    }

    // Fetch server time
    function fetchServerTime() {
        fetch('{{ url('/api/server-time') }}')
            .then(response => response.json())
            .then(data => {
                serverTimeSpan.textContent = data.server_time;
            })
            .catch(error => {
                console.error('Error fetching server time:', error);
            });
    }

    updateDeviceTime();
    fetchServerTime();
    setInterval(updateDeviceTime, 1000);

    function showResult(message, type = 'success') {
        resultDiv.style.display = 'none';
        errorDiv.style.display = 'none';
        timeMismatchDiv.style.display = 'none';

        if (type === 'success') {
            resultDiv.style.display = 'block';
            document.getElementById('result-text').textContent = message;
        } else if (type === 'error') {
            errorDiv.style.display = 'block';
            document.getElementById('error-text').textContent = message;
        } else if (type === 'time-mismatch') {
            timeMismatchDiv.style.display = 'block';
            document.getElementById('time-mismatch-text').textContent = message;
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        console.log(`Code scanned: ${decodedText}`, decodedResult);

        // Stop scanning
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                startBtn.style.display = 'block';
                stopBtn.style.display = 'none';
            });
        }

        // Send to server with device time
        const deviceTime = new Date().toISOString();

        fetch('{{ route('attendance.qr-checkin') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                qr_data: decodedText,
                device_time: deviceTime
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showResult(`${data.message} at ${data.time}`, 'success');
            } else if (data.time_mismatch) {
                showResult(`Time mismatch detected. Server time: ${data.server_time}, Device time: ${data.device_time}`, 'time-mismatch');
            } else {
                showResult(data.message || 'Unknown error', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showResult('Network error occurred', 'error');
        });
    }

    function onScanError(errorMessage) {
        // Ignore scan errors, only show on successful scan
        console.log(`Scan error: ${errorMessage}`);
    }

    startBtn.addEventListener('click', function() {
        html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader",
            { fps: 10, qrbox: { width: 250, height: 250 } },
            false
        );

        html5QrcodeScanner.render(onScanSuccess, onScanError);

        startBtn.style.display = 'none';
        stopBtn.style.display = 'block';
    });

    stopBtn.addEventListener('click', function() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                startBtn.style.display = 'block';
                stopBtn.style.display = 'none';
            });
        }
    });
});
</script>
@endpush