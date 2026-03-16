@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">QR Attendance Scanner</h4>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <!-- Simple Scanner -->
                        <div id="scanner-container" style="max-width: 500px; margin: 0 auto;">
                            <div id="qr-reader" style="width: 100%;"></div>
                        </div>

                        <!-- Status Messages -->
                        <div id="status" class="mt-3"></div>
                        <div id="loading" class="mt-3" style="display: none;">
                            <div class="spinner-border text-primary"></div>
                            <p>Processing...</p>
                        </div>

                        <button id="start-btn" class="btn btn-primary mt-3">Start Scanner</button>
                        <button id="stop-btn" class="btn btn-secondary mt-3" style="display: none;">Stop Scanner</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let scanner = null;
    const startBtn = document.getElementById('start-btn');
    const stopBtn = document.getElementById('stop-btn');
    const status = document.getElementById('status');
    const loading = document.getElementById('loading');

    function showMessage(msg, isError = false) {
        status.innerHTML = `<div class="alert alert-${isError ? 'danger' : 'success'}">${msg}</div>`;
    }

    startBtn.addEventListener('click', function() {
        scanner = new Html5QrcodeScanner("qr-reader", { 
            fps: 10, 
            qrbox: 250 
        });
        
        scanner.render(onScanSuccess, onScanError);
        
        startBtn.style.display = 'none';
        stopBtn.style.display = 'inline-block';
    });

    stopBtn.addEventListener('click', function() {
        if (scanner) {
            scanner.clear();
            scanner = null;
        }
        startBtn.style.display = 'inline-block';
        stopBtn.style.display = 'none';
    });

    function onScanSuccess(decodedText) {
        if (scanner) {
            scanner.pause();
        }
        
        loading.style.display = 'block';
        status.innerHTML = '';

        fetch('{{ route("simple.qr.process") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ qr_data: decodedText })
        })
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            
            if (data.success) {
                showMessage(data.message);
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                showMessage(data.message, true);
                if (scanner) {
                    scanner.resume();
                }
            }
        })
        .catch(error => {
            loading.style.display = 'none';
            showMessage('Error: ' + error.message, true);
            if (scanner) {
                scanner.resume();
            }
        });
    }

    function onScanError(error) {
        console.warn('Scan error:', error);
    }
});
</script>
@endsection