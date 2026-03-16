@extends('layouts.master')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">QR Attendance</li>
</ul>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">QR Code Attendance Scanner</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Scanner Column -->
                        <div class="col-md-7">
                            <div class="text-center">
                                <h5 class="mb-3">Scan QR Code</h5>
                                
                                <!-- Scanner Container -->
                                <div id="scanner-container" class="border rounded p-2 bg-light">
                                    <div id="qr-reader" style="width: 100%;"></div>
                                </div>

                                <!-- Status Messages -->
                                <div id="status-messages" class="mt-3"></div>

                                <!-- Loading Indicator -->
                                <div id="loading-indicator" class="mt-3" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Processing...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Processing your attendance...</p>
                                </div>

                                <!-- Success Animation -->
                                <div id="success-animation" class="mt-3" style="display: none;">
                                    <div class="success-checkmark">
                                        <div class="check-icon">
                                            <span class="icon-line line-tip"></span>
                                            <span class="icon-line line-long"></span>
                                            <div class="icon-circle"></div>
                                            <div class="icon-fix"></div>
                                        </div>
                                    </div>
                                    <h5 class="text-success mt-2" id="success-message"></h5>
                                    <p class="text-muted">Redirecting to dashboard <span id="countdown">2</span>s</p>
                                </div>

                                <!-- Control Buttons -->
                                <div class="mt-4">
                                    <button id="start-scanner" class="btn btn-primary">
                                        <i class="ti ti-camera"></i> Start Scanner
                                    </button>
                                    <button id="stop-scanner" class="btn btn-secondary" style="display: none;">
                                        <i class="ti ti-camera-off"></i> Stop Scanner
                                    </button>
                                    <button id="scan-again" class="btn btn-info" style="display: none;">
                                        <i class="ti ti-refresh"></i> Scan Again
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions Column -->
                        <div class="col-md-5">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="ti ti-info-circle text-primary"></i> 
                                        Quick Instructions
                                    </h5>
                                    
                                    <div class="instruction-steps mt-4">
                                        <div class="step-item d-flex mb-4">
                                            <div class="step-number bg-primary text-white rounded-circle me-3">1</div>
                                            <div>
                                                <h6 class="mb-1">Position QR Code</h6>
                                                <p class="text-muted small mb-0">Hold your camera steady and point it at the QR code</p>
                                            </div>
                                        </div>

                                        <div class="step-item d-flex mb-4">
                                            <div class="step-number bg-primary text-white rounded-circle me-3">2</div>
                                            <div>
                                                <h6 class="mb-1">Wait for Scan</h6>
                                                <p class="text-muted small mb-0">The scanner will automatically detect and process the QR code</p>
                                            </div>
                                        </div>

                                        <div class="step-item d-flex mb-4">
                                            <div class="step-number bg-primary text-white rounded-circle me-3">3</div>
                                            <div>
                                                <h6 class="mb-1">Check In/Out</h6>
                                                <p class="text-muted small mb-0">First scan = Check In, Second scan = Check Out</p>
                                            </div>
                                        </div>

                                        <div class="step-item d-flex mb-4">
                                            <div class="step-number bg-primary text-white rounded-circle me-3">4</div>
                                            <div>
                                                <h6 class="mb-1">Auto Redirect</h6>
                                                <p class="text-muted small mb-0">You'll be automatically redirected to dashboard after success</p>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Tips -->
                                    <h6 class="mb-3">
                                        <i class="ti ti-tips text-warning"></i> 
                                        Tips for Best Results
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="ti ti-check text-success me-2"></i>
                                            Ensure good lighting on the QR code
                                        </li>
                                        <li class="mb-2">
                                            <i class="ti ti-check text-success me-2"></i>
                                            Hold your phone 6-8 inches away
                                        </li>
                                        <li class="mb-2">
                                            <i class="ti ti-check text-success me-2"></i>
                                            Avoid glare and reflections
                                        </li>
                                        <li class="mb-2">
                                            <i class="ti ti-check text-success me-2"></i>
                                            Make sure the entire QR code is visible
                                        </li>
                                    </ul>
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

@push('styles')
<style>
/* Success Animation */
.success-checkmark {
    width: 80px;
    height: 80px;
    margin: 0 auto;
}

.check-icon {
    width: 80px;
    height: 80px;
    position: relative;
    border-radius: 50%;
    box-sizing: content-box;
    border: 4px solid #4CAF50;
}

.check-icon::before {
    top: 3px;
    left: -2px;
    width: 30px;
    transform-origin: 100% 50%;
    border-radius: 100px 0 0 100px;
}

.check-icon::after {
    top: 0;
    left: 30px;
    width: 60px;
    transform-origin: 0 50%;
    border-radius: 0 100px 100px 0;
    animation: rotate-circle 4.25s ease-in;
}

.check-icon .icon-line {
    height: 5px;
    background-color: #4CAF50;
    display: block;
    border-radius: 2px;
    position: absolute;
    z-index: 10;
}

.check-icon .icon-line.line-tip {
    top: 46px;
    left: 14px;
    width: 25px;
    transform: rotate(45deg);
    animation: icon-line-tip 0.75s;
}

.check-icon .icon-line.line-long {
    top: 38px;
    right: 8px;
    width: 47px;
    transform: rotate(-45deg);
    animation: icon-line-long 0.75s;
}

.check-icon .icon-circle {
    top: -4px;
    left: -4px;
    z-index: 10;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    position: absolute;
    box-sizing: content-box;
    border: 4px solid rgba(76, 175, 80, 0.5);
}

.check-icon .icon-fix {
    top: 8px;
    width: 5px;
    left: 26px;
    z-index: 1;
    height: 85px;
    position: absolute;
    transform: rotate(-45deg);
    background-color: #fff;
}

@keyframes rotate-circle {
    0% { transform: rotate(-45deg); }
    5% { transform: rotate(-45deg); }
    12% { transform: rotate(-405deg); }
    100% { transform: rotate(-405deg); }
}

@keyframes icon-line-tip {
    0% { width: 0; left: 1px; top: 19px; }
    54% { width: 0; left: 1px; top: 19px; }
    70% { width: 50px; left: -8px; top: 37px; }
    84% { width: 17px; left: 21px; top: 48px; }
    100% { width: 25px; left: 14px; top: 46px; }
}

@keyframes icon-line-long {
    0% { width: 0; right: 46px; top: 54px; }
    65% { width: 0; right: 46px; top: 54px; }
    84% { width: 55px; right: 0px; top: 35px; }
    100% { width: 47px; right: 8px; top: 38px; }
}

/* Step Numbers */
.step-number {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

/* Scanner Container */
#qr-reader {
    border: none !important;
    border-radius: 8px;
    overflow: hidden;
}

#qr-reader video {
    border-radius: 8px;
}

/* Alerts Animation */
.alert {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateY(-10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Loading Spinner */
.spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const scanner = {
        instance: null,
        isScanning: false,
        isProcessing: false
    };
    
    const elements = {
        startBtn: document.getElementById('start-scanner'),
        stopBtn: document.getElementById('stop-scanner'),
        scanAgainBtn: document.getElementById('scan-again'),
        statusMessages: document.getElementById('status-messages'),
        loadingIndicator: document.getElementById('loading-indicator'),
        successAnimation: document.getElementById('success-animation'),
        successMessage: document.getElementById('success-message'),
        countdown: document.getElementById('countdown')
    };

    let countdownInterval = null;

    // Helper Functions
    function showMessage(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 
                          'alert-info';
        
        elements.statusMessages.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="ti ti-${type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }

    function hideAllStates() {
        elements.statusMessages.innerHTML = '';
        elements.loadingIndicator.style.display = 'none';
        elements.successAnimation.style.display = 'none';
    }

    function showLoading() {
        hideAllStates();
        elements.loadingIndicator.style.display = 'block';
    }

    function showSuccess(message) {
        hideAllStates();
        elements.successMessage.textContent = message;
        elements.successAnimation.style.display = 'block';
        
        let counter = 2;
        elements.countdown.textContent = counter;
        
        countdownInterval = setInterval(() => {
            counter--;
            elements.countdown.textContent = counter;
            
            if (counter === 0) {
                clearInterval(countdownInterval);
                window.location.href = '{{ route("dashboard") }}';
            }
        }, 1000);
    }

    function resetScanner() {
        if (scanner.instance) {
            try {
                scanner.instance.clear();
            } catch (e) {
                console.log('Error clearing scanner:', e);
            }
            scanner.instance = null;
        }
        
        scanner.isScanning = false;
        scanner.isProcessing = false;
        
        elements.startBtn.style.display = 'inline-block';
        elements.stopBtn.style.display = 'none';
        elements.scanAgainBtn.style.display = 'none';
        
        hideAllStates();
    }

    // QR Code Handlers
    function onScanSuccess(decodedText) {
        // Prevent multiple scans while processing
        if (scanner.isProcessing) {
            console.log('Already processing a scan, ignoring...');
            return;
        }

        scanner.isProcessing = true;
        
        // Pause scanner
        if (scanner.instance) {
            scanner.instance.pause();
        }

        // Show loading
        showLoading();

        // Send to server
        fetch('{{ route("attendance.qr-checkin") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                qr_data: decodedText,
                device_time: new Date().toISOString()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                elements.startBtn.style.display = 'none';
                elements.stopBtn.style.display = 'none';
                elements.scanAgainBtn.style.display = 'none';
            } else {
                showMessage(data.message || 'Failed to process QR code', 'error');
                
                // Show scan again button
                elements.scanAgainBtn.style.display = 'inline-block';
                
                // Resume scanner
                if (scanner.instance) {
                    scanner.instance.resume();
                }
                scanner.isProcessing = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Network error. Please try again.', 'error');
            
            // Show scan again button
            elements.scanAgainBtn.style.display = 'inline-block';
            
            // Resume scanner
            if (scanner.instance) {
                scanner.instance.resume();
            }
            scanner.isProcessing = false;
        });
    }

    function onScanError(error) {
        // Only log errors, don't show to user
        console.log('Scan error:', error);
    }

    // Event Listeners
    elements.startBtn.addEventListener('click', function() {
        // Initialize scanner
        scanner.instance = new Html5QrcodeScanner(
            "qr-reader",
            { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                rememberLastUsedCamera: true,
                showTorchButtonIfSupported: true
            },
            false
        );

        scanner.instance.render(onScanSuccess, onScanError);
        
        scanner.isScanning = true;
        
        elements.startBtn.style.display = 'none';
        elements.stopBtn.style.display = 'inline-block';
        elements.scanAgainBtn.style.display = 'none';
        
        hideAllStates();
        showMessage('Scanner activated. Point your camera at the QR code.', 'info');
    });

    elements.stopBtn.addEventListener('click', function() {
        resetScanner();
        showMessage('Scanner stopped. Click "Start Scanner" to begin again.', 'info');
    });

    elements.scanAgainBtn.addEventListener('click', function() {
        if (scanner.instance && scanner.isScanning) {
            scanner.instance.resume();
            elements.scanAgainBtn.style.display = 'none';
            scanner.isProcessing = false;
            hideAllStates();
            showMessage('Scanner resumed. Point your camera at the QR code.', 'info');
        }
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
        if (scanner.instance) {
            try {
                scanner.instance.clear();
            } catch (e) {
                console.log('Cleanup error:', e);
            }
        }
    });
});
</script>
@endpush