@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">QR Scanner Debug Mode</h4>
                </div>
                <div class="card-body">
                    <!-- Debug Info Panel -->
                    <div class="alert alert-info">
                        <h5>Debug Information</h5>
                        <pre id="debug-info" style="background: #f4f4f4; padding: 10px; border-radius: 5px;">Loading...</pre>
                    </div>

                    <!-- Test Buttons -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5>Test 1: Connectivity Test</h5>
                                    <button id="test-get" class="btn btn-primary">Test GET Request</button>
                                    <button id="test-post" class="btn btn-success">Test POST Request</button>
                                    <div id="test-result" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5>Test 2: Manual QR Simulation</h5>
                                    <input type="text" id="test-qr-data" class="form-control mb-2" placeholder="Enter test QR data" value="test-qr-123">
                                    <button id="simulate-scan" class="btn btn-warning">Simulate QR Scan</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Scanner -->
                    <div class="row">
                        <div class="col-md-8 mx-auto">
                            <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
                            
                            <div id="status" class="mt-3">
                                <div id="buffering" style="display: none;">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary"></div>
                                        <p>Processing...</p>
                                    </div>
                                </div>
                                <div id="result" class="alert" style="display: none;"></div>
                            </div>

                            <div class="text-center mt-3">
                                <button id="start-scan" class="btn btn-primary">Start Scanner</button>
                                <button id="stop-scan" class="btn btn-secondary" style="display: none;">Stop Scanner</button>
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
    const debugInfo = document.getElementById('debug-info');
    const testGet = document.getElementById('test-get');
    const testPost = document.getElementById('test-post');
    const simulateScan = document.getElementById('simulate-scan');
    const testResult = document.getElementById('test-result');
    const startBtn = document.getElementById('start-scan');
    const stopBtn = document.getElementById('stop-scan');
    const buffering = document.getElementById('buffering');
    const resultDiv = document.getElementById('result');

    let scanner = null;
    let isProcessing = false;

    // Show debug info
    function updateDebugInfo(message, data = null) {
        const timestamp = new Date().toLocaleTimeString();
        let debugText = `[${timestamp}] ${message}\n`;
        if (data) {
            debugText += JSON.stringify(data, null, 2) + '\n';
        }
        debugText += `\nCSRF Token: {{ csrf_token() }}\n`;
        debugText += `User Agent: ${navigator.userAgent}\n`;
        debugText += `URL: {{ route('attendance.qr-checkin') }}\n`;
        debugInfo.textContent = debugText;
    }

    updateDebugInfo('Debug mode initialized');

    // Test GET request
    testGet.addEventListener('click', function() {
        testResult.innerHTML = '<span class="text-info">Testing GET...</span>';
        updateDebugInfo('Testing GET request to /test-qr');
        
        fetch('/test-qr')
            .then(response => response.json())
            .then(data => {
                testResult.innerHTML = '<span class="text-success">✓ GET successful</span>';
                updateDebugInfo('GET test successful', data);
            })
            .catch(error => {
                testResult.innerHTML = '<span class="text-danger">✗ GET failed: ' + error.message + '</span>';
                updateDebugInfo('GET test failed', { error: error.message });
            });
    });

    // Test POST request
    testPost.addEventListener('click', function() {
        testResult.innerHTML = '<span class="text-info">Testing POST...</span>';
        updateDebugInfo('Testing POST request to /test-qr-post');
        
        fetch('/test-qr-post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ test: 'data', timestamp: new Date().toISOString() })
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            testResult.innerHTML = '<span class="text-success">✓ POST successful</span>';
            updateDebugInfo('POST test successful', data);
        })
        .catch(error => {
            testResult.innerHTML = '<span class="text-danger">✗ POST failed: ' + error.message + '</span>';
            updateDebugInfo('POST test failed', { error: error.message });
        });
    });

    // Simulate QR scan
    simulateScan.addEventListener('click', function() {
        const testQrData = document.getElementById('test-qr-data').value;
        updateDebugInfo('Simulating QR scan with data: ' + testQrData);
        
        // Show buffering
        buffering.style.display = 'block';
        resultDiv.style.display = 'none';
        
        // Send to server
        fetch('{{ route('attendance.qr-checkin') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                qr_data: testQrData,
                device_time: new Date().toISOString()
            })
        })
        .then(async response => {
            buffering.style.display = 'none';
            
            const contentType = response.headers.get('content-type');
            updateDebugInfo('Response received', {
                status: response.status,
                statusText: response.statusText,
                contentType: contentType
            });
            
            if (!response.ok) {
                const text = await response.text();
                throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
            }
            
            return response.json();
        })
        .then(data => {
            resultDiv.className = data.success ? 'alert alert-success' : 'alert alert-danger';
            resultDiv.textContent = data.message || JSON.stringify(data);
            resultDiv.style.display = 'block';
            updateDebugInfo('QR simulation successful', data);
        })
        .catch(error => {
            resultDiv.className = 'alert alert-danger';
            resultDiv.textContent = 'Error: ' + error.message;
            resultDiv.style.display = 'block';
            updateDebugInfo('QR simulation failed', { error: error.message });
        });
    });

    // Actual QR Scanner
    startBtn.addEventListener('click', function() {
        scanner = new Html5QrcodeScanner("qr-reader", { 
            fps: 10, 
            qrbox: { width: 250, height: 250 },
            rememberLastUsedCamera: true
        });

        scanner.render(onScanSuccess, onScanError);
        
        startBtn.style.display = 'none';
        stopBtn.style.display = 'inline-block';
        updateDebugInfo('Scanner started');
    });

    stopBtn.addEventListener('click', function() {
        if (scanner) {
            scanner.clear();
            scanner = null;
        }
        startBtn.style.display = 'inline-block';
        stopBtn.style.display = 'none';
        updateDebugInfo('Scanner stopped');
    });

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) {
            updateDebugInfo('Scan ignored - already processing');
            return;
        }

        isProcessing = true;
        updateDebugInfo('QR code scanned', { text: decodedText, result: decodedResult });
        
        // Show buffering
        buffering.style.display = 'block';
        resultDiv.style.display = 'none';

        // Stop scanner
        if (scanner) {
            scanner.pause();
        }

        // Add timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => {
            controller.abort();
            buffering.style.display = 'none';
            resultDiv.className = 'alert alert-danger';
            resultDiv.textContent = 'Request timed out after 15 seconds';
            resultDiv.style.display = 'block';
            updateDebugInfo('Request timed out');
            isProcessing = false;
        }, 15000);

        // Send to server
        fetch('{{ route('attendance.qr-checkin') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                qr_data: decodedText,
                device_time: new Date().toISOString()
            }),
            signal: controller.signal
        })
        .then(async response => {
            clearTimeout(timeoutId);
            buffering.style.display = 'none';
            
            updateDebugInfo('Response received', {
                status: response.status,
                statusText: response.statusText,
                headers: Object.fromEntries(response.headers)
            });
            
            if (!response.ok) {
                const text = await response.text();
                throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
            }
            
            return response.json();
        })
        .then(data => {
            resultDiv.className = data.success ? 'alert alert-success' : 'alert alert-danger';
            resultDiv.textContent = data.message || JSON.stringify(data);
            resultDiv.style.display = 'block';
            updateDebugInfo('Scan successful', data);
            
            if (data.success) {
                setTimeout(() => {
                    window.location.href = data.redirect_url || '{{ route('dashboard') }}';
                }, 2000);
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            buffering.style.display = 'none';
            
            if (error.name === 'AbortError') {
                resultDiv.textContent = 'Request timed out. Please try again.';
            } else {
                resultDiv.textContent = 'Error: ' + error.message;
            }
            resultDiv.className = 'alert alert-danger';
            resultDiv.style.display = 'block';
            updateDebugInfo('Scan error', { error: error.message });
        })
        .finally(() => {
            isProcessing = false;
        });
    }

    function onScanError(error) {
        updateDebugInfo('Scan error', { error: error });
    }
});
</script>
@endpush